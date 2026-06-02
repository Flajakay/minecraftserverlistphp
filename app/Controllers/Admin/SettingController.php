<?php

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Core\System\SiteSettings;
use App\Core\Support\Config;
/**
 * Admin settings controller.
 *
 * Allows updating site-wide settings and running privileged maintenance actions.
 */
class SettingController
{
    /**
     * Render settings form.
     */
    public function index(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        $settings = Setting::get();
        
        // Merge PayPal config from environment-backed app config for the view.
        $paypalConfig = Config::get('app.paypal', []);
        $settings->paypal_email = $paypalConfig['email'] ?? '';
        $settings->paypal_client_id = $paypalConfig['client_id'] ?? '';
        $settings->paypal_client_secret = $paypalConfig['client_secret'] ?? '';
        $settings->paypal_sandbox = $paypalConfig['sandbox'] ?? true;

        view('admin.settings', ['settings' => $settings]);
    }

    /**
     * Persist settings changes.
     */
    public function update(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        $result = SiteSettings::update($_POST, $_FILES);

        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect('/admin/settings');
    }

    /**
     * Reset votes for all servers.
     */
    public function resetVotes(): void
    {
        if (!isAdmin()) {
            flash('error', lang('access_denied'));
            redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentUser = auth();
            $result = SiteSettings::resetVotes($currentUser->id);

            if ($result['success']) {
                flash('success', $result['message']);
            } else {
                flash('error', $result['error']);
            }
        }

        redirect('/admin/settings');
    }
}
