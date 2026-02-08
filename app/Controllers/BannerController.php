<?php

namespace App\Controllers;

use App\Models\Server;

/**
 * Banner generator controller.
 *
 * Renders a banner preview/generator page. When `server_id` is provided, the view is pre-filled
 * with server data; otherwise it loads in a blank state.
 */
class BannerController
{
    /**
     * Render the banner generator UI.
     */
    public function show(): void
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
