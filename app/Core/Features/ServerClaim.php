<?php

namespace App\Core\Features;

use App\Core\Integrations\MinecraftPing;
use App\Models\Server;

class ServerClaim
{
    /**
     * Check if server was already verified by a different user.
     * Prevents ownership transfers without admin intervention.
     */
    public static function isAlreadyVerifiedByOther($user, $server): bool
    {
        return (int) $server->verification_status === 2 
            && $server->verified_owner_user_id 
            && (int) $server->verified_owner_user_id !== (int) $user->id;
    }

    public static function isClaimInProgressByOther($user, $server): bool
    {
        return (int) $server->verification_status === 1 
            && $server->verification_requested_by_user_id 
            && (int) $server->verification_requested_by_user_id !== (int) $user->id;
    }

    public static function canStartClaim($user, $server): array
    {
        if (!$user || !$server || !$server->active) {
            return ['allowed' => false, 'error' => lang('server_not_found')];
        }

        if (self::isAlreadyVerifiedByOther($user, $server)) {
            return ['allowed' => false, 'error' => lang('server_claim_already_verified')];
        }

        if (self::isClaimInProgressByOther($user, $server)) {
            return ['allowed' => false, 'error' => lang('server_claim_in_progress')];
        }

        return ['allowed' => true];
    }

    public static function canVerifyClaim($user, $server): array
    {
        if (!$user || !$server || !$server->active) {
            return ['allowed' => false, 'error' => lang('server_not_found')];
        }

        if ((int) $server->verification_status !== 1 || (int) $server->verification_requested_by_user_id !== (int) $user->id) {
            return ['allowed' => false, 'error' => lang('access_denied')];
        }

        return ['allowed' => true];
    }

    public static function canCancelClaim($user, $server): array
    {
        return self::canVerifyClaim($user, $server);
    }

    public static function isTokenExpired($server): bool
    {
        return empty($server->verification_token) 
            || empty($server->verification_token_expires_at) 
            || strtotime($server->verification_token_expires_at) < time();
    }

    /**
     * Generate unique verification token with MSL prefix.
     * Format: MSL-[16 random hex characters]
     */
    public static function generateVerificationToken(): string
    {
        return 'MSL-' . bin2hex(random_bytes(8));
    }

    public static function startClaim($serverId, $userId): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId];

        $check = self::canStartClaim($user, $server);
        if (!$check['allowed']) {
            return [
                'success' => false,
                'error' => $check['error'],
                'server' => $server
            ];
        }

        $token = self::generateVerificationToken();
        // Token expires after 30 minutes for security
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);

        Server::startClaim($serverId, $userId, $token, $expiresAt);

        return [
            'success' => true,
            'message' => lang('server_claim_started'),
            'server' => $server
        ];
    }

    public static function verifyClaim($serverId, $userId): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId];

        $check = self::canVerifyClaim($user, $server);
        if (!$check['allowed']) {
            return [
                'success' => false,
                'error' => $check['error'],
                'server' => $server
            ];
        }

        if (self::isTokenExpired($server)) {
            return [
                'success' => false,
                'error' => lang('server_claim_token_expired'),
                'server' => $server
            ];
        }

        // Rate limit verification attempts to prevent spam pinging the Minecraft server
        if (!Server::canAttemptVerification($serverId, 30)) {
            return [
                'success' => false,
                'error' => lang('server_claim_rate_limited'),
                'server' => $server
            ];
        }

        Server::markVerificationAttempt($serverId);

        // Ping the Minecraft server to retrieve MOTD for token verification
        $response = (new MinecraftPing($server->address, $server->port, 2))->query();
        if (!$response) {
            return [
                'success' => false,
                'error' => lang('server_claim_offline'),
                'server' => $server
            ];
        }

        $motdText = self::extractMotdText($response['description'] ?? '');

        // Verify token appears in MOTD (case-insensitive)
        if (stripos($motdText, (string) $server->verification_token) === false) {
            return [
                'success' => false,
                'error' => lang('server_claim_token_missing'),
                'server' => $server
            ];
        }

        Server::markVerified($serverId, $userId);

        return [
            'success' => true,
            'message' => lang('server_claim_verified'),
            'server' => $server
        ];
    }

    public static function cancelClaim($serverId, $userId): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId];

        $check = self::canCancelClaim($user, $server);
        if (!$check['allowed']) {
            return [
                'success' => false,
                'error' => $check['error'],
                'server' => $server
            ];
        }

        Server::cancelClaim($serverId);

        return [
            'success' => true,
            'message' => lang('server_claim_cancelled'),
            'server' => $server
        ];
    }

    /**
     * Extract plain text from Minecraft MOTD description.
     * Handles legacy strings, modern chat components, and nested extra components.
     */
    public static function extractMotdText($description): string
    {
        if (is_string($description)) {
            return $description;
        }

        if (is_array($description)) {
            return self::extractFromChatComponent($description);
        }

        if (is_object($description)) {
            return self::extractFromChatComponent((array) $description);
        }

        return '';
    }

    /**
     * Recursively extract text from Minecraft's chat component format.
     * Reference: https://wiki.vg/Chat#Current_system_.28JSON_Chat.29
     */
    private static function extractFromChatComponent(array $component): string
    {
        $text = '';

        if (isset($component['text']) && is_string($component['text'])) {
            $text .= $component['text'];
        }

        // Process extra components that may contain additional text
        if (isset($component['extra']) && is_array($component['extra'])) {
            foreach ($component['extra'] as $extra) {
                if (is_string($extra)) {
                    $text .= $extra;
                } elseif (is_array($extra)) {
                    $text .= self::extractFromChatComponent($extra);
                } elseif (is_object($extra)) {
                    $text .= self::extractFromChatComponent((array) $extra);
                }
            }
        }

        return $text;
    }

    public static function getShowPageData($user, $server): array
    {
        if (!$server || !$server->active) {
            return [
                'success' => false,
                'error' => lang('server_not_found'),
                'redirect' => '/servers'
            ];
        }

        $effectiveOwnerId = Server::getEffectiveOwnerUserId($server);
        $isEffectiveOwner = $effectiveOwnerId && $user->id == $effectiveOwnerId;

        if ($isEffectiveOwner && (int) $server->verification_status === 2) {
            return [
                'success' => false,
                'redirect' => url("/server/{$server->address}:{$server->port}")
            ];
        }

        if (self::isAlreadyVerifiedByOther($user, $server)) {
            return [
                'success' => false,
                'error' => lang('server_claim_already_verified'),
                'redirect' => url("/server/{$server->address}:{$server->port}")
            ];
        }

        return [
            'success' => true,
            'server' => $server,
            'is_effective_owner' => $isEffectiveOwner,
            'is_pending' => (int) $server->verification_status === 1,
            'is_requestor' => $server->verification_requested_by_user_id && (int) $server->verification_requested_by_user_id === (int) $user->id
        ];
    }
}
