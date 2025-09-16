<?php

namespace App\Controllers;

class RobotsController
{
    public function txt()
    {
        header('Content-Type: text/plain; charset=utf-8');
        
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /settings/\n";
        $robots .= "Disallow: /my-servers\n";
        $robots .= "Disallow: /my-favorites\n";
        $robots .= "Disallow: /edit-server/\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        echo $robots;
    }
}
