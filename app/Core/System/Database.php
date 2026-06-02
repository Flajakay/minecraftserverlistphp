<?php

namespace App\Core\System;

use App\Core\Support\Config;
use PDO;

/**
 * PDO wrapper used across the app.
 *
 * Values are parameterized via prepared statements. Callers should ensure any dynamic
 * table/column names are trusted or properly whitelisted.
 */
class Database
{
    private static $pdo;

    public static function connect(): void
    {
        $db = Config::get('app.db');
        
        $dsn = "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4";
        
        self::$pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);
    }

    public static function pdo()
    {
        return self::$pdo;
    }

    public static function query($sql, $params = [])
    {
        self::logQuery($sql, $params);

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Log executed queries for auditing and analysis in the testing environment.
     */
    private static function logQuery(string $sql, array $params): void
    {
        if (Config::get('app.env') === 'testing') {
            $logDir = dirname(__DIR__, 3) . '/storage/logs';
            if (is_dir($logDir)) {
                $logFile = $logDir . '/test_queries.log';
                $timestamp = date('Y-m-d H:i:s');
                $paramsJson = !empty($params) ? json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';
                $logMessage = "[{$timestamp}] SQL: {$sql} | Params: {$paramsJson}\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        }
    }

    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch($sql, $params = [])
    {
        return self::query($sql, $params)->fetch();
    }

    public static function insert($table, $data)
    {
        // $table and keys are interpolated into SQL; callers must not pass untrusted names.
        $keys = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$keys}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));
        
        return self::$pdo->lastInsertId();
    }

    public static function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $set[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $set = implode(', ', $set);
        $params = array_merge($params, $whereParams);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        return self::query($sql, $params);
    }

    public static function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return self::query($sql, $params);
    }
}
