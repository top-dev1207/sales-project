<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
    use HasFactory;
    
    public function iva_r()
    {
        return $this->belongsTo('App\Models\TipoIVA', 'iva', 'valor');      //(clase a relacionar, columna local, columna dest)
    }

    public function rubro()
    {
        return $this->belongsTo('App\Models\Rubro', 'clasInsumos', 'valor');      //(clase a relacionar, columna local, columna dest)
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }

}
