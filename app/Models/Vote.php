<?php

namespace App\Models;

use App\Core\Database;

class Vote
{
    public static function canVote($serverId, $ip)
    {
        $vote = Database::fetch(
            'SELECT * FROM points WHERE server_id = ? AND ip = ? AND type = 1 AND timestamp > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)',
            [$serverId, $ip]
        );
        
        return !$vote;
    }

    public static function create($serverId, $ip, $username = null)
    {
        return Database::insert('points', [
            'type' => 1,
            'server_id' => $serverId,
            'ip' => $ip,
            'timestamp' => time()
        ]);
    }

    public static function getServerVotes($serverId, $period = 'month')
    {
        // Use whitelist approach for security
        $allowedIntervals = [
            'day' => '1 DAY',
            'month' => '1 MONTH'
        ];
        
        $interval = $allowedIntervals[$period] ?? '1 MONTH';
        
        $result = Database::fetch(
            'SELECT COUNT(*) as count FROM points WHERE server_id = ? AND type = 1 AND timestamp > UNIX_TIMESTAMP(NOW() - INTERVAL ' . $interval . ')',
            [$serverId]
        );
        
        return $result->count;
    }

    public static function recordHit($serverId, $ip)
    {
        $existing = Database::fetch(
            'SELECT * FROM points WHERE server_id = ? AND ip = ? AND type = 0 AND timestamp > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)',
            [$serverId, $ip]
        );
        
        if (!$existing) {
            return Database::insert('points', [
                'type' => 0,
                'server_id' => $serverId,
                'ip' => $ip,
                'timestamp' => time()
            ]);
        }
        
        return false;
    }

    public static function getStatistics($serverId, $days = 7)
    {
        return Database::fetchAll(
            'SELECT 
                DATE(FROM_UNIXTIME(timestamp)) as date,
                SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) as hits,
                SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) as votes
             FROM points 
             WHERE server_id = ? AND timestamp > UNIX_TIMESTAMP(NOW() - INTERVAL ? DAY)
             GROUP BY DATE(FROM_UNIXTIME(timestamp))
             ORDER BY date DESC',
            [$serverId, $days]
        );
    }

    public static function getServerHits($serverId, $period = 'month')
    {
        // Use whitelist approach for security
        $allowedIntervals = [
            'day' => '1 DAY',
            'month' => '1 MONTH'
        ];
        
        $interval = $allowedIntervals[$period] ?? '1 MONTH';
        
        $result = Database::fetch(
            'SELECT COUNT(*) as count FROM points WHERE server_id = ? AND type = 0 AND timestamp > UNIX_TIMESTAMP(NOW() - INTERVAL ' . $interval . ')',
            [$serverId]
        );
        
        return $result ? $result->count : 0;
    }
}
