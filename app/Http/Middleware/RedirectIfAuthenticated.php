<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Pruebas ML:  pasa por aquí al poner localhost:8000/login
         if (Auth::guard($guard)->check()) {

        //     if(auth()->user()->hasAnyPermission('ver_accidentes'))
        //         return redirect('/accidentes/listar');

        //     if(auth()->user()->hasAnyPermission('ver_reclamos'))
                 return redirect('/');            
         }

        return $next($request);       //Original ML
        //return redirect('/pasa_por_else_RedirectIfAuthenticated');
    }
}
