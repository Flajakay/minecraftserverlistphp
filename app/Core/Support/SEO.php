<?php

namespace App\Core\Support;

/**
 * Simple per-request SEO metadata store.
 *
 * Controllers/helpers populate values; views call `renderMetaTags()` to output standard
 * meta/OG/Twitter tags with sensible defaults.
 */
class SEO
{
    private static string $title = '';
    private static string $description = '';
    private static array $keywords = [];
    private static string $canonical = '';
    private static string $robots = 'index, follow';
    private static string $ogImage = '';

    public static function setTitle($title): void
    {
        self::$title = $title;
    }

    public static function setDescription($description): void
    {
        self::$description = $description;
    }

    public static function setKeywords($keywords): void
    {
        self::$keywords = is_array($keywords) ? $keywords : explode(',', $keywords);
    }

    public static function setCanonical($url): void
    {
        self::$canonical = $url;
    }

    public static function setRobots($robots): void
    {
        self::$robots = $robots;
    }

    public static function setOgImage($image): void
    {
        self::$ogImage = $image;
    }

    public static function getTitle()
    {
        $siteTitle = setting('title', 'Minecraft Server List');
        // If a page title is set, append the site title for consistency.
        return self::$title ? self::$title . ' - ' . $siteTitle : $siteTitle;
    }

    public static function getDescription()
    {
        return self::$description ?: setting('meta_description', 'Find the best Minecraft servers to play on');
    }

    public static function getKeywords(): string
    {
        return implode(', ', self::$keywords);
    }

    public static function getCanonical(): string
    {
        return self::$canonical ?: url($_SERVER['REQUEST_URI']);
    }

    public static function getRobots(): string
    {
        return self::$robots;
    }

    public static function getOgImage(): string
    {
        return self::$ogImage ?: asset('images/og-default.jpg');
    }

    public static function renderMetaTags(): string
    {
        $output = '';
        
        // Escape all values to prevent injection through settings/user content.
        $output .= '<title>' . htmlspecialchars(self::getTitle()) . '</title>' . "\n";
        $output .= '<meta name="description" content="' . htmlspecialchars(self::getDescription()) . '">' . "\n";
        
        if (!empty(self::$keywords)) {
            $output .= '<meta name="keywords" content="' . htmlspecialchars(self::getKeywords()) . '">' . "\n";
        }
        
        $output .= '<meta name="robots" content="' . htmlspecialchars(self::getRobots()) . '">' . "\n";
        $output .= '<link rel="canonical" href="' . htmlspecialchars(self::getCanonical()) . '">' . "\n";
        
        $output .= '<meta property="og:title" content="' . htmlspecialchars(self::getTitle()) . '">' . "\n";
        $output .= '<meta property="og:description" content="' . htmlspecialchars(self::getDescription()) . '">' . "\n";
        $output .= '<meta property="og:image" content="' . htmlspecialchars(self::getOgImage()) . '">' . "\n";
        $output .= '<meta property="og:url" content="' . htmlspecialchars(self::getCanonical()) . '">' . "\n";
        $output .= '<meta property="og:type" content="website">' . "\n";
        $output .= '<meta property="og:site_name" content="' . htmlspecialchars(setting('title', 'Minecraft Server List')) . '">' . "\n";
        
        $output .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $output .= '<meta name="twitter:title" content="' . htmlspecialchars(self::getTitle()) . '">' . "\n";
        $output .= '<meta name="twitter:description" content="' . htmlspecialchars(self::getDescription()) . '">' . "\n";
        $output .= '<meta name="twitter:image" content="' . htmlspecialchars(self::getOgImage()) . '">' . "\n";
        
        return $output;
    }

    public static function configureServerPage($server): void
    {
        self::setTitle($server->name);
        $description = sprintf(lang('seo.server_description'), $server->name, $server->address . ':' . $server->port, strip_tags($server->description));
        self::setDescription(substr($description, 0, 160));
        self::setKeywords(sprintf(lang('seo.keywords_server'), $server->name, $server->address));
        self::setCanonical(url("/server/{$server->address}:{$server->port}"));
    }

    public static function configureCategoryPage($category): void
    {
        self::setTitle($category->name . ' Minecraft Servers');
        $description = $category->description ?: sprintf(lang('seo.category_description'), $category->name);
        self::setDescription($description);
        self::setKeywords(sprintf(lang('seo.keywords_category'), $category->name, $category->name));
        self::setCanonical(url("/category/{$category->url}"));
    }

    public static function configureHomePage(): void
    {
        self::setTitle('');
        self::setDescription(lang('seo.home_description'));
        self::setKeywords(lang('seo.keywords_default'));
        self::setCanonical(url('/'));
    }
}
