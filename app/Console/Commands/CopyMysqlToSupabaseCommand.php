<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CopyMysqlToSupabaseCommand extends Command
{
    protected $signature = 'db:copy-mysql-to-supabase
        {--source=mysql : Nama koneksi source MySQL}
        {--target=supabase : Nama koneksi target PostgreSQL/Supabase}
        {--chunk=500 : Jumlah row per batch insert}
        {--migrate-target : Jalankan migrate --database=<target> sebelum copy}
        {--append : Jangan truncate data target sebelum copy}
        {--dry-run : Tampilkan rencana tanpa menulis data}';

    protected $description = 'Copy seluruh tabel dari MySQL ke Supabase PostgreSQL';

    public function handle(): int
    {
        $sourceName = (string) $this->option('source');
        $targetName = (string) $this->option('target');
        $chunkSize = (int) $this->option('chunk');
        $append = (bool) $this->option('append');
        $dryRun = (bool) $this->option('dry-run');
        $migrateTarget = (bool) $this->option('migrate-target');

        if ($chunkSize < 1) {
            $this->error('Nilai --chunk harus >= 1.');

            return self::FAILURE;
        }

        $sourceConfig = config("database.connections.{$sourceName}");
        $targetConfig = config("database.connections.{$targetName}");

        if (! is_array($sourceConfig)) {
            $this->error("Koneksi source '{$sourceName}' tidak ditemukan di config/database.php.");

            return self::FAILURE;
        }

        if (! is_array($targetConfig)) {
            $this->error("Koneksi target '{$targetName}' tidak ditemukan di config/database.php.");

            return self::FAILURE;
        }

        $sourceDriver = (string) ($sourceConfig['driver'] ?? '');
        $targetDriver = (string) ($targetConfig['driver'] ?? '');

        if (! in_array($sourceDriver, ['mysql', 'mariadb'], true)) {
            $this->error("Driver source harus mysql/mariadb, saat ini: '{$sourceDriver}'.");

            return self::FAILURE;
        }

        if ($targetDriver !== 'pgsql') {
            $this->error("Driver target harus pgsql, saat ini: '{$targetDriver}'.");

            return self::FAILURE;
        }

        $sourceDatabase = (string) ($sourceConfig['database'] ?? '');
        if ($sourceDatabase === '') {
            $this->error("Database source untuk koneksi '{$sourceName}' belum terisi.");

            return self::FAILURE;
        }

        $source = DB::connection($sourceName);
        $target = DB::connection($targetName);

        try {
            $source->getPdo();
        } catch (\Throwable $exception) {
            $this->error("Gagal konek ke source '{$sourceName}': {$exception->getMessage()}");

            return self::FAILURE;
        }

        try {
            $target->getPdo();
        } catch (\Throwable $exception) {
            $this->error("Gagal konek ke target '{$targetName}': {$exception->getMessage()}");
            $this->line('Isi env SUPABASE_DB_* atau SUPABASE_DB_URL terlebih dahulu.');

            return self::FAILURE;
        }

        if ($migrateTarget && ! $dryRun) {
            $this->info("Menjalankan migrate pada koneksi target '{$targetName}'...");
            $exitCode = Artisan::call('migrate', [
                '--database' => $targetName,
                '--force' => true,
            ]);

            $this->line(trim(Artisan::output()));
            if ($exitCode !== self::SUCCESS) {
                $this->error('Gagal menjalankan migrasi target.');

                return self::FAILURE;
            }
        }

        $sourceTables = $this->fetchSourceTables($source, $sourceDatabase);
        if ($sourceTables === []) {
            $this->warn('Tidak ada tabel pada source.');

            return self::SUCCESS;
        }

        $orderedTables = $this->resolveTableOrder($source, $sourceDatabase, $sourceTables);
        $targetTables = $this->fetchTargetTables($target);
        $targetTableMap = array_fill_keys($targetTables, true);
        $copyTables = array_values(array_filter(
            $orderedTables,
            fn (string $table): bool => isset($targetTableMap[$table])
        ));
        $missingInTarget = array_values(array_filter(
            $sourceTables,
            fn (string $table): bool => ! isset($targetTableMap[$table])
        ));

        $this->info('Ringkasan koneksi:');
        $this->line("- source: {$sourceName} ({$sourceDriver}) / db: {$sourceDatabase}");
        $this->line("- target: {$targetName} ({$targetDriver})");
        $this->line('- tabel source: '.count($sourceTables));
        $this->line('- tabel akan dicopy: '.count($copyTables));

        if ($missingInTarget !== []) {
            $this->warn('Tabel source yang tidak ada di target (dilewati): '.implode(', ', $missingInTarget));
        }

        if ($dryRun) {
            $this->comment('Dry run aktif, tidak ada perubahan data.');

            return self::SUCCESS;
        }

        if (! $append) {
            $this->truncateTargetTables($target, $copyTables);
        }

        $totalInserted = 0;
        foreach ($copyTables as $table) {
            [$inserted, $sourceCount] = $this->copyTable(
                $source,
                $target,
                $sourceDatabase,
                $table,
                $chunkSize
            );

            $totalInserted += $inserted;
            $this->line("  {$table}: {$inserted}/{$sourceCount} row");
        }

        $this->syncTargetSequences($target, $copyTables);
        $this->info("Selesai copy data. Total row terinsert: {$totalInserted}");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function fetchSourceTables(ConnectionInterface $source, string $database): array
    {
        $rows = $source->select(
            'SELECT TABLE_NAME
             FROM information_schema.tables
             WHERE TABLE_SCHEMA = ?
               AND TABLE_TYPE = ?
             ORDER BY TABLE_NAME',
            [$database, 'BASE TABLE']
        );

        $tables = [];
        foreach ($rows as $row) {
            $tableName = (string) ($row->TABLE_NAME ?? $row->table_name ?? '');
            if ($tableName !== '') {
                $tables[] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * @param  array<int, string>  $tables
     * @return array<int, string>
     */
    private function resolveTableOrder(ConnectionInterface $source, string $database, array $tables): array
    {
        $rows = $source->select(
            'SELECT TABLE_NAME, REFERENCED_TABLE_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database]
        );

        $inDegree = array_fill_keys($tables, 0);
        $graph = [];
        foreach ($tables as $table) {
            $graph[$table] = [];
        }

        foreach ($rows as $row) {
            $child = (string) ($row->TABLE_NAME ?? $row->table_name ?? '');
            $parent = (string) ($row->REFERENCED_TABLE_NAME ?? $row->referenced_table_name ?? '');

            if (
                $child === '' ||
                $parent === '' ||
                $child === $parent ||
                ! isset($inDegree[$child]) ||
                ! isset($inDegree[$parent])
            ) {
                continue;
            }

            if (! in_array($child, $graph[$parent], true)) {
                $graph[$parent][] = $child;
                $inDegree[$child]++;
            }
        }

        $queue = [];
        foreach ($inDegree as $table => $degree) {
            if ($degree === 0) {
                $queue[] = $table;
            }
        }

        sort($queue);
        $ordered = [];

        while ($queue !== []) {
            $current = array_shift($queue);
            if ($current === null) {
                break;
            }

            $ordered[] = $current;
            $children = $graph[$current] ?? [];
            sort($children);

            foreach ($children as $child) {
                $inDegree[$child]--;
                if ($inDegree[$child] === 0) {
                    $queue[] = $child;
                }
            }

            sort($queue);
        }

        if (count($ordered) !== count($tables)) {
            $remaining = array_values(array_filter(
                $tables,
                fn (string $table): bool => ! in_array($table, $ordered, true)
            ));
            sort($remaining);
            $ordered = array_merge($ordered, $remaining);
        }

        return $ordered;
    }

    /**
     * @return array<int, string>
     */
    private function fetchTargetTables(ConnectionInterface $target): array
    {
        $rows = $target->select(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = 'public'
               AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        );

        $tables = [];
        foreach ($rows as $row) {
            $tableName = (string) ($row->table_name ?? $row->TABLE_NAME ?? '');
            if ($tableName !== '') {
                $tables[] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function truncateTargetTables(ConnectionInterface $target, array $tables): void
    {
        if ($tables === []) {
            return;
        }

        $qualifiedTables = array_map(
            fn (string $table): string => 'public.'.$this->quotePgIdentifier($table),
            $tables
        );

        $sql = 'TRUNCATE TABLE '.implode(', ', $qualifiedTables).' RESTART IDENTITY CASCADE';
        $this->info('Truncate data target...');
        $target->statement($sql);
    }

    /**
     * @return array{0:int,1:int}
     */
    private function copyTable(
        ConnectionInterface $source,
        ConnectionInterface $target,
        string $sourceDatabase,
        string $table,
        int $chunkSize
    ): array {
        $sourceColumns = $this->fetchSourceColumns($source, $sourceDatabase, $table);
        $targetColumns = $this->fetchTargetColumns($target, $table);
        $targetColumnMap = array_fill_keys($targetColumns, true);

        $columns = array_values(array_filter(
            $sourceColumns,
            fn (string $column): bool => isset($targetColumnMap[$column])
        ));

        if ($columns === []) {
            $this->warn("  {$table}: tidak ada kolom yang cocok, dilewati.");

            return [0, 0];
        }

        $sourceCount = (int) $source->table($table)->count();
        if ($sourceCount === 0) {
            return [0, 0];
        }

        $quotedColumns = implode(', ', array_map(
            fn (string $column): string => $this->quoteMysqlIdentifier($column),
            $columns
        ));
        $sql = 'SELECT '.$quotedColumns.' FROM '.$this->quoteMysqlIdentifier($table);

        $statement = $source->getPdo()->prepare($sql);
        $statement->execute();

        $batch = [];
        $inserted = 0;

        while (($row = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $preparedRow = [];
            foreach ($columns as $column) {
                $value = $row[$column] ?? null;
                if (is_string($value) && str_starts_with($value, '0000-00-00')) {
                    $value = null;
                }
                $preparedRow[$column] = $value;
            }

            $batch[] = $preparedRow;

            if (count($batch) >= $chunkSize) {
                $target->table($table)->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $target->table($table)->insert($batch);
            $inserted += count($batch);
        }

        return [$inserted, $sourceCount];
    }

    /**
     * @return array<int, string>
     */
    private function fetchSourceColumns(ConnectionInterface $source, string $database, string $table): array
    {
        $rows = $source->select(
            'SELECT COLUMN_NAME
             FROM information_schema.columns
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION',
            [$database, $table]
        );

        $columns = [];
        foreach ($rows as $row) {
            $column = (string) ($row->COLUMN_NAME ?? $row->column_name ?? '');
            if ($column !== '') {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @return array<int, string>
     */
    private function fetchTargetColumns(ConnectionInterface $target, string $table): array
    {
        $rows = $target->select(
            "SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = 'public'
               AND table_name = ?
             ORDER BY ordinal_position",
            [$table]
        );

        $columns = [];
        foreach ($rows as $row) {
            $column = (string) ($row->column_name ?? $row->COLUMN_NAME ?? '');
            if ($column !== '') {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function syncTargetSequences(ConnectionInterface $target, array $tables): void
    {
        foreach ($tables as $table) {
            $rows = $target->select(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = 'public'
                   AND table_name = ?
                   AND column_default LIKE 'nextval(%'",
                [$table]
            );

            if ($rows === []) {
                continue;
            }

            $escapedTable = str_replace('"', '""', $table);
            $qualifiedTable = 'public.'.$this->quotePgIdentifier($table);
            $tableLiteral = "public.\"{$escapedTable}\"";

            foreach ($rows as $row) {
                $column = (string) ($row->column_name ?? $row->COLUMN_NAME ?? '');
                if ($column === '') {
                    continue;
                }

                $escapedColumn = str_replace("'", "''", $column);
                $quotedColumn = $this->quotePgIdentifier($column);
                $sql = "SELECT setval(
                    pg_get_serial_sequence('{$tableLiteral}', '{$escapedColumn}'),
                    COALESCE(MAX({$quotedColumn}), 1),
                    MAX({$quotedColumn}) IS NOT NULL
                ) FROM {$qualifiedTable}";

                $target->statement($sql);
            }
        }
    }

    private function quoteMysqlIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    private function quotePgIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
