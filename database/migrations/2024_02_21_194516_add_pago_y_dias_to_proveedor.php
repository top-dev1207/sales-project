<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoYDiasToProveedor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->integer('diasCredito')->nullable();        //Nivel de privilegio para carga, segun gerente o encargado
            $table->integer('metodoPago')->nullable();        //Nivel de privilegio para carga, segun gerente o encargado
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('diasCredito');  
            $table->dropColumn('metodoPago');  
        });
    }
}
