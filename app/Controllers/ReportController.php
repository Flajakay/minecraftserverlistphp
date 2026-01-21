<?php

namespace App\Controllers;

use App\Models\Report;
use App\Core\Security\Auth;

/**
 * Reports controller.
 *
 * Provides an authenticated AJAX endpoint for submitting reports against different entity types.
 */
class ReportController
{
    /**
     * Create a new report.
     *
     * Notes:
     * - JSON-only response
     * - enforces a single report per user/type/reported_id tuple
     */
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
