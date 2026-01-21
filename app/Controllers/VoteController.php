<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Vote;
use App\Core\Security\Auth;
use App\Core\Integrations\Votifier;
use App\Core\System\Database;
use App\Core\Features\Votes;

/**
 * Voting controller.
 *
 * Records daily votes for a server. Optionally forwards the vote to the server via Votifier
 * when a public key and username are provided.
 */
class VoteController
{
    /**
     * Cast a vote.
     *
     * JSON-only endpoint.
     */
    public function vote()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to vote']);
            return;
        }

        $serverId = (int)($_POST['server_id'] ?? 0);
        $username = sanitize($_POST['username'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'];

        $result = Votes::castVote($serverId, $ip, $username);

        echo json_encode($result);
    }
}
