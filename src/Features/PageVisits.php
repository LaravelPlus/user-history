<?php

namespace LaravelPlus\UserHistory\Features;

use Illuminate\Support\Facades\Config;

class PageVisits
{
    public static function register(): void
    {
        // Page visit tracking is handled by the TrackUserActivity middleware
        // which should be registered in bootstrap/app.php for Laravel 11
    }
} 