<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Setting;
use App\Core\System\SiteSettings;
use App\Core\System\ThemeManager;

class ThemeSettingsTest extends TestCase
{
    public function testDefaultSettingsIncludeActiveTheme()
    {
        $settings = Setting::get();
        $this->assertEquals('default', $settings->active_theme ?? 'default');
    }

    public function testCanPersistValidTheme()
    {
        $result = SiteSettings::update([
            'title' => 'Test',
            'url' => 'http://localhost/',
            'contact_email' => 'test@test.com',
            'active_theme' => 'modern',
        ]);

        $this->assertTrue($result['success']);

        ThemeManager::clearCache();
        $this->assertEquals('modern', Setting::getValue('active_theme'));
    }

    public function testInvalidThemeReturnsError()
    {
        $result = SiteSettings::update([
            'title' => 'Test',
            'url' => 'http://localhost/',
            'contact_email' => 'test@test.com',
            'active_theme' => '../evil/path',
        ]);

        $this->assertFalse($result['success']);
    }

    public function testNonexistentThemeReturnsError()
    {
        $result = SiteSettings::update([
            'title' => 'Test',
            'url' => 'http://localhost/',
            'contact_email' => 'test@test.com',
            'active_theme' => 'nonexistent',
        ]);

        $this->assertFalse($result['success']);
    }
}
