<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReciboSueldosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recibo_sueldos', function (Blueprint $table) {
            $table->id();
            $table->integer('nro')->unsigned();                //ID para busquedas de historial del recibo
            $table->integer('indice')->unsigned();             

            $table->integer('empleado')->unsigned();            
            //$table->string('asignado_a');                       //Sector destino / Cierre
            
            $table->float(  'base',         11, 2)  ->nullable();       
            $table->float(  'bruto',        11, 2)  ->nullable();       
            $table->float(  'presentismo',  11, 2)  ->nullable();       
            $table->float(  'aguinaldo',    11, 2)  ->nullable();       
            $table->float(  'plus',         11, 2)  ->nullable();       
            $table->float(  'feriados',     11, 2)  ->nullable();       
            $table->float(  'ausencias',    11, 2)  ->nullable();       
            $table->float(  'vacaciones',   11, 2)  ->nullable();       
            $table->float(  'banco',        11, 2)  ->nullable();       
            $table->float(  'cash',         11, 2)  ->nullable();       
            $table->float(  'adelantos',    11, 2)  ->nullable();       
            $table->float(  'redondeo',     11, 2)  ->nullable();
            $table->float(  'total',        11, 2)  ->nullable();
            $table->float(  'saldo',        11, 2)  ->nullable();
            
            $table->date (  'periodo')              ;                               
            $table->date (  'fechaRecibo')          ->nullable();                               
            $table->date (  'fechaDeposito')        ->nullable();                               
            $table->date (  'fechaCash')            ->nullable();                               
            $table->boolean('cumplePresentismo');   
            $table->boolean('cobraAguinaldo');   
            $table->integer('diasFeriado')          ->nullable();       
            $table->integer('diasAusente')          ->nullable();   
            $table->integer('diasVacaciones')       ->nullable();   
            
            
            $table->integer('users_id')->unsigned();                //Quien realiza/modifica el recibo          
            $table->string('obs')->nullable();

            $table->boolean('pagado');          // 1 pagado, 0 sin pagar //Lo hago booleano y lo saco del estado del documento
                                                //porque puede haber pagado el encargado, sin que se valide el documento
            $table->integer('tipoPago')->unsigned()->nullable();   // Ver listado de cajas en tabla
            
            $table->integer('estadoRecibo')->unsigned();       // 1 cargado, 2 VIP , 6 borrado
            $table->integer('asignadoA')->nullable();        //Hacia quien o que sector està asignada la factura/remito
            
            // Se generaran tantos registros como modificaciones del Documento, para trazabilidad de usuario/modificacion 
            // Como los reclamos.  Habrá Id documento con subindices.  Se pasará de sector a sector, como los reclamos
       
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
        Schema::dropIfExists('recibo_sueldos');
    }
}
