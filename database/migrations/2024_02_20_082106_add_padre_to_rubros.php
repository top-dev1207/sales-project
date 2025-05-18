<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPadreToRubros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rubros', function (Blueprint $table) {
            $table->integer('orden');        //Nivel de privilegio para carga, segun gerente o encargado
            $table->integer('padre');        //Nivel de privilegio para carga, segun gerente o encargado
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rubros', function (Blueprint $table) {
            $table->dropColumn('orden');  
            $table->dropColumn('padre');  
        });
    }
}
