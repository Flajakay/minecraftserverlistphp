<?php

namespace App\Models;

use App\Core\System\Database;

class Session
{
    /**
     * Find a session by its ID.
     *
     * @param string $id
     * @return object|false
     */
    public static function find($id)
    {
        return Database::fetch("SELECT payload FROM sessions WHERE id = ? LIMIT 1", [$id]);
    }

    /**
     * Create or update a session.
     *
     * @param string $id
     * @param array $data
     * @return bool
     */
    public static function save($id, $payload, $userId, $ipAddress, $userAgent, $lastActivity)
    {
        $sql = "INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                user_id = VALUES(user_id), 
                ip_address = VALUES(ip_address), 
                user_agent = VALUES(user_agent), 
                payload = VALUES(payload), 
                last_activity = VALUES(last_activity)";

        return Database::query($sql, [
            $id, 
            $userId, 
            $ipAddress, 
            $userAgent, 
            $payload, 
            $lastActivity
        ]);
    }

    /**
     * Delete a session by ID.
     *
     * @param string $id
     * @return bool
     */
    public static function delete($id)
    {
        return Database::query("DELETE FROM sessions WHERE id = ?", [$id]);
    }

    /**
     * Garbage collect old sessions.
     *
     * @param int $maxLifetime
     * @return int Number of deleted sessions
     */
    public static function gc($maxLifetime)
    {
        $stmt = Database::query("DELETE FROM sessions WHERE last_activity < ?", [time() - $maxLifetime]);
        return $stmt->rowCount();
    }
}
