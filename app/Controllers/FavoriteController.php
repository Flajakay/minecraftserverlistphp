<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Favorite;
use App\Core\Auth;

class FavoriteController
{
    public function toggle()
    {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in']);
            return;
        }

        $serverId = (int)($_POST['server_id'] ?? 0);
        $userId = auth()->id;

        $server = Server::find($serverId);
        if (!$server) {
            echo json_encode(['success' => false, 'message' => 'Server not found']);
            return;
        }

        $result = Favorite::toggle($userId, $serverId);
        
        if ($result) {
            $updatedServer = Server::find($serverId);
            echo json_encode(['success' => true, 'action' => $result, 'new_count' => $updatedServer->favorites]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update favorite']);
        }
    }
}
