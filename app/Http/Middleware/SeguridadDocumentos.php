<?php

namespace App\Http\Middleware;

use App\Models\Empleados;
use Closure;
use Illuminate\Http\Request;
use App\Models\Facturas;
use App\Models\Ventas;
use App\Models\ReciboSueldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class SeguridadDocumentos
{   
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle(Request $request, Closure $next)
    {
        $d = explode("/", $request->path());        //facturas / pagar / id
        $id=  (int)$d[2];
        $operacion= $d[1];
        $tipo=      $d[0];

        
        //dd(Facturas::find($id)->estadoDocumentoR->estado);
        
        // Tipo puede ser o "ventas" o "facturas"
        //dump($request->path());
        //dump($request->id);     //93
        //dump($d);
        //dump($id);

        switch($tipo){
            case 'facturas':
                $factura =Facturas::find($id); 
                $NroDoc = $factura->nro_documento;    

                $rta = Facturas::selectRaw('max(id) as idMax, nro_documento')->where ('nro_documento','=',$NroDoc)->get();
                $idMax =$rta[0]->idMax;
                
                if($id < $idMax) {
                    //dd("Id menor t a IdMax.  No se puede dar esta condición");
                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
 
                    return redirect()->back()
                        ->with('mensaje','Error! Factura/Remito inexistente')
                        ->with('danger',"Error! Factura/Remito inexistente");                    
                }
                    //error
                else{
                    if($id == $idMax)   {//ok
                        //dd("Mismo ID! OK");
                        if($factura->estadoDocumento == 6){  //Documento borrado
                            if($operacion == "detalle")
                                return $next($request);     //Solo muestro el detalle del doc borrado    
                            else{
                                Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax y El documento está BORRADO | ");
 
                                return redirect()->back()
                                    ->with('mensaje','Error! Factura/Remito borrado')
                                    ->with('danger',"Error! Factura/Remito borrado"); 
                            }
                                
                        }
                        else
                            return $next($request);     //OK, muestro todo
                    }
                    else {
                        //dd("Id mayot a IdMax.  No se puede dar esta condición");
                        Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
                        return abort( 401 );        //Mauricio
                    }
                }
                break;

            case 'ventas':
                $venta = Ventas::find($id);
                $NroDoc = $venta->nro_venta;    

                $rta = Ventas::selectRaw('max(id) as idMax')->where ('nro_venta','=',$NroDoc)->get();
                $idMax =$rta[0]->idMax;
                
                if($id < $idMax)   {
                    //dd("Id menor a IdMax.  Documento anterior al actual");
                    //return abort( 401 );       
                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
 
                    return redirect()->back()
                        ->with('mensaje','Error! Venta inexistente')
                        ->with('danger',"Error! Venta inexistente");
                } 
                    //error
                else{
                    if($id == $idMax)  {//ok
                        //dd("Mismo ID! Verificar si está borrado");
                        if($venta->estado_venta == 6){ //Venta borrada
                            if($operacion == "detalle"){
                                return $next($request);     //Solo muestro el detalle del doc borrado    
                            }
                            else{
                                Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax y El documento está BORRADO | ");
 
                                return redirect()->back()
                                    ->with('mensaje','Error! Venta borrada')
                                    ->with('danger',"Error! Venta borrada"); 
                            }
                        }
                        else
                            return $next($request);
                    } 
                    else {  //id mayor a Ida Max???
                        //dd("Id mayor a IdMax.  No se puede dar nunca esta condición");
                        Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
                        return abort( 401 );        
                    }
                    //error
                }
                break;
            case 'empleados':
                $empleado = Empleados::find($id);
                $legajo = $empleado->legajo;    

                $rta = Empleados::selectRaw('max(id) as idMax')->where ('legajo','=',$legajo)->get();
                $idMax =$rta[0]->idMax;     //Obtengo el id Maximo del legajo en curso. 
                
                if($id < $idMax)   {
                    //dd("Id menor a IdMax.  Documento anterior al actual");
                    //return abort( 401 );       
                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
 
                    return redirect()->back()
                        ->with('mensaje','Error! Empleado inexistente')
                        ->with('danger',"Error! Empleado inexistente");
                } 
                    //error
                else{
                    if($id == $idMax)  {//ok
                        //dd("Mismo ID! Verificar si está borrado");
                        if($empleado->estadoInterno == 6){ //Empleado borrado
                            if($operacion == "detalle"){
                                return $next($request);     //Solo muestro el detalle del doc borrado    
                            }
                            else{
                                Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax y El documento está BORRADO | ");
 
                                return redirect()->back()
                                    ->with('mensaje','Error! Empleado borrado')
                                    ->with('danger',"Error! Empleado borrado"); 
                            }
                        }
                        else
                            return $next($request);
                    } 
                    else {  //id mayor a Ida Max???
                        //dd("Id mayor a IdMax.  No se puede dar nunca esta condición");
                        Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
                        return abort( 401 );        
                    }
                    //error
                }
                break;

            case 'sueldos':
                $recibo = ReciboSueldo::find($id);
                $nro = $recibo->nro;    
                $rta = ReciboSueldo::selectRaw('max(id) as idMax')->where ('nro','=',$nro)->get();
                $idMax =$rta[0]->idMax;     //Obtengo el id Maximo del legajo en curso. 
                
                if($id < $idMax)   {
                    //dd("Id menor a IdMax.  Documento anterior al actual");
                    //return abort( 401 );       
                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
 
                    return redirect()->back()
                        ->with('mensaje','Error! Recibo inexistente')
                        ->with('danger',"Error! Recibo inexistente");
                } 
                    //error
                else{
                    if($id == $idMax)  {//ok
                        //Verifico privilegios
                        switch($recibo->estadoRecibo){
                            case 6:  //Recibo borrado
                                if($operacion == "detalle"){
                                    return $next($request);     //Solo muestro el detalle del doc borrado    
                                }
                                else{
                                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax y El documento está BORRADO | ");
    
                                    return redirect()->back()
                                        ->with('mensaje','Error! Recibo borrado')
                                        ->with('danger',"Error! Recibo borrado"); 
                                }
                                break;

                            case 2: //Recibo VIP
                                if(auth()->user()->can('editar_recibos_vip')){
                                    return $next($request);    
                                }
                                else{
                                    Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax y no tiene privilegios | ");
    
                                    return redirect()->back()
                                        ->with('mensaje','Error! No posee privilegios')
                                        ->with('danger',"Error! No posee privilegios"); 
                                }
                                break;

                            case 1: //Recibo normal
                                return $next($request);
                                break;

                            default:
                                Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Estado Recibo INEXISTENTE - Max id es $idMax | ");
                                return abort( 401 );                            
                                break;
                        }
                    } 
                    else {  //id mayor a Ida Max???
                        //dd("Id mayor a IdMax.  No se puede dar nunca esta condición");
                        Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
                        return abort( 401 );        
                    }
                    //error
                }
                // break;

                default:
                    // Log::error(Auth::user()->name." | Seguridad Documento - Intento de ver ".$request->path()." - Max id es $idMax | ");
                    // return abort( 401 );        //Mauricio
        }

    }
}
