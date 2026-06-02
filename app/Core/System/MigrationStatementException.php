<?php

namespace App\Core\System;

use PDOException;
use RuntimeException;

class MigrationStatementException extends RuntimeException
{
    private string $migrationStatement;
    private int $migrationStatementIndex;

    public function __construct(PDOException $previous, string $statement, int $statementIndex)
    {
        parent::__construct($previous->getMessage(), 0, $previous);

        $this->migrationStatement = $statement;
        $this->migrationStatementIndex = $statementIndex;
    }

    public function getMigrationStatement(): string
    {
        return $this->migrationStatement;
    }

    public function getMigrationStatementIndex(): int
    {
        return $this->migrationStatementIndex;
    }
}
