<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReciboSueldo extends Model
{
    use HasFactory;
    // protected $primaryKey = ['empleado','periodo'];
    // public $incrementing = false;   //Lo pongo para que funcione el doble primary key anterior

    public function empleado_r()
    {
        return $this->belongsTo('App\Models\Empleados', 'empleado','legajo');      //(clase a relacionar, columna local, columna dest)
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }
}
