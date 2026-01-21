<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Vote;
use App\Core\Security\Auth;
use App\Core\Integrations\Votifier;
use App\Core\System\Database;

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
        
        $server = Server::find($serverId);
        if (!$server) {
            echo json_encode(['success' => false, 'message' => 'Server not found']);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!Vote::canVote($serverId, $ip)) {
            echo json_encode(['success' => false, 'message' => 'You can only vote once per day']);
            return;
        }

        $customData = json_decode($server->custom_data ?? '{}', true);
        
        if (!empty($customData['votifier_public_key']) && !empty($username)) {
            $votifierIp = $customData['votifier_ip'] ?? $server->address;
            $votifierPort = $customData['votifier_port'] ?? 8192;
            
            if (!Votifier::sendVote($customData['votifier_public_key'], $votifierIp, $votifierPort, $username)) {
                echo json_encode(['success' => false, 'message' => 'Votifier error occurred']);
                return;
            }
        }

        Vote::create($serverId, $ip, $username);
        Server::addVote($serverId);

        echo json_encode(['success' => true, 'message' => 'Vote recorded successfully']);
    }
}
