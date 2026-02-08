<?php

namespace App\Core\System;

use App\Models\Setting;
use App\Models\Server;
use App\Models\AuditLog;

class SiteSettings
{
    public static function update($data): array
    {
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
            'paypal_email' => sanitize($data['paypal_email'] ?? ''),
            'paypal_client_id' => sanitize($data['paypal_client_id'] ?? ''),
            'paypal_client_secret' => sanitize($data['paypal_client_secret'] ?? ''),
            'paypal_sandbox' => isset($data['paypal_sandbox']) ? 1 : 0,
            'payment_currency' => sanitize($data['payment_currency'] ?? 'USD'),
            'per_day_cost' => (float)($data['per_day_cost'] ?? 0.00),
            'minimum_days' => (int)($data['minimum_days'] ?? 1),
            'maximum_days' => (int)($data['maximum_days'] ?? 30)
        ];

        Setting::update($settingsData);

        return [
            'success' => true,
            'message' => lang('settings_updated_successfully')
        ];
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
