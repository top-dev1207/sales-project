<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetalleCajaStudioToVentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Nuevo campo para almacenar "Studio" o "Bosque" cuando la caja es "Studio"
            // Se añade después del campo 'caja' para mantener un orden lógico.
            $table->float('valor_studio_caja_chica', 11, 2)->nullable();
            $table->float('valor_bosque_caja_chica', 11, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('valor_studio_caja_chica');
            $table->dropColumn('valor_bosque_caja_chica');
        });
    }
}
