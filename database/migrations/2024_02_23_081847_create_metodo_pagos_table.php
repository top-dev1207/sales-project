<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetodoPagosTable extends Migration
{
    /**
     * Lo uso para "Pagos a Proveedor", pagos salientes
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metodo_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->integer('estado');      //1 - activo; 0 - inactivo ...
            $table->integer('fiscal');      //1 - blanco; 0 - negro ...
            $table->string('divisa');       

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
        Schema::dropIfExists('metodo_pagos');
    }
}
