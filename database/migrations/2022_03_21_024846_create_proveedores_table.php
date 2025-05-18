<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_proveedor')->unsigned();
            $table->string('nombre');
            $table->string('razonSocial')->nullable();
            $table->string('direccion')->nullable();
            $table->string('cuit')->nullable();
            $table->string('iva');
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->integer('clasInsumos');
            $table->text('obs')->nullable();
            $table->integer('estado')->unsigned();  //Activo, Pausado, Deuda, etc
            $table->integer('estadoInterno')->unsigned();  //Editado, borrado, alta, etc.  Para no borrar registros
            $table->integer('users_id')->unsigned();    //Usuario que edito
            $table->boolean('oculto');    
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
        Schema::dropIfExists('proveedores');
    }
}
