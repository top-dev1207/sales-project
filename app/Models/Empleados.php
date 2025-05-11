<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleados extends Model
{
    use HasFactory;
        
    public function puesto_r()
    {
        return $this->belongsTo('App\Models\Puestos', 'puesto');      //(clase a relacionar, columna local, columna dest)
    }
    

    public function estado_r()
    {
        return $this->belongsTo('App\Models\EstadoEmpleado', 'estado', 'id');      //(clase a relacionar, columna local, columna dest)
    }

    
    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }
}