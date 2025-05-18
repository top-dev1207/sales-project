<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjetivoVentas extends Model
{
    use HasFactory;
    
    protected $table = 'objetivos_ventas';
    
    protected $fillable = [
        'month',
        'year',
        'monto',
        'descripcion'
    ];
}
