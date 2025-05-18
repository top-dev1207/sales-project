<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormaPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forma_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->integer('estado');      //1 - activo; 0 - inactivo ...
            $table->integer('fiscal');      //1 - blanco; 0 - negro ...
            $table->integer('opciones');    //XXX0 - impacta en caja;   XXX1 - No impacta en caja
                                            //XX1X - Medio Electrónico; XX0X - Medio No electrónico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forma_pagos');
    }
}
