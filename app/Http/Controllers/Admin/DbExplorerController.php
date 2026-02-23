<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DbExplorerController extends Controller
{
    public function index()
    {
        $tables = $this->getTables();

        return view('admin.db.index', [
            'tables' => $tables,
        ]);
    }

    public function table(Request $request, string $table)
    {
        $tables = $this->getTables();
        $this->ensureTableAllowed($table, $tables);

        $columns = $this->getColumns($table);
        $primaryKey = $this->getPrimaryKey($columns);
        $perPage = (int) config('admin.db_page_size', 25);

        $query = DB::table($table);

        $searchColumn = $request->string('column')->toString();
        $searchValue = $request->string('q')->toString();

        if ($searchColumn && $searchValue && $this->columnExists($columns, $searchColumn)) {
            $query->where($searchColumn, 'like', '%' . $searchValue . '%');
        }

        if ($primaryKey) {
            $query->orderBy($primaryKey, 'desc');
        }

        $rows = $query->paginate($perPage)->withQueryString();

        return view('admin.db.table', [
            'tables' => $tables,
            'table' => $table,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
            'rows' => $rows,
            'searchColumn' => $searchColumn,
            'searchValue' => $searchValue,
            'canEdit' => (bool) config('admin.db_edit_enabled', false),
        ]);
    }

    public function show(string $table, string $recordId)
    {
        $tables = $this->getTables();
        $this->ensureTableAllowed($table, $tables);

        $columns = $this->getColumns($table);
        $primaryKey = $this->getPrimaryKey($columns);

        if (! $primaryKey) {
            abort(404);
        }

        $record = DB::table($table)->where($primaryKey, $recordId)->first();

        if (! $record) {
            abort(404);
        }

        return view('admin.db.show', [
            'tables' => $tables,
            'table' => $table,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
            'record' => $record,
            'canEdit' => (bool) config('admin.db_edit_enabled', false),
        ]);
    }

    public function edit(string $table, string $recordId)
    {
        if (! config('admin.db_edit_enabled', false)) {
            abort(403);
        }

        $tables = $this->getTables();
        $this->ensureTableAllowed($table, $tables);

        $columns = $this->getColumns($table);
        $primaryKey = $this->getPrimaryKey($columns);

        if (! $primaryKey) {
            abort(404);
        }

        $record = DB::table($table)->where($primaryKey, $recordId)->first();

        if (! $record) {
            abort(404);
        }

        return view('admin.db.edit', [
            'tables' => $tables,
            'table' => $table,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
            'record' => $record,
        ]);
    }

    public function update(Request $request, string $table, string $recordId)
    {
        if (! config('admin.db_edit_enabled', false)) {
            abort(403);
        }

        $request->validate([
            'confirm_update' => ['accepted'],
        ]);

        $tables = $this->getTables();
        $this->ensureTableAllowed($table, $tables);

        $columns = $this->getColumns($table);
        $primaryKey = $this->getPrimaryKey($columns);

        if (! $primaryKey) {
            abort(404);
        }

        $record = DB::table($table)->where($primaryKey, $recordId)->first();

        if (! $record) {
            abort(404);
        }

        $payload = $this->buildPayload($request, $columns, $primaryKey);

        if (empty($payload)) {
            return redirect()->route('admin.db.show', [$table, $recordId])
                ->with('status', __('Tidak ada perubahan yang disimpan.'));
        }

        DB::table($table)->where($primaryKey, $recordId)->update($payload);

        AuditLog::create([
            'admin_id' => $request->user('admin')->id,
            'action' => 'db_update',
            'table_name' => $table,
            'record_id' => $recordId,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->route('admin.db.show', [$table, $recordId])
            ->with('status', __('Perubahan berhasil disimpan.'));
    }

    private function getTables(): array
    {
        $tablesRaw = DB::select('SHOW TABLES');

        if (empty($tablesRaw)) {
            return [];
        }

        $firstRow = (array) $tablesRaw[0];
        $key = array_key_first($firstRow);

        return collect($tablesRaw)
            ->map(fn ($row) => (array) $row)
            ->map(fn ($row) => $row[$key] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function getColumns(string $table): array
    {
        $columnsRaw = DB::select('SHOW COLUMNS FROM `'.$table.'`');

        return collect($columnsRaw)
            ->map(fn ($row) => (array) $row)
            ->values()
            ->all();
    }

    private function getPrimaryKey(array $columns): ?string
    {
        foreach ($columns as $column) {
            if (($column['Key'] ?? '') === 'PRI') {
                return $column['Field'] ?? null;
            }
        }

        return null;
    }

    private function ensureTableAllowed(string $table, array $tables): void
    {
        if (! in_array($table, $tables, true)) {
            abort(404);
        }
    }

    private function columnExists(array $columns, string $name): bool
    {
        foreach ($columns as $column) {
            if (($column['Field'] ?? '') === $name) {
                return true;
            }
        }

        return false;
    }

    private function buildPayload(Request $request, array $columns, ?string $primaryKey): array
    {
        $payload = [];

        foreach ($columns as $column) {
            $field = $column['Field'] ?? null;
            if (! $field || $field === $primaryKey) {
                continue;
            }

            if (! $request->has($field)) {
                continue;
            }

            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $value = $request->input($field);
            $nullable = ($column['Null'] ?? '') === 'YES';

            if ($value === '' && $nullable) {
                $payload[$field] = null;
                continue;
            }

            $type = strtolower((string) ($column['Type'] ?? ''));

            if (str_contains($type, 'int')) {
                if ($value === '' && $nullable) {
                    $payload[$field] = null;
                    continue;
                }

                if (! is_numeric($value)) {
                    abort(422, __('Kolom :field harus berupa angka.', ['field' => $field]));
                }

                $payload[$field] = (int) $value;
                continue;
            }

            if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                if (! is_numeric($value)) {
                    abort(422, __('Kolom :field harus berupa angka.', ['field' => $field]));
                }

                $payload[$field] = (float) $value;
                continue;
            }

            if (str_contains($type, 'json')) {
                if ($value === '' && $nullable) {
                    $payload[$field] = null;
                    continue;
                }

                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    abort(422, __('Kolom :field harus berformat JSON valid.', ['field' => $field]));
                }

                $payload[$field] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                continue;
            }

            if (str_contains($type, 'date') || str_contains($type, 'time')) {
                if ($value === '' && $nullable) {
                    $payload[$field] = null;
                    continue;
                }

                try {
                    if (str_contains($type, 'date') && ! str_contains($type, 'datetime') && ! str_contains($type, 'timestamp')) {
                        $payload[$field] = Carbon::parse($value)->toDateString();
                    } else {
                        $payload[$field] = Carbon::parse($value)->toDateTimeString();
                    }
                } catch (\Throwable $e) {
                    abort(422, __('Kolom :field harus berupa tanggal yang valid.', ['field' => $field]));
                }

                continue;
            }

            if (str_contains($field, 'password') && $value !== '') {
                $payload[$field] = Hash::make($value);
                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }
}
