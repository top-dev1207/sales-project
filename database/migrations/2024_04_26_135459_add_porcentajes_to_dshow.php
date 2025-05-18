<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPorcentajesToDshow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dshows', function (Blueprint $table) {
            $table->float('porcArtista', 11, 2)->unsigned()->nullable();         
            $table->float('porcTemple', 11, 2)->unsigned()->nullable();         
            $table->float('porcProduccion', 11, 2)->unsigned()->nullable();         
            $table->float('entradaTemple', 11, 2)->unsigned()->nullable();         
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
            $table->dropColumn('porcArtista');         
            $table->dropColumn('porcTemple');         
            $table->dropColumn('porcProduccion');         
            $table->dropColumn('entradaTemple');         
        });
    }
}
