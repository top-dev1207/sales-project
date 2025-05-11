<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Areas extends Model
{
    public function reclamos()
    {
        return $this->hasMany('App\Models\Reclamos');
    }

    public function user()
    {
        return $this->hasMany('App\User');
    }

}
