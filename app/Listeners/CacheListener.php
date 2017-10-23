<?php

namespace App\Listeners;

use App\Events\KeepoCache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Cache;

class CacheListener
{
    public $keepoTags = [];
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  KeepoCache  $event
     * @return void
     */
    public function handle(KeepoCache $event)
    {
        // Flush cache here 
        // Better i used {Eloquent Events} for handle every changes on database
        // Rather than use this ..
        $tags = [
            // Dashboard
            'mypost' => [
                'env:'.$event->env,
                'mmf:'.$event->post->user_id
            ],
            'gfa' => [
                'env:'.$event->env,
                'gfa:'
            ],

            // // Detail
            'detailPost' => [
                'env:'.$event->env,
                'ldp',
                'ldp:'.$event->post->slug
            ],

            // // Homepage?
            'feedPost' => [
                'env:'.$event->env,
                'mpl:'
            ],

            // Homepage?
            'hmp'  => [
                'env:'.$event->env,
                'hmp:'.$event->post->post_type
            ]
        ];

        // Flush Cache
        foreach ($tags as $cacheTags) {
            Cache::tags($cacheTags)->flush();
        }
    }
}
