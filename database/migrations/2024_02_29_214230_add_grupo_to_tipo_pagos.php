<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGrupoToTipoPagos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_pagos', function (Blueprint $table) {
            $table->integer('grupo')->nullable();        //Nivel de privilegio para carga, segun gerente o encargado
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_pagos', function (Blueprint $table) {
            $table->dropColumn('grupo');  
        });
    }
}
