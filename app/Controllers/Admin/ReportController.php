<?php

namespace App\Controllers\Admin;

use App\Models\Report;
use App\Core\Features\Reports;
/**
 * Admin reports controller.
 *
 * Allows admins to browse reports, view report details, and delete/resolve reports.
 */
class ReportController
{
    /**
     * List reports with optional filters.
     */
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

    /**
     * Show a single report with related details.
     */
    public function view($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $report = Report::getWithDetails($id);
        if (!$report) {
            flash('error', lang('report_not_found'));
            redirect('/admin/reports');
        }

        view('admin.reports-view', ['report' => $report]);
    }

    /**
     * Delete a report.
     */
    public function delete($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $currentUser = auth();
        $result = Reports::delete($id, $currentUser->id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['error']);
        }

        redirect('/admin/reports');
    }

    /**
     * Perform a report moderation action.
     */
    public function action($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $action = $_POST['action'] ?? '';
        $report = Report::find($id);
        
        if (!$report) {
            flash('error', lang('report_not_found'));
            redirect('/admin/reports');
        }

        $currentUser = auth();
        $result = null;

        switch ($action) {
            case 'delete':
                $result = Reports::delete($id, $currentUser->id);
                break;
            case 'resolve':
                $result = Reports::resolve($id, $currentUser->id);
                break;
            default:
                flash('error', 'Invalid action');
                redirect('/admin/reports');
        }

        if ($result && $result['success']) {
            flash('success', $result['message']);
        } elseif ($result) {
            flash('error', $result['error']);
        }

        redirect('/admin/reports');
    }
}
