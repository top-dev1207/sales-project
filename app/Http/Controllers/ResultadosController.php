<?php

namespace App\Http\Controllers;

use App\Models\DatosResultados;
use App\Models\FormaPago;
use App\Models\ReciboSueldo;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Ventas;
use App\Models\Facturas;
use App\Models\Rubro;
use Illuminate\Support\Carbon;
use App\Models\EstadoEntrega;
use App\Models\TipoPago;
use App\Models\Transacciones;


class tableroStruct{
    public $fecha=array();
    public $detalleGastos=array();
    public $formasDePago=array();
    public $ventasTotales=array();
    public $totalGastos=array();
    public $saldo=array();
    public $saldoPorcentaje=array();
    public $ventaFiscal=array();
    public $ventaNoFiscal=array();
    public $ventaAlimentos=array();
    public $ventaNoBebidas=array();
    public $IvaPagado=array();

}


class ResultadosController extends Controller
{
    public function calcular(Request $request)
    {
        //datos del request
        $fechaInicio = $request->fechaInicio;
        $fechaFin    = $request->fechaFin;

        Log::info(Auth::user()->name." | consulta RESULTADOS desde $fechaInicio hasta $fechaFin | ");


        //-----------------------------------------------------------------------------------------------------
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

        //-----------------------------------------------------------------------------------------------------


        //-----------------------------------------------------------------------------------------------------
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

        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        //Obtención de los gastos, por mes y día agrupado por RUBRO  OK!!!!!  (se repiten fecha en rubro)
        //-----------------------------------------------------------------------------------------------------
            //$e = e()    ->select(DB::raw("DATE_FORMAT(fecha_limite,'%Y') as anio, DATE_FORMAT(fecha_limite,'%M') as mes, DATE_FORMAT(fecha_limite,'%d') as dia,
            $e = e()    ->select(DB::raw("fecha_limite as fecha, rubro, SUM(total) as total
                    "))
                    ->whereBetween('fecha_limite', [$fechaInicio, $fechaFin])    //Filtro fechas
                    ->where('rubro','!=',55)    //Elimino las inversiones.  55=Inversiones
                    ->groupBy('fecha_limite')
                    ->groupBy('rubro')
                    ->get()

                    ->sortBy(['fecha', 'desc'])      //Orden anual y mensual desc, diario asc
                    ///->sortBy(['fecha_limite', 'desc'])      //Orden anual y mensual desc, diario asc
                    ->toArray()
                    ;

                //en $e tengo todos los registros de facturas/remitos TOTALIZADOS por RUBRO y FECHA
        //-----------------------------------------------------------------------------------------------------


        //-----------------------------------------------------------------------------------------------------
        //Obtención de los ingresos, por día, sumados
        //-----------------------------------------------------------------------------------------------------

            $sql = "
            select v.fecha_venta as fecha, SUM(v.ventas_fiscal) as ventasFiscal, SUM(v.ventas_no_fiscal) as ventasNoFiscal,
            SUM(v.venta_alimentos) as ventaAlimentos, SUM(v.venta_bebidas) as ventaBebidas,
            SUM(v.fp1) as fp1,
            SUM(v.fp2) as fp2,
            SUM(v.fp3) as fp3,
            SUM(v.fp4) as fp4,
            SUM(v.fp5) as fp5,
            SUM(v.fp6) as fp6,
            SUM(v.fp7) as fp7,
            SUM(v.fp8) as fp8,
            SUM(v.fp9) as fp9,
            SUM(v.fp10) as fp10,
            SUM(v.ingresos) as ventasTotales, SUM(anulaciones) as anulaciones, SUM(egresos) as egresos,
            SUM(IF(caja=1, ingresos, 0)) as temple_1, SUM(IF(caja=2, ingresos, 0)) as temple_2,
            SUM(s.entradaTemple) as ingreso_por_derecho_show, SUM(indice_pinta) as indice_pinta,
            SUM(cuenta_corriente) as cuenta_corriente, SUM(diferenciaCaja) as diferencia_de_caja


                from (
                        select v1.*
                        from ventas                                     as v1
                        inner join (
                                    select nro_venta, max(id) as idMax
                                    from ventas
                                    group by nro_venta
                                    )                                   as max
                        on v1.id = max.idMax
                    )                                                           as v        -- v= Esta es la tabla de ventas con idMax
                left join dshows                                                as s
                on (v.dshowId = s.id and v.nro_venta = s.idVenta)
                and s.estado=1                                                              -- s = tabla dshows más condiciones


        where v.fecha_venta between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
        and v.estado_venta in (1,2)  -- toma estados cargados y validados solamente
        group by v.fecha_venta
        order by v.fecha_venta asc

            ";

            $a =  DB::select(DB::raw($sql));

            //----------------------------------------------------------------------------------------------------
            //Este loop es para pasar de StdClass a array, de lo recibido por $a con DB:select $sql
            foreach($a as &$aa){
                $aa = json_decode(json_encode($aa), true);  // StdClass to Array
            }
            unset($aa);        //desasocia la referencia &dia
            $b = $a;        //igualo a $b, que es el que se calcula más adelante
            //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        // Verificación de datos vacíos  //$b = Ingresos - $e = Gastos
        //----------------------------------------------------------------------------------------------------
            if(empty($b) || empty($e)) {        //Si no obtengo datos en las fechas seleccionadas, vuelvo con error
                //return redirect()->route('resultados.formulario')
                return redirect()->back()
                ->with('danger',"Error: no existen datos validados en rango de fechas seleccionado!");

            }
        //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        //Fórmula nueva, con consulta del campo de iva total pagado
        //----------------------------------------------------------------------------------------------------
            $i = e()    ->select(DB::raw("SUM(ivaPagado) as ivaTotalPagado
                    "))
                    //->where('iva','!=',0)       //Solo los registros con iva
                    ->whereBetween('fecha_limite', [$fechaInicio, $fechaFin])    //Filtro fechas
                    ->get()
                    ;

                    $ivaTotalPagado = $i[0]->ivaTotalPagado;
        //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        // Obtencion de los Totales de ingresos medios electrónicos y Venta Fiscal (del periodo consultado)
        //----------------------------------------------------------------------------------------------------

            $sql = "
                select v.fecha_venta as fecha, SUM(v.ventas_fiscal) as ventasFiscal,
                SUM(v.fp1) as fp1,
                SUM(v.fp2) as fp2,
                SUM(v.fp3) as fp3,
                SUM(v.fp4) as fp4,
                SUM(v.fp5) as fp5,
                SUM(v.fp6) as fp6,
                SUM(v.fp7) as fp7,
                SUM(v.fp8) as fp8,
                SUM(v.fp9) as fp9,
                SUM(v.fp10) as fp10

                    from (
                            select v1.*
                            from ventas                                     as v1
                            inner join (
                                        select nro_venta, max(id) as idMax
                                        from ventas
                                        group by nro_venta
                                        )                                   as max
                            on v1.id = max.idMax
                        )                                                           as v        -- v= Esta es la tabla de ventas con idMax
                    left join dshows                                                as s
                    on (v.dshowId = s.id and v.nro_venta = s.idVenta)
                    and s.estado=1                                                              -- s = tabla dshows más condiciones


            where v.fecha_venta between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
            and v.estado_venta in (1,2)  -- toma estados cargados y validados solamente
            -- group by v.fecha_venta       // Lo comento para que me de bien el total de venta fiscal
            order by v.fecha_venta asc

                ";

            $c =  DB::select(DB::raw($sql));
            //dd($c);
            $c=$c[0];
            $totalVentasFiscal = $c->ventasFiscal;


            //Calcular totalMediosElect
            // Viejo:
            // $totalMediosElect = $c->mp + $c->t + $c->py + $c->rap + $c->ot;     //suma de medios electrónicos

            // Nuevo:
            //dd($c->fp1);

            $totalVentasFiscal2 = 0;
            $totalMediosElect = 0;
            $impactaEnCaja = 0;

            for($i=1; $i<11; $i++){         //OJO QUE ESTA FIJO EL 11!!!!!
                $formaPago = "fp".$i;

                //Solo si está definido...calculo
                if(isset(FormaPago::find($i)->tipo)){
                    $fp = FormaPago::find($i);

                    if($fp->fiscal == 1){
                        //Aumento fiscal
                        $totalVentasFiscal2 += $c->$formaPago;
                    }

                    if(($fp->opciones & 0b00000001) == 1){          //Opción 1 - cuenta impacta en caja
                        //Aumento opciones cuenta impacta en caja
                        //echo "Opcion Impacta en caja <BR>";
                        $impactaEnCaja += $c->$formaPago;

                    }

                    if(($fp->opciones & 0b00000010) == 2){          //Opción 2 - medio electrónico
                        //Aumento opciones cuenta impacta en caja
                        //echo "Opcion Medio electrónico <BR>";
                        $totalMediosElect += $c->$formaPago;
                    }
                }
            }

            //dump($totalMediosElect);
            //dump($impactaEnCaja);
            //dump($totalVentasFiscal2);
            //dd($totalVentasFiscal);

            //$totalVentasFiscal = $totalVentasFiscal2;       //actualizo valor correcto de tot ventas fiscal

            // dd($totalMediosElect);
            //$totalMediosElect= 1;

        //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        //array patron de egresos, con cada rubro DIARIO
        //----------------------------------------------------------------------------------------------------
            //$rubros = Rubro::all();
            $rubros = Rubro::where('tipo','!=',10)->get();  //Tomo todos los rubros de gastos, menos los de INVERSIONES (10)
            $egresos = array();
            array_push($egresos, array('Fecha', '', 0));
            foreach($rubros as $ru){
                array_push($egresos, array($ru->nombre, 0, $ru->orden));    //cargo Nombre, importe, y orden
            }
            $g = array(
                'egresos' => $egresos,

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
                    "ingreso_por_derecho_show" => 0,
                    "indice_pinta" => 0,
                    "cuenta_corriente" => 0,
                    'diferencia_de_caja' => 0

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

        //----------------------------------------------------------------------------------------------------
        // Inicialización de arrays y variables para el loop
        //----------------------------------------------------------------------------------------------------
            $r = array();       //array con respuesta global.  Fecha con cada rubro totalizado del día.  Según la consulta puede ser por mes...(ver)
            $a = array();       //usado como array temporal
            $fechaAnterior="";  //la uso para comparar fechas del foreach, que viene ordenado por fechas asc. Cuando cambia, grabo y armo un nuevo array
            $primerregistro=1;
        //----------------------------------------------------------------------------------------------------

        //----------------------------------------------------------------------------------------------------
        // Loop para completar datos de Gastos por día
        //----------------------------------------------------------------------------------------------------
            //dd($e);
            foreach($e as $ef){ //loop de lo recibido. en $e tengo los gastos realizados por rubro y dia
                $fecha = $ef['fecha'];  //verifico fecha
                if($fecha != $fechaAnterior){

                    if($primerregistro){
                        $primerregistro=0;      //lo hago para no grabar el array al principio
                    }
                    else {
                        array_push($r,$a);
                    }
                    //creo array
                    $a = $g;    //creo array temporal
                    $a['egresos'][0][1] = $fecha;   //asigno la nueva fecha en array
                    $fechaAnterior= $fecha;
                }
                else{
                    //no hago nada, seguira cargando el dato en el array de fecha actual
                }

                //Continuo ingresando datos

                //Cargo total dependiendo el rubro

                $a['egresos'][$ef['rubro']+1][1] =       $ef['total'];        //LO HAGO CON TODOS LOS RUBROS, PARA APROVECHAR EL INDICE NATURAL.
                                                                            //LUEGO ORDENO POR ORDEN

            }
            array_push($r,$a);  //grabo ultimo array temporal, cuando sale del loop
        //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        // Loop para sumar datos de Egreso de Salarios por Recibo de Sueldos
        //----------------------------------------------------------------------------------------------------
            //En $r debo agregar los totales de recibos de sueldo cargados, en el rubro Salarios

            $sql = "
                    SELECT sum(total) as 'Salarios & C.S.', periodo, monthname(periodo) as mes, year(periodo) as año
                    FROM recibo_sueldos as a
                    where estadoRecibo <> 6
                    and periodo between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
                    group by periodo
                    order by periodo
            ";

            $s =  DB::select(DB::raw($sql));

            if(isset($s[0]))        //Si es nulo, da error
                $salarioDesdeRecibosSueldo = $s[0];

            //dd($salarioDesdeRecibosSueldo);


            //consultar si existe el dia
            foreach($s as $sal){      //por cada salario mensual

                $fecha = $sal->periodo; //Lo llevo al último día del mes!
                //$fecha = Carbon::parse($sal->periodo)->endOfMonth()->format('Y-m-d');
                //Lo cambio en el origen VER!!!
                $i=0;
                $copiado=0;     //flag para avisar que se copiaron datos de salario en matriz global
                foreach ($r as $t) {        //verifico si está en la matriz global
                    if(array_search($fecha,$r[$i]['egresos'][0])){     //Si ya está la fecha en egresos, copio ahí
                        $pepe = 'Salarios & C.S.' ;
                        $r[$i]['egresos'][22][1]  += $sal->$pepe;        //sumo los datos de sueldos en rubro salarios

                        $copiado = 1;
                        break;  //salgo del loop para que no verifique todo
                    }
                    else{

                    }
                    $i++;

                }
                if($copiado==0){    //llega acá si no encontró la fecha en egresos.  Debe crear la fecha nueva.
                    //crear nuevo array y agregarlo al global.  No tiene documentos de egresos en esa fecha
                    //dd($sal->periodo);
                    $a = $g;
                    $a['egresos'][0][1] = $sal->periodo;

                    $pepe = 'Salarios & C.S.' ;
                    $a['egresos'][22][1] = $sal->$pepe;        //sumo los datos de sueldos en rubro salarios

                    array_push($r,$a);  //grabo ultimo array temporal, cuando sale del loop

                    //echo('Crea y copia nuevo registro para fecha'.$fecha);
                    //echo('<BR>');
                }
                //Loop con $r para verificar si está la fecha ya

            }

        //----------------------------------------------------------------------------------------------------
        // Loop para sumar datos de venta a matriz global que ya tiene datos de egresos
        //----------------------------------------------------------------------------------------------------
            //consultar si existe el dia
            foreach($b as $v){      //por cada venta
                //dd($b);
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
        //----------------------------------------------------------------------------------------------------


        //----------------------------------------------------------------------------------------------------
        //Cálculo de totales por CADA día
        //----------------------------------------------------------------------------------------------------
            foreach($r as &$dia){
                //Cálculo de suma de egresos del día
                foreach($dia['egresos'] as $dato){
                    if(is_numeric($dato[1]))
                        $dia['resultados']['totalGastos']+=$dato[1];
                }

                //Calculo el total sumando la forma de pago, eld de fiscal no fiscal.  Elimino Derecho Show
                $dia['resultados']['totalVentas'] = $dia['ingresos']['fp1'] +
                                                    $dia['ingresos']['fp2'] +
                                                    $dia['ingresos']['fp3'] +
                                                    $dia['ingresos']['fp4'] +
                                                    $dia['ingresos']['fp5'] +
                                                    $dia['ingresos']['fp6'] +
                                                    $dia['ingresos']['fp7'] +
                                                    $dia['ingresos']['fp8'] +
                                                    $dia['ingresos']['fp9'] +
                                                    $dia['ingresos']['fp10'] ;

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
        //----------------------------------------------------------------------------------------------------


        $respuesta = array_multisort($r, SORT_ASC);     //reordeno por fecha
        //dd($r);

        //----------------------------------------------------------------------------------------------------
        // Agregar último item con la sumatoria lateral de cada item.  Ej.  Suma de gasto1/día1 + gasto1/día2 + ...
        //----------------------------------------------------------------------------------------------------
            $totalVentas = 0;
            $totalGastos = 0;
            $totalCostoAlimentos = 0;
            $totalCostoBebidas = 0;
            $totalVentaBebidas = 0;
            $totalVentaAlimentos = 0;
            $totalDerechoShow = 0;
            $totalDiferenciaCaja = 0;
            $totalCuentaCorriente = 0;
            //$cuentaIndicesPinta = 0;

            //$cuentaDias = 0;

            foreach($r as $dia){
                $totalVentas            += $dia['resultados']['totalVentas'];
                $totalGastos            += $dia['resultados']['totalGastos'];
                //$totalCostoAlimentos    += $dia['egresos']['Alimentos'];
                $totalCostoAlimentos    += $dia['egresos'][2][1];
                //$totalCostoBebidas      += $dia['egresos']['Bebidas'];
                $totalCostoBebidas      += $dia['egresos'][3][1];
                $totalVentaBebidas      += $dia['ingresos']['ventaBebidas'];
                $totalVentaAlimentos    += $dia['ingresos']['ventaAlimentos'];
                $totalDerechoShow       += $dia['ingresos']['ingreso_por_derecho_show'];
                $totalDiferenciaCaja    += $dia['ingresos']['diferencia_de_caja'];
                $totalCuentaCorriente   += $dia['ingresos']['cuenta_corriente'];
                //$cuentaIndicesPinta     += $dia['ingresos']['indice_pinta'];

                //$cuentaDias++;
            }

            //Cálculo de indices_pinta
            //if($cuentaDias) $promedioIndicePinta = $cuentaIndicesPinta / $cuentaDias;
            //else $promedioIndicePinta = 0;

            $saldo       = $totalVentas - $totalGastos;
            if($totalVentaAlimentos)
                $foodCost = $totalCostoAlimentos *100 / $totalVentaAlimentos;
            if($totalVentaBebidas)
                $beverageCost = $totalCostoBebidas *100 / $totalVentaBebidas;
            if($totalVentas)
            $mixCost = ($totalCostoBebidas+$totalCostoAlimentos) *100 / $totalVentas;

            if($totalVentas)
                $rentabilidad = $saldo / $totalVentas * 100;


            //Calculo totales
            $tot = $g;        //Inicializo array de datos
            $i=0;
            //dd($r);
            foreach($r as &$dia){

                $tot['egresos'][0][1]            = 'Totales';

                for($i=1; $i<Rubro::where('tipo','!=',10)->get()->count()+1; $i++){     //Salteo primer registro y lo hago por todos los rubros
                    $tot['egresos'][$i][1]        += $dia['egresos'][$i][1];

                }

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
                $tot['ingresos']['ingreso_por_derecho_show']    += $dia['ingresos']['ingreso_por_derecho_show'];
                $tot['ingresos']['diferencia_de_caja']          += $dia['ingresos']['diferencia_de_caja'];
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
                    $tot['egresos'][0][1]            = 'Incidencia (%)';
                    for($i=1; $i<Rubro::where('tipo','!=',10)->get()->count()+1; $i++){
                        $tot['egresos'][$i][1]        = number_format($dia['egresos'][$i][1] / $dia['resultados']['totalGastos'] * 100, 1);
                    }


                    $tot['ingresos']['fecha']           = 'Totales';
                    $tot['ingresos']['ventasFiscal']    = number_format($dia['ingresos']['ventasFiscal'] / ($dia['ingresos']['ventasFiscal']+$dia['ingresos']['ventasNoFiscal']) *100, 2);
                    $tot['ingresos']['ventasNoFiscal']  = number_format($dia['ingresos']['ventasNoFiscal']/($dia['ingresos']['ventasFiscal']+$dia['ingresos']['ventasNoFiscal']) *100, 2);
                    $tot['ingresos']['ventaAlimentos']  = number_format($dia['ingresos']['ventaAlimentos']/($dia['ingresos']['ventaAlimentos']+$dia['ingresos']['ventaBebidas']) *100, 1);
                    $tot['ingresos']['ventaBebidas']    = number_format($dia['ingresos']['ventaBebidas'] / ($dia['ingresos']['ventaAlimentos']+$dia['ingresos']['ventaBebidas']) *100, 1);
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
                    $tot['ingresos']['diferencia_de_caja'] = number_format($dia['ingresos']['diferencia_de_caja'] / $dia['ingresos']['ventasTotales'] *100, 1);
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


        //----------------------------------------------------------------------------------------------------

        //----------------------------------------------------------------------------------------------------
        // Reordeno EGRESOS
        //----------------------------------------------------------------------------------------------------
        foreach($r as &$tipo){
            foreach($tipo as $si => &$s){
                if($si == "egresos"){
                    array_multisort(array_column($s,2), SORT_ASC, $s);      //Ordeno los egresos segun orden de Base de Datos
                }
            }
            unset($s);        //desasocia la referencia &s
        }
        unset($tipo);

        //----------------------------------------------------------------------------------------------------

        //Llego bien, pero falta poner los títulos de manero correcta en $m
        $cantidadDeRegistros = count($r);

        //Mando a la vista las distintas formas de pago
        $fp = FormaPago::all();

        $rubro = Rubro::all();     //lo uso para habilitar o no la visualizacion de los rubros
        //VOY A HABILITAR LA VISUALIZACION DESDE ACA

        // return view('crm.resultados.listar4', compact('cantidadDeRegistros','r','foodCost','beverageCost','mixCost','saldo','totalVentas', 'totalGastos',
        return view('crm.resultados.listar4CompletoConCeros', compact('cantidadDeRegistros','r','foodCost','beverageCost','mixCost','saldo','totalVentas', 'totalGastos',
        'totalCostoAlimentos','totalCostoBebidas','totalVentaBebidas','totalVentaAlimentos', 'fechaInicio','fechaFin',
        'totalMediosElect','totalVentasFiscal', 'ivaTotalPagado','fp','rubro','rentabilidad'))

                    ->with('exito',"Consulta generada ok!")
                    ->with(session('exito'));

    }

    public function formulario(){
        return view('crm.resultados.consultar');

    }

}




