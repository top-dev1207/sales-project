<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_documento')->unsigned();                //ID para busquedas de historial del documento
            $table->integer('indice_documento')->unsigned();             //ID para busquedas de historial del documento

            $table->integer('users_id')->unsigned();            //user ID, tomo el email, o el nombre, para que no dependa solo de la tabla users, por si se rompe
            $table->date('fecha_limite');                       //Plazo para la resolucion 
            //$table->string('asignado_a');                       //Sector destino / Cierre

            $table->date('fecha_factura')->nullable();                               
            $table->date('fecha_remito')->nullable();                               
            $table->string('nro_factura')->nullable();            
            $table->string('nro_remito')->nullable();            
            $table->integer('proveedor');       
            $table->integer('rubro')->unsigned()->nullable();         
            $table->integer('condicionIVA')->unsigned()->nullable();         

            $table->float('bruto', 11, 2)->nullable();       
            $table->float('iva')->unsigned()->nullable();        
            $table->float('total', 11, 2)->nullable();         
            $table->float('impuestos', 11, 2)->nullable();         
            //$table->integer('otros_impuestos')->unsigned()->nullable();         

            $table->string('obs')->nullable();

            $table->boolean('existe_remito');   //swicht para indicar si se llenan los datos
            $table->boolean('existe_factura');  //swicht para indicar si se llenan los datos
            $table->boolean('pagado');          // 1 pagado, 0 sin pagar //Lo hago booleano y lo saco del estado del documento
                                                //porque puede haber pagado el encargado, sin que se valide el documento
            
            $table->integer('tipoPago')->unsigned()->nullable();   // Ver listado de cajas en tabla
            $table->integer('estado_remito')->unsigned();       // 1 sin cargar, 2 carga remito, 3 validado, 4 rechazado, 5 anulado, 6 borrado
            $table->integer('estado_factura')->unsigned();     // 1 sin cargar, 2 carga factura,3 validado, 4 rechazado, 5 anulado, 6 borrado

            $table->integer('estadoDocumento')->unsigned(); // 1 cargada, 2 pagada, 3 rechazada, 4 anulada
            //  0='Para Validar',1='Validado',2='A verificar',3='Pagado',4='Cerrado',5='Borrado',6='Anulado'

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
        Schema::dropIfExists('facturas');
    }
}
