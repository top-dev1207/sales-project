<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemsToDshows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dshows', function (Blueprint $table) {
            $table->float(  'artista',      11, 2)  ->nullable();       
            $table->float(  'local',        11, 2)  ->nullable();       
            $table->float(  'produccion',   11, 2)  ->nullable();       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dshows', function (Blueprint $table) {
            $table->dropColumn('artista');       
            $table->dropColumn('local');              
            $table->dropColumn('produccion');       
        });
    }
}
