<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatedUpContentsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->tinyInteger('is_up_contents')->default(0)->after('channel_id');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('posts')) {
            Schema::table('posts', function(Blueprint $table) {
                // if( Schema::hasColumn('is_up_contents') ){
                $table->dropColumn('is_up_contents');
                // }
                // else{
                    // die('Columns doesn\'t exists!');
                // }
            });
        }
    }
}
