<?php

namespace App\Models;

use App\Core\System\Database;


class Payment
{
    public static function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pending';

        return Database::insert('payments', $data);
    }

    public static function updateStatus($id, $status, $paypalOrderId = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($paypalOrderId) {
            $data['paypal_order_id'] = $paypalOrderId;
        }

        return Database::update('payments', $data, 'id = ?', [$id]);
    }

    public static function findByPayPalOrderId($paypalOrderId)
    {
        return Database::fetch('SELECT * FROM payments WHERE paypal_order_id = ?', [$paypalOrderId]);
    }

    public static function markCompleted($id)
    {
        return Database::query(
            'UPDATE payments SET status = ?, updated_at = ? WHERE id = ? AND status = ?',
            ['completed', date('Y-m-d H:i:s'), $id, 'pending']
        )->rowCount();
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

    public static function getAllPaginated($limit, $offset, $search = '')
    {
        $sql = 'SELECT p.*, s.name as server_name, u.username
                FROM payments p
                LEFT JOIN servers s ON p.server_id = s.id
                LEFT JOIN users u ON p.user_id = u.id';

        $params = [];

        if (!empty($search)) {
            $sql .= ' WHERE (u.username LIKE ? OR u.email LIKE ? OR s.name LIKE ? OR p.email LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $sql .= ' ORDER BY p.created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return Database::fetchAll($sql, $params);
    }

    public static function countAll($search = '')
    {
        $sql = 'SELECT COUNT(*) as count FROM payments p
                LEFT JOIN servers s ON p.server_id = s.id
                LEFT JOIN users u ON p.user_id = u.id';

        $params = [];

        if (!empty($search)) {
            $sql .= ' WHERE (u.username LIKE ? OR u.email LIKE ? OR s.name LIKE ? OR p.email LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $result = Database::fetch($sql, $params);
        return $result->count;
    }

    public static function find($id)
    {
        return Database::fetch('SELECT * FROM payments WHERE id = ?', [$id]);
    }

    public static function delete($id)
    {
        return Database::delete('payments', 'id = ?', [$id]);
    }

    public static function expireHighlights(): int
    {
        $expiredPayments = Database::fetchAll(
            'SELECT p.id, p.server_id, p.user_id, p.highlighted_days, p.created_at, s.name as server_name, u.username
             FROM payments p
             JOIN servers s ON p.server_id = s.id
             JOIN users u ON p.user_id = u.id
             WHERE p.status = "completed"
             AND DATE_ADD(p.created_at, INTERVAL p.highlighted_days DAY) < NOW()
             AND s.highlight = 1'
        );

        $expiredCount = 0;
        foreach ($expiredPayments as $payment) {
            Server::updateHighlight($payment->server_id, 0);
            Payment::updateStatus($payment->id, 'expired');

            error_log(sprintf(
                'Expired highlight for server "%s" (ID: %d) owned by user "%s" (ID: %d). Payment ID: %d, Duration: %d days',
                $payment->server_name,
                $payment->server_id,
                $payment->username,
                $payment->user_id,
                $payment->id,
                $payment->highlighted_days
            ));

            $expiredCount++;
        }

        return $expiredCount;
    }
}
