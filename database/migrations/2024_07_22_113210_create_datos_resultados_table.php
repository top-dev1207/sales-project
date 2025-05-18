<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosResultadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datos_resultados', function (Blueprint $table) {
            $table->id();
            $table->float(  'iibb',                 11, 2)  ->nullable();       
            $table->float(  'iva',                  11, 2)  ->nullable();       
            $table->float(  'impuestoGanancias',    11, 2)  ->nullable();       
            $table->float(  'ingresoPropietarios',  11, 2)  ->nullable();       
            $table->float(  'inversiones',          11, 2)  ->nullable();       
            $table->float(  'pagoDeudaAtrasada',    11, 2)  ->nullable();
            $table->float(  'retiroDividendos',     11, 2)  ->nullable();       
            $table->float(  'gastosCtaCte',         11, 2)  ->nullable();       
            $table->integer('estado')                       ->unsigned();   // 1 cargado, 2 , 3 validado, 4 rechazado, 5 anulado, 6 borrado
            $table->string( 'obs')                          ->nullable();
            $table->date (  'periodo')                                  ;
            $table->integer('users_id')                     ->unsigned();   //Quien realiza/modifica el dato           
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
        Schema::dropIfExists('datos_resultados');
    }
}
