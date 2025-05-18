<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->integer('movimiento')->unsigned();                //ID para busquedas de historial del documento
            $table->integer('origen')->unsigned();             
            $table->integer('destino')->unsigned();             
            $table->float('importeOrigen', 11, 2)->nullable();       
            $table->float('tipoCambio')->nullable();       
            $table->float('importeDestino', 11, 2)->nullable();       
            $table->integer('users_id')->unsigned();            
            $table->string('obs')->nullable();
            $table->bigInteger('idMovimiento')->unsigned();            
            $table->integer('estado')->unsigned();       // 1 y 2 cargado, 3 validado, 4 rechazado, 5 anulado, 6 borrado
            $table->datetime('fecha');       // 1 y 2 cargado, 3 validado, 4 rechazado, 5 anulado, 6 borrado
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
        Schema::dropIfExists('transacciones');
    }
}
