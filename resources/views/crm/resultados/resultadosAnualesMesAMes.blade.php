@extends('crm.plantillas.resumenTablaBuilder')

@section('config PDF')
    filename: function() {
        return "CRM - Resultados mes a mes {{ $anio }}"
    },
    title: function() {
        return "CRM - Resultados mes a mes {{ $anio }}"
    }
@endsection

@section('config Excel')
    autoFilter: true,
    title: 'CRM - Movimientos de cuenta',
    filename: function() {
        return "CRM - Resultados mes a mes {{ $anio }}"
    },
    title: function() {
        return "CRM - Resultados mes a mes {{ $anio }}"
    }
@endsection

@section('srcJsDatatable')

  <script type="text/javascript">

    var GastosisHidden = true;
    var VentasisHidden = true;

    gastos = $(".rubrosGastos");
    ingresos = $(".rubrosVentas");

    function toogleGastosRubro() {
      console.log("presiono Gastos");
      if (GastosisHidden) {
        gastos.show();
        //toggleButton.textContent = "Ocultar filas";
      }
      else {
        gastos.hide();
        //toggleButton.textContent = "Mostrar filas";
      }
      GastosisHidden = !GastosisHidden;

    }

    function toogleVentasRubro() {
      console.log("presiono Ventas");
      if (VentasisHidden) ingresos.show();
      else ingresos.hide();
      VentasisHidden = !VentasisHidden;
    }

    // ajusta los headres con el body cuando la pantlla cambia de tamaño
    $(window).on('resize', function () {
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });

    $(dt.layout.table).on('resize', function(){
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();


    })


  </script>

@endsection

@section('srcCssDatatable')

  <style>
    table.dataTable thead th,table.dataTable thead td,table.dataTable tfoot th,table.dataTable tfoot td {
      text-align: center
    }
  </style>


@endsection

@section('configuracion Datatable')

    layout: {
        topEnd: {
          buttons: [
            {
                text: 'excel',
                extend: 'excelHtml5',
                @yield('config Excel')
            },
            {
                text: 'pdf',
                extend: 'pdfHtml5',
                @yield('config PDF')
            },
          ]
        },

        topStart: {
          search: { placeholder: 'Ingrese dato a buscar...' }
        },

        bottomEnd: {
          //paging: { numbers: 3 }
        }
    },

    columnDefs: [
        { className: 'dt-center', targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] },
        { className: 'dt-left',   targets: 0 },
    ],
    language: {"url": "{{ asset('js/datatable_espaniol.json') }}"},

    title: 'CRM - Resultados Anuales 2024',

    bSort: false,               //Orden por defecto
    order: [],                  //Orden por defecto
    ordering: false,            //Para que ordene columnas
    searching: true,            //Muestra el buscador

    //lengthChange: true,         //Moestra el selector de tamanio
    //paging: true,
    info: false,
    //pageLength : 120,
    //responsive: false,          //Hace tabla responsive estilo datatable, que despliega hacia abajo

    //fixedColumns: true,
    autoWidth: false, // Desactiva el ancho automático

    deferRender: true,
    scrollCollapse: true,
    scrollY: 400,
    scrollX: true,
    scroller: true,

    //fixedHeader: true,

    {{-- fixedColumns: {
      start: 1
    }, --}}

    colReorder: true,
@endsection

@section('configuracionTabla')
  @include('crm.plantillas.datatableBuilder')
@endsection

@section('tituloTabla')
  <div class="row">
    <div class="col-sm-12 ">
      Resultados mes a mes {{ $anio }}
    </div>
  </div>
@endsection

@section('operacionesTabla')
<form method="POST" action="{{ route('resultados.anual.anio') }}">

  <div class="row">
    <div class="col">
      <select class="form-control" name="anio" id="anio" onchange="javascript:calcularFecha()">
        <option value='2024'>2024</option>
        <option value='2025'>2025</option>
      </select>
    </div>

    <div class="col mt-1">
      <button type="submit" class="btn btn-primary btn-sm" >Cambiar Periodo</button>
      @csrf
    </div>
  </div>
