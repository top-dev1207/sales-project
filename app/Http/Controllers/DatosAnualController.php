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


class DatosAnualController extends Controller
{

    public function calcularAnual(Request $request, $anio)
    {
        //$fechaInicio = $request->fechaInicio;
        //$fechaFin    = $request->fechaFin;

        //Variables en las consultas

        //$fechaInicio = "2024-10-01";    //A pedido de Bellomo para los socios
        //$fechaInicio = env('FECHA_INICIO','2024-02-01');
        if($anio==2024){
            $fechaInicio = Carbon::create('01-10-'.$anio)->format('Y-m-d');
        }
        else
            $fechaInicio = Carbon::create('01-01-'.$anio)->format('Y-m-d');
        //$fechaFin    = "2024-12-31";
        //$fechaFin = env('FECHA_FIN','2024-12-31');
        $fechaFin = Carbon::create('31-12-'.$anio)->format('Y-m-d');

        //dd($fechaInicio);
        //$anio lo toma dinámicamente
        //$anio = 2024;
        //$anio = env('ANIO_RESULTADOS', '2024');

        //$mesInicio = 10, para 2024;
        if($anio == 2024)
            $mesInicio = env('MES_INICIO', '10');
        else
            $mesInicio = 1;

        Log::info(Auth::user()->name." | consulta RESULTADOS mes a mes $anio | ");

        //-----------------------------------------------------------------------------------------------------
        //  *** Resultados basados en sql puro ***
        //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------
        // Total Egresos Anual - OK
        //-----------------------------------------------------------------------------------------------------

            $sql_total="
                SELECT
                        SUM(importe) as totalAnual
                FROM (
                        SELECT
                            monthname(fecha_limite) as mes, year(fecha_limite) as año,
                            SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                        FROM (
                            select id, fecha_limite, total
                            from facturas as a
                                    join (
                                        select nro_documento, max(id) as idMax
                                        from facturas
                                        group by nro_documento
                                        ) as sub
                                    where a.id = sub.idMax
                                    and a.nro_documento = sub.nro_documento
                                    and estadoDocumento != 6
                                    and year(fecha_limite) = $anio
                                    and month(fecha_limite) >= $mesInicio -- Comienza desde X 2024
                                    and rubro != 55 -- inversiones no entran

                        ) as sub1

                group by year(fecha_limite),monthname(fecha_limite)
                order by year(fecha_limite) asc, monthname(fecha_limite) asc
                ) as sub2
                ;
                ";

            $total = DB::select(DB::raw($sql_total));
            //dd($total[0]->totalAnual);
            $total = $total[0]->totalAnual;
            $totalGastos = $total;  //Falta sumar salarios por recibos

            $sql="
            SELECT sum(total) as total
            FROM recibo_sueldos as a
            where estadoRecibo <> 6
            and month(periodo) >= $mesInicio -- Comienza desde X 2024
            and year(periodo) = $anio


             ;";
             $totalXRecibos = DB::select(DB::raw($sql));
             $totalXRecibos = $totalXRecibos[0]->total;
             //dd($totalXRecibos);

             $totalGastos += $totalXRecibos;  //Sumo el gasto de recibos de sueldo

        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // Egresos por rubro, mes a mes, con incidencia - OK
        //-----------------------------------------------------------------------------------------------------
            $sql=
                "
                SELECT      n.nombre as rubro,
                        SUM(IF(mes = 'January', s.importe, 0)) AS enero,
                        SUM(IF(mes = 'February', s.importe, 0)) AS febrero,
                        SUM(IF(mes = 'March', s.importe, 0)) AS marzo,
                        SUM(IF(mes = 'April', s.importe, 0)) AS abril,
                        SUM(IF(mes = 'May', s.importe, 0)) AS mayo,
                        SUM(IF(mes = 'June', s.importe, 0)) AS junio,
                        SUM(IF(mes = 'July', s.importe, 0)) AS julio,
                        SUM(IF(mes = 'August', s.importe, 0)) AS agosto,
                        SUM(IF(mes = 'September', s.importe, 0)) AS septiembre,
                        SUM(IF(mes = 'October', s.importe, 0)) AS octubre,
                        SUM(IF(mes = 'November', s.importe, 0)) AS noviembre,
                        SUM(IF(mes = 'December', s.importe, 0)) AS diciembre,
                        SUM(s.importe) AS total";
                        //,
                        //round((SUM(s.importe)*100/$total),2) as Incidencia,
                        //s.rubro as 'cod Rubro', SUM(s.cantidadFacturas) as 'cant Fact'
            $sql.="
                FROM (
                        SELECT
                            monthname(fecha_limite) as mes, year(fecha_limite) as año, rubro,
                            SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                        FROM (
                                select id, rubro, fecha_limite, total
                                from facturas as a
                                        join (
                                            select nro_documento, max(id) as idMax
                                            from facturas
                                            group by nro_documento
                                            ) as sub
                                        where a.id = sub.idMax
                                        and a.nro_documento = sub.nro_documento
                                        and estadoDocumento != 6
                                        and year(fecha_limite) = $anio
                                        and month(fecha_limite) >= $mesInicio -- Comienza desde X 2024
                                        and rubro != 55 -- inversiones no entran
                        ) as sub1

                        group by rubro, year(fecha_limite),monthname(fecha_limite)
                        order by year(fecha_limite) asc, monthname(fecha_limite) asc
                ) as s
                join rubros as n
                where s.rubro = n.valor
                GROUP BY s.rubro
                order by n.orden asc;

            ";
            $egresoAnualXRubro = DB::select(DB::raw($sql));

            if(!empty($egresoAnualXRubro[0])){
                if($egresoAnualXRubro[0]->rubro =='Alimentos')
                    $costoAlimentosAnual =  $egresoAnualXRubro[0]->total;       //Solo modifico si el rubro es Alimentos
                else
                    $costoAlimentosAnual =  0;
            }
            else {
                $costoAlimentosAnual =  0;
            }

            if(!empty($egresoAnualXRubro[1])){
                if($egresoAnualXRubro[1]->rubro =='Bebidas')
                    $costoBebidasAnual =  $egresoAnualXRubro[1]->total;       //Solo modifico si el rubro es Bebidas
                else
                    $costoBebidasAnual =  0;
            }
            else {
                $costoBebidasAnual =    0;
            }

            //dd($costoAlimentosAnual);
            //dd($egresoAnualXRubro);             //Si no hay sueldos por factura, no lo toma!!!!
        //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------
        // Egresos Totales de todos los rubros, mes a mes, (con incidencia)
        //-----------------------------------------------------------------------------------------------------
            $sql=
                "
                SELECT      'TOTAL GASTOS' as rubro,
                    SUM(IF(mes = 'January', s.importe, 0)) AS enero,
                    SUM(IF(mes = 'February', s.importe, 0)) AS febrero,
                    SUM(IF(mes = 'March', s.importe, 0)) AS marzo,
                    SUM(IF(mes = 'April', s.importe, 0)) AS abril,
                    SUM(IF(mes = 'May', s.importe, 0)) AS mayo,
                    SUM(IF(mes = 'June', s.importe, 0)) AS junio,
                    SUM(IF(mes = 'July', s.importe, 0)) AS julio,
                    SUM(IF(mes = 'August', s.importe, 0)) AS agosto,
                    SUM(IF(mes = 'September', s.importe, 0)) AS septiembre,
                    SUM(IF(mes = 'October', s.importe, 0)) AS octubre,
                    SUM(IF(mes = 'November', s.importe, 0)) AS noviembre,
                    SUM(IF(mes = 'December', s.importe, 0)) AS diciembre,
                    SUM(s.importe) AS total";
                    // round((SUM(s.importe)*100/1000000),2) as Incidencia,     //Comento acá y agrego .sql para que quede historico
                    // SUM(s.cantidadFacturas) as 'cant Fact'
            $sql.="
                FROM (

                    SELECT
                        monthname(fecha_limite) as mes, year(fecha_limite) as año,
                        SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                    FROM (
                            select id, rubro, fecha_limite, total
                            from facturas as a
                                    join (
                                        select nro_documento, max(id) as idMax
                                        from facturas
                                        group by nro_documento
                                        ) as sub
                                    where a.id = sub.idMax
                                    and a.nro_documento = sub.nro_documento
                                    and estadoDocumento != 6
                                    and year(fecha_limite) = $anio
                                    and month(fecha_limite) >= $mesInicio -- Comienza desde X 2024
                                    and rubro != 55 -- inversiones no entran
                    ) as sub1

                    group by year(fecha_limite),monthname(fecha_limite)
                    order by year(fecha_limite) asc, monthname(fecha_limite) asc

                ) as s;

            ";
            $egresosAnualTotal = DB::select(DB::raw($sql));

            //dd($costoAlimentosAnual);
            //dd($egresosAnualTotal);     //No toma los salarios
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de  SALARIOS, desde Recibos de Sueldos y desde Facturas, mes a mes
        //-----------------------------------------------------------------------------------------------------
                    $sql = "
                    SELECT      n.nombre as rubro,
                        SUM(IF(mes = 'January', s.importe, 0)) AS enero,
                        SUM(IF(mes = 'February', s.importe, 0)) AS febrero,
                        SUM(IF(mes = 'March', s.importe, 0)) AS marzo,
                        SUM(IF(mes = 'April', s.importe, 0)) AS abril,
                        SUM(IF(mes = 'May', s.importe, 0)) AS mayo,
                        SUM(IF(mes = 'June', s.importe, 0)) AS junio,
                        SUM(IF(mes = 'July', s.importe, 0)) AS julio,
                        SUM(IF(mes = 'August', s.importe, 0)) AS agosto,
                        SUM(IF(mes = 'September', s.importe, 0)) AS septiembre,
                        SUM(IF(mes = 'October', s.importe, 0)) AS octubre,
                        SUM(IF(mes = 'November', s.importe, 0)) AS noviembre,
                        SUM(IF(mes = 'December', s.importe, 0)) AS diciembre,
                        SUM(s.importe) AS total

                FROM (
                        SELECT
                            monthname(fecha_limite) as mes, year(fecha_limite) as año, rubro,
                            SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                        FROM (
                                select id, rubro, fecha_limite, total
                                from facturas as a
                                        join (
                                            select nro_documento, max(id) as idMax
                                            from facturas
                                            group by nro_documento
                                            ) as sub
                                        where a.id = sub.idMax
                                        and a.nro_documento = sub.nro_documento
                                        and estadoDocumento != 6
                                        and year(fecha_limite) = $anio
                                        and month(fecha_limite) >= $mesInicio -- Comienza desde X 2024
                                        and rubro=21 	-- salarios
                        ) as sub1

                        group by rubro, year(fecha_limite),monthname(fecha_limite)
                        order by year(fecha_limite) asc, monthname(fecha_limite) asc
                ) as s
                join rubros as n
                where s.rubro = n.valor
                GROUP BY s.rubro
                order by n.orden asc;

                ";

            $s =  DB::select(DB::raw($sql));
            if(empty($s))   $salarioDesdeFacturas = 0;
            else            $salarioDesdeFacturas = $s[0];


            //dd($salarioDesdeFacturas);

            $sql = "
                SELECT      'Salarios & C.S.' as tipo,
                        SUM(IF(mes = 'January', s.sueldo, 0)) AS enero,
                        SUM(IF(mes = 'February', s.sueldo, 0)) AS febrero,
                        SUM(IF(mes = 'March', s.sueldo, 0)) AS marzo,
                        SUM(IF(mes = 'April', s.sueldo, 0)) AS abril,
                        SUM(IF(mes = 'May', s.sueldo, 0)) AS mayo,
                        SUM(IF(mes = 'June', s.sueldo, 0)) AS junio,
                        SUM(IF(mes = 'July', s.sueldo, 0)) AS julio,
                        SUM(IF(mes = 'August', s.sueldo, 0)) AS agosto,
                        SUM(IF(mes = 'September', s.sueldo, 0)) AS septiembre,
                        SUM(IF(mes = 'October', s.sueldo, 0)) AS octubre,
                        SUM(IF(mes = 'November', s.sueldo, 0)) AS noviembre,
                        SUM(IF(mes = 'December', s.sueldo, 0)) AS diciembre,
                        SUM(s.sueldo) AS total

                FROM (
                                SELECT sum(total) as sueldo, monthname(periodo) as mes, year(periodo) as año
                                FROM recibo_sueldos as a
                                where estadoRecibo <> 6
                                and month(periodo) >= $mesInicio -- Comienza desde X 2024
                                and year(periodo) = $anio
                                group by periodo
                                order by periodo
                ) as s


                ";

            $s =  DB::select(DB::raw($sql));
            $salarioDesdeRecibosSueldo = $s[0];
            //dd($salarioDesdeRecibosSueldo);

            //-----------------------------------------------------------------------------------------------------

            //dd($salarioDesdeRecibosSueldo);
            if($salarioDesdeFacturas)       //Si existe al menos una factura con un salario
            {
                //Suma de los 2 resultados
                $salario = array();
                $i=0;
                foreach($salarioDesdeRecibosSueldo as $key=>$valor){
                    if($i++ == 0)
                        $salario[$key] = $valor;
                        //dump($i);
                    else
                        $salario[$key] = $valor + $salarioDesdeFacturas->$key;      //Le sumo a los salarios por recibo, los salarios por factura
                        //dump($valor);
                }
            }
                //dd($salario);

            //-----------------------------------------------------------------------------------------------------
               //dd($egresoAnualXRubro);

            //Busco el indice de Salarios en $egresoAnualXRubro
            $encontrado = 0;        //Encuentra al menos 1 salario por facturas
            $j=0;
            foreach($egresoAnualXRubro as $key=>$valor){
                //dd($valor);
                if(strcmp($valor->rubro,"Salarios & C.S.") == 0){
                    $encontrado = 1;
                    break;
                }
                $j++;   //Recorro el array y obtengo el indice buscado para usarlo en la proxima rutina
            }
            //dd($j);     //En j tengo el indice de "Salarios & C.S"
            //dd($egresoAnualXRubro);
            //Inclusión de salarios desde Recibos en egresosAnualXRubro y egresosAnualTotal

            //dd($egresosAnualTotal[0]);      //Tengo que sumarle lo de los salarios por recibos


            if($encontrado==1){
                $i=0;
                foreach($salarioDesdeRecibosSueldo as $key=>$valor){
                    if($i++ != 0)  { //Omite titulo
                        $egresoAnualXRubro[$j]->$key += $valor;     //Sumo datos de cálculo de salarios desde Recibos de sueldo
                        $egresosAnualTotal[0]->$key += $valor;      //Sumo datos de cálculo de salarios desde Recibos de sueldo
                        //dump($egresoAnualXRubro[3]->$key);
                    }
                }
                //dd($egresoAnualXRubro);
                //dd($egresosAnualTotal);
            }
            else{   //No hay Salarios por factura.  Debo crear uno nuevo
                //Agrego al final los salarios por recibos al total de gastos
                array_push($egresoAnualXRubro, $salarioDesdeRecibosSueldo);
                $i=0;
                foreach($salarioDesdeRecibosSueldo as $key=>$valor){
                    if($i++ != 0)  { //Omite titulo
                        $egresosAnualTotal[0]->$key += $valor;      //Sumo datos de cálculo de salarios desde Recibos de sueldo
                        //dump($egresoAnualXRubro[3]->$key);
                    }
                }

                //dd($salarioDesdeRecibosSueldo);
            }

            //dd($egresoAnualXRubro);
            //dd($egresosAnualTotal);

            //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------



        //-----------------------------------------------------------------------------------------------------
        // Ingresos, mes a mes - OK
        //-----------------------------------------------------------------------------------------------------

            $sql_ingresos = "
            select
                    month(v.fecha_venta) as Mes,year(v.fecha_venta) as Año,
                    SUM(v.ventas_fiscal) as ventasFiscal, SUM(v.ventas_no_fiscal) as ventasNoFiscal, (SUM(v.ventas_fiscal) + SUM(v.ventas_no_fiscal)) as Tot1,
                    SUM(v.venta_alimentos) as ventaAlimentos, SUM(v.venta_bebidas) as ventaBebidas, (SUM(v.venta_alimentos) + SUM(v.venta_bebidas)) as Tot2,
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
                    IFNULL(SUM(v.fp1),0)+IFNULL(SUM(v.fp2),0)+IFNULL(SUM(v.fp3),0)+IFNULL(SUM(v.fp4),0)+IFNULL(SUM(v.fp5),0)+IFNULL(SUM(v.fp6),0)+IFNULL(SUM(v.fp7),0)+IFNULL(SUM(v.fp8),0)+IFNULL(SUM(v.fp9),0)+IFNULL(SUM(v.fp10),0) as Tot3,

                    SUM(v.ingresos) as ventasTotales, SUM(anulaciones) as anulaciones, SUM(egresos) as egresos,
                    SUM(IF(caja=1, ingresos, 0)) as temple_1, SUM(IF(caja=2, ingresos, 0)) as temple_2,
                    SUM(s.entradaTemple) as derecho_show, SUM(indice_pinta) as indice_pinta,
                    SUM(cuenta_corriente) as cuenta_corriente


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
                and s.estado=1

            where v.fecha_venta between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
            and year(v.fecha_venta) = $anio

            group by month(v.fecha_venta)
            order by month(v.fecha_venta) asc

            ";

            $ingresosMesMes =  DB::select(DB::raw($sql_ingresos));
            if(!empty($ingresosMesMes))
                $ingresosAnuales = $ingresosMesMes[0]->ventasTotales;
            else
                $ingresosAnuales = 0;

        //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------
        // Venta Total Anual - OK
        //-----------------------------------------------------------------------------------------------------
            $sql_ventas_totales = "
            select
                    SUM(v.ingresos) as ventasTotales

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
                and s.estado=1

            where v.fecha_venta between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
            and year(v.fecha_venta) = $anio
            ";

            $i =  DB::select(DB::raw($sql_ventas_totales));
            //dd($i);
            $ventasTotalAnual = $i[0]->ventasTotales;
            //dd($ventasTotalAnual);
        //-----------------------------------------------------------------------------------------------------


        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // Cálculo de ingresos Mes a Mes
        //-----------------------------------------------------------------------------------------------------
            $ingresosAnuales = array();


            $a= $this->obtenerMesMes('Ventas Fiscal','ventas_fiscal', $anio, $mesInicio);
            array_push($ingresosAnuales, $a);
            $a= $this->obtenerMesMes('Ventas No Fiscal','ventas_no_fiscal', $anio, $mesInicio);
            array_push($ingresosAnuales, $a);
            $a = $this->obtenerMesMes('Invitaciones','cuenta_corriente', $anio, $mesInicio);
            array_push($ingresosAnuales, $a);
            $a = $this->obtenerMesMesCaja('Temple 1','ingresos', $anio, $mesInicio);
            array_push($ingresosAnuales, $a );
            $a = $this->obtenerMesMesCaja('Temple 2','ingresos', $anio, $mesInicio);
            array_push($ingresosAnuales, $a );
            $a = $this->obtenerMesMes('Indice Pinta','indice_pinta', $anio, $mesInicio);
            array_push($ingresosAnuales, $a );
            $a = $this->obtenerMesMes('Entrada Shows','derecho_show', $anio, $mesInicio);
            array_push($ingresosAnuales, $a );
            $a = $this->obtenerMesMes('Venta Alimentos','venta_alimentos', $anio, $mesInicio);
            array_push($ingresosAnuales, $a);
            $a = $this->obtenerMesMes('Venta Bebidas','venta_bebidas', $anio, $mesInicio);
            array_push($ingresosAnuales, $a);

            //dd($ingresosAnuales);
            //dump($this->obtenerMesMes('Ventas Fiscal', 2024,'ventas_fiscal'));
            //dump($this->obtenerMesMes('Ventas No Fiscal', 2024,'ventas_no_fiscal'));
            //dd("fin");
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // Cálculo de ingresos TOTALES Mes a Mes
        //-----------------------------------------------------------------------------------------------------
            $ingresosAnualesTotales = array();

            $a = $this->obtenerMesMes('VENTAS TOTALES','ingresos', $anio, $mesInicio);
            array_push($ingresosAnualesTotales, $a);
        //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------
        // Cálculo de Retiro de Dividendoes TOTALES Mes a Mes
        //-----------------------------------------------------------------------------------------------------
            $retiroDividendos = array();

            $sql = "
                SELECT 'RETIRO DIVIDENDOS' as tipo,
                    SUM(IF(mes = 'January',     t.importe, 0)) AS enero,
                    SUM(IF(mes = 'February',    t.importe, 0)) AS febrero,
                    SUM(IF(mes = 'March',       t.importe, 0)) AS marzo,
                    SUM(IF(mes = 'April',       t.importe, 0)) AS abril,
                    SUM(IF(mes = 'May',         t.importe, 0)) AS mayo,
                    SUM(IF(mes = 'June',        t.importe, 0)) AS junio,
                    SUM(IF(mes = 'July',        t.importe, 0)) AS julio,
                    SUM(IF(mes = 'August',      t.importe, 0)) AS agosto,
                    SUM(IF(mes = 'September',   t.importe, 0)) AS septiembre,
                    SUM(IF(mes = 'October',     t.importe, 0)) AS octubre,
                    SUM(IF(mes = 'November',    t.importe, 0)) AS noviembre,
                    SUM(IF(mes = 'December',    t.importe, 0)) AS diciembre,
                    SUM(t.importe) AS total
                FROM (
                    SELECT monthname(fecha) as mes, year(fecha) as año,
                    importeOrigen as importe

                    FROM (
                        SELECT *
                        FROM `transacciones`
                        where destino = 200
                        and movimiento != 4
                        and estado!=5
                        and fecha between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta

                    ) as t1
                ) as t;

            ";

            $r =  DB::select(DB::raw($sql));
            //dd($r[0]);
            $retiroDividendos = $r[0];
            //dd($r);
            //dd($retiroDividendos);


        //-----------------------------------------------------------------------------------------------------
        //-----------------------------------------------------------------------------------------------------
        // Cálculo de Datos Adicionales (completados manualmente)  Mes a Mes
        //-----------------------------------------------------------------------------------------------------
            $iibb =                 $this->obtenerDatosResultados('IIBB',                   'iibb',                 $anio, $mesInicio);
            $iva =                  $this->obtenerDatosResultados('IVA',                    'iva',                  $anio, $mesInicio);
            $impuestoGanancia =     $this->obtenerDatosResultados('IMPUESTO GANANCIA',      'impuestoGanancias',    $anio, $mesInicio);
            $ingresoPropietarios =  $this->obtenerDatosResultados('INGRESOS PROPIETARIOS',  'ingresoPropietarios',  $anio, $mesInicio);
            $inversiones =          $this->obtenerDatosResultados('INVERSIONES',            'inversiones',          $anio, $mesInicio);
            //dump($inversiones);
            $pagoDeudaAtrasada =    $this->obtenerDatosResultados('PAGO DEUDA ATRASADA',    'pagoDeudaAtrasada',    $anio, $mesInicio);
            $gastosCtaCte =         $this->obtenerDatosResultados('GASTOS CTA. CTE.',       'gastosCtaCte',         $anio, $mesInicio);
            //dd($ingresoPropietarios);
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // Cálculo de Inversiones cargadas en documentos tipo Facturas/Remitos
        //-----------------------------------------------------------------------------------------------------
            $sql=
            "
            SELECT      n.nombre as tipo,
                    SUM(IF(mes = 'January', s.importe, 0)) AS enero,
                    SUM(IF(mes = 'February', s.importe, 0)) AS febrero,
                    SUM(IF(mes = 'March', s.importe, 0)) AS marzo,
                    SUM(IF(mes = 'April', s.importe, 0)) AS abril,
                    SUM(IF(mes = 'May', s.importe, 0)) AS mayo,
                    SUM(IF(mes = 'June', s.importe, 0)) AS junio,
                    SUM(IF(mes = 'July', s.importe, 0)) AS julio,
                    SUM(IF(mes = 'August', s.importe, 0)) AS agosto,
                    SUM(IF(mes = 'September', s.importe, 0)) AS septiembre,
                    SUM(IF(mes = 'October', s.importe, 0)) AS octubre,
                    SUM(IF(mes = 'November', s.importe, 0)) AS noviembre,
                    SUM(IF(mes = 'December', s.importe, 0)) AS diciembre,
                    SUM(s.importe) AS total";
            $sql.="
                FROM (
                        SELECT
                            monthname(fecha_limite) as mes, year(fecha_limite) as año, rubro,
                            SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                        FROM (
                                select id, rubro, fecha_limite, total
                                from facturas as a
                                        join (
                                            select nro_documento, max(id) as idMax
                                            from facturas
                                            group by nro_documento
                                            ) as sub
                                        where a.id = sub.idMax
                                        and a.nro_documento = sub.nro_documento
                                        and estadoDocumento != 6
                                        and year(fecha_limite) = $anio
                                        and month(fecha_limite) >= $mesInicio -- Comienza desde X 2024
                                        and rubro = 55 -- inversiones SI entran (Únicamente)
                        ) as sub1

                        group by rubro, year(fecha_limite),monthname(fecha_limite)
                        order by year(fecha_limite) asc, monthname(fecha_limite) asc
                ) as s
                join rubros as n
                where s.rubro = n.valor
                GROUP BY s.rubro
                order by n.orden asc;

            ";
            $inv = DB::select(DB::raw($sql));
            //dd($inv);
            if(!empty($inv))   {
                //dd($inv);
                $inversionesDesdeFacturas = $inv [0];
            }
            else
                $inversionesDesdeFacturas = 0;
            //dump($inversionesDesdeFacturas);

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de Inversiones totales (desde facturas + cargadas a mano)
        //-----------------------------------------------------------------------------------------------------
            if(!empty($inv))   {
                $i=0;
                foreach($inversiones as $key => $inv){
                    if($i++){   //Para saltear el primer campo
                        //echo $key;
                        $inversiones->$key += $inversionesDesdeFacturas->$key;

                        // echo $inversionesDesdeFacturas->$key;
                        //echo '<BR>';
                        // echo $inversiones->$key;
                        // echo '<BR>';
                    }
                }
            }

            //$inversiones->ene += $inversionesDesdeFacturas->dic;
            //dd($inversiones);

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de Ganancia Bruta
        //-----------------------------------------------------------------------------------------------------
            //dd($egresosAnualTotal);
            //dd($ingresosAnualesTotales[0]);

            //$ingresosAnualesTotales
            //egresosAnualTotal

            $i=0;
            $gananciaBruta = array();
            foreach($egresosAnualTotal[0] as $key => $gasto){
                if($i++ == 0) $gananciaBruta['tipo'] = "GANANCIA BRUTA";
                else{
                    if(isset($ingresosAnualesTotales[0]->$key)) {
                        $gananciaBruta[$key] = $ingresosAnualesTotales[0]->$key- $gasto;
                    }
                    else $gananciaBruta[$key] = - $gasto;

                }
            }

            //dd($gananciaBruta);
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de Ganancia Neta
        //-----------------------------------------------------------------------------------------------------
            $i=0;

            $gananciaNetaMesMes = array();
            foreach($gananciaBruta as $key => $bruto){

                if($i++ == 0) $gananciaNetaMesMes['tipo'] = "GANANCIA NETA";
                else{
                    $gananciaNetaMesMes[$key]= $bruto - $iibb->$key - $iva->$key - $impuestoGanancia->$key;
                }
            }

            //dd($gananciaNetaMesMes);
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de Caja Final
        //-----------------------------------------------------------------------------------------------------
            $i=0;

            $cajaFinal = array();
            foreach($gananciaNetaMesMes as $key => $netoMensual){

                if($i++ == 0) $cajaFinal['tipo'] = "CAJA FINAL";
                else{
                    $cajaFinal[$key]= $netoMensual + $ingresoPropietarios->$key - $inversiones->$key - $pagoDeudaAtrasada->$key
                    - $retiroDividendos->$key - $gastosCtaCte->$key;
                }
            }

        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // --- Cálculo de Costos Alimentos, Bebidas, Mixtos y % mes a mes
        //-----------------------------------------------------------------------------------------------------
            //dd($ingresosMesMes);
            //dd($ingresosAnualesTotales);

            $ventaBebidasMesAMes =      $this->obtenerMesMes('VENTAS Bebidas','venta_bebidas', $anio, $mesInicio);
            $ventaAlimentosMesAMes =    $this->obtenerMesMes('VENTAS Alimentos','venta_alimentos', $anio, $mesInicio);
            $ventaTotalMesAMes =        $this->obtenerMesMes('VENTAS Totales','ingresos', $anio, $mesInicio);
            //gananciaNetaMesMes
            if(!empty($egresoAnualXRubro[0]))
                $CostoAlimentosMesAMes =    $egresoAnualXRubro[0];
            else $CostoAlimentosMesAMes = 0;
            if(!empty($egresoAnualXRubro[1]))
                $CostoBebidasMesAMes =      $egresoAnualXRubro[1];       //OJO QUE ESTÁ AGARRADO DEL ORDEN DEL COSTOS ANUALES.  POSIBLE PROBLEMA A FUTURO!!!
            else $CostoBebidasMesAMes = 0;


            //dd($ventaAlimentosMesAMes);
            //dd($CostoAlimentosMesAMes);

            //$costoMixto = ($costoAlimentosAnual + $costoBebidasAnual)/ $ventasTotalAnual * 100;
            //$gananciaBruta =  $ventasTotalAnual - $totalGastos;
            //$gananciaNeta = $gananciaBruta;    // -IIBB-IVA-Impuestos Ganancia
            //$gananciaPorcentaje = $gananciaNeta / $ventasTotalAnual * 100;

            $i=0;

            $gananciaPorcentajeMesAMes = array();
            foreach($gananciaNetaMesMes as $key => $netoMensual){

                if($i++ == 0) $gananciaPorcentajeMesAMes['tipo'] = "GANANCIA %";
                else{
                    if(!empty($ventaTotalMesAMes->$key))  //Para evitar el DIV/0
                        $gananciaPorcentajeMesAMes[$key]= $netoMensual / $ventaTotalMesAMes->$key * 100;
                    else $gananciaPorcentajeMesAMes[$key]= '-';
                }
            }

            $i=0;

            $costoAlimentoPorcentajeMesAMes = array();
            foreach($CostoAlimentosMesAMes as $key => $costoAlim){

                if($i++ == 0) $costoAlimentoPorcentajeMesAMes['tipo'] = "COSTO ALIMENTO %";
                else{
                    if(!empty($ventaAlimentosMesAMes->$key))  //Para evitar el DIV/0
                        $costoAlimentoPorcentajeMesAMes[$key]= $costoAlim / $ventaAlimentosMesAMes->$key * 100;
                    else $costoAlimentoPorcentajeMesAMes[$key]= '-';
                }
            }

            $i=0;

            //if(!empty($CostoBebidasMesAMes)){
                $costoBebidaPorcentajeMesAMes = array();
                foreach($CostoBebidasMesAMes as $key => $costoBeb){

                    if($i++ == 0) $costoBebidaPorcentajeMesAMes['tipo'] = "COSTO BEBIDA %";
                    else{
                        if(!empty($ventaBebidasMesAMes->$key))  //Para evitar el DIV/0
                            $costoBebidaPorcentajeMesAMes[$key]= $costoBeb / $ventaBebidasMesAMes->$key * 100;
                        else $costoBebidaPorcentajeMesAMes[$key]= '-';
                    }
                }

            //}
            //else $costoBebidaPorcentajeMesAMes = 0;

            $i=0;

            //if(!empty($CostoBebidasMesAMes)){
                $costoMixtoMesAMes = array();
                foreach($CostoBebidasMesAMes as $key => $costoBeb){

                    if($i++ == 0) $costoMixtoMesAMes['tipo'] = "COSTO MIXTO %";
                    else{
                        if(!empty($ventaTotalMesAMes->$key))  //Para evitar el DIV/0
                            $costoMixtoMesAMes[$key]= ($costoBeb + $CostoAlimentosMesAMes->$key) / $ventaTotalMesAMes->$key * 100;
                        else $costoMixtoMesAMes[$key]= '-';
                    }
                }
            //}
            //else $costoMixtoMesAMes = 0;
            //dd($gananciaPorcentaje);
        //-----------------------------------------------------------------------------------------------------

        //-----------------------------------------------------------------------------------------------------
        // Cálculo de Indicadores
        //-----------------------------------------------------------------------------------------------------

            //Costo Mixto
            if($ventasTotalAnual!=0)
                $costoMixto = ($costoAlimentosAnual + $costoBebidasAnual)/ $ventasTotalAnual * 100;
            else $costoMixto = 0;
            //dd($costoMixto);

            //Ganancia Bruta
            //$gananciaBruta =  $ventasTotalAnual - $totalGastos;
            //dd($gananciaBruta);

            //dd($gananciaNetaMesMes["total"]);
            $gananciaNeta = $gananciaNetaMesMes["total"];    // -IIBB-IVA-Impuestos Ganancia
            if($ventasTotalAnual!=0)
                $gananciaPorcentaje = $gananciaNeta / $ventasTotalAnual * 100;
            else
                $gananciaPorcentaje = 0;
            //dd($gananciaPorcentaje);

        //-----------------------------------------------------------------------------------------------------
        // --- Prueba ingresos similar a egresos
        //-----------------------------------------------------------------------------------------------------


            //dump($egresoAnualXRubro);
            //dump($ingresosMesMes);

            $sql_ingresos = "

            select
            'Ventas Totales' as 'tipo',
            SUM(IF(mes = 'January', n.ingresos, 0)) AS enero,
            SUM(IF(mes = 'February', n.ingresos, 0)) AS febrero,
            SUM(IF(mes = 'March', n.ingresos, 0)) AS marzo,
            SUM(IF(mes = 'April', n.ingresos, 0)) AS abril,
            SUM(IF(mes = 'May', n.ingresos, 0)) AS mayo,
            SUM(IF(mes = 'June', n.ingresos, 0)) AS junio,
            SUM(IF(mes = 'July', n.ingresos, 0)) AS julio,
            SUM(IF(mes = 'August', n.ingresos, 0)) AS agosto,
            SUM(IF(mes = 'September', n.ingresos, 0)) AS septiembre,
            SUM(IF(mes = 'October', n.ingresos, 0)) AS octubre,
            SUM(IF(mes = 'November', n.ingresos, 0)) AS noviembre,
            SUM(IF(mes = 'December', n.ingresos, 0)) AS diciembre,
            SUM(n.ingresos) AS total

            from(

            select monthname(v.fecha_venta) as mes,v.*


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
                and s.estado=1



                where v.fecha_venta between '$fechaInicio' and '$fechaFin'                          -- Condiciones generales de la consulta
                -- group by month(v.fecha_venta)
                -- order by month(v.fecha_venta) asc
            ) as n

            ";

            $i =  DB::select(DB::raw($sql_ingresos));
            //dd($i);
            //$ingresosAnuales = $i[0]->ventasTotales;
        //-----------------------------------------------------------------------------------------------------
        Log::info(Auth::user()->name." | Consulta de resultados Anuales (mes a mes)");


        return view('crm.resultados.resultadosAnualesMesAMes',
            compact('egresoAnualXRubro','costoMixto','ventasTotalAnual','gananciaPorcentaje','gananciaNeta','ingresosMesMes',
                    'egresosAnualTotal','ingresosAnuales','ingresosAnualesTotales', 'gananciaBruta', 'ingresoPropietarios',
                    'retiroDividendos', 'gananciaNetaMesMes','iibb','iva','impuestoGanancia','inversiones',
                    'pagoDeudaAtrasada','gastosCtaCte', 'cajaFinal', 'gananciaPorcentajeMesAMes',
                    'costoAlimentoPorcentajeMesAMes', 'costoBebidaPorcentajeMesAMes', 'costoMixtoMesAMes', 'anio'));


    }

