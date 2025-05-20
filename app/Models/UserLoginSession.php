<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginSession extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'session_duration',
        'ip_address',
        'user_agent'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'session_duration' => 'integer',
    ];

    /**
     * Obtener el usuario al que pertenece esta sesión.
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}