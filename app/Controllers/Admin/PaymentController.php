<?php

namespace App\Controllers\Admin;

use App\Models\Payment;

/**
 * Admin payments controller.
 *
 * Provides a searchable, paginated list of recorded payments.
 */
class PaymentController
{
    /**
     * List payments.
     */
    public function index(): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $payments = Payment::getAllPaginated($limit, $offset, $search);
        $totalPayments = Payment::countAll($search);
        $totalPages = ceil($totalPayments / $limit);

        view('admin.payments-index', [
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }
}
