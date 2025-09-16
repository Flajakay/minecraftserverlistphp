<?php

namespace App\Controllers\Admin;

use App\Models\Report;
use App\Models\AuditLog;

class ReportController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');
        $filters = [
            'type' => $_GET['type'] ?? ''
        ];

        $limit = 20;
        $reports = Report::getAllPaginated($page, $limit, $search, $filters);
        $totalReports = Report::countAllAdmin($search, $filters);
        $totalPages = ceil($totalReports / $limit);

        view('admin.reports-index', [
            'reports' => $reports,
            'totalReports' => $totalReports,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'filters' => $filters
        ]);
    }

    public function view($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $report = Report::getWithDetails($id);
        if (!$report) {
            flash('error', 'Report not found');
            redirect('/admin/reports');
        }

        view('admin.reports-view', ['report' => $report]);
    }

    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $report = Report::find($id);
        if (!$report) {
            flash('error', 'Report not found');
            redirect('/admin/reports');
        }

        if (Report::delete($id)) {
            $currentUser = auth();
            AuditLog::log('delete', 'reports', $id, $currentUser->id, 'Deleted report #' . $id);
            flash('success', lang('report_deleted'));
        } else {
            flash('error', 'Failed to delete report');
        }

        redirect('/admin/reports');
    }

    public function action($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $action = $_POST['action'] ?? '';
        $report = Report::find($id);
        
        if (!$report) {
            flash('error', 'Report not found');
            redirect('/admin/reports');
        }

        $currentUser = auth();

        switch ($action) {
            case 'delete':
                if (Report::delete($id)) {
                    AuditLog::log('delete', 'reports', $id, $currentUser->id, 'Deleted report #' . $id);
                    flash('success', lang('report_deleted'));
                }
                break;
            case 'resolve':
                AuditLog::log('resolve', 'reports', $id, $currentUser->id, 'Resolved report #' . $id);
                if (Report::delete($id)) {
                    flash('success', 'Report resolved');
                }
                break;
            default:
                flash('error', 'Invalid action');
        }

        redirect('/admin/reports');
    }
}
