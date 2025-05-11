<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }
    public function clima_r()
    {
        return $this->belongsTo('App\Models\Climas', 'clima', 'valor');
    }
    public function turno_r()
    {
        return $this->belongsTo('App\Models\Turnos', 'turno', 'valor');
    }
    public function estado_r()
    {
        return $this->belongsTo('App\Models\Estado_Carga', 'estado_venta','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function caja_r()
    {
        return $this->belongsTo('App\Models\TipoPago', 'caja','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function show()
    {
        return $this->belongsTo('App\Models\Dshow', 'dshowId','id');      //(clase a relacionar, columna local, columna dest)
    }

}
