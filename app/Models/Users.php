<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    use SoftDeletes;

    /**
     * Get the notes for the users.
     */
    public function notes()
    {
        return $this->hasMany('App\Models\Notes');
    }
    public function reclamos()
    {
        return $this->hasMany('App\Models\Reclamos');
    }
    public function transacciones()
    {
        return $this->hasMany('App\Models\Transacciones');
    }

    public function areas()
    {
        return $this->belongsTo('App\Models\Areas', 'area_id');      //(clase a relacionar, columna local, columna dest)
    }

    protected $dates = [
        'deleted_at'
    ];
}