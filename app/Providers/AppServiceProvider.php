<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {   
        // Set sesion name diff keepo.me
        // \Config::set('session.cookie', 'laravel_session_admin');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
