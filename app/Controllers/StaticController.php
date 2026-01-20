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
    public function termsOfService()
    {
        view('static.terms-of-service');
    }

    /**
     * Render privacy policy page.
     */
    public function privacyPolicy()
    {
        view('static.privacy-policy');
    }
}
