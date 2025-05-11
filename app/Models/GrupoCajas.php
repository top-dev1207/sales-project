<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoCajas extends Model
{
    use HasFactory;
    public function cajas()
    {
        return $this->hasMany('App\Models\TipoPago');
    }
}
