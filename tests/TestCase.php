<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\System\Database;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Start a database transaction to ensure a clean state per test
        Database::pdo()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Roll back the transaction to revert any modifications made by the test
        if (Database::pdo()->inTransaction()) {
            Database::pdo()->rollBack();
        }
        parent::tearDown();
    }
}