    public function calcularAnualAnio(Request $request)
    {
        return redirect(route('resultados.anual', ['anio'=>$request->anio]));
    }

    //-----------------------------------------------------------------------------------------------------
    //  Funcion que devuelve item de venta, analizado mes a mes en el anio consultado
    //-----------------------------------------------------------------------------------------------------
    public function obtenerMesMes($descripcion, $nombreEnTabla, $anio, $mesInicio){

        $nombreEnTabla = 'n.'.$nombreEnTabla;       //debe ser n.nombreEnTabla en la consulta
        $sql = "
            select
                '$descripcion' as 'tipo',
                SUM(IF(mes = 'January',     $nombreEnTabla, 0)) AS enero,
                SUM(IF(mes = 'February',    $nombreEnTabla, 0)) AS febrero,
                SUM(IF(mes = 'March',       $nombreEnTabla, 0)) AS marzo,
                SUM(IF(mes = 'April',       $nombreEnTabla, 0)) AS abril,
                SUM(IF(mes = 'May',         $nombreEnTabla, 0)) AS mayo,
                SUM(IF(mes = 'June',        $nombreEnTabla, 0)) AS junio,
                SUM(IF(mes = 'July',        $nombreEnTabla, 0)) AS julio,
                SUM(IF(mes = 'August',      $nombreEnTabla, 0)) AS agosto,
                SUM(IF(mes = 'September',   $nombreEnTabla, 0)) AS septiembre,
                SUM(IF(mes = 'October',     $nombreEnTabla, 0)) AS octubre,
                SUM(IF(mes = 'November',    $nombreEnTabla, 0)) AS noviembre,
                SUM(IF(mes = 'December',    $nombreEnTabla, 0)) AS diciembre,
                SUM($nombreEnTabla) AS total

            from(
                select monthname(v.fecha_venta) as mes,v.*


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
                    and s.estado=1

                where year(v.fecha_venta) = $anio                -- Condiciones generales de la consulta
                and month(v.fecha_venta) >= $mesInicio -- a partir de X mes
            ) as n
        ";

        $i =  DB::select(DB::raw($sql));

        //dd($i[0]);
        return ($i[0]);
    }