</form>
@endsection

@section('contenidoEspacioResumen')

  <div class="container sticky-top">
    <div class="row">
      <div class="col-md-3 col-sm-12 text-white  ">
          <div class="row  justify-content-center h4 ">
            Ventas Totales:
          </div>
          <div class="row  justify-content-center h2">
            ${{ number_format($ventasTotalAnual, 0,',','.') }}
          </div>
      </div>

      <div class="col-md-3 col-sm-12 text-white ">
        <div class="row  justify-content-center h4 ">
          Costo Mixto:
        </div>
        <div class="row  justify-content-center h2">
          {{ number_format($costoMixto, 2, ',', '.') }}%
        </div>
      </div>

      <div class="col-md-3 col-sm-12 font-weight-bold text-white  ">
        <div class="row  justify-content-center h4 ">
        Ganancia Neta:
        </div>
        <div class="row  justify-content-center h2">
          ${{ number_format($gananciaNeta, 0, ',', '.') }}
        </div>
      </div>

      <div class="col-md-3 col-sm-12 text-center font-weight-bold text-white  ">
        <div class="row  justify-content-center h4 text-center">
        % Ganancia:
        </div>
        <div class="row  justify-content-center text-center h2">
          {{ number_format($gananciaPorcentaje, 0, ',', '.') }}%
        </div>
      </div>
    </div>
  </div>

  <script type='text/javascript'>
    window.onload = function () {
      var element = document.getElementById("resumen");
      element.style.backgroundColor= '#202c46' ;    //color del panel
      //Colores copados:  '#202c46'
    }
  </script>

@endsection

@section('espacioResumen')
  <div class="card" id="resumen">
            @yield('contenidoEspacioResumen')
  </div>
@endsection

