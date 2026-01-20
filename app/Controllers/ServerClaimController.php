<?php

namespace App\Controllers;

use App\Models\Server;
use App\Core\MinecraftPing;
use App\Core\Database;

/**
 * Server claim/verification controller.
 *
 * Implements a lightweight ownership proof flow:
 * - user starts a claim to receive a short-lived token
 * - user places the token in the server MOTD
 * - the app pings the server and verifies the token appears in the MOTD text
 */
class ServerClaimController
{
    /**
     * Render the claim page for a server.
     */
    public function show($id)
    {
        if (!isLoggedIn()) {
            flash('error', lang('logged_in_action'));
            redirect('/login');
        }

        $server = Server::find((int) $id);
        if (!$server || !$server->active) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        $effectiveOwnerId = Server::getEffectiveOwnerUserId($server);
        $isEffectiveOwner = $effectiveOwnerId && auth()->id == $effectiveOwnerId;

        if ($isEffectiveOwner && (int) $server->verification_status === 2) {
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        if ((int) $server->verification_status === 2 && $server->verified_owner_user_id && (int) $server->verified_owner_user_id !== (int) auth()->id) {
            flash('error', lang('server_claim_already_verified'));
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        view('servers.claim', [
            'server' => $server,
            'is_effective_owner' => $isEffectiveOwner,
            'is_pending' => (int) $server->verification_status === 1,
            'is_requestor' => $server->verification_requested_by_user_id && (int) $server->verification_requested_by_user_id === (int) auth()->id
        ]);
    }

    /**
     * Start a new claim by generating a short-lived verification token.
     */
    public function start($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find((int) $id);
        if (!$server || !$server->active) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        if ((int) $server->verification_status === 2 && $server->verified_owner_user_id && (int) $server->verified_owner_user_id !== (int) auth()->id) {
            flash('error', lang('server_claim_already_verified'));
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        if ((int) $server->verification_status === 1 && $server->verification_requested_by_user_id && (int) $server->verification_requested_by_user_id !== (int) auth()->id) {
            flash('error', lang('server_claim_in_progress'));
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        $token = 'MSL-' . bin2hex(random_bytes(8));
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);

        Server::startClaim((int) $server->id, (int) auth()->id, $token, $expiresAt);

        flash('success', lang('server_claim_started'));
        redirect(url("/server-claim/{$server->id}"));
    }

    /**
     * Verify ownership by pinging the server and checking the token in the MOTD.
     */
    public function verify($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find((int) $id);
        if (!$server || !$server->active) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        if ((int) $server->verification_status !== 1 || (int) $server->verification_requested_by_user_id !== (int) auth()->id) {
            flash('error', lang('access_denied'));
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        if (empty($server->verification_token) || empty($server->verification_token_expires_at) || strtotime($server->verification_token_expires_at) < time()) {
            flash('error', lang('server_claim_token_expired'));
            redirect(url("/server-claim/{$server->id}"));
        }

        if (!Server::canAttemptVerification((int) $server->id, 30)) {
            flash('error', lang('server_claim_rate_limited'));
            redirect(url("/server-claim/{$server->id}"));
        }

        Server::markVerificationAttempt((int) $server->id);

        $response = (new MinecraftPing($server->address, $server->port, 2))->query();
        if (!$response) {
            flash('error', lang('server_claim_offline'));
            redirect(url("/server-claim/{$server->id}"));
        }

        $motdText = $this->extractMotdText($response['description'] ?? '');

        if (stripos($motdText, (string) $server->verification_token) === false) {
            flash('error', lang('server_claim_token_missing'));
            redirect(url("/server-claim/{$server->id}"));
        }

        Server::markVerified((int) $server->id, (int) auth()->id);

        flash('success', lang('server_claim_verified'));
        redirect(url("/server/{$server->address}:{$server->port}"));
    }

    /**
     * Cancel an in-progress claim.
     */
    public function cancel($id)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $server = Server::find((int) $id);
        if (!$server || !$server->active) {
            flash('error', lang('server_not_found'));
            redirect('/servers');
        }

        if ((int) $server->verification_status !== 1 || (int) $server->verification_requested_by_user_id !== (int) auth()->id) {
            flash('error', lang('access_denied'));
            redirect(url("/server/{$server->address}:{$server->port}"));
        }

        Server::cancelClaim((int) $server->id);

        flash('success', lang('server_claim_cancelled'));
        redirect(url("/server/{$server->address}:{$server->port}"));
    }

    /**
     * Extract plain text from different MOTD description formats returned by ping.
     */
    private function extractMotdText($description): string
    {
        if (is_string($description)) {
            return $description;
        }

        if (is_array($description)) {
            return $this->extractFromChatComponent($description);
        }

        if (is_object($description)) {
            return $this->extractFromChatComponent((array) $description);
        }

        return '';
    }

    /**
     * Recursively extract text from a Minecraft chat component structure.
     */
    private function extractFromChatComponent(array $component): string
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
                    $text .= $this->extractFromChatComponent($extra);
                } elseif (is_object($extra)) {
                    $text .= $this->extractFromChatComponent((array) $extra);
                }
            }
        }

        return $text;
    }
}