    //-----------------------------------------------------------------------------------------------------
    //  Funcion que devuelve item de venta para CAJA, analizado mes a mes en el anio consultado
    //-----------------------------------------------------------------------------------------------------
    public function obtenerMesMesCaja($descripcion, $nombreEnTabla, $anio, $mesInicio){

        if($descripcion == 'Temple 1') $caja=1;
        else $caja = 2;

        $nombreEnTabla = 'n.'.$nombreEnTabla;       //debe ser n.nombreEnTabla en la consulta
        $sql = "
            select
                '$descripcion' as 'tipo',
                SUM(IF(mes = 'January',     (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS enero,
                SUM(IF(mes = 'February',    (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS febrero,
                SUM(IF(mes = 'March',       (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS marzo,
                SUM(IF(mes = 'April',       (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS abril,
                SUM(IF(mes = 'May',         (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS mayo,
                SUM(IF(mes = 'June',        (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS junio,
                SUM(IF(mes = 'July',        (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS julio,
                SUM(IF(mes = 'August',      (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS agosto,
                SUM(IF(mes = 'September',   (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS septiembre,
                SUM(IF(mes = 'October',     (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS octubre,
                SUM(IF(mes = 'November',    (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS noviembre,
                SUM(IF(mes = 'December',    (IF(caja=$caja, $nombreEnTabla, 0)), 0)) AS diciembre,
                SUM((IF(caja=$caja, $nombreEnTabla, 0))) AS total

            from(
                select monthname(v.fecha_venta) as mes,v.*


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
                    and s.estado=1

                where year(v.fecha_venta) = $anio                -- Condiciones generales de la consulta
                and month(v.fecha_venta) >= $mesInicio -- a partir de X mes
            ) as n
        ";

        $i =  DB::select(DB::raw($sql));
        //dd(i[0]);
        return ($i[0]);
    }

    //-----------------------------------------------------------------------------------------------------
    //  Funcion que devuelve item de datosResultados, analizado mes a mes y total en el anio consultado
    //-----------------------------------------------------------------------------------------------------
    public function obtenerDatosResultados($descripcion, $nombreEnTabla, $anio, $mesInicio){

        //$nombreEnTabla = 'n.'.$nombreEnTabla;       //debe ser n.nombreEnTabla en la consulta
        $sql = "
            select
                '$descripcion' as 'tipo',
                SUM(IF(mes = 'January',     n.importe, 0)) AS enero,
                SUM(IF(mes = 'February',    n.importe, 0)) AS febrero,
                SUM(IF(mes = 'March',       n.importe, 0)) AS marzo,
                SUM(IF(mes = 'April',       n.importe, 0)) AS abril,
                SUM(IF(mes = 'May',         n.importe, 0)) AS mayo,
                SUM(IF(mes = 'June',        n.importe, 0)) AS junio,
                SUM(IF(mes = 'July',        n.importe, 0)) AS julio,
                SUM(IF(mes = 'August',      n.importe, 0)) AS agosto,
                SUM(IF(mes = 'September',   n.importe, 0)) AS septiembre,
                SUM(IF(mes = 'October',     n.importe, 0)) AS octubre,
                SUM(IF(mes = 'November',    n.importe, 0)) AS noviembre,
                SUM(IF(mes = 'December',    n.importe, 0)) AS diciembre,
                SUM( n.importe) AS total

            from(
                SELECT $nombreEnTabla as importe, monthname(periodo) as mes, year(periodo) as año

                    FROM (
                        SELECT *
                        FROM `datos_resultados` as t1
                        where estado=1
                        and year(periodo) = $anio                 -- Condiciones generales de la consulta
                        and month(periodo) >= $mesInicio -- Comienza desde X 2024

                    ) as t2
            ) as n
        ";

        $i =  DB::select(DB::raw($sql));
        //dd($i[0]);
        return ($i[0]);
    }

    //-----------------------------------------------------------------------------------------------------
    //  Funcion que devuelve detalle de items, generalizado
    //-----------------------------------------------------------------------------------------------------
    public function detalleItems(Request $request, $tipo, $rango, $unidad, $anio){

        switch ($rango){
            case 1: $mes=$unidad;       //Mensual
            break;
            case 0:
                break;
            default:
                $mes=$unidad;
        }

        $nombreMes = $this->obtenerNombreMes($mes);

        $origen = substr($tipo, 0, 1);

        switch($origen){
            case 'e':
                $tipo = str_replace('e', '', $tipo);

                //Saber el id del rubro
                //en $tipo tengo el nro de rubro.  Cuento y obtengo el id haciendo la inversa de la tabla
                $sql=
                    "SELECT n.nombre as rubro, n.valor as id
                        FROM (
                                SELECT
                                    monthname(fecha_limite) as mes, year(fecha_limite) as año, rubro,
                                    SUM(total) as importe, count(month(fecha_limite)) as cantidadFacturas
                                FROM (
                                        select id, rubro, fecha_limite, total
                                        -- select *
                                        from facturas as a
                                                join (
                                                    select nro_documento, max(id) as idMax
                                                    from facturas
                                                    group by nro_documento
                                                    ) as sub
                                                where a.id = sub.idMax
                                                and a.nro_documento = sub.nro_documento
                                                and estadoDocumento != 6
                                                and year(fecha_limite) = $anio
                                                and rubro != 55 -- inversiones no entran
                                ) as sub1

                                group by rubro, year(fecha_limite),monthname(fecha_limite)
                                order by year(fecha_limite) asc, monthname(fecha_limite) asc
                        ) as s
                        join rubros as n
                        where s.rubro = n.valor
                        GROUP BY s.rubro
                        order by n.orden asc;
                    ";

                $listadoGastosId = DB::select(DB::raw($sql));

                $tipo = $tipo-1;        //Listado de gastos con indice 0 acá.
                //dd($listadoGastosId[$tipo-1]);
                $rubroId = $listadoGastosId[$tipo]->id;
                $nombreRubro = $listadoGastosId[$tipo]->rubro;



                $titulo = "de ".$listadoGastosId[$tipo]->rubro." - $nombreMes $anio";
                $items_pag = 50;

                $ultimos_por_nro_documento = Facturas::select('nro_documento', DB::raw('MAX(id) as idMax'))        //armo tabla temporal, con los id y sus maximos created at
                ->groupBy('nro_documento');

                if($mes == 13)  //anual
                    $facturas = Facturas::joinSub($ultimos_por_nro_documento, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                        $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                        $join->on('facturas.id', '=', 'ultimo_doc.idMax');  })
                            ->whereDate('created_at','>=', '2024-02-01' )
                            ->where('estadoDocumento','!=',6)         //Que no sea factura borrado
                            ->whereYear('fecha_limite', '=',$anio)
                            ->where('rubro','=',$rubroId)
                            ->OrderBy('facturas.nro_documento', 'desc')
                            ->paginate($items_pag);

                else    //mensual
                    $facturas = Facturas::joinSub($ultimos_por_nro_documento, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                        $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                        $join->on('facturas.id', '=', 'ultimo_doc.idMax');  })
                            ->whereDate('created_at','>=', '2024-02-01' )
                            ->where('estadoDocumento','!=',6)         //Que no sea factura borrado
                            ->whereMonth('fecha_limite', '=', $mes)
                            ->whereYear('fecha_limite', '=',$anio)
                            ->where('rubro','=',$rubroId)
                            ->OrderBy('facturas.nro_documento', 'desc')
                            ->paginate($items_pag);

                $con_vencimiento=false;



                if($facturas->count()){
                    $paginate = true;
                    $searchPanes=false;
                    $tipoPago = FacturasController::seleccionarTipoPagosHabilitados(TipoPago::where('estado','=',1)->get(), 'pagos', 0);    //envío los posibles, y recibo los válidos segun rol
                    $estadoOperativo = FacturasController::seleccionarEstadosEntregaHabilitados(EstadoEntrega::where('estado','!=',0)->get());   //envío los posibles, y recibo los válidos segun rol
                    return view('crm.facturas.listar_pagarPro2', compact('facturas','searchPanes','tipoPago','estadoOperativo', 'titulo','paginate','con_vencimiento') );
                }
                else{
                    $texto = "No se encontraron registros.";
                    $titulo = "Facturas / Remitos ".$titulo;
                    return view('crm.plantillas.sinDatos', compact('texto', 'titulo') );
                }
            break;

            case 'v':

                break;
            default:           //verifico por nro
                switch($tipo){
                    case 2:     //inversiones
                        $r=Rubro::where('nombre','=','Inversiones')->get();
                        $rubroId = $r[0]->valor;
                        $nombreRubro = $r[0]->nombre;

                        $titulo = "de ".$nombreRubro." - $nombreMes $anio";
                        $items_pag = 50;

                        $ultimos_por_nro_documento = Facturas::select('nro_documento', DB::raw('MAX(id) as idMax'))        //armo tabla temporal, con los id y sus maximos created at
                        ->groupBy('nro_documento');

                        if($mes == 13)  //anual
                            $facturas = Facturas::joinSub($ultimos_por_nro_documento, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                                $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                                $join->on('facturas.id', '=', 'ultimo_doc.idMax');  })
                                    ->whereDate('created_at','>=', '2024-02-01' )
                                    ->where('estadoDocumento','!=',6)         //Que no sea factura borrado
                                    //->whereIn('estadoDocumento', [2])      //Validado
                                    ->whereYear('fecha_limite', '=',$anio)
                                    ->where('rubro','=',$rubroId)
                                    ->OrderBy('facturas.nro_documento', 'desc')
                                    ->paginate($items_pag);

                        else    //mensual
                            $facturas = Facturas::joinSub($ultimos_por_nro_documento, 'ultimo_doc', function($join) {      //Hago join con la tabla ppal, para tener todos los datos
                                $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                                $join->on('facturas.id', '=', 'ultimo_doc.idMax');  })
                                    ->whereDate('created_at','>=', '2024-02-01' )
                                    ->where('estadoDocumento','!=',6)         //Que no sea factura borrado
                                    //->whereIn('estadoDocumento', [2])      //Validado
                                    ->whereMonth('fecha_limite', '=', $mes)
                                    ->whereYear('fecha_limite', '=',$anio)
                                    ->where('rubro','=',$rubroId)
                                    ->OrderBy('facturas.nro_documento', 'desc')
                                    ->paginate($items_pag);

                        $con_vencimiento=false;

                        if($facturas->count()){
                            $paginate = true;
                            $searchPanes=false;

                            $tipoPago = FacturasController::seleccionarTipoPagosHabilitados(TipoPago::where('estado','=',1)->get(), 'pagos', 0);    //envío los posibles, y recibo los válidos segun rol
                            $estadoOperativo = FacturasController::seleccionarEstadosEntregaHabilitados(EstadoEntrega::where('estado','!=',0)->get());   //envío los posibles, y recibo los válidos segun rol
                            return view('crm.facturas.listar_pagarPro2', compact('facturas','searchPanes','tipoPago','estadoOperativo', 'titulo','paginate','con_vencimiento') );
                        }
                        else{
                            $texto = "No se encontraron registros.";
                            $titulo = "Facturas / Remitos ".$titulo;
                            return view('crm.plantillas.sinDatos', compact('texto', 'titulo') );
                        }
                    break;
                    case 4:     //retiro de dividendos

                        $items_pag = 50;

                        if($mes < 13)   //Mensual
                            $transacciones = Transacciones::OrderBy('fecha', 'desc')
                                ->where('destino','=',200)
                                ->where('movimiento','!=',4)
                                ->where('estado','!=',5)
                                ->whereMonth('fecha', '=', $mes)
                                ->whereYear('fecha', '=',$anio)
                                ->paginate($items_pag);
                        else            //Anual
                            $transacciones = Transacciones::OrderBy('fecha', 'desc')
                                ->where('destino','=',200)
                                ->where('movimiento','!=',4)
                                ->where('estado','!=',5)
                                ->whereYear('fecha', '=',$anio)
                                ->paginate($items_pag);


                        $titulo = "Retiros Societarios - $nombreMes $anio";


                        if(!empty($transacciones)){
                            $paginate = true;
                            return view('crm.transacciones.listarPro', compact('transacciones', 'titulo','paginate') );
                        }
                        else{
                            $texto = "No se encontraron registros.";
                            return view('crm.plantillas.sinDatos', compact('texto', 'titulo') );
                        }
                    break;
                }
        }
    }


    private function obtenerNombreMes($numeroMes) {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
            13 => 'Anual'
        ];
        return isset($meses[$numeroMes]) ? $meses[$numeroMes] : null;
    }

}
