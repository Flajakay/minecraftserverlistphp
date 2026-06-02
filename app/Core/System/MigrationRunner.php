<?php

namespace App\Core\System;

use PDO;
use PDOException;
use Throwable;

/**
 * Database migration runner for `database/migrations/*.sql`.
 *
 * Discovers `.up.sql` migrations, executes pending files statement by statement,
 * and tracks applied migrations in `schema_migrations`.
 */
class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(?PDO $pdo = null, ?string $migrationsPath = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
        $this->migrationsPath = $migrationsPath ?: dirname(__DIR__, 3) . '/database/migrations';
    }

    public function status(): array
    {
        $this->ensureMigrationsTable();

        $discovery = $this->discoverMigrations();
        $migrations = $discovery['up'];
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
            'ignored' => count($discovery['ignored']),
            'ignored_items' => $discovery['ignored'],
            'items' => $items
        ];
    }

    public function runAllPending(): array
    {
        $this->ensureMigrationsTable();

        $migrations = $this->discoverMigrations()['up'];
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

        $migrations = $this->discoverMigrations()['up'];
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
                $contents = file_get_contents($migration['path']);
                if ($contents === false) {
                    throw new \RuntimeException('Unable to read migration file.');
                }

                $sql = trim($contents);
                $statements = $this->splitStatements($sql);
                if (empty($statements)) {
                    // Empty migration files are treated as no-ops but still recorded.
                    $this->markApplied($migration['name'], $migration['direction']);
                    $executed[] = [
                        'name' => $migration['name'],
                        'status' => 'skipped_empty',
                        'statements' => []
                    ];
                    continue;
                }

                $statementResults = $this->executeStatements($statements);
                $this->markApplied($migration['name'], $migration['direction']);
                $executed[] = [
                    'name' => $migration['name'],
                    'status' => $this->hasAppliedStatement($statementResults) ? 'applied' : 'skipped_idempotent',
                    'statements' => $statementResults
                ];
            } catch (Throwable $e) {
                $pdoException = $this->extractPdoException($e);
                $failed = [
                    'name' => $migration['name'],
                    'message' => $e->getMessage(),
                    'statement' => method_exists($e, 'getMigrationStatement') ? $e->getMigrationStatement() : null,
                    'statement_index' => method_exists($e, 'getMigrationStatementIndex') ? $e->getMigrationStatementIndex() : null,
                    'sqlstate' => $pdoException ? $pdoException->getCode() : null,
                    'driver_code' => $pdoException ? ($pdoException->errorInfo[1] ?? null) : null,
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
            return ['up' => [], 'ignored' => []];
        }

        $files = glob($this->migrationsPath . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        $upMigrations = [];
        $ignored = [];
        foreach ($files as $file) {
            $name = basename($file);
            $direction = str_contains($name, '.down.') ? 'down' : 'up';

            $item = [
                'name' => $name,
                'path' => $file,
                'direction' => $direction
            ];

            if ($direction === 'down') {
                $item['reason'] = 'rollback_not_supported';
                $ignored[] = $item;
                continue;
            }

            $upMigrations[] = $item;
        }

        return ['up' => $upMigrations, 'ignored' => $ignored];
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $length = strlen($sql);
        $quote = null;
        $inLineComment = false;
        $inBlockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($inLineComment) {
                $current .= $char;
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            if ($inBlockComment) {
                $current .= $char;
                if ($char === '*' && $next === '/') {
                    $current .= $next;
                    $i++;
                    $inBlockComment = false;
                }
                continue;
            }

            if ($quote !== null) {
                $current .= $char;

                if ($char === '\\' && ($quote === '\'' || $quote === '"') && $next !== '') {
                    $current .= $next;
                    $i++;
                    continue;
                }

                if ($char === $quote) {
                    if (($quote === '\'' || $quote === '"') && $next === $quote) {
                        $current .= $next;
                        $i++;
                        continue;
                    }

                    $quote = null;
                }

                continue;
            }

            if ($char === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
                $current .= $char . $next;
                $i++;
                $inLineComment = true;
                continue;
            }

            if ($char === '#') {
                $current .= $char;
                $inLineComment = true;
                continue;
            }

            if ($char === '/' && $next === '*') {
                $current .= $char . $next;
                $i++;
                $inBlockComment = true;
                continue;
            }

            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                $statement = trim($current);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $statement = trim($current);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }

    private function executeStatements(array $statements): array
    {
        $results = [];

        foreach ($statements as $index => $statement) {
            try {
                $stmt = $this->pdo->query($statement);
                if ($stmt !== false) {
                    $stmt->closeCursor();
                }

                $results[] = [
                    'index' => $index + 1,
                    'status' => 'applied',
                    'statement' => $this->summarizeStatement($statement)
                ];
            } catch (PDOException $e) {
                if ($this->isIgnorableDdlError($e, $statement)) {
                    $results[] = [
                        'index' => $index + 1,
                        'status' => 'skipped_idempotent',
                        'statement' => $this->summarizeStatement($statement),
                        'message' => $e->getMessage(),
                        'driver_code' => $e->errorInfo[1] ?? null
                    ];
                    continue;
                }

                throw new MigrationStatementException($e, $statement, $index + 1);
            }
        }

        return $results;
    }

    private function isIgnorableDdlError(PDOException $e, string $statement): bool
    {
        $driverCode = (int)($e->errorInfo[1] ?? 0);
        $normalized = strtoupper(ltrim($statement));

        if (str_starts_with($normalized, 'ALTER TABLE')) {
            return in_array($driverCode, [1060, 1061, 1091], true);
        }

        if (str_starts_with($normalized, 'CREATE TABLE')) {
            return $driverCode === 1050;
        }

        if (str_starts_with($normalized, 'CREATE INDEX') || str_starts_with($normalized, 'CREATE UNIQUE INDEX')) {
            return $driverCode === 1061;
        }

        return false;
    }

    private function hasAppliedStatement(array $statementResults): bool
    {
        foreach ($statementResults as $result) {
            if (($result['status'] ?? '') === 'applied') {
                return true;
            }
        }

        return false;
    }

    private function summarizeStatement(string $statement): string
    {
        $summary = preg_replace('/\s+/', ' ', trim($statement));
        if (strlen($summary) > 240) {
            return substr($summary, 0, 237) . '...';
        }

        return $summary;
    }

    private function extractPdoException(Throwable $e): ?PDOException
    {
        if ($e instanceof PDOException) {
            return $e;
        }

        $previous = $e->getPrevious();
        return $previous instanceof PDOException ? $previous : null;
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
