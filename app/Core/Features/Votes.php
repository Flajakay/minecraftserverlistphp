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
    public static function castVote(int $serverId, string $ip, string $username = ''): array
    {
        $server = Server::find($serverId);
        if (!$server) {
            return ['success' => false, 'message' => 'Server not found'];
        }

        // Enforce one vote per IP per 24 hours to prevent abuse
        if (!Vote::canVote($serverId, $ip)) {
            return ['success' => false, 'message' => 'You can only vote once per day'];
        }

        // Votifier delivery is only supported for Minecraft Java servers
        if ($server->protocol === 'minecraft_java') {
            $customData = json_decode($server->custom_data ?? '{}', true);
            
            // Send vote notification to game server via Votifier protocol if configured
            if (!empty($customData['votifier_public_key']) && !empty($username)) {
                // Use custom votifier IP if set, otherwise default to server address
                $votifierIp = $customData['votifier_ip'] ?? $server->address;
                $votifierPort = $customData['votifier_port'] ?? 8192;
                
                if (!Votifier::sendVote($customData['votifier_public_key'], $votifierIp, $votifierPort, $username)) {
                    return ['success' => false, 'message' => 'Votifier error occurred'];
                }
            }
        }

        Vote::create($serverId, $ip, $username);
        Server::addVote($serverId);

        return ['success' => true, 'message' => 'Vote recorded successfully'];
    }
}
