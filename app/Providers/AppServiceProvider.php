<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;

use Config;
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
        $file      = [
            'login'        => \Storage::disk('public')->get('login.txt'),
            'contributor'  => []
        ];
        $loginList        = empty($file['login']) ? [] : explode(",", $file['login']);
        $contributorList  = $file['contributor']; 
        
        // Set login config
        Config::set('login.email', $loginList);
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
