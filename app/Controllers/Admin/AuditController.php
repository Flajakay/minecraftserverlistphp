<?php

namespace App\Controllers\Admin;

use App\Models\AuditLog;

class AuditController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');

        $limit = 50;
        $logs = AuditLog::getAll($page, $limit, $search);
        $totalLogs = AuditLog::count($search);
        $totalPages = ceil($totalLogs / $limit);

        view('admin.audit-index', [
            'logs' => $logs,
            'totalLogs' => $totalLogs,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }
}