@section('tabla')
    <thead>
      <tr style="background-color: #3b3b3c;" class="text-white" >
        @foreach($egresoAnualXRubro[0] as $tit=>$val)
        <th class="text-center">
          {{ mb_strtoupper($tit) }}
        </th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($ingresosAnualesTotales as $r)
      <tr style="background-color: #94cb43;" onclick="toogleVentasRubro()">
        @php($i=0)
        @foreach($r as $val)
        <td>
          @if(!is_string($val) && $val!=0)
                              <a href='{{ route("resultados.detalle", ["tipo"=>"v", "rango"=>1, "anio"=>$anio, "unidad"=>$i]) }}'
                                style="color:rgb(70, 65, 65);">
                                ${{  number_format(floatVal($val), 0, ',', '.')  }}
                              </a>
          @else                   {{  $val }}         @endif
        </td>
        @php($i++)
        @endforeach
      </tr>
      @endforeach
      @foreach($ingresosAnuales as $r)
      <tr class="rubrosVentas" onclick="toogleVentasRubro()" style="display: none; " >
        @foreach($r as $val)
        <td>
          @if(!is_string($val))   {{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      @endforeach




      @foreach($egresosAnualTotal as $r)
      <tr style="background-color: #e7e7e1 ;" class="text-dark" onclick="toogleGastosRubro()">
        @foreach($r as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      @endforeach

      @php($j=0)
      @foreach($egresoAnualXRubro as $r)
      <tr class="rubrosGastos" style="display: none;">
        @php($i=0) @php($j++)
        @foreach($r as $val)
        <td>
          @if(!is_string($val) && $val!=0)
                                <a href='{{ route("resultados.detalle", ["tipo"=>"e$j", "rango"=>1, "anio"=>$anio,"unidad"=>$i]) }}'
                                  style="color:rgb(70, 65, 65);">
                                  {{  number_format(floatVal($val), 0, ',', '.')  }}
                                </a>
          @else                   {{  $val }}         @endif
        </td>
        @php($i++)
        @endforeach
      </tr>
      @endforeach

      <tr style="background-color: #94cb43;" >
        @foreach($gananciaBruta as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($iibb as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($iva as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($impuestoGanancia as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr style="background-color: #74c2dd ;">
        @foreach($gananciaNetaMesMes as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr style="background-color: #048e8e ;" class="text-white" >
        @foreach($gananciaPorcentajeMesAMes as $val)
        <td>
          @if(!is_string($val))   {{  number_format(floatVal($val), 2, ',', '.')  }}%
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($costoAlimentoPorcentajeMesAMes as $val)
        <td>
          @if(!is_string($val))   {{  number_format(floatVal($val), 2, ',', '.')  }}%
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($costoBebidaPorcentajeMesAMes as $val)
        <td>
          @if(!is_string($val))   {{  number_format(floatVal($val), 2, ',', '.')  }}%
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr style="background-color: #94cb43;" >
        @foreach($costoMixtoMesAMes as $val)
        <td>
          @if(!is_string($val))   {{  number_format(floatVal($val), 2, ',', '.')  }}%
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @foreach($gananciaNetaMesMes as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @php($i=0)
        @foreach($ingresoPropietarios as $val)
        <td>
          @if(!is_string($val) && $val!=0)
                              <a href='{{ route("resultados.detalle", ["tipo"=>1, "rango"=>1, "anio"=>$anio,"unidad"=>$i]) }}'
                                style="color:rgb(70, 65, 65);">
                                ${{  number_format(floatVal($val), 0, ',', '.')  }}
                              </a>
          @else                   {{  $val }}         @endif
        </td>
        @php($i++)
        @endforeach
      </tr>
      <tr>
        @php($i=0)
        @foreach($inversiones as $val)
        <td>
          @if(!is_string($val) && $val!=0)
                                  <a href='{{ route("resultados.detalle", ["tipo"=>2, "rango"=>1, "anio"=>$anio,"unidad"=>$i]) }}'
                                    style="color:rgb(70, 65, 65);">
                                    ${{  number_format(floatVal($val), 0, ',', '.')  }}
                                  </a>
          @else                   {{  $val }}         @endif

        </td>
        @php($i++)
        @endforeach
      </tr>
      <tr>
        @foreach($pagoDeudaAtrasada as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>
      <tr>
        @php($i=0)
        @foreach($retiroDividendos as $ret)
        <td>
          @if(!is_string($ret))
                                  <a href='{{ route("resultados.detalle", ["tipo"=>4, "rango"=>1, "anio"=>$anio,"unidad"=>$i]) }}'
                                    style="color:rgb(70, 65, 65);">
                                    ${{  number_format(floatVal($ret), 0, ',', '.')  }}
                                  </a>
          @else                   {{  $ret }}         @endif
        </td>
        @php($i++)
        @endforeach
      </tr>


      <tr>
        @php($i=0)
        @foreach($gastosCtaCte as $val)
        <td>
          @if(!is_string($val))
                                  <a href='{{ route("resultados.detalle", ["tipo"=>5, "rango"=>1, "anio"=>$anio,"unidad"=>$i]) }}'
                                    style="color:rgb(70, 65, 65);">
                                    ${{  number_format(floatVal($val), 0, ',', '.')  }}
                                  </a>
          @else                   {{  $val }}         @endif
        </td>
        @php($i++)
        @endforeach
      </tr>
      <tr>
        @foreach($cajaFinal as $val)
        <td>
          @if(!is_string($val))   ${{  number_format(floatVal($val), 0, ',', '.')  }}
          @else                   {{  $val }}         @endif
        </td>
        @endforeach
      </tr>

      {{-- @dd($ingresosMesMes)

      @foreach($ingresosMesMes as $r)
      <tr>
        @foreach($r as $val)
        <td class='text-center'>
          {{ $val }}
        </td>
        @endforeach
      </tr>
      @endforeach  --}}

    </tbody>

@endsection
