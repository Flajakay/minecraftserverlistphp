<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Setting;

class SettingsTest extends TestCase
{
    /**
     * Test retrieving default settings from the test database.
     */
    public function testCanRetrieveDefaultSettings()
    {
        $settings = Setting::get();

        $this->assertNotNull($settings);
        $this->assertEquals(1, $settings->id);
        $this->assertEquals('Minecraft Server List', $settings->title);
        $this->assertEquals('admin@admin.com', $settings->contact_email);
    }

    /**
     * Test updating settings in the database.
     */
    public function testCanUpdateSettings()
    {
        // Assert initial value
        $this->assertEquals('Minecraft Server List', Setting::getValue('title'));

        // Update settings in database
        $updated = Setting::update([
            'title' => 'Updated Test Server List',
            'contact_email' => 'updated-admin@example.com'
        ]);

        $this->assertTrue($updated->rowCount() > 0);

        // Re-fetch settings - cache is automatically cleared by Setting::update()
        $settings = Setting::get();
        $this->assertEquals('Updated Test Server List', $settings->title);
        $this->assertEquals('updated-admin@example.com', $settings->contact_email);
        $this->assertEquals('Updated Test Server List', Setting::getValue('title'));
    }
}
