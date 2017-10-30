<?php

use Illuminate\Database\Seeder;

class ViewLogEmbedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	factory(App\EmbedLog::class, 10)->create();
    }
}
