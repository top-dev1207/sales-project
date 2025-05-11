<?php

namespace App\Http\Controllers;

use App\Models\Ventas;
use App\Models\Facturas;
use App\Models\FormaPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Rubro;




class GraficosController extends Controller
{

    public function resumen(Request $request){
        $anio = 2023;
        if(isset($request->fechaInicio))
            dd($request->fechaInicio);
        $Tamanio_Grafico =0;
        return view('crm.graficos.graficos', compact('anio','Tamanio_Grafico'));

    }


    public function tablero(Request $request){
            
        if(isset($request->fechaInicio)){
            //Entra por POST desde el formulario
            $fechaInicio = $request->fechaInicio;
            $fechaFin    = $request->fechaFin;
        }
        else{
            //Entra por GET.  Pongo fechas a mano
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin    = \Carbon\Carbon::now();
        }

        $Tamanio_Grafico = $request->Tamanio_Grafico;

        Log::info(Auth::user()->name." | consulta GRAFICOS TABLERO desde $fechaInicio hasta $fechaFin | ");


        /****************************************************************************************** */
        //Esta función pre arma la tabla de ventas, solamente con los últimos índices de cada venta y VALIDADOS
            function v(){
                $ultimos_por_nro_venta = Ventas::select('nro_venta', DB::raw('MAX(id) as idMax'))        //armo tabla temporal, con los id y sus maximos created at
                ->groupBy('nro_venta');
                //dd($ultimos_por_nro_venta->get());
                $z = Ventas::joinSub($ultimos_por_nro_venta, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                    $join->on('ventas.nro_venta', '=', 'ultimo_doc.nro_venta');
                    $join->on('ventas.id', '=', 'ultimo_doc.idMax');
                })
                    ->OrderBy('ventas.nro_venta', 'desc')  
                    //->where('estado_venta', '=', '2');      //Validado
                    ->where('estado_venta', '!=', '6')      //No borrados.  //No existen borrado todavia, pero dejo el filtro  
                    ;
                    //dd($z->get());
                    return $z;
            }

