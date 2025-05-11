<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado_Documento extends Model
{
    use HasFactory;
    public function estadoDocumentoNombre()
    {
        return $this->belongsTo('App\Models\Estado_Documento', 'id','estado');      //(clase a relacionar, columna local, columna dest)
    }
}
