<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiposEgresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipos_egresos', function (Blueprint $table) {
            $table->id();
            $table->string('motivo');               //Alimentos, Luz, etc
            $table->integer('clasificacion');       //Materia prima, Servicios, Personal, Otros, etc
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
        Schema::dropIfExists('tipos_egresos');
    }
}
