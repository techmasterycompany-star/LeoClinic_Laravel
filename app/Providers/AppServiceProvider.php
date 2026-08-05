<?php

namespace App\Providers;

use App\Mail\CustomMailManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->extend('mail.manager', function ($manager, $app) {
            if ($manager instanceof CustomMailManager) {
                return $manager;
            }

            return new CustomMailManager($app);
        });
    }
}
