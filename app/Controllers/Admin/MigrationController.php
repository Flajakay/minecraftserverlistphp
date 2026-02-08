<?php

namespace App\Controllers\Admin;

use App\Core\System\MigrationRunner;
use App\Models\AuditLog;
use Throwable;

/**
 * Admin migrations controller.
 *
 * Exposes migration status and execution from the admin panel.
 */
class MigrationController
{
    /**
     * Show migration status (applied vs pending).
     */
    public function index(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        $runner = new MigrationRunner();
        $status = $runner->status();

        view('admin.migrations', [
            'status' => $status
        ]);
    }

    /**
     * Run all pending migrations.
     */
    public function runAll(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        $runner = new MigrationRunner();
        $result = $runner->runAllPending();

        if (!empty($result['failed'])) {
            flash('error', lang('migration_failed') . ': ' . $result['failed']['name']);
        } else {
            $count = count($result['executed'] ?? []);
            flash('success', sprintf(lang('migrations_ran_successfully'), $count));
        }

        if (class_exists(AuditLog::class)) {
            try {
                $currentUser = auth();
                $details = json_encode($result);
                AuditLog::log('migrations_run', 'schema_migrations', 0, $currentUser->id, $details ?: '');
            } catch (Throwable $e) {
            }
        }

        redirect('/admin/migrations');
    }

    /**
     * Run only the selected migrations.
     */
    public function runSelected(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        $runner = new MigrationRunner();
        $selected = $_POST['migrations'] ?? [];
        if (!is_array($selected)) {
            $selected = [];
        }
        $result = $runner->runSelected($selected);

        if (!empty($result['failed'])) {
            flash('error', lang('migration_failed') . ': ' . $result['failed']['name']);
        } else {
            $count = count($result['executed'] ?? []);
            flash('success', sprintf(lang('migrations_ran_successfully'), $count));
        }

        if (class_exists(AuditLog::class)) {
            try {
                $currentUser = auth();
                $details = json_encode($result);
                AuditLog::log('migrations_run_selected', 'schema_migrations', 0, $currentUser->id, $details ?: '');
            } catch (Throwable $e) {
            }
        }

        redirect('/admin/migrations');
    }
}
