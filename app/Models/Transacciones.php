<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacciones extends Model
{
    use HasFactory;
        //protected $table = 'reclamos'       //Mauricio
        public function user()
        {
            return $this->belongsTo('App\User', 'users_id')->withTrashed();
        }
    
        public function status()
        {
            return $this->belongsTo('App\Models\Estado_Carga', 'estado');      //(clase a relacionar, columna local, columna dest)
        }
    
        public function movimiento_r()
        {
            return $this->belongsTo('App\Models\Movimientos', 'movimiento', 'id');
        }
        public function origen_r()
        {
            return $this->belongsTo('App\Models\TipoPago', 'origen', 'id');
        }
        public function destino_r()
        {
            return $this->belongsTo('App\Models\TipoPago', 'destino', 'id');
        }
}