        /****************************************************************************************** */
    
            
        /****************************************************************************************** */
        //Esta función pre arma la tabla de egresos, solamente con los últimos índices de cada factura/remito.  OK
            function e(){
                $ultimos_por_nro_documento = Facturas::select('nro_documento', DB::raw('MAX(id) as idMax'))        //armo tabla temporal, con los id y sus maximos created at
                ->groupBy('nro_documento');

                $e = Facturas::joinSub($ultimos_por_nro_documento, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                    $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                    $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                })
                ->OrderBy('facturas.nro_documento', 'desc')  
                //->where('estadoDocumento', '=', '2')      //Validado
                ->where('estadoDocumento', '!=', '6')      //No Borrados
                ;
                //dd($e->get());
                return $e;
            }

        /****************************************************************************************** */
        //$e = e() ->take(10)->get();
            //dd($e);       //anda bien
    
        //*****************************************************************************************************
        //Obtención de los gastos, por mes y día agrupado por RUBRO  OK!!!!!  (se repiten fecha en rubro)
        //*****************************************************************************************************
            //$e = e()    ->select(DB::raw("DATE_FORMAT(fecha_limite,'%Y') as anio, DATE_FORMAT(fecha_limite,'%M') as mes, DATE_FORMAT(fecha_limite,'%d') as dia,  
            $e = e()    ->select(DB::raw("fecha_limite as fecha,  
                    rubro, SUM(total) as total
                    "))
                    //->groupBy('anio')
                    //->groupBy('mes')
                    //->groupBy('dia')
                    ->whereBetween('fecha_limite', [$fechaInicio, $fechaFin])    //Filtro fechas
                    //->groupBy('fecha')
                    ->groupBy('fecha_limite')
                    ->groupBy('rubro')
                    ->get()

                    //dd($e);
                    //->sortBy(['anio', 'desc'])->sortBy(['mes', 'desc'])->sortBy('dia')      //Orden anual y mensual desc, diario asc
                    ->sortBy(['fecha', 'desc'])      //Orden anual y mensual desc, diario asc
                    ///->sortBy(['fecha_limite', 'desc'])      //Orden anual y mensual desc, diario asc
                    ->toArray()
                    ;

            //en $e tengo todos los registros de facturas/remitos TOTALIZADOS por RUBRO y FECHA
        //*****************************************************************************************************
    
            //echo "Egresos:";
            //dd($e);
    
    
        //*****************************************************************************************************
        // Obtencion de las Sumas de ingresos por fecha Año y Mes
        //*****************************************************************************************************
            /*$b = v()    ->select(DB::raw("fecha_venta as fecha,
                    SUM(ventas_fiscal) as ventasFiscal, SUM(ventas_no_fiscal) as ventasNoFiscal,
                    SUM(venta_alimentos) as ventaAlimentos, SUM(venta_bebidas) as ventaBebidas,
                    SUM(fp1) as fp1, SUM(fp2) as fp2, SUM(fp3) as fp3, 
                    SUM(fp4) as fp4, SUM(fp5) as fp5, SUM(fp6) as fp6, SUM(fp6) as fp6, SUM(fp7) as fp7,
                    SUM(fp8) as fp8, SUM(fp9) as fp9, SUM(fp10) as fp10, 
                    SUM(ingresos) as ventasTotales, SUM(anulaciones) as anulaciones, SUM(egresos) as egresos
                    "))
            */

            $b = v()    ->select(DB::raw("fecha_venta as fecha,
                    SUM(ventas_fiscal) as ventasFiscal, SUM(ventas_no_fiscal) as ventasNoFiscal,
                    SUM(venta_alimentos) as ventaAlimentos, SUM(venta_bebidas) as ventaBebidas,
                    SUM(fp1) as fp1, SUM(fp2) as fp2, SUM(fp3) as fp3, 
                    SUM(fp4) as fp4, SUM(fp5) as fp5, SUM(fp6) as fp6, SUM(fp6) as fp6, SUM(fp7) as fp7,
                    SUM(fp8) as fp8, SUM(fp9) as fp9, SUM(fp10) as fp10, 
                    SUM(ingresos) as ventasTotales, SUM(anulaciones) as anulaciones, SUM(egresos) as egresos,
                    SUM(IF(caja=1, ingresos, 0)) as temple_1, SUM(IF(caja=2, ingresos, 0)) as temple_2,
                    SUM(derecho_show) as derecho_show,  SUM(indice_pinta) as indice_pinta, SUM(cuenta_corriente) as cuenta_corriente
                    "))                    
                    //->whereYear('fecha_venta', 2022)
                //->whereMonth('fecha_venta', 1)
                //->orderBy('anio')->orderBy('mes')->orderBy('dia')
                //->groupBy('anio')
                //->groupBy('mes')
                //->groupBy('dia')
                ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])    //Filtro fechas
                ->groupBy('fecha')
                ->get()
                //->sortBy(['anio', 'desc'])->sortBy(['mes', 'desc'])->sortBy('dia')      //Orden anual y mensual desc, diario asc
                ->sortBy(['fecha', 'desc'])      //Orden anual y mensual desc, diario asc
                ->toArray()
                ;

                //dd($b);
        //*****************************************************************************************************


        
        //*****************************************************************************************************
        // Verificación de datos vacíos  //$b = Ingresos - $e = Gastos
        //*****************************************************************************************************
            if(empty($b) || empty($e)) {        //Si no obtengo datos en las fechas seleccionadas, vuelvo con error
                //return redirect()->route('resultados.formulario')
                return redirect()->back()
                ->with('danger',"Error: no existen datos validados en rango de fechas seleccionado!"); 
                
            }
        //*****************************************************************************************************
    

        //*****************************************************************************************************
        // Cálculo del IVA total Pagado en el rango de fechas seleccionado.  (Select de los importes brutos e impuestos)
        //*****************************************************************************************************
            //$e = e()    ->select(DB::raw("DATE_FORMAT(fecha_limite,'%Y') as anio, DATE_FORMAT(fecha_limite,'%M') as mes, DATE_FORMAT(fecha_limite,'%d') as dia,  
            
            //Esta consulta selecciona el detalle de todo.  Abajo solo deje el total buscado
            // $i = e()    ->select(DB::raw("fecha_limite as fecha,  
            //         rubro, total as total, bruto as bruto, iva as iva, (bruto*iva/100) as ivaPagado
            //         "))

            //Selecciono el total de iva Pagado en las facturas del periodo consultado
            $i = e()    ->select(DB::raw("SUM(bruto*iva/100) as ivaTotalPagado     
                    "))
                    ->where('iva','!=',0)       //Solo los registros con iva
                    ->whereBetween('fecha_limite', [$fechaInicio, $fechaFin])    //Filtro fechas
                    ->get()
                    ;

                    $ivaTotalPagado = $i[0]->ivaTotalPagado;
                    //dd($ivaTotalPagado);
                    //en $i tengo el IVA total pagado en las facturas del periodo consultado
        //*****************************************************************************************************


        //*****************************************************************************************************
        // Obtencion de los Totales de ingresos medios electrónicos y Venta Fiscal (del periodo consultado)
        //*****************************************************************************************************
                $c = v()    ->select(DB::raw("
                SUM(ventas_fiscal) as ventasFiscal,
                SUM(fp1) as fp1, SUM(fp2) as fp2, SUM(fp3) as fp3, SUM(fp4) as fp4, SUM(fp5) as fp5,
                SUM(fp6) as fp6, SUM(fp7) as fp7, SUM(fp8) as fp8, SUM(fp9) as fp9, SUM(fp10) as fp10
                "))
                
                //SUM(pedidos_ya) as py, SUM(rappi) as rap, SUM(otros) as ot

            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])    //Filtro fechas
            ->get()
            ;
            $c = $c[0];
            $totalVentasFiscal = $c->ventasFiscal;
            //dd($c);

            //Calcular totalMediosElect
            // Viejo:    
            // $totalMediosElect = $c->mp + $c->t + $c->py + $c->rap + $c->ot;     //suma de medios electrónicos

            // Nuevo:
            //dd($c->fp1);

            $totalVentasFiscal2 = 0;
            $totalMediosElect = 0;
            $impactaEnCaja = 0;

            for($i=1; $i<11; $i++){
                $formaPago = "fp".$i;
                
                // echo $formaPago;
                // echo ": ";
                // if(isset(FormaPago::find($i)->tipo)) {
                //     echo FormaPago::find($i)->tipo;
                // }
                // else
                // echo "Sin datos de $formaPago";
                
                // echo " -> $";
                // echo $c->$formaPago;
                // echo "<BR>";
                
                //Solo si está definido...calculo
                if(isset(FormaPago::find($i)->tipo)){
                    $fp = FormaPago::find($i);
                    
                    if($fp->fiscal == 1){
                        //Aumento fiscal
                        $totalVentasFiscal2 += $c->$formaPago;
                    }
                    
                    // echo ($fp->opciones & 0b00000001);
                    // echo "<BR>";
                    // echo ($fp->opciones & 0b00000010); 
                    // echo "<BR>";

                    if(($fp->opciones & 0b00000001) == 1){          //Opción 1
                        //Aumento opciones cuenta impacta en caja
                        //echo "Opcion Impacta en caja <BR>";
                        $impactaEnCaja += $c->$formaPago;

                    }
                    //else
                    //echo "Opcion NO Impacta en caja <BR>";

                    if(($fp->opciones & 0b00000010) == 2){          //Opción 2
                        //Aumento opciones cuenta impacta en caja
                        //echo "Opcion Medio electrónico <BR>";
                        $totalMediosElect += $c->$formaPago;
                    }
                    //else
                    //echo "Opcion NO es medio electrónico <BR>";


                }

            }

        //*****************************************************************************************************

    
            //*****************************************************************************************************
            //array patron de egresos, con cada rubro DIARIO
            //*****************************************************************************************************
                $rubros = Rubro::all();
                //$rubros = Rubro::where('tipo','!=',0)->OrderBy('orden')->get();
                //dd($rubros);
                $egresos = array();
                //$egresos['Fecha']='';
                array_push($egresos, array('Fecha', '', 0));
                foreach($rubros as $ru){
                    //dd($ru);
                    //$egresos[$ru->nombre]=0;    //inicializo todos los rubros en cero
                    array_push($egresos, array($ru->nombre, 0, $ru->orden));    //cargo Nombre, importe, y orden
                }    
            
            
                $g = array(
                    'egresos' => $egresos,

                    /*'egresos' => array(
                        'Fecha'=>'',
                            'Sin datos'=>0,
                            'Alimentos'=>0,
                            'Bebidas'=>0,
                            'Luz'=>0,
                            'Teléfono, Internet, Cable'=>0,
                            'Aysa'=>0,
                            'Fumigación'=>0,
                            'Librería'=>0,
                            'Descartables'=>0,
                            'Mantenimiento'=>0,
                            'Equipamiento'=>0,
                            'Vajilla'=>0,
                            'Decoración'=>0,    
                            'Art. limpieza'=>0,    
                            'Viáticos'=>0,   
                            'Lavandería'=>0,    
                            'Imprenta / Gráfica'=>0,    
                            'Alquiler y expensas'=>0,    
                            'Personal Extra'=>0,    
                            'ABL'=>0,    
                            'Seguro'=>0,    
                            'Salarios'=>0,    
                            'Seguridad'=>0,    
                            'Contador'=>0,    
                            'Abogado'=>0,    
                            'Marketing'=>0,    
                            'Entretenimiento'=>0,    
                            'Banco'=>0,    
                            'Autónomos'=>0,    
                            'Uniformes'=>0,    
                            'Sadaic'=>0,    
                            'Liquidación y legales'=>0,    
                            'Sistemas'=>0,    
                            'Fee'=>0,    
                            'Comida Personal'=>0,    
                            'Tarjeta Control'=>0,    
                            'Otros'=>0, 
                            'Limpieza del Local'=>0,
                            'Recolección de basura'=>0,
                            'Fletes y Viáticos'=>0,
                            'Alarma'=>0,
                            'Farmacia'=>0,
                            'Tubos de Gas'=>0,
                            'Regalías'=>0,
                            'Facturas adicionales de IVA'=>0,
                            'Ascensores'=>0,
                            'Gas'=>0,
                            'Thinkion'=>0,
                            'Linkedin'=>0,
                            'Comunity'=>0,
                            'Asesor Gastronómico'=>0
    
                        ),
                        */
                        'ingresos' => array(
                            'fecha'=>'',
                            "ventasFiscal" => 0,
                            "ventasNoFiscal" => 0,
                            "ventaAlimentos" => 0,
                            "ventaBebidas" => 0,
                            "fp1" => 0,
                            "fp2" => 0,
                            "fp3" => 0,
                            "fp4" => 0,
                            "fp5" => 0,
                            "fp6" => 0,
                            "fp7" => 0,
                            "fp8" => 0,
                            "fp9" => 0,
                            "fp10" => 0,
                            "ventasTotales" =>0,
                            "anulaciones" => 0,
                            "egresos" => 0,
                            "temple_1" => 0,
                            "temple_2" => 0,
                            "derecho_show" => 0,
                            "indice_pinta" => 0,
                            "cuenta_corriente" => 0
                
                        ),

                        'resultados' => array(
                            'totalVentas'=>0,
                            "totalGastos" => 0,
                            "saldo" => 0,
                            "saldoPorcentaje" => 0,
                            "foodCost" => 0,
                            "beverageCost" => 0,
                            "mixCost" => 0,
                            "ivaPagado" => 0
                            
                        )
                );
    
            //*****************************************************************************************************
            // Inicialización de arrays y variables para el loop
            //*****************************************************************************************************
                $r = array();       //array con respuesta global.  Fecha con cada rubro totalizado del día.  Según la consulta puede ser por mes...(ver)
                $a = array();       //usado como array temporal
                $fechaAnterior="";  //la uso para comparar fechas del foreach, que viene ordenado por fechas asc. Cuando cambia, grabo y armo un nuevo array
                $primerregistro=1;
            //*****************************************************************************************************
    
            
            //*****************************************************************************************************
            // Loop para completar datos de Gastos por día
            //*****************************************************************************************************
                foreach($e as $ef){ //loop de lo recibido. en $e tengo los gastos realizados por rubro y dia
                    //$c++;
                    //echo('<BR>');
                    //echo($c);
                    
                    $fecha = $ef['fecha'];  //verifico fecha
                    //echo('<BR>');
                    //echo($fecha);
                    if($fecha != $fechaAnterior){
                        
                        if($primerregistro){
                            $primerregistro=0;      //lo hago para no grabar el array al principio
                            //echo('<BR>');
                            //echo("Primer registro");
                        }
                        else {
                            //$r+=$a;      //no grabo la primera vez que entra.  
                            //echo('<BR>');
                            array_push($r,$a);
                            //dd($r);
                        }
                        //creo array
                        $a = $g;    //creo array temporal
                        //$a['egresos']['Fecha'] = $fecha;   //asigno la nueva fecha en array
                        $a['egresos'][0][1] = $fecha;   //asigno la nueva fecha en array

                        $fechaAnterior= $fecha;
                        //dd('sdf');
                        //dd($fechaAnterior);
                    }
                    else{
                        //no hago nada, seguira cargando el dato en el array de fecha actual
                        //dd($fechaAnterior);
                    }

                    //Continuo ingresando datos

                    $a['egresos'][$ef['rubro']+1][1] =       $ef['total'];        //LO HAGO CON TODOS LOS RUBROS, PARA APROVECHAR EL INDICE NATURAL.
                    //LUEGO ORDENO POR ORDEN

                    /*
                    switch($ef['rubro']){       //verifico rubro
                        case 0:  $a['egresos']['Sin datos'] =       $ef['total']; break;
                        case 1:  $a['egresos']['Alimentos'] =       $ef['total']; break;
                        case 2:  $a['egresos']['Bebidas'] =         $ef['total']; break;
                        case 3:  $a['egresos']['Luz'] =             $ef['total']; break;
                        case 4:  $a['egresos']['Teléfono, Internet, Cable'] =$ef['total']; break;
                        case 5:  $a['egresos']['Aysa'] =            $ef['total']; break;
                        case 6:  $a['egresos']['Fumigación'] =      $ef['total']; break;
                        case 7:  $a['egresos']['Librería'] =        $ef['total']; break;
                        case 8:  $a['egresos']['Descartables'] =    $ef['total']; break;
                        case 9:  $a['egresos']['Mantenimiento'] =   $ef['total']; break;
                        case 10:  $a['egresos']['Equipamiento'] =   $ef['total']; break;
                        case 11:  $a['egresos']['Vajilla'] =        $ef['total']; break;
                        case 12:  $a['egresos']['Decoración'] =     $ef['total']; break;    
                        case 13:  $a['egresos']['Art. limpieza'] =  $ef['total']; break;    
                        case 14:  $a['egresos']['Viáticos'] =       $ef['total']; break;   
                        case 15:  $a['egresos']['Lavandería'] =     $ef['total']; break;    
                        case 16:  $a['egresos']['Imprenta / Gráfica'] =$ef['total']; break;    
                        case 17:  $a['egresos']['Alquiler y expensas'] =$ef['total']; break;    
                        case 18:  $a['egresos']['Personal Extra'] = $ef['total']; break;    
                        case 19:  $a['egresos']['ABL'] =            $ef['total']; break;    
                        case 20:  $a['egresos']['Seguro'] =         $ef['total']; break;    
                        case 21:  $a['egresos']['Salarios'] =       $ef['total']; break;    
                        case 22:  $a['egresos']['Seguridad'] =      $ef['total']; break;    
                        case 23:  $a['egresos']['Contador'] =       $ef['total']; break;    
                        case 24:  $a['egresos']['Abogado'] =        $ef['total']; break;    
                        case 25:  $a['egresos']['Marketing'] =      $ef['total']; break;    
                        case 26:  $a['egresos']['Entretenimiento']= $ef['total']; break;    
                        case 27:  $a['egresos']['Banco'] =          $ef['total']; break;    
                        case 28:  $a['egresos']['Autónomos'] =      $ef['total']; break;    
                        case 29:  $a['egresos']['Uniformes'] =      $ef['total']; break;    
                        case 30:  $a['egresos']['Sadaic'] =         $ef['total']; break;    
                        case 31:  $a['egresos']['Liquidación y legales'] =$ef['total']; break;    
                        case 32:  $a['egresos']['Sistemas'] =       $ef['total']; break;    
                        case 33:  $a['egresos']['Fee'] =            $ef['total']; break;    
                        case 34:  $a['egresos']['Comida Personal']= $ef['total']; break;    
                        case 35:  $a['egresos']['Tarjeta Control']= $ef['total']; break;  
                        case 36:  $a['egresos']['Otros']=           $ef['total']; break; 
                        case 37:  $a['egresos']['Limpieza del Local']=   $ef['total']; break; 
                        case 38:  $a['egresos']['Recolección de basura']=$ef['total']; break; 
                        case 39:  $a['egresos']['Fletes y Viáticos']=    $ef['total']; break; 
                        case 40:  $a['egresos']['Alarma']=          $ef['total']; break; 
                        case 41:  $a['egresos']['Farmacia']=        $ef['total']; break; 
                        case 42:  $a['egresos']['Tubos de Gas']=    $ef['total']; break; 
                        case 43:  $a['egresos']['Regalías']=        $ef['total']; break; 
                        case 44:  $a['egresos']['Facturas adicionales de IVA']= $ef['total']; break; 
                        case 45:  $a['egresos']['Ascensores']=      $ef['total']; break; 
                        case 46:  $a['egresos']['Gas']=      $ef['total']; break; 
                        case 47:  $a['egresos']['Thinkion']=      $ef['total']; break; 
                        case 48:  $a['egresos']['Linkedin']=      $ef['total']; break; 
                        case 49:  $a['egresos']['Comunity']=      $ef['total']; break; 
                        case 50:  $a['egresos']['Asesor Gastronómico']= $ef['total']; break; 
    
                        default: dd($ef['rubro']);
                            dd('Error: Ver con Mauricio');
    
                    }*/
                    
                }
                array_push($r,$a);  //grabo ultimo array temporal, cuando sale del loop
                //dd($r);
            //*****************************************************************************************************
    
           
            
    
            //************************************************************************************************** */
            //Sumar datos de venta a matriz global que ya tiene datos de egresos
            //************************************************************************************************** */
                //consultar si existe el dia
                foreach($b as $v){      //por cada venta 
                    $fecha = $v['fecha'];       //tomo valor de fecha de ventas
                    $i=0;
                    $copiado=0;     //flag para avisar que se copiaron datos de venta en matriz global
                    foreach ($r as $s) {        //verifico si está en la matriz global
                        //if(array_search($fecha,$r[$i]['egresos'])){     //Si ya está la fecha en egresos, copio ahí
                        if(array_search($fecha,$r[$i]['egresos'][0])){     //Si ya está la fecha en egresos, copio ahí
                                //echo($fecha." Existe");
                            //echo('<BR>');
                            //echo("encontrado en posición ".$i);
                            //copiar datos de venta en registro global
                            $r[$i]['ingresos'] = $v;        //copio los datos de venta en ingresos del array global, a pasar a vista
    
                            $copiado = 1;
                            //echo('<BR>');
                            //echo("Copia en registro existente de fecha ".$fecha);
                            break;  //salgo del loop para que no verifique todo
                            //dd('Existe');
                        }
                        else{
                            //echo($fecha." No Existe");
                            //echo('<BR>');
                            //echo($i);
                            //echo('<BR>');
                            //dd('Existe');
                            //dd('Error');
                        }  
                        $i++;
                        
                    }
                    if($copiado==0){    //llega acá si no encontró la fecha en egresos.  Debe crear la fecha nueva.
                        //crear nuevo array y agregarlo al global.  No tiene documentos de egresos en esa fecha
                        $a = $g;
                        $a['ingresos'] = $v;
                        //$a['egresos']['Fecha'] = $v['fecha'];   //guardo fecha en egresos para cuando quiera buscar en vista
                        $a['egresos'][0][1] = $v['fecha'];   //guardo fecha en egresos para cuando quiera buscar en vista
                        array_push($r,$a);  //grabo ultimo array temporal, cuando sale del loop
    
                        //echo('Crea y copia nuevo registro para fecha'.$fecha);
                        //echo('<BR>');
                    }
                    
                    }
            //************************************************************************************************** */
    
    
            
            //************************************************************************************************** */
            //Cálculo de totales por cada día
            //************************************************************************************************** */
                foreach($r as &$dia){
                    //Cálculo de suma de egresos del día
                    foreach($dia['egresos'] as $dato){
                        if(is_numeric($dato[1]))
                            $dia['resultados']['totalGastos']+=$dato[1];
                    }
                    //dd($r);
                    //Cálculo de suma de ingresos del día
                    //$dia['resultados']['totalVentas'] = $dia['ingresos']['ventasFiscal'] + $dia['ingresos']['ventasNoFiscal'];
                    //Agrego derecho_show
                    $dia['resultados']['totalVentas'] = $dia['ingresos']['ventasFiscal'] + $dia['ingresos']['ventasNoFiscal'] + $dia['ingresos']['derecho_show'];
                    
                    //dd($dia['ingresos']);
                    
                    //Cálculo de resultados del dia
                    $dia['resultados']['saldo'] = $dia['resultados']['totalVentas'] - $dia['resultados']['totalGastos'];

                    if($dia['resultados']['totalVentas'])
                        $dia['resultados']['saldoPorcentaje'] = $dia['resultados']['saldo'] * 100 / $dia['resultados']['totalVentas']; 

                    if($dia['ingresos']['ventaAlimentos'])
                        //$dia['resultados']['foodCost'] = $dia['egresos']['Alimentos'] * 100 / $dia['ingresos']['ventaAlimentos']; 
                        $dia['resultados']['foodCost'] = $dia['egresos'][2][1] * 100 / $dia['ingresos']['ventaAlimentos']; 

                    if($dia['ingresos']['ventaBebidas'])
                        //$dia['resultados']['beverageCost'] = $dia['egresos']['Bebidas'] * 100 / $dia['ingresos']['ventaBebidas']; 
                        $dia['resultados']['beverageCost'] = $dia['egresos'][3][1] * 100 / $dia['ingresos']['ventaBebidas']; 

                    if($dia['resultados']['totalVentas'])
                        //$dia['resultados']['mixCost'] = ($dia['egresos']['Bebidas']+ $dia['egresos']['Alimentos']) * 100 / $dia['resultados']['totalVentas']; 
                        $dia['resultados']['mixCost'] = ($dia['egresos'][2][1]+ $dia['egresos'][3][1]) * 100 / $dia['resultados']['totalVentas']; 


                }
                unset($dia);        //desasocia la referencia &dia
            //************************************************************************************************** */
    
    
            $respuesta = array_multisort($r, SORT_ASC);     //reordeno por fecha
            //dd($r);
    
            //************************************************************************************************** */
            // Agregar último item con la sumatoria lateral de cada item.  Ej.  Suma de gasto1/día1 + gasto1/día2 + ...
            //************************************************************************************************** */
                $totalVentas = 0; 
                $totalGastos = 0; 
                $totalCostoAlimentos = 0;
                $totalCostoBebidas = 0; 
                $totalVentaBebidas = 0;
                $totalVentaAlimentos = 0;
                $totalDerechoShow = 0;
                $totalCuentaCorriente = 0;
                
                foreach($r as $dia){
                    $totalVentas            += $dia['resultados']['totalVentas'];
                    $totalGastos            += $dia['resultados']['totalGastos'];
                    //$totalCostoAlimentos    += $dia['egresos']['Alimentos'];
                    $totalCostoAlimentos    += $dia['egresos'][2][1];
                    //$totalCostoBebidas      += $dia['egresos']['Bebidas'];
                    $totalCostoBebidas      += $dia['egresos'][3][1];
                    $totalVentaBebidas      += $dia['ingresos']['ventaBebidas'];
                    $totalVentaAlimentos    += $dia['ingresos']['ventaAlimentos'];
                    $totalDerechoShow       += $dia['ingresos']['derecho_show'];
                    $totalCuentaCorriente   += $dia['ingresos']['cuenta_corriente'];
    
                }
    
                $saldo       = $totalVentas - $totalGastos;
                if($totalVentaAlimentos)
                    $foodCost = $totalCostoAlimentos *100 / $totalVentaAlimentos;
                if($totalVentaBebidas)
                    $beverageCost = $totalCostoBebidas *100 / $totalVentaBebidas;
                if($totalVentas)
                    $mixCost = ($totalCostoBebidas+$totalCostoAlimentos) *100 / $totalVentas;
                
                // echo $totalVentas;
                // echo '<BR>';
                // echo $totalGastos;
                // echo '<BR>';
                // echo $totalCostoAlimentos;
                // echo '<BR>';
                // echo $totalCostoBebidas;
                // echo '<BR>';
    
                //dd($beverageCost);
                //Calculo totales
                $tot = $g;        //Inicializo array de datos
                $i=0;
                foreach($r as &$dia){

                    $tot['egresos'][0][1]            = 'Totales';

                    for($i=1; $i<Rubro::all()->count()+1; $i++){     //Salteo primer registro y lo hago por todos los rubros 
                        $tot['egresos'][$i][1]        += $dia['egresos'][$i][1];
                    }

                    //echo $i++.'<BR>';
                    /*
                    $tot['egresos']['Fecha']            = 'Totales';
                    $tot['egresos']['Sin datos']        += $dia['egresos']['Sin datos'];
                    $tot['egresos']['Alimentos']        += $dia['egresos']['Alimentos'];
                    $tot['egresos']['Bebidas']          += $dia['egresos']['Bebidas'];
                    $tot['egresos']['Luz']              += $dia['egresos']['Luz'];
                    $tot['egresos']['Teléfono, Internet, Cable'] += $dia['egresos']['Teléfono, Internet, Cable'];
                    $tot['egresos']['Aysa']             += $dia['egresos']['Aysa'];
                    $tot['egresos']['Fumigación']       += $dia['egresos']['Fumigación'];
                    $tot['egresos']['Librería']         += $dia['egresos']['Librería'];
                    $tot['egresos']['Descartables']     += $dia['egresos']['Descartables'];
                    $tot['egresos']['Mantenimiento']    += $dia['egresos']['Mantenimiento'];
                    $tot['egresos']['Equipamiento']     += $dia['egresos']['Equipamiento'];
                    $tot['egresos']['Vajilla']          += $dia['egresos']['Vajilla'];
                    $tot['egresos']['Decoración']       += $dia['egresos']['Decoración'];
                    $tot['egresos']['Art. limpieza']    += $dia['egresos']['Art. limpieza'];
                    $tot['egresos']['Viáticos']         += $dia['egresos']['Viáticos'];
                    $tot['egresos']['Lavandería']       += $dia['egresos']['Lavandería'];
                    $tot['egresos']['Imprenta / Gráfica']   += $dia['egresos']['Imprenta / Gráfica'];
                    $tot['egresos']['Alquiler y expensas']  += $dia['egresos']['Alquiler y expensas'];
                    $tot['egresos']['Personal Extra']   += $dia['egresos']['Personal Extra'];
                    $tot['egresos']['ABL']              += $dia['egresos']['ABL'];
                    $tot['egresos']['Seguro']           += $dia['egresos']['Seguro'];
                    $tot['egresos']['Salarios']         += $dia['egresos']['Salarios'];
                    $tot['egresos']['Seguridad']        += $dia['egresos']['Seguridad'];
                    $tot['egresos']['Contador']         += $dia['egresos']['Contador'];
                    $tot['egresos']['Abogado']          += $dia['egresos']['Abogado'];
                    $tot['egresos']['Marketing']        += $dia['egresos']['Marketing'];
                    $tot['egresos']['Entretenimiento']  += $dia['egresos']['Entretenimiento'];
                    $tot['egresos']['Banco']            += $dia['egresos']['Banco'];
                    $tot['egresos']['Autónomos']        += $dia['egresos']['Autónomos'];
                    $tot['egresos']['Uniformes']        += $dia['egresos']['Uniformes'];
                    $tot['egresos']['Sadaic']           += $dia['egresos']['Sadaic'];
                    $tot['egresos']['Liquidación y legales'] += $dia['egresos']['Liquidación y legales'];
                    $tot['egresos']['Sistemas']         += $dia['egresos']['Sistemas'];
                    $tot['egresos']['Fee']              += $dia['egresos']['Fee'];
                    $tot['egresos']['Comida Personal']  += $dia['egresos']['Comida Personal'];
                    $tot['egresos']['Tarjeta Control']  += $dia['egresos']['Tarjeta Control'];
                    $tot['egresos']['Otros']            += $dia['egresos']['Otros'];
                    $tot['egresos']['Limpieza del Local']     += $dia['egresos']['Limpieza del Local'];
                    $tot['egresos']['Recolección de basura']  += $dia['egresos']['Recolección de basura'];
                    $tot['egresos']['Fletes y Viáticos']      += $dia['egresos']['Fletes y Viáticos'];
                    $tot['egresos']['Alarma']           += $dia['egresos']['Alarma'];
                    $tot['egresos']['Farmacia']         += $dia['egresos']['Farmacia'];
                    $tot['egresos']['Tubos de Gas']     += $dia['egresos']['Tubos de Gas'];
                    $tot['egresos']['Regalías']         += $dia['egresos']['Regalías'];
                    $tot['egresos']['Facturas adicionales de IVA'] += $dia['egresos']['Facturas adicionales de IVA'];
                    $tot['egresos']['Ascensores']       += $dia['egresos']['Ascensores'];
                    $tot['egresos']['Gas']              += $dia['egresos']['Gas'];
                    $tot['egresos']['Thinkion']         += $dia['egresos']['Thinkion'];
                    $tot['egresos']['Linkedin']         += $dia['egresos']['Linkedin'];
                    $tot['egresos']['Comunity']         += $dia['egresos']['Comunity'];
                    $tot['egresos']['Asesor Gastronómico']  += $dia['egresos']['Asesor Gastronómico'];                    
                    */

                    $tot['ingresos']['fecha']           = 'Totales';
                    $tot['ingresos']['ventasFiscal']    += $dia['ingresos']['ventasFiscal'];
                    $tot['ingresos']['ventasNoFiscal']  += $dia['ingresos']['ventasNoFiscal'];
                    $tot['ingresos']['ventaAlimentos']  += $dia['ingresos']['ventaAlimentos'];
                    $tot['ingresos']['ventaBebidas']    += $dia['ingresos']['ventaBebidas'];
                    $tot['ingresos']['fp1']             += $dia['ingresos']['fp1'];
                    $tot['ingresos']['fp2']             += $dia['ingresos']['fp2'];
                    $tot['ingresos']['fp3']             += $dia['ingresos']['fp3'];
                    $tot['ingresos']['fp4']             += $dia['ingresos']['fp4'];
                    $tot['ingresos']['fp5']             += $dia['ingresos']['fp5'];
                    $tot['ingresos']['fp6']             += $dia['ingresos']['fp6'];
                    $tot['ingresos']['fp7']             += $dia['ingresos']['fp7'];
                    $tot['ingresos']['fp8']             += $dia['ingresos']['fp8'];
                    $tot['ingresos']['fp9']             += $dia['ingresos']['fp9'];
                    $tot['ingresos']['fp10']            += $dia['ingresos']['fp10'];
                    $tot['ingresos']['temple_1']        += $dia['ingresos']['temple_1'];          
                    $tot['ingresos']['temple_2']        += $dia['ingresos']['temple_2'];          
                    $tot['ingresos']['derecho_show']    += $dia['ingresos']['derecho_show'];          
                    $tot['ingresos']['cuenta_corriente']+= $dia['ingresos']['cuenta_corriente'];
                    $tot['ingresos']['indice_pinta']    += $dia['ingresos']['indice_pinta'];

                    $tot['ingresos']['ventasTotales']   += $dia['ingresos']['ventasTotales'];
                    $tot['ingresos']['anulaciones']     += $dia['ingresos']['anulaciones'];
    
                    $tot['resultados']['totalVentas']   += $dia['resultados']['totalVentas'];
                    $tot['resultados']['totalGastos']   += $dia['resultados']['totalGastos'];
                    $tot['resultados']['saldo']         += $dia['resultados']['saldo'];
                    $tot['resultados']['saldoPorcentaje'] += $dia['resultados']['saldoPorcentaje'];
                    $tot['resultados']['foodCost']      += $dia['resultados']['foodCost'];
                    $tot['resultados']['beverageCost']  += $dia['resultados']['beverageCost'];
                    $tot['resultados']['mixCost']       += $dia['resultados']['mixCost'];
                }
    
                unset($dia);        //desasocia la referencia &dia
                array_push($r,$tot);  //grabo ultimo array de totales en array reapuesta
                
    
                //Calculo Porcentajes de totales
                $tot = $g;        //Inicializo array de datos
                $i=0;
                foreach($r as &$dia){

                    if($dia['egresos'][0][1]=='Totales'){    //Busco el ultimo registro para calcular %
                        //$tot['egresos']['Fecha']            = 'Incidencia (%)';
                        $tot['egresos'][0][1]            = 'Incidencia (%)';
                        //for($i=1; $i<Rubro::where('tipo','!=',0)->count()+1; $i++){
                        for($i=1; $i<Rubro::all()->count()+1; $i++){
                            $tot['egresos'][$i][1]        = number_format($dia['egresos'][$i][1] / $dia['resultados']['totalGastos'] * 100, 1);
                        }
    

                    /*
                    //echo $i++.'<BR>';
                    if($dia['egresos']['Fecha']=='Totales'){    //Busco el ultimo registro para calcular %
                        $tot['egresos']['Fecha']            = 'Incidencia (%)';
                        $tot['egresos']['Sin datos']        = number_format($dia['egresos']['Sin datos'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Alimentos']        = number_format($dia['egresos']['Alimentos'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Bebidas']          = number_format($dia['egresos']['Bebidas'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Luz']              = number_format($dia['egresos']['Luz'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Teléfono, Internet, Cable'] = number_format($dia['egresos']['Teléfono, Internet, Cable'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Aysa']             = number_format($dia['egresos']['Aysa'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Fumigación']       = number_format($dia['egresos']['Fumigación'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Librería']         = number_format($dia['egresos']['Librería'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Descartables']     = number_format($dia['egresos']['Descartables'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Mantenimiento']    = number_format($dia['egresos']['Mantenimiento'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Equipamiento']     = number_format($dia['egresos']['Equipamiento'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Vajilla']          = number_format($dia['egresos']['Vajilla'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Decoración']       = number_format($dia['egresos']['Decoración'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Art. limpieza']    = number_format($dia['egresos']['Art. limpieza'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Viáticos']         = number_format($dia['egresos']['Viáticos'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Lavandería']       = number_format($dia['egresos']['Lavandería'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Imprenta / Gráfica']   = number_format($dia['egresos']['Imprenta / Gráfica'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Alquiler y expensas']  = number_format($dia['egresos']['Alquiler y expensas'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Personal Extra']   = number_format($dia['egresos']['Personal Extra'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['ABL']              = number_format($dia['egresos']['ABL'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Seguro']           = number_format($dia['egresos']['Seguro'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Salarios']         = number_format($dia['egresos']['Salarios'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Seguridad']        = number_format($dia['egresos']['Seguridad'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Contador']         = number_format($dia['egresos']['Contador'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Abogado']          = number_format($dia['egresos']['Abogado'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Marketing']        = number_format($dia['egresos']['Marketing'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Entretenimiento']  = number_format($dia['egresos']['Entretenimiento'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Banco']            = number_format($dia['egresos']['Banco'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Autónomos']        = number_format($dia['egresos']['Autónomos'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Uniformes']        = number_format($dia['egresos']['Uniformes'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Sadaic']           = number_format($dia['egresos']['Sadaic'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Liquidación y legales'] = number_format($dia['egresos']['Liquidación y legales'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Sistemas']         = number_format($dia['egresos']['Sistemas'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Fee']              = number_format($dia['egresos']['Fee'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Comida Personal']  = number_format($dia['egresos']['Comida Personal'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Tarjeta Control']  = number_format($dia['egresos']['Tarjeta Control'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Otros']            = number_format($dia['egresos']['Otros'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Limpieza del Local']   = number_format($dia['egresos']['Limpieza del Local'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Recolección de basura']= number_format($dia['egresos']['Recolección de basura']  / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Fletes y Viáticos']    = number_format($dia['egresos']['Fletes y Viáticos']  / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Alarma']           = number_format($dia['egresos']['Alarma']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Farmacia']         = number_format($dia['egresos']['Farmacia'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Tubos de Gas']     = number_format($dia['egresos']['Tubos de Gas']  / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Regalías']         = number_format($dia['egresos']['Regalías'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Facturas adicionales de IVA'] = number_format($dia['egresos']['Facturas adicionales de IVA'] / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Ascensores']           = number_format($dia['egresos']['Ascensores']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Gas']           = number_format($dia['egresos']['Gas']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Thinkion']           = number_format($dia['egresos']['Thinkion']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Linkedin']           = number_format($dia['egresos']['Linkedin']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Comunity']           = number_format($dia['egresos']['Comunity']   / $dia['resultados']['totalGastos'] * 100, 1);
                        $tot['egresos']['Asesor Gastronómico'] = number_format($dia['egresos']['Asesor Gastronómico']   / $dia['resultados']['totalGastos'] * 100, 1);
                        */
                        $tot['ingresos']['fecha']           = 'Totales';
                        $tot['ingresos']['ventasFiscal']    = number_format($dia['ingresos']['ventasFiscal'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['ventasNoFiscal']  = number_format($dia['ingresos']['ventasNoFiscal'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['ventaAlimentos']  = number_format($dia['ingresos']['ventaAlimentos'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['ventaBebidas']    = number_format($dia['ingresos']['ventaBebidas'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp1']             = number_format($dia['ingresos']['fp1'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp2']             = number_format($dia['ingresos']['fp2'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp3']             = number_format($dia['ingresos']['fp3'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp4']             = number_format($dia['ingresos']['fp4'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp5']             = number_format($dia['ingresos']['fp5'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp6']             = number_format($dia['ingresos']['fp6'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp7']             = number_format($dia['ingresos']['fp7'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp8']             = number_format($dia['ingresos']['fp8'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp9']             = number_format($dia['ingresos']['fp9'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['fp10']            = number_format($dia['ingresos']['fp10'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['ventasTotales']   = number_format($dia['ingresos']['ventasTotales'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['anulaciones']     = number_format($dia['ingresos']['anulaciones'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['ingresos']['temple_1']        = number_format($dia['ingresos']['temple_1'] / ($dia['ingresos']['temple_1']+$dia['ingresos']['temple_2'])  *100, 1);
                        $tot['ingresos']['temple_2']        = number_format($dia['ingresos']['temple_2'] / ($dia['ingresos']['temple_1']+$dia['ingresos']['temple_2'])  *100, 1);
                            
                        $tot['resultados']['totalVentas']   = number_format($dia['ingresos']['ventasTotales'] / $dia['ingresos']['ventasTotales'] *100, 1);
                        $tot['resultados']['totalGastos']   = number_format($dia['resultados']['totalGastos'] / $dia['resultados']['totalGastos'] *100, 1);
                        $tot['resultados']['saldo']         = number_format($dia['resultados']['totalVentas'] - $dia['resultados']['totalGastos'],1 );
                        $tot['resultados']['saldoPorcentaje'] =1;
                        
                        if($dia['ingresos']['ventaAlimentos']){
                            //$tot['resultados']['foodCost']      = number_format($dia['egresos']['Alimentos'] / $dia['ingresos']['ventaAlimentos'] * 100, 1);
                            $tot['resultados']['foodCost']      = number_format($dia['egresos'][2][1] / $dia['ingresos']['ventaAlimentos'] * 100, 1);
                        }
                        else  $tot['resultados']['foodCost'] = 0;
    
                        if($dia['ingresos']['ventaBebidas'])
                            //$tot['resultados']['beverageCost']  = $dia['egresos']['Bebidas'] / $dia['ingresos']['ventaBebidas'] * 100;
                            $tot['resultados']['beverageCost']  = $dia['egresos'][3][1] / $dia['ingresos']['ventaBebidas'] * 100;
                        else $tot['resultados']['beverageCost']  = 0;
                    
                        if($dia['resultados']['totalVentas'])
                            //$tot['resultados']['mixCost']       = ($dia['egresos']['Bebidas']+ $dia['egresos']['Alimentos'])/ $dia['resultados']['totalVentas'] * 100;
                            $tot['resultados']['mixCost']       = ($dia['egresos'][3][1]+ $dia['egresos'][2][1])/ $dia['resultados']['totalVentas'] * 100;
                    }
    
                }
                unset($dia);        //desasocia la referencia &dia
                array_push($r,$tot);  //grabo ultimo array de totales en array reapuesta
            
    
            //************************************************************************************************** */
            
                
            //************************************************************************************************** */
            // Reordeno EGRESOS
            //************************************************************************************************** */
            foreach($r as &$tipo){
                foreach($tipo as $si => &$s){
                    //dump($si);  
                    //dd($s);
                    //array_multisort(array_column($s,2), SORT_ASC, $s);      //Ordeno los egresos segun orden de Base de Datos
                    //dd($s);

                    //dd($si);
                    if($si == "egresos"){
                        array_multisort(array_column($s,2), SORT_ASC, $s);      //Ordeno los egresos segun orden de Base de Datos
                        //dd($s);
                    }
                }
                unset($s);        //desasocia la referencia &s
            }
            unset($tipo);
            //************************************************************************************************** */
            //Transformación de tabla para vista traspuesta
            //************************************************************************************************** */
                //dd($r);
                $m = array();   //datos
                
                //Cargo titulos 
                foreach($r as $c){
                    foreach($c as $bi => $b){        //Array de ingresos, egresos y totales  --> ($bi = indice, $b = valor)
                        foreach ($b as $ai => $a){  //Array de items 
                            array_push($m, array($ai));
                        }
                    }
                    break;  //Solo cargo 1 vez los titulos
                }
                //dd($m);
                
                //Cargo datos
                $i=0;
                $count = 0;

                foreach($r as $c){
                    foreach($c as $bi => $b){           //Array de ingresos, egresos y totales  --> ($bi = indice, $b = valor)
                        foreach ($b as $ai => $a){      //Array de items 
                            //array_push($m[$i++], $ii++);
                            //echo $i."<BR>";
                            array_push($m[$i++], $a);
                        }
                    }
                    $i=0;   //reinicio cada dia
                    $count++;   //cuento dias
                    //echo $count."<BR>";

                }
                //dd($m);


                //Agrego campo peso/orden a a matriz
                $i=0;
                foreach($m as &$a){      //filas
                    array_push($a, $i++);
                }
                unset($a);

                //Asigno pesos, según items
                foreach($m as &$a){      //filas
                    array_push($a, $i++);
                }
                unset($a);

                /** Retoco matriz.  Elimino blancoa y campos especiales */
                $i=0;
                $countA =0;
                $borrar=0;      //flag

                //dd($m);
                //Elimino filas vacías
                foreach($m as &$a){      //filas
                    //Borro la fila completa si detecto un valor nulo en el total
                    if($borrar){
                        unset($m[$countA-1]);
                        $borrar=0;
                    }
                    
                    foreach($a as &$b){  //Items de cada fila
                        //echo "$b ";
                        //echo '<BR>';

                        //Borro celdas fecha (de ventas) que no aportan nada
                        if($i == 0){
                            if($b == 'fecha')
                                $borrar = 1;
                        }
                        
                        //Transcribo bien los campos genéricos fpx
                        if($i == 0){
                            switch($b){
                                case 'fp1': $b=FormaPago::find(1)->tipo;    break;
                                case 'fp2': $b=FormaPago::find(2)->tipo;    break;
                                case 'fp3': $b=FormaPago::find(3)->tipo;    break;
                                case 'fp4': $b=FormaPago::find(4)->tipo;    break;
                                case 'fp5': $b=FormaPago::find(5)->tipo;    break;
                                case 'fp6': $b=FormaPago::find(6)->tipo;    break;
                                case 'fp7': $b=FormaPago::find(7)->tipo;    break;
                                case 'fp8': $b=FormaPago::find(8)->tipo;    break;
                                default:
                            }
                                
                        }
                        //Borro filas nulas o con cero
                        if($i++ == $count-1){   //Evaluo el anteultimo item de la fila
                            //Evalúo si borro la fila
                            if(is_numeric($b) ){
                                //echo "Es numerico";
                                if(empty(intval($b))){
                                    $borrar=1;
                                    //echo "Resultado en CERO";
                                }
                                else{
                                    //echo "Se debe mostrar";
                                }
                            }
                            else{
                                //echo "Se Muestra, Es String";
                            } 
                            
                            
                        }
                        
                    }
                    unset($b);
                    $i=0;
                    $countA++;  //cuenta filas

                }
                unset($a);
                //dd($m);

                //Listo resultado
                // foreach($m as $a){                  //Array de días y totales/%             
                //     foreach($a as $bi => $b){        //Array de ingresos, egresos y totales  --> ($bi = indice, $b = valor)
                //         //echo '<BR>';
                //         echo "$b - ";
                //     }
                //     echo '<BR>';
                // }
                
                $datos_json = json_encode($m);
                
                //dd($datos_json);
                
                //Llego bien, pero falta poner los títulos de manero correcta en $m
                $cantidadDeRegistros = count($r);
                
                //Mando a la vista las distintas formas de pago
                $fp = FormaPago::all();
            //************************************************************************************************** */




            //************************************************************************************************** */
                //Area de llenado de datos para Graficos
                $costoRubro[0]= array();       //Tipo de rubro (costo)
                $costoRubro[1]= array();       //Incidencia %
                $costoRubroGrande[0]= array(); //Tipo de rubro Grandes (costo)
                $costoRubroGrande[1]= array(); //Incidencia %
                $costoRubroPeq[0]= array();    //Tipo de rubro chicos(costo)
                $costoRubroPeq[1]= array();    //Incidencia %

                count($r);  //Tamanio del array armado.  

                //Para valores de incidencia, tengo que tomar el ultimo, siempre y cuando sean 4
                if(count($r)>3) $indiceIncidencia = count($r)-1;
                //else ....salir
                //    dd($r);
                $egresos = $r[$indiceIncidencia]["egresos"];
                arsort($egresos, SORT_NUMERIC );     //Ordena sin perder la asociaciòn, por nùmeros
                //dd($egresos);

                //$iMax = count($egresos);        //cantidad de elementos del array

                //Llenado de datos en array que pasa a gráficos
                $i=0;  
                $j=0;
                $k=0;              

                //Lo adapto al nuevo formato de egresos
                foreach($egresos as $key=>$valor){
                    //dd($valor);
                    if(is_numeric($valor[1])){     //Sólo los que son valores y no son cero
                        if($valor[1]!=0){

                            $costoRubro[0][$i]= $valor[0];           //Formato para gráfico  
                            $costoRubro[1][$i++]= $valor[1];       //Formato para gráfico  

                            if($valor[1] > 5){        //Acá defino el umbral de separación de tipo de gastos, para mejor visualización en gráfico
                                $costoRubroGrande[0][$j]= $valor[0];       //Formato para gráfico  
                                $costoRubroGrande[1][$j++]= $valor[1];     //Formato para gráfico  
                            }
                            else{
                                $costoRubroPeq[0][$k]= $valor[0];       //Formato para gráfico  
                                $costoRubroPeq[1][$k++]= $valor[1];     //Formato para gráfico  
                            }
                        }
                    }
                }

                //Ordenar Array de menor a mayor
                //var_dump($costoRubro);
                //dd($costoRubroPeq);

                //dd($r);


            //************************************************************************************************** */
            

            //************************************************************************************************** */
                //Area de llenado de datos para Graficos
                $ventasDia[0]= array();       //Dia
                $ventasDia[1]= array();       //Ventas en $

                count($r);  //Tamanio del array armado.  

                //Solo me interesan los dìas, asi que tomo count($r)-2.
                $cantDias = count($r)-2;
                if($cantDias>0){
                    //Ejecuto el script
                    $i=0;
                    foreach($r as $dia){
                        $ventasDia[0][$i]= \Carbon\Carbon::parse($dia['ingresos']['fecha'])
                                            ->locale('es')->isoFormat('dddd D-MMM');      
                        $ventasDia[1][$i]= $dia['ingresos']['ventasTotales']; 
                        
                        if($i++ > $cantDias-2) break; //Solo tomo los dias, y no los totales ni el %
                    }
                }

                
                //dd($ventasDia);
            //************************************************************************************************** */


            
            //************************************************************************************************** */
            // Pruebas
            //************************************************************************************************** */
            
                //dd($r);
                //dd("fin");
                
                //dd($tot);
                // $saldo       = $totalVentas - $totalGastos;
                // if($totalVentaAlimentos)
                //     $foodCost = $totalCostoAlimentos *100 / $totalVentaAlimentos;
                // if($totalVentaBebidas)
                //     $beverageCost = $totalCostoBebidas *100 / $totalVentaBebidas;
                // if($totalVentas)
                //     $mixCost = ($totalCostoBebidas+$totalCostoAlimentos) *100 / $totalVentas;
                
                // echo $totalVentas;
                // echo '<BR>';
                // echo $tot['resultados']['totalVentas'];
                // dd ($r);
                
                //    dd($r);
                
                //dd($r);
    
                //dd($r);     //hasta acá tengo todo joya!!! PUTA MADRE!  10HS
            //************************************************************************************************** */
    
            return view('crm.graficos.graficos', compact('costoRubroGrande','costoRubroPeq','costoRubro', 'ventasDia','Tamanio_Grafico','r','foodCost','beverageCost','mixCost','saldo','totalVentas', 'totalGastos',
            'totalCostoAlimentos','totalCostoBebidas','totalVentaBebidas','totalVentaAlimentos', 'fechaInicio','fechaFin',
            'totalMediosElect','totalVentasFiscal', 'ivaTotalPagado'))
    
                        //->with('mensaje','Consulta generada correctamente!')
                        ->with('exito',"Consulta generada ok!")
                        ->with(session('exito')); 
    
        }
    

    public function formulario(){
        return view('crm.graficos.consultar'); 

    }

 
}


/*
COMPARATIVO DE PAQUETES NODE.  ACTUALIZACION Y PRUEBAS

 @babel/cli                ^7.12.1  →  ^7.23.0 / OK
 @babel/core               ^7.12.3  →  ^7.23.3 / OK
 @babel/preset-env         ^7.12.1  →  ^7.23.3 / OK
 @coreui/chartjs            ^2.0.0  →   ^3.1.2 / OK
 @coreui/coreui             ^3.2.0  →   ^4.3.2 -!!!  Migrar a 4 es diferente.  Ver documentacion
 @coreui/icons              ^1.0.1  →   ^3.0.1 / OK
 @coreui/utils              ^1.2.4  →   ^2.0.2 / OK
 @coreui/vendors-injector   ^1.0.2  →   ^1.1.4 / OK
 @popperjs/core             ^2.5.4  →  ^2.11.8 / OK


@tailwindcss/forms                             ^0.2.1  →    ^0.5.7  / OK
 alpinejs                                       ^2.7.3  →   ^3.13.2 / OK
 autoprefixer                                  ^10.1.0  →  ^10.4.16 / OK
 babel-plugin-transform-es2015-modules-strip    ^0.1.1  →    ^0.1.2 / OK
 browser-sync                                 ^2.26.13  →   ^2.29.3 / OK
 chalk                                          ^4.0.0  →    ^5.3.0 / OK
 clean-css-cli                                  ^4.3.0  →    ^5.6.2 / OK
 copyfiles                                      ^2.4.0  →    ^2.4.1 / OK
 cropperjs                                      ^1.5.7  →    ^1.6.1 / OK
 cross-env                                      ^7.0.2  →    ^7.0.3 / OK
 eslint                                        ^7.13.0  →   ^8.53.0 / OK
 eslint-config-xo                              ^0.29.1  →   ^0.43.1 / OK
 eslint-plugin-import                          ^2.22.1  →   ^2.29.0 / OK
 eslint-plugin-unicorn                         ^20.0.0  →   ^49.0.0 / OK
 jquery                                         ^3.6.0  →    ^3.7.1 / OK
 js-beautify                                   ^1.13.0  →  ^1.14.11 / OK
 jsdom                                         ^16.2.2  →   ^22.1.0 / OK
 laravel-mix                                   ^6.0.16  →   ^6.0.49 / OK
 minimist                                       ^1.2.5  →    ^1.2.8 / OK
 node-notifier                                  ^9.0.1  →   ^10.0.1 / OK
 nodemon                                        ^2.0.6  →    ^3.0.1 / OK
 perfect-scrollbar                               1.5.0  →     1.5.5 / OK
 postcss                                        ^8.2.1  →   ^8.4.31 / OK
 postcss-cli                                    ^7.1.2  →   ^10.1.0 / OK
 postcss-combine-duplicated-selectors           ^8.1.0  →   ^10.0.3 / OK
 postcss-import                                ^12.0.1  →   ^15.1.0 / OK
 postcss-merge-rules                            ^4.0.3  →    ^6.0.1 / OK
 resolve-url-loader                             ^3.1.2  →    ^5.0.0 / OK
 rimraf                                         ^3.0.2  →    ^5.0.5 / OK
 sass                                         ^1.32.10  →   ^1.69.5 / OK
 sass-loader                                    ^8.0.2  →   ^13.3.2 / OK
 shelljs                                        ^0.8.4  →    ^0.8.5 / OK
 stylelint                                     ^13.7.2  →  ^15.11.0 / OK
 stylelint-config-recommended-scss              ^4.2.0  →   ^13.1.0 / OK
 stylelint-config-standard                     ^20.0.0  →   ^34.0.0 / OK
 stylelint-order                                ^4.0.0  →    ^6.0.3 / OK
 stylelint-scss                                ^3.17.2  →    ^5.3.1 / OK
 tailwindcss                                    ^2.0.2  →    ^3.3.5 / OK
 vue                                           ^2.6.12  →    ^3.3.8 / OK
 vue-loader                                    ^15.9.5  →   ^17.3.1 / OK
 vue-template-compiler                         ^2.6.12  →   ^2.7.15 / OK


 */