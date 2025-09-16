<?php

namespace App\Controllers\Admin;

use App\Models\Payment;

class PaymentController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $payments = Payment::getAll();

        view('admin.payments.index', ['payments' => $payments]);
    }
}
