<?php

namespace App\Controllers;

use App\Models\Server;
use App\Models\Category;

class SitemapController
{
    public function xml()
    {
        header('Content-Type: application/xml; charset=utf-8');
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');
        $sitemap .= $this->addUrl(url('/servers'), '0.9', 'daily');
        $sitemap .= $this->addUrl(url('/submit'), '0.7', 'weekly');
        $sitemap .= $this->addUrl(url('/login'), '0.5', 'monthly');
        $sitemap .= $this->addUrl(url('/register'), '0.5', 'monthly');
        
        $categories = Category::getAll();
        foreach ($categories as $category) {
            $sitemap .= $this->addUrl(url("/category/{$category->url}"), '0.8', 'daily');
        }
        
        $servers = $this->getActiveServers();
        foreach ($servers as $server) {
            $sitemap .= $this->addUrl(url("/server/{$server->address}:{$server->port}"), '0.6', 'weekly');
        }
        
        $sitemap .= '</urlset>';
        
        echo $sitemap;
    }

    private function addUrl($url, $priority = '0.5', $changefreq = 'weekly', $lastmod = null)
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        
        if ($lastmod) {
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        }
        
        $xml .= "  </url>\n";
        
        return $xml;
    }

    private function getActiveServers()
    {
        return Server::getAll(['limit' => 1000]);
    }
}
