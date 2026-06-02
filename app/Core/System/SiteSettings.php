<?php

namespace App\Core\System;

use App\Core\Support\Env;
use App\Models\Setting;
use App\Models\Server;
use App\Models\AuditLog;

class SiteSettings
{
    public static function update($data, array $files = []): array
    {
        $currentSettings = Setting::get();
        $settingsData = [
            'title' => sanitize($data['title'] ?? ''),
            'url' => sanitize($data['url'] ?? ''),
            'meta_description' => sanitize($data['meta_description'] ?? ''),
            'contact_email' => sanitize($data['contact_email'] ?? ''),
            'servers_pagination' => (int)($data['servers_pagination'] ?? 15),
            'display_offline_servers' => isset($data['display_offline_servers']) ? 1 : 0,
            'new_servers_visibility' => isset($data['new_servers_visibility']) ? 1 : 0,
            'email_confirmation' => isset($data['email_confirmation']) ? 1 : 0,
            'smtp_host' => sanitize($data['smtp_host'] ?? ''),
            'smtp_port' => sanitize($data['smtp_port'] ?? ''),
            'smtp_user' => sanitize($data['smtp_user'] ?? ''),
            'smtp_pass' => sanitize($data['smtp_pass'] ?? ''),
            'smtp_secure' => sanitize($data['smtp_secure'] ?? ''),
            'payment_currency' => sanitize($data['payment_currency'] ?? 'USD'),
            'per_day_cost' => (float)($data['per_day_cost'] ?? 0.00),
            'minimum_days' => (int)($data['minimum_days'] ?? 1),
            'maximum_days' => (int)($data['maximum_days'] ?? 30)
        ];
        $faviconError = null;

        if (isset($files['favicon']) && ($files['favicon']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $favicon = (new FaviconManager())->processUpload($files['favicon'], $settingsData['title']);

            if (!$favicon['success']) {
                $faviconError = $favicon['error'];
            } elseif (empty($favicon['skipped'])) {
                $settingsData['favicon_source'] = $favicon['source'];
                $settingsData['favicon_version'] = ((int)($currentSettings->favicon_version ?? 0)) + 1;
            }
        }

        self::updatePayPalEnv([
            'PAYPAL_EMAIL' => sanitize($data['paypal_email'] ?? ''),
            'PAYPAL_CLIENT_ID' => sanitize($data['paypal_client_id'] ?? ''),
            'PAYPAL_CLIENT_SECRET' => sanitize($data['paypal_client_secret'] ?? ''),
            'PAYPAL_SANDBOX' => isset($data['paypal_sandbox']),
        ]);

        Setting::update($settingsData);

        return [
            'success' => $faviconError === null,
            'message' => $faviconError ?? lang('settings_updated_successfully')
        ];
    }

    private static function updatePayPalEnv(array $values): void
    {
        Env::writeValues(dirname(__DIR__, 3) . '/.env', $values);
    }

    public static function resetVotes($initiatorId): array
    {
        if (Server::resetAllVotes()) {
            AuditLog::log('reset_votes', 'servers', 0, $initiatorId, 'Reset all server votes');
            
            return [
                'success' => true,
                'message' => lang('votes_reset_success')
            ];
        }

        return [
            'success' => false,
            'error' => lang('votes_reset_failed')
        ];
    }
}
