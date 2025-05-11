<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimientos extends Model
{
    use HasFactory;
    public function transacciones()
    {
        return $this->hasMany('App\Models\Transacciones');
    }
}
