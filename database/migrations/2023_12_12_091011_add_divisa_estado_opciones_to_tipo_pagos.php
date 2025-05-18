<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDivisaEstadoOpcionesToTipoPagos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_pagos', function (Blueprint $table) {
            $table->string('divisa')->nullable();       
            $table->integer('estado')->nullable();  
            $table->integer('opciones')->nullable();       
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
            $table->dropColumn('divisa');       
            $table->dropColumn('estado');  
            $table->dropColumn('opciones');       
        });
    }
}
