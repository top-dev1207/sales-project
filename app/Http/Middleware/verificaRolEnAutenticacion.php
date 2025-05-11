<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Facturas;

class verificaRolEnAutenticacion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //dd(Facturas::findOrFail($request->id)->users_id);
        $user = auth()->user(); 
        if($user->hasRole('Cajeros')){
            //Los cajeros no pueden ver un documento que no los hayan editado ellos. 
            $factura = Facturas::findOrFail($request->id);

            if($factura->users_id != $user->id && $factura->asignadoA != $user->id)     //solo ve los que genero o los que les asignaron (a verificar)
                return abort(401);
        }  
                  
        return $next($request);
    }
}
