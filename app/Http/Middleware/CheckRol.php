<?php

namespace App\Http\Middleware;

use Closure;

class CheckRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();             //Mauricio, editando, pero pase a Middleware de Spatie
        if($user->can('crear_y_ver_accidentes'))
            return $next($request);
        //return('Error de acceso');
        //return redirect()->route('login');
        //return route('login');
        else 
            return redirect('/');       //Este redirect funciona ok!!!
    }
}
