<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Storage;
use Config;

class AdminSettingsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        if(!Storage::disk('public')->exists('login.txt'))
        {
            $list = [
                'michael_rendy@hotmail.com', 'willytoba@gmail.com', 
                'feny@rocketmail.com', 'ferdi@keepo', 
                'ferdi@gmail.com', 'ferdiardiansa@rocketmail.com', 
                'nabila@yukepo.com'
            ];
            
            Storage::disk('public')->put('login.txt', implode(",", $list));
            
            die('Created');
        }
        
         // Set sesion name diff keepo.me
        $file      = [
            'login'        => Storage::disk('public')->get('login.txt'),
            'contributor'  => Storage::disk('public')->get('contributor.txt')
        ];

        $loginList        = empty($file['login']) ? [] : explode(",", $file['login']);
        $contributorList  = empty($file['contributor']) ? [] : explode(",", $file['contributor']); 
        
        // Set login config
        Config::set('login.email', $loginList);
        Config::set('list.contributor', $contributorList);

        // dd( \Request() );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // dd( 'admin' );
        //
    }
}
