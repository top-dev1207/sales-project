<?php

namespace App\Http\Middleware;

use App\Models\TipoPago;
use Closure;
use Illuminate\Http\Request;

class VerificaCuentaPorRol
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
        $id = $request->id;
        //dd($id);
        $user = auth()->user();    
        //dd($request->path());
        //Verifico que exista la cuenta
        //$f = TipoPago::findOrFail($id);
        //    dd($f);

        /*
        --  -------------------------------
        id  cuenta
        --  -------------------------------
        1	Caja chica Arena	
        2	Caja chica Templo	
        3	Caja Gerente Arena	
        4	Caja mayor Arena	
        5	Caja mayor Templo	
        6	Caja Administración	
        7	Caja chica Administración	
        8	Caja Gerencia Administración $ Temple	
        9	Caja Gerencia Administración $ Cervelar	
        90	Caja Gerencia Administracion U$S Temple
        91	USD Administración	
        92	Caja Gerencia Administracion U$S Cervelar
        100	Facturas	
        101	Ventas	
        102	Dif Arqueo	
        200	Retiro Socios	
        201	Otros
        --  -------------------------------
        */
        
        /*
        ---------------------------------
        Nombre de Roles:
        ---------------------------------
        Caja chica Arena                                
        Caja chica Templo                               
        Caja Gerente Arena                               
        Caja mayor Arena                                
        Caja mayor Templo                               
        Caja Administración                              
        Caja Administración USD                          
        Caja chica Administración                        
        Caja Gerencia Administración                     
        Caja Gerencia Administración USD  
        ---------------------------------
        */


        //dd(str_contains($request->path(), "6"));
        //dd(strpos($request->path(), 'cajas/movimientos/'));

        //Verifico roles
        switch($id){
            case 1:
                if($user->hasPermissionTo('Caja chica Arena')) return $next($request);
                else return abort(401);
            case 2:
                if($user->hasPermissionTo('Caja chica Templo')) return $next($request);
                else return abort(401);
            case 3:
                if($user->hasPermissionTo('Caja Gerente Arena')) return $next($request);
                else return abort(401);
            case 4:
                if($user->hasPermissionTo('Caja mayor Arena')) return $next($request);
                else return abort(401);
            case 5:
                if($user->hasPermissionTo('Caja mayor Templo')) return $next($request);
                else return abort(401);
            case 6:     //Analizo OPERAR y Ver la caja, según permisos de usuario
                if($user->hasPermissionTo('Caja Administración')) return $next($request);
                else {
                if(str_contains($request->path(), 'cajas/movimientos/') && $user->hasPermissionTo('Ver Caja Administración') )
                    return $next($request);
                else return abort(401);
                }
            case 7:
                if($user->hasPermissionTo('Caja chica Administración')) return $next($request);
                else{
                    if(str_contains($request->path(), 'cajas/movimientos/') && $user->hasPermissionTo('Ver Caja chica Administración') )
                    return $next($request);
                else return abort(401);
                }
            case 8:
            case 9:
                if($user->hasPermissionTo('Caja Gerencia Administración')) return $next($request);
                else return abort(401);
            case 20:
                if($user->hasPermissionTo('Banco')) return $next($request);
                else return abort(401);
            case 10:
                if($user->hasPermissionTo('MercadoPago')) return $next($request);
                else return abort(401);
            case 90:
            case 92:
                if($user->hasPermissionTo('Caja Gerencia Administración USD')) return $next($request);
                else return abort(401);
            case 91:
                if($user->hasPermissionTo('Caja Administración USD')) return $next($request);
                else return abort(401);
            
            case ($id>=100 && $id<=102):    //Facturas Ventas
                if($user->hasAnyRole( ['developer','Gerente_Administrativo','Administración_Validador'])) return $next($request);
                else return abort(401);

            case ($id>=200 && $id<=204):    //Dif Arqueos, Retiros, Socios
                if($user->hasAnyRole( ['developer','Gerente_Administrativo'])) return $next($request);
                else return abort(401);
                

            /*   
            case ($id>=4):
                    //verifico por Administrador
                if($user->hasAnyRole( ['developer','Gerente_Administrativo','Admninistración'])) return $next($request);
                else return abort(401);
                break;
                
            case ($id>0 && $id<4):
                    //verifico por Encargado
                if($user->hasAnyRole( ['developer','Gerente_Administrativo','Admninistración','Gerente_Local'])) return $next($request);
                else return abort(401);
                break;
            */
            default:
                return abort(401);

        }

    }
}
