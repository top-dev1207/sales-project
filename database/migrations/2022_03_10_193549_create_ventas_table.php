<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_venta')->unsigned();                //ID para busquedas de historial del documento
            $table->integer('indice_venta')->unsigned();             //ID para busquedas de historial del documento

            $table->date('fecha_venta');
            $table->integer('turno')->unsigned();
            $table->integer('clima')->unsigned();
            $table->integer('users_id')->unsigned();            //user ID, tomo el email, o el nombre, para que no dependa solo de la tabla users, por si se rompe

            $table->float('ingresos', 11, 2)->nullable();
            $table->float('anulaciones', 11, 2)->unsigned()->nullable();
            $table->float('egresos', 11, 2)->unsigned();

            $table->float('ventas_fiscal', 11, 2)->unsigned()->nullable();
            $table->float('ventas_no_fiscal', 11, 2)->unsigned()->nullable();

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

            $table->float('venta_alimentos', 11, 2)->unsigned()->nullable();
            $table->float('venta_bebidas', 11, 2)->unsigned()->nullable();

            $table->float('art1', 11, 2)->unsigned()->nullable();
            $table->float('art2', 11, 2)->unsigned()->nullable();
            $table->float('art3', 11, 2)->unsigned()->nullable();
            $table->float('art4', 11, 2)->unsigned()->nullable();
            $table->float('art5', 11, 2)->unsigned()->nullable();
            $table->float('art6', 11, 2)->unsigned()->nullable();
            $table->float('art7', 11, 2)->unsigned()->nullable();
            $table->float('art8', 11, 2)->unsigned()->nullable();
            $table->float('art9', 11, 2)->unsigned()->nullable();
            $table->float('art10', 11, 2)->unsigned()->nullable();
            $table->float('art11', 11, 2)->unsigned()->nullable();
            $table->float('art12', 11, 2)->unsigned()->nullable();

            $table->string('obs')->nullable();
            $table->integer('caja');    //Id de Caja en la cual se realiza la carga del Z
            $table->integer('estado_venta')->unsigned();      // 1 para validar, 2 validado, 3 a verificar, (??? 4 rechazado, 5 anulado, 6 borrado ???)

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
        Schema::dropIfExists('ventas');
    }
}
