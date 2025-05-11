<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'tipo'
    ];

    public function grupos()
    {
        return $this->belongsTo('App\Models\GrupoCajas', 'grupo','id');      //(clase a relacionar, columna local, columna dest)
    }
}
