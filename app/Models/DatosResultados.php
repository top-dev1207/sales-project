<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosResultados extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }
}
