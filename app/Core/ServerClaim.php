<?php

namespace App\Core;

use App\Models\Server;

class ServerClaim
{
    public static function isAlreadyVerifiedByOther($user, $server)
    {
        return (int) $server->verification_status === 2 
            && $server->verified_owner_user_id 
            && (int) $server->verified_owner_user_id !== (int) $user->id;
    }

    public static function isClaimInProgressByOther($user, $server)
    {
        return (int) $server->verification_status === 1 
            && $server->verification_requested_by_user_id 
            && (int) $server->verification_requested_by_user_id !== (int) $user->id;
    }

    public static function canStartClaim($user, $server)
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

    public static function canVerifyClaim($user, $server)
    {
        if (!$user || !$server || !$server->active) {
            return ['allowed' => false, 'error' => lang('server_not_found')];
        }

        if ((int) $server->verification_status !== 1 || (int) $server->verification_requested_by_user_id !== (int) $user->id) {
            return ['allowed' => false, 'error' => lang('access_denied')];
        }

        return ['allowed' => true];
    }

    public static function canCancelClaim($user, $server)
    {
        return self::canVerifyClaim($user, $server);
    }

    public static function isTokenExpired($server)
    {
        return empty($server->verification_token) 
            || empty($server->verification_token_expires_at) 
            || strtotime($server->verification_token_expires_at) < time();
    }

    public static function generateVerificationToken()
    {
        return 'MSL-' . bin2hex(random_bytes(8));
    }

    public static function startClaim($serverId, $userId)
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
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);

        Server::startClaim($serverId, $userId, $token, $expiresAt);

        return [
            'success' => true,
            'message' => lang('server_claim_started'),
            'server' => $server
        ];
    }

    public static function verifyClaim($serverId, $userId)
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

        if (!Server::canAttemptVerification($serverId, 30)) {
            return [
                'success' => false,
                'error' => lang('server_claim_rate_limited'),
                'server' => $server
            ];
        }

        Server::markVerificationAttempt($serverId);

        $response = (new MinecraftPing($server->address, $server->port, 2))->query();
        if (!$response) {
            return [
                'success' => false,
                'error' => lang('server_claim_offline'),
                'server' => $server
            ];
        }

        $motdText = self::extractMotdText($response['description'] ?? '');

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

    public static function cancelClaim($serverId, $userId)
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

    private static function extractFromChatComponent(array $component): string
    {
        $text = '';

        if (isset($component['text']) && is_string($component['text'])) {
            $text .= $component['text'];
        }

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

    public static function getShowPageData($user, $server)
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
