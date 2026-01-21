<?php

namespace App\Controllers;

/**
 * robots.txt controller.
 *
 * Emits a simple robots.txt allowing public pages while discouraging indexing of user/admin areas.
 */
class RobotsController
{
    /**
     * Output robots.txt.
     */
    public function txt()
    {
        header('Content-Type: text/plain; charset=utf-8');
        
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /settings/\n";
        $robots .= "Disallow: /edit-server/\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        echo $robots;
    }
}
