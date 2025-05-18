<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarDatosProveedores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proveedores', function (Blueprint $table){
            $table->boolean('dBancarios')->nullable();  //los pongo nulleable porque agrego     
            $table->string('banco')->nullable();        //los campos a tabla existente
            $table->string('cuenta')->nullable();  
            $table->string('cbu')->nullable();  
            $table->string('alias')->nullable();      
        });

    }    
            

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proveedores', function (Blueprint $table){
            $table->dropColumn('dBancarios');    
            $table->dropColumn('banco');  
            $table->dropColumn('cuenta');  
            $table->dropColumn('cbu');  
            $table->dropColumn('alias');      
        });


    }     
    
}
