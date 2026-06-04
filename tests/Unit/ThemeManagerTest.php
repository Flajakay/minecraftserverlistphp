<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Core\System\ThemeManager;

class ThemeManagerTest extends TestCase
{
    public function testValidSlugPasses()
    {
        $this->assertTrue(ThemeManager::isValidSlug('default'));
        $this->assertTrue(ThemeManager::isValidSlug('modern'));
        $this->assertTrue(ThemeManager::isValidSlug('my-custom-theme'));
        $this->assertTrue(ThemeManager::isValidSlug('theme_v2'));
    }

    public function testInvalidSlugFails()
    {
        $this->assertFalse(ThemeManager::isValidSlug(''));
        $this->assertFalse(ThemeManager::isValidSlug('../etc/passwd'));
        $this->assertFalse(ThemeManager::isValidSlug('spaces not allowed'));
        $this->assertFalse(ThemeManager::isValidSlug('UPPERCASE'));
        $this->assertFalse(ThemeManager::isValidSlug('special!chars'));
    }

    public function testAvailableThemesIncludesDefault()
    {
        $themes = ThemeManager::availableThemes();
        $this->assertArrayHasKey('default', $themes);
        $this->assertEquals('Default', $themes['default']['name']);
    }

    public function testAvailableThemesIncludesModern()
    {
        $themes = ThemeManager::availableThemes();
        $this->assertArrayHasKey('modern', $themes);
    }

    public function testActiveSlugReturnsDefaultByDefault()
    {
        $slug = ThemeManager::activeSlug();
        $this->assertEquals('default', $slug);
    }

    public function testHasThemeReturnsTrueForExisting()
    {
        $this->assertTrue(ThemeManager::hasTheme('default'));
        $this->assertTrue(ThemeManager::hasTheme('modern'));
    }

    public function testHasThemeReturnsFalseForMissing()
    {
        $this->assertFalse(ThemeManager::hasTheme('nonexistent'));
    }

    public function testResolveViewResolvesFromDefaultTheme()
    {
        $resolved = ThemeManager::resolveView('home');
        $this->assertStringContainsString('resources/themes/default/views/home.php', $resolved);
        $this->assertFileExists($resolved);
    }

    public function testResolveViewResolvesNestedFromDefaultTheme()
    {
        $resolved = ThemeManager::resolveView('auth.login');
        $this->assertStringContainsString('resources/themes/default/views/auth/login.php', $resolved);
        $this->assertFileExists($resolved);
    }

    public function testResolveViewResolvesThemeOverride()
    {
        ThemeManager::clearCache();
        $resolved = ThemeManager::resolveView('partials.navbar');
        $this->assertFileExists($resolved);
    }

    public function testThemeAssetUrlReturnsCorrectPath()
    {
        $url = ThemeManager::themeAssetUrl('css/theme.css', 'default');
        $this->assertStringContainsString('/themes/default/css/theme.css', $url);
    }
}
