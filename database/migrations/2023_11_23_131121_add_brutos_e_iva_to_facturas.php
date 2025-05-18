<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrutosEIvaToFacturas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->float('bruto2', 11, 2)->nullable();       
            $table->float('iva2')->unsigned()->nullable();  
            $table->float('bruto3', 11, 2)->nullable();       
            $table->float('iva3')->unsigned()->nullable();  
            $table->float('bruto4', 11, 2)->nullable();       
            $table->float('iva4')->unsigned()->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('bruto2');       
            $table->dropColumn('iva2');  
            $table->dropColumn('bruto3');       
            $table->dropColumn('iva3');  
            $table->dropColumn('bruto4');       
            $table->dropColumn('iva4');          
        });
    }
}
