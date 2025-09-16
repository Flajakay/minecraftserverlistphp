<?php

namespace App\Models;

use App\Core\Database;


class Payment
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return Database::insert('payments', $data);
    }

    public static function getAll()
    {
        return Database::fetchAll(
            'SELECT p.*, s.name as server_name, u.username 
             FROM payments p 
             LEFT JOIN servers s ON p.server_id = s.id 
             LEFT JOIN users u ON p.user_id = u.id 
             ORDER BY p.created_at DESC'
        );
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM payments WHERE id = ?', [$id]);
    }

    public static function delete($id)
    {
        return Database::delete('payments', 'id = ?', [$id]);
    }

    public static function expireHighlights()
    {
        return Database::query(
            'UPDATE servers s 
             JOIN payments p ON s.id = p.server_id 
             SET s.highlight = 0 
             WHERE DATE_ADD(p.created_at, INTERVAL p.highlighted_days DAY) < NOW()'
        );
    }
}
