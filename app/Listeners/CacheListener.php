<?php

namespace App\Listeners;

use App\Events\KeepoCache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Cache;

class CacheListener extends KeepoCache
{
    public $keepoTags = [];
    public $env       = null;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

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
        $this->env = $event->env;

        foreach ($event->post->toArray() as $item) 
        { $this->flushCache($item); }
    }


    private function flushCache($data = null)
    {
        $tags = [
            // Dashboard
            'mypost' => [
                'env:'.$this->env,
                'mmf:'.$data['user_id']
            ],
            'gfa' => [
                'env:'.$this->env,
                'gfa:'
            ],

            // // Detail
            'detailPost' => [
                'env:'.$this->env,
                'ldp',
                'ldp:'.$data['slug']
            ],

            // // Homepage?
            'feedPost' => [
                'env:'.$this->env,
                'mpl:'
            ],

            // Homepage?
            'hmp'  => [
                'env:'.$this->env,
                'hmp:'.$data['post_type']
            ]
        ];
        // dd( $tags );

        // Flush Cache
        foreach ($tags as $cacheTags) {
            Cache::tags($cacheTags)->flush();
        }
    }
}
