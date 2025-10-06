<?php

namespace App\Models;

use App\Core\Database;

class PlayerHistory
{
    public static function record($serverId, $players, $maxPlayers, $status)
    {
        return Database::insert('server_player_history', [
            'server_id' => $serverId,
            'players' => $players,
            'max_players' => $maxPlayers,
            'status' => $status
        ]);
    }

    public static function getStatistics($serverId, $days = 7)
    {
        return Database::fetchAll(
            'SELECT
                DATE(created_at) as date,
                AVG(players) as avg_players,
                MAX(players) as max_players,
                MIN(players) as min_players,
                COUNT(*) as samples
             FROM server_player_history
             WHERE server_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date DESC',
            [$serverId, $days]
        );
    }

    public static function getHourlyStatistics($serverId, $hours = 24)
    {
        return Database::fetchAll(
            'SELECT
                DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour,
                AVG(players) as avg_players,
                MAX(players) as max_players,
                MIN(players) as min_players,
                COUNT(*) as samples
             FROM server_player_history
             WHERE server_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
             GROUP BY DATE(created_at), HOUR(created_at)
             ORDER BY hour DESC',
            [$serverId, $hours]
        );
    }

    public static function cleanOldData($daysToKeep = 30)
    {
        return Database::query(
            'DELETE FROM server_player_history WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$daysToKeep]
        );
    }

    public static function getLatestForServer($serverId)
    {
        return Database::fetch(
            'SELECT * FROM server_player_history WHERE server_id = ? ORDER BY created_at DESC LIMIT 1',
            [$serverId]
        );
    }

    public static function shouldRecord($serverId, $intervalMinutes = 15)
    {
        $lastRecord = self::getLatestForServer($serverId);

        if (!$lastRecord) {
            return true;
        }

        $lastRecordTime = strtotime($lastRecord->created_at);
        $currentTime = time();
        $intervalSeconds = $intervalMinutes * 60;

        return ($currentTime - $lastRecordTime) >= $intervalSeconds;
    }
}
