<?php

namespace App\Controllers;

use App\Models\Server;

class BannerController
{
    public function show()
    {
        $serverId = $_GET['server_id'] ?? null;
        $server = null;
        
        if ($serverId) {
            $server = Server::find($serverId);
            if (!$server) {
                redirect('/');
                exit;
            }
        }
        
        $backgrounds = [
            'default' => 'Default Texture',
        ];
        
        view('servers.banner-generator', [
            'server' => $server,
            'backgrounds' => $backgrounds
        ]);
    }
}
