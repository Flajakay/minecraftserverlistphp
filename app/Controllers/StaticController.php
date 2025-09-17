<?php

namespace App\Controllers;

class StaticController
{
    public function termsOfService()
    {
        view('static.terms-of-service');
    }

    public function privacyPolicy()
    {
        view('static.privacy-policy');
    }
}
