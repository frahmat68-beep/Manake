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
    private function isSensitiveField(string $field): bool
    {
        $fieldLower = strtolower($field);
        $sensitiveKeywords = [
            'password', 'token', 'signature', 'secret', 'key', 'snap', 'payload',
            'nik', 'national_id', 'identity', 'email', 'phone', 'telephone',
            'address', 'emergency', 'birth', 'maps', 'map', 'lat', 'lng'
        ];

        foreach ($sensitiveKeywords as $kw) {
            if (str_contains($fieldLower, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function maskSensitiveValue(string $field, mixed $value): mixed
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        $fieldLower = strtolower($field);
        $strValue = (string) $value;

        if (
            str_contains($fieldLower, 'password') ||
            str_contains($fieldLower, 'token') ||
            str_contains($fieldLower, 'signature') ||
            str_contains($fieldLower, 'secret') ||
            str_contains($fieldLower, 'key') ||
            str_contains($fieldLower, 'snap') ||
            str_contains($fieldLower, 'payload')
        ) {
            return '********';
        }

        if (str_contains($fieldLower, 'email')) {
            $parts = explode('@', $strValue);
            if (count($parts) === 2) {
                $name = $parts[0];
                $domain = $parts[1];
                $maskedName = strlen($name) > 1 ? $name[0] . str_repeat('*', max(1, strlen($name) - 1)) : '*';
                return $maskedName . '@' . $domain;
            }
            return '********';
        }

        if (str_contains($fieldLower, 'phone') || str_contains($fieldLower, 'telephone') || str_contains($fieldLower, 'emergency')) {
            $len = strlen($strValue);
            if ($len > 6) {
                return substr($strValue, 0, 3) . str_repeat('*', $len - 6) . substr($strValue, -3);
            }
            return str_repeat('*', $len);
        }

        if (str_contains($fieldLower, 'nik') || str_contains($fieldLower, 'national_id') || str_contains($fieldLower, 'identity')) {
            $len = strlen($strValue);
            if ($len > 4) {
                return str_repeat('*', $len - 4) . substr($strValue, -4);
            }
            return str_repeat('*', $len);
        }

        if (str_contains($fieldLower, 'address') || str_contains($fieldLower, 'maps') || str_contains($fieldLower, 'map') || str_contains($fieldLower, 'lat') || str_contains($fieldLower, 'lng')) {
            return '[masked address]';
        }

        if (str_contains($fieldLower, 'birth')) {
            return '[masked date]';
        }

        return '********';
    }

    private function canEditTable(string $table): bool
    {
        if (! config('admin.db_edit_enabled', false)) {
            return false;
        }

        $nonEditableTables = ['users', 'admins', 'payments', 'payment_webhook_events', 'audit_logs'];
        return ! in_array(strtolower($table), $nonEditableTables, true);
    }

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
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                $wrappedColumn = DB::connection()->getQueryGrammar()->wrap($searchColumn);
                $query->whereRaw("CAST({$wrappedColumn} AS TEXT) ILIKE ?", ['%' . $searchValue . '%']);
            } else {
                $query->where($searchColumn, 'like', '%' . $searchValue . '%');
            }
        }

        if ($primaryKey) {
            $query->orderBy($primaryKey, 'desc');
        }

        $rows = $query->paginate($perPage)->withQueryString();

        $rows->getCollection()->transform(function ($row) use ($columns) {
            $rowArray = (array) $row;
            foreach ($columns as $column) {
                $field = $column['Field'];
                if ($this->isSensitiveField($field) && isset($rowArray[$field])) {
                    $rowArray[$field] = $this->maskSensitiveValue($field, $rowArray[$field]);
                }
            }
            return (object) $rowArray;
        });

        return view('admin.db.table', [
            'tables' => $tables,
            'table' => $table,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
            'rows' => $rows,
            'searchColumn' => $searchColumn,
            'searchValue' => $searchValue,
            'canEdit' => $this->canEditTable($table),
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

        $recordArray = (array) $record;
        foreach ($columns as $column) {
            $field = $column['Field'];
            if ($this->isSensitiveField($field) && isset($recordArray[$field])) {
                $recordArray[$field] = $this->maskSensitiveValue($field, $recordArray[$field]);
            }
        }
        $record = (object) $recordArray;

        return view('admin.db.show', [
            'tables' => $tables,
            'table' => $table,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
            'record' => $record,
            'canEdit' => $this->canEditTable($table),
        ]);
    }

    public function edit(string $table, string $recordId)
    {
        if (! $this->canEditTable($table)) {
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

        $recordArray = (array) $record;
        foreach ($columns as $column) {
            $field = $column['Field'];
            if ($this->isSensitiveField($field) && isset($recordArray[$field])) {
                $recordArray[$field] = $this->maskSensitiveValue($field, $recordArray[$field]);
            }
        }
        $record = (object) $recordArray;

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
        if (! $this->canEditTable($table)) {
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
        $driver = DB::connection()->getDriverName();
        $allTables = [];

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablesRaw = DB::select('SHOW TABLES');

            if (! empty($tablesRaw)) {
                $firstRow = (array) $tablesRaw[0];
                $key = array_key_first($firstRow);

                $allTables = collect($tablesRaw)
                    ->map(fn ($row) => (array) $row)
                    ->map(fn ($row) => $row[$key] ?? null)
                    ->filter()
                    ->values()
                    ->all();
            }
        } elseif ($driver === 'pgsql') {
            $allTables = collect(DB::select(
                "SELECT table_name
                 FROM information_schema.tables
                 WHERE table_schema = 'public'
                   AND table_type = 'BASE TABLE'
                 ORDER BY table_name"
            ))
                ->map(fn ($row) => (array) $row)
                ->map(fn ($row) => $row['table_name'] ?? null)
                ->filter()
                ->values()
                ->all();
        } elseif ($driver === 'sqlite') {
            $allTables = collect(DB::select(
                "SELECT name
                 FROM sqlite_master
                 WHERE type = 'table'
                   AND name NOT LIKE 'sqlite_%'
                 ORDER BY name"
            ))
                ->map(fn ($row) => (array) $row)
                ->map(fn ($row) => $row['name'] ?? null)
                ->filter()
                ->values()
                ->all();
        } else {
            $allTables = DB::connection()->getSchemaBuilder()->getTableListing();
        }

        $blacklist = ['password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs'];

        return collect($allTables)
            ->filter(fn ($t) => ! in_array(strtolower($t), $blacklist, true))
            ->values()
            ->all();
    }

    private function getColumns(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $columnsRaw = DB::select('SHOW COLUMNS FROM `'.$table.'`');

            return collect($columnsRaw)
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all();
        }

        if ($driver === 'pgsql') {
            $columnsRaw = DB::select(
                "SELECT
                    c.column_name,
                    c.data_type,
                    c.udt_name,
                    c.is_nullable,
                    c.column_default,
                    CASE WHEN tc.constraint_type = 'PRIMARY KEY' THEN 'PRI' ELSE '' END AS key_type
                 FROM information_schema.columns c
                 LEFT JOIN information_schema.key_column_usage kcu
                    ON c.table_schema = kcu.table_schema
                   AND c.table_name = kcu.table_name
                   AND c.column_name = kcu.column_name
                 LEFT JOIN information_schema.table_constraints tc
                    ON kcu.constraint_name = tc.constraint_name
                   AND kcu.table_schema = tc.table_schema
                   AND kcu.table_name = tc.table_name
                 WHERE c.table_schema = 'public'
                   AND c.table_name = ?
                 ORDER BY c.ordinal_position",
                [$table]
            );

            return collect($columnsRaw)
                ->map(fn ($row) => (array) $row)
                ->map(function (array $row): array {
                    $type = (string) ($row['data_type'] ?? '');
                    $udtName = (string) ($row['udt_name'] ?? '');
                    $normalizedType = $udtName !== '' ? $udtName : $type;

                    return [
                        'Field' => (string) ($row['column_name'] ?? ''),
                        'Type' => $normalizedType,
                        'Null' => ((string) ($row['is_nullable'] ?? 'NO')) === 'YES' ? 'YES' : 'NO',
                        'Key' => (string) ($row['key_type'] ?? ''),
                        'Default' => $row['column_default'] ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        if ($driver === 'sqlite') {
            $tableSafe = str_replace("'", "''", $table);
            $columnsRaw = DB::select("PRAGMA table_info('{$tableSafe}')");

            return collect($columnsRaw)
                ->map(fn ($row) => (array) $row)
                ->map(function (array $row): array {
                    return [
                        'Field' => (string) ($row['name'] ?? ''),
                        'Type' => (string) ($row['type'] ?? 'text'),
                        'Null' => ((int) ($row['notnull'] ?? 0)) === 0 ? 'YES' : 'NO',
                        'Key' => ((int) ($row['pk'] ?? 0)) === 1 ? 'PRI' : '',
                        'Default' => $row['dflt_value'] ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        return [];
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

            if ($this->isSensitiveField($field)) {
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

            if (str_contains($type, 'bool')) {
                if ($value === '' && $nullable) {
                    $payload[$field] = null;
                    continue;
                }

                $payload[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($payload[$field] === null && ! $nullable) {
                    abort(422, __('Kolom :field harus berupa nilai true/false.', ['field' => $field]));
                }
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
