<?php

namespace App\Controllers;

use App\Models\Report;
use App\Core\Auth;

class ReportController
{
    public function store()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Must be logged in']);
            return;
        }

        $type = (int)($_POST['type'] ?? 0);
        $reportedId = (int)($_POST['reported_id'] ?? 0);
        $message = sanitize($_POST['message'] ?? '');

        if (empty($message) || strlen($message) < 5 || strlen($message) > 512) {
            echo json_encode(['success' => false, 'message' => 'Message must be between 5 and 512 characters']);
            return;
        }

        if (Report::exists(auth()->id, $type, $reportedId)) {
            echo json_encode(['success' => false, 'message' => 'Already reported']);
            return;
        }

        Report::create([
            'user_id' => auth()->id,
            'type' => $type,
            'reported_id' => $reportedId,
            'message' => $message
        ]);

        echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
    }
}
