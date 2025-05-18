<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('estado');      //1 - activo; 0 - inactivo ...
            $table->integer('color');      //1 - activo; 0 - inactivo ...
            $table->integer('ver_por_defecto');      //1 - blanco; 0 - negro ...
            $table->integer('opciones');    //Ordenamiento, etc
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
        Schema::dropIfExists('grupo_cajas');
    }
}
