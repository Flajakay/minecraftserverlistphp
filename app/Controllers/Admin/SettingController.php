<?php

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Models\Server;
use App\Models\AuditLog;

class SettingController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $settings = Setting::get();

        view('admin.settings', ['settings' => $settings]);
    }

    public function update()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $data = [
            'title' => sanitize($_POST['title'] ?? ''),
            'url' => sanitize($_POST['url'] ?? ''),
            'meta_description' => sanitize($_POST['meta_description'] ?? ''),
            'contact_email' => sanitize($_POST['contact_email'] ?? ''),
            'servers_pagination' => (int)($_POST['servers_pagination'] ?? 15),
            'display_offline_servers' => isset($_POST['display_offline_servers']) ? 1 : 0,
            'new_servers_visibility' => isset($_POST['new_servers_visibility']) ? 1 : 0,
            'email_confirmation' => isset($_POST['email_confirmation']) ? 1 : 0,
            'smtp_host' => sanitize($_POST['smtp_host'] ?? ''),
            'smtp_port' => sanitize($_POST['smtp_port'] ?? ''),
            'smtp_user' => sanitize($_POST['smtp_user'] ?? ''),
            'smtp_pass' => sanitize($_POST['smtp_pass'] ?? ''),
            'smtp_secure' => sanitize($_POST['smtp_secure'] ?? '')
        ];

        Setting::update($data);

        flash('success', 'Settings updated successfully');
        redirect('/admin/settings');
    }

    public function resetVotes()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Server::resetAllVotes()) {
                $currentUser = auth();
                AuditLog::log('reset_votes', 'servers', 0, $currentUser->id, 'Reset all server votes');
                flash('success', lang('votes_reset_success'));
            } else {
                flash('error', 'Failed to reset votes');
            }
        }

        redirect('/admin/settings');
    }
}
