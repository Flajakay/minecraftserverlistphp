<?php

namespace App\Core;

use PDO;

/**
 * Database migration runner for `database/migrations/*.sql`.
 *
 * Discovers migrations by filename, executes pending `.up.sql` (and `.down.sql` if used)
 * via `PDO::exec()`, and tracks applied migrations in `schema_migrations`.
 */
class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(?PDO $pdo = null, ?string $migrationsPath = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
        $this->migrationsPath = $migrationsPath ?: dirname(__DIR__, 2) . '/database/migrations';
    }

    public function status(): array
    {
        $this->ensureMigrationsTable();

        $migrations = $this->discoverMigrations();
        $applied = $this->appliedMigrations();

        $items = [];
        $pendingCount = 0;
        $appliedCount = 0;

        foreach ($migrations as $migration) {
            $isApplied = array_key_exists($migration['name'], $applied);
            if ($isApplied) {
                $appliedCount++;
            } else {
                $pendingCount++;
            }

            $items[] = [
                'name' => $migration['name'],
                'path' => $migration['path'],
                'direction' => $migration['direction'],
                'applied' => $isApplied,
                'applied_at' => $isApplied ? ($applied[$migration['name']]->applied_at ?? null) : null
            ];
        }

        return [
            'total' => count($items),
            'applied' => $appliedCount,
            'pending' => $pendingCount,
            'items' => $items
        ];
    }

    public function runAllPending(): array
    {
        $this->ensureMigrationsTable();

        $migrations = $this->discoverMigrations();
        $applied = $this->appliedMigrations();

        $toRun = [];
        foreach ($migrations as $migration) {
            if (!array_key_exists($migration['name'], $applied)) {
                $toRun[] = $migration['name'];
            }
        }

        return $this->runSelected($toRun);
    }

    public function runSelected(array $selectedMigrationNames): array
    {
        $this->ensureMigrationsTable();

        $selected = [];
        foreach ($selectedMigrationNames as $name) {
            // Prevent path traversal: only allow selecting by basename.
            $name = basename((string)$name);
            if ($name !== '') {
                $selected[$name] = true;
            }
        }

        $migrations = $this->discoverMigrations();
        $applied = $this->appliedMigrations();

        $executed = [];
        $failed = null;

        foreach ($migrations as $migration) {
            if (!isset($selected[$migration['name']])) {
                continue;
            }

            if (array_key_exists($migration['name'], $applied)) {
                continue;
            }

            try {
                $sql = trim((string)file_get_contents($migration['path']));
                if ($sql === '') {
                    // Empty migration files are treated as no-ops but still recorded.
                    $this->markApplied($migration['name'], $migration['direction']);
                    $executed[] = ['name' => $migration['name'], 'status' => 'skipped_empty'];
                    continue;
                }

                // Migration files are executed as-is; prefer single-statement migrations.
                $this->pdo->exec($sql);
                $this->markApplied($migration['name'], $migration['direction']);
                $executed[] = ['name' => $migration['name'], 'status' => 'applied'];
            } catch (\Throwable $e) {
                $failed = [
                    'name' => $migration['name'],
                    'message' => $e->getMessage()
                ];
                break;
            }
        }

        return [
            'executed' => $executed,
            'failed' => $failed
        ];
    }

    private function discoverMigrations(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        $migrations = [];
        foreach ($files as $file) {
            $name = basename($file);
            $direction = str_contains($name, '.down.') ? 'down' : 'up';

            $migrations[] = [
                'name' => $name,
                'path' => $file,
                'direction' => $direction
            ];
        }

        return $migrations;
    }

    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS `schema_migrations` (\n" .
            "  `id` int(11) NOT NULL AUTO_INCREMENT,\n" .
            "  `migration` varchar(255) NOT NULL,\n" .
            "  `direction` enum('up','down') NOT NULL DEFAULT 'up',\n" .
            "  `applied_at` datetime DEFAULT CURRENT_TIMESTAMP,\n" .
            "  PRIMARY KEY (`id`),\n" .
            "  UNIQUE KEY `uniq_migration` (`migration`)\n" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
    }

    private function appliedMigrations(): array
    {
        $rows = $this->pdo
            ->query('SELECT migration, direction, applied_at FROM schema_migrations')
            ->fetchAll(PDO::FETCH_OBJ);

        $map = [];
        foreach ($rows as $row) {
            $map[$row->migration] = $row;
        }

        return $map;
    }

    private function markApplied(string $name, string $direction): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO schema_migrations (migration, direction, applied_at) VALUES (?, ?, NOW())');
        $stmt->execute([$name, $direction]);
    }
}
