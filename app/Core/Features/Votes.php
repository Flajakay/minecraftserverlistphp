<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\Vote;
use App\Core\Integrations\Votifier;

/**
 * Feature class for handling server votes.
 */
class Votes
{
    /**
     * Cast a vote for a server.
     * 
     * @param int $serverId The ID of the server to vote for.
     * @param string $ip The IP address of the voter.
     * @param string $username Optional Minecraft username for Votifier.
     * @return array Result of the voting operation: ['success' => bool, 'message' => string].
     */
    public static function castVote(int $serverId, string $ip, string $username = ''): array
    {
        $server = Server::find($serverId);
        if (!$server) {
            return ['success' => false, 'message' => 'Server not found'];
        }

        if (!Vote::canVote($serverId, $ip)) {
            return ['success' => false, 'message' => 'You can only vote once per day'];
        }

        $customData = json_decode($server->custom_data ?? '{}', true);
        
        if (!empty($customData['votifier_public_key']) && !empty($username)) {
            $votifierIp = $customData['votifier_ip'] ?? $server->address;
            $votifierPort = $customData['votifier_port'] ?? 8192;
            
            if (!Votifier::sendVote($customData['votifier_public_key'], $votifierIp, $votifierPort, $username)) {
                return ['success' => false, 'message' => 'Votifier error occurred'];
            }
        }

        Vote::create($serverId, $ip, $username);
        Server::addVote($serverId);

        return ['success' => true, 'message' => 'Vote recorded successfully'];
    }
}
