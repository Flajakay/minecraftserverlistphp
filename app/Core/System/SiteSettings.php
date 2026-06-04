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
            'maximum_days' => (int)($data['maximum_days'] ?? 30),
            'minecraft_java_enabled' => isset($data['minecraft_java_enabled']) ? 1 : 0,
            'steam_a2s_enabled' => isset($data['steam_a2s_enabled']) ? 1 : 0,
            'minecraft_bedrock_enabled' => isset($data['minecraft_bedrock_enabled']) ? 1 : 0,
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

        self::updateRateLimitEnv([
            'RATE_LIMIT_ENABLED' => isset($data['rate_limit_enabled']) ? 'true' : 'false',
            'RATE_LIMIT_AUTH_LIMIT' => (int)($data['rate_limit_auth_limit'] ?? 5),
            'RATE_LIMIT_AUTH_WINDOW' => (int)($data['rate_limit_auth_window'] ?? 300),
            'RATE_LIMIT_VOTING_LIMIT' => (int)($data['rate_limit_voting_limit'] ?? 10),
            'RATE_LIMIT_VOTING_WINDOW' => (int)($data['rate_limit_voting_window'] ?? 60),
            'RATE_LIMIT_SERVER_ACTIONS_LIMIT' => (int)($data['rate_limit_server_actions_limit'] ?? 5),
            'RATE_LIMIT_SERVER_ACTIONS_WINDOW' => (int)($data['rate_limit_server_actions_window'] ?? 60),
            'RATE_LIMIT_CONTACT_LIMIT' => (int)($data['rate_limit_contact_limit'] ?? 3),
            'RATE_LIMIT_CONTACT_WINDOW' => (int)($data['rate_limit_contact_window'] ?? 300),
            'RATE_LIMIT_PAYMENTS_LIMIT' => (int)($data['rate_limit_payments_limit'] ?? 10),
            'RATE_LIMIT_PAYMENTS_WINDOW' => (int)($data['rate_limit_payments_window'] ?? 300),
            'RATE_LIMIT_API_LIMIT' => (int)($data['rate_limit_api_limit'] ?? 60),
            'RATE_LIMIT_API_WINDOW' => (int)($data['rate_limit_api_window'] ?? 60),
            'RATE_LIMIT_ADMIN_LIMIT' => (int)($data['rate_limit_admin_limit'] ?? 200),
            'RATE_LIMIT_ADMIN_WINDOW' => (int)($data['rate_limit_admin_window'] ?? 60),
            'RATE_LIMIT_GENERAL_LIMIT' => (int)($data['rate_limit_general_limit'] ?? 150),
            'RATE_LIMIT_GENERAL_WINDOW' => (int)($data['rate_limit_general_window'] ?? 60),
            'RATE_LIMIT_BYPASS_IPS' => sanitize($data['rate_limit_bypass_ips'] ?? '127.0.0.1'),
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

    private static function updateRateLimitEnv(array $values): void
    {
        Env::writeValues(dirname(__DIR__, 3) . '/.env', $values);
    }

    public static function resetVotes($initiatorId): array
    {
        if (Server::resetAllVotes() !== false) {
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
