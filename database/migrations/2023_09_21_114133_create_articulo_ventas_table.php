<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticuloVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articulo_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');  //Nombre descriptivo
            $table->integer('estado');      //1 - activo; 0 - inactivo ...
            $table->integer('tipo');        //1 - Bebidas, 2 - alimentos, 3 - Promos ...
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
        Schema::dropIfExists('articulo_ventas');
    }
}
