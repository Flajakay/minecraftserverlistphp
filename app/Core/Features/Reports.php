<?php

namespace App\Core\Features;

use App\Models\Report;
use App\Models\AuditLog;

class Reports
{
    public static function delete($id, $deleterId)
    {
        $report = Report::find($id);
        if (!$report) {
            return [
                'success' => false,
                'error' => lang('report_not_found')
            ];
        }

        if (Report::delete($id)) {
            AuditLog::log('delete', 'reports', $id, $deleterId, 'Deleted report #' . $id);
            
            return [
                'success' => true,
                'message' => lang('report_deleted')
            ];
        }

        return [
            'success' => false,
            'error' => lang('report_delete_failed')
        ];
    }

    public static function resolve($id, $resolverId)
    {
        $report = Report::find($id);
        if (!$report) {
            return [
                'success' => false,
                'error' => lang('report_not_found')
            ];
        }

        AuditLog::log('resolve', 'reports', $id, $resolverId, 'Resolved report #' . $id);
        
        if (Report::delete($id)) {
            return [
                'success' => true,
                'message' => 'Report resolved'
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to resolve report'
        ];
    }
}
