<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->integer('legajo')->unsigned();
            $table->integer('indice')->unsigned();  //Para no borrar registros
            $table->string('nombre', 25);
            $table->string('apellido', 20);
            $table->string('dni', 13);
            $table->string('cuit', 15)->nullable();
            $table->string('puesto', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('cel', 15)->nullable();
            $table->string('tel', 15)->nullable();
            $table->string('direccion', 35)->nullable();
            $table->string('cbu', 20)->nullable();
            $table->string('alias', 20)->nullable();
            $table->string('cuenta', 20)->nullable();
            $table->string('banco', 15)->nullable();
            $table->text('obs')->nullable();
            $table->integer('estado')->unsigned();  //1 = Activo, 2 = Suspendido, 3 = Juicio, 4= etc
            $table->integer('estadoInterno')->unsigned();  //1 = Activo, 6 = Borrado, etc
            $table->integer('users_id')->unsigned();    //Usuario que edito
            $table->float('salario', 11, 2)->unsigned()->nullable();        
            $table->date('fechaIngreso')->nullable();
            $table->date('fechaEgreso')->nullable();
            $table->integer('vacaciones')->unsigned()->nullable();  //Días de vacaciones
            $table->string('foto')->nullable();     //A futuro cargaré la foto
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
        Schema::dropIfExists('empleados');
    }
}
