<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDshowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dshows', function (Blueprint $table) {
            $table->id();
            $table->string('show');         //Banda, show presentado      
            $table->string('tipo');         //Lo dejo para más adelante
            $table->string('obs');          //Comentarios - Observaciones

            $table->integer('opciones');    //XXX0 - impacta en caja;   XXX1 - No impacta en caja
                                            //XX1X - Medio Electrónico; XX0X - Medio No electrónico 
              
            //tomo las mismas características de las formas de pago configuradas para el Z                                
            $table->float('fp1', 11, 2)->unsigned()->nullable();         
            $table->float('fp2', 11, 2)->unsigned()->nullable();         
            $table->float('fp3', 11, 2)->unsigned()->nullable();         
            $table->float('fp4', 11, 2)->unsigned()->nullable();         
            $table->float('fp5', 11, 2)->unsigned()->nullable();         
            $table->float('fp6', 11, 2)->unsigned()->nullable();         
            $table->float('fp7', 11, 2)->unsigned()->nullable();         
            $table->float('fp8', 11, 2)->unsigned()->nullable();         
            $table->float('fp9', 11, 2)->unsigned()->nullable();         
            $table->float('fp10', 11, 2)->unsigned()->nullable();         
            $table->float('fp11', 11, 2)->unsigned()->nullable();         
            $table->float('fp12', 11, 2)->unsigned()->nullable();        
            $table->float('total', 11, 2)->unsigned()->nullable();        

            $table->bigInteger('idVenta')->unsigned();            //Id de la venta con la cual se enlaza
            $table->integer('estado')->unsigned();       // 1 y 2 cargado, 3 validado, 4 rechazado, 5 anulado, 6 borrado

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
        Schema::dropIfExists('dshows');
    }
}
