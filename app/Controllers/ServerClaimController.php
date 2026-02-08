<?php

namespace App\Controllers;

use App\Models\Server;
use App\Core\Features\ServerClaim;

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
    public function show($id): void
    {
        if (!isLoggedIn()) {
            flash('error', lang('logged_in_action'));
            redirect('/login');
        }

        $server = Server::find((int) $id);
        $result = ServerClaim::getShowPageData(auth(), $server);

        if (!$result['success']) {
            if (isset($result['error'])) {
                flash('error', $result['error']);
            }
            redirect($result['redirect']);
        }

        view('servers.claim', [
            'server' => $result['server'],
            'is_effective_owner' => $result['is_effective_owner'],
            'is_pending' => $result['is_pending'],
            'is_requestor' => $result['is_requestor']
        ]);
    }

    /**
     * Start a new claim by generating a short-lived verification token.
     */
    public function start($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $result = ServerClaim::startClaim((int) $id, auth()->id);

        if (!$result['success']) {
            flash('error', $result['error']);
            $server = $result['server'];
            if ($server) {
                redirect(url("/server/{$server->address}:{$server->port}"));
            }
            redirect('/servers');
        }

        $server = $result['server'];
        flash('success', $result['message']);
        redirect(url("/server-claim/{$server->id}"));
    }

    /**
     * Verify ownership by pinging the server and checking the token in the MOTD.
     */
    public function verify($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $result = ServerClaim::verifyClaim((int) $id, auth()->id);
        $server = $result['server'];

        if (!$result['success']) {
            flash('error', $result['error']);
            if ($server) {
                if (strpos($result['error'], lang('access_denied')) !== false) {
                    redirect(url("/server/{$server->address}:{$server->port}"));
                }
                redirect(url("/server-claim/{$server->id}"));
            }
            redirect('/servers');
        }

        flash('success', $result['message']);
        redirect(url("/server/{$server->address}:{$server->port}"));
    }

    /**
     * Cancel an in-progress claim.
     */
    public function cancel($id): void
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $result = ServerClaim::cancelClaim((int) $id, auth()->id);
        $server = $result['server'];

        if (!$result['success']) {
            flash('error', $result['error']);
            if ($server) {
                redirect(url("/server/{$server->address}:{$server->port}"));
            }
            redirect('/servers');
        }

        flash('success', $result['message']);
        redirect(url("/server/{$server->address}:{$server->port}"));
    }
}
