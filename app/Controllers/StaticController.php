<?php

namespace App\Controllers;

/**
 * Static pages controller.
 *
 * Routes that only render static templates (terms, privacy, etc.).
 */
class StaticController
{
    /**
     * Render terms of service page.
     */
    public function termsOfService(): void
    {
        view('static.terms-of-service');
    }

    /**
     * Render privacy policy page.
     */
    public function privacyPolicy(): void
    {
        view('static.privacy-policy');
    }
}
