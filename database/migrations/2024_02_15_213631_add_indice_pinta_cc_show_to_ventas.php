<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndicePintaCcShowToVentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->float('derecho_show', 11, 2)->nullable()->nullable();       
            $table->float('cuenta_corriente', 11, 2)->unsigned()->nullable();        
            $table->float('indice_pinta', 11, 2)->unsigned()->nullable();
      
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
            $table->dropColumn('derecho_show');  
            $table->dropColumn('cuenta_corriente');  
            $table->dropColumn('indice_pinta');  
        });
    }
}
