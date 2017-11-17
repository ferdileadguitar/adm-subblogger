<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;

use Cache;
use App\Post;

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
        \Config::set('login.email', explode(",", \Storage::disk('public')->get('login.txt')));

        // $data = Cache::tags(['env:local', 'mmf:2'])->get("2///1////1/50");
        // dd( $data );
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
