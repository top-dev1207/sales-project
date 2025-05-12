@extends('crm.plantillas.resumenTabla')


{{-- @section('configuracionTabla')
  @include('crm.plantillas.datatableSinOrdenExcel')
@endsection --}}

@section('contenidoEspacioResumen')

  {{-- <div class="row h5 font-italic font-weight-bold text-white">
    {{ \Carbon\Carbon::parse($fechaInicio)->format('jS \o\f F ') }} ... {{ \Carbon\Carbon::parse($fechaFin)->format('jS \o\f F ') }}
  </div> --}}
  {{-- <div class="container"> --}}
    {{-- <h1>dashboard</h1>
    <div id="react-widget"></div> --}}
    <div class="row justify-content-center">
      <div class="col-md-3 col-sm-4 text-white  ">
          <div class="row  justify-content-center h5 ">
            Ventas:
          </div>
          <div class="row  justify-content-center h3">
            ${{ number_format($totalVentas, 0,',','.') }}
          </div>
      </div>

      @can('ver_info_sensible')
      <div class="col-md-3 col-sm-4 text-white  ">
        <div class="row  justify-content-center h5 ">
          Gastos:
        </div>
        <div class="row  justify-content-center h3">
          ${{ number_format($totalGastos, 0,',','.') }}
        </div>
      </div>

      <div class="col-md-3 col-sm-4 font-weight-bold text-white  ">
          <div class="row  justify-content-center h5 ">
          Ganancia:
          </div>
          <div class="row  justify-content-center h3">
            ${{ number_format($saldo, 0, ',', '.') }}
          </div>
      </div>

      <div class="col-md-3 col-sm-4 text-center font-weight-bold text-white  ">
        <div class="row  justify-content-center h5 text-center">
          Rentabilidad:
        </div>
        <div class="row  justify-content-center text-center h3">
          {{ number_format($rentabilidad, 1, ',', '.') }}%
        </div>
      </div>
      @endcan


    </div>
    <hr>
    <div class="row justify-content-center">

      @switch($foodCost)
      @case($foodCost>32)                   <div style="color:red;" class="col-md-2 col-sm-4">    @break
      @case($foodCost<=32 && $foodCost>=30) <div style="color:yellow;" class="col-md-2 col-sm-4"> @break
      @case($foodCost<30)                   <div style="color:green;" class="col-md-2 col-sm-4">  @break
      @default                              <div style="color:brown;" class="col-md-2 col-sm-4">  @endswitch
        <div class="row  justify-content-center h5 ">
          Food Cost:
        </div>
        <div class="row  justify-content-center h3">
          {{ number_format($foodCost, 1, ',', '.') }}%
        </div>
      </div>

      @switch($beverageCost)
      @case($beverageCost>32)                       <div style="color:red;" class="col-md-2 col-sm-4">    @break
      @case($beverageCost<=32 && $beverageCost>=30) <div style="color:yellow;" class="col-md-2 col-sm-4"> @break
      @case($beverageCost<30)                       <div style="color:green;" class="col-md-2 col-sm-4">  @break
      @default                                      <div style="color:brown;" class="col-md-2 col-sm-4">  @endswitch
        <div class="row  justify-content-center h5 ">
          Beverage Cost:
        </div>
        <div class="row  justify-content-center h3">
          {{ number_format($beverageCost, 1, ',', '.') }}%
        </div>
      </div>

      @switch($mixCost)
      @case($mixCost>38)                    <div style="color:red;" class="col-md-2 col-sm-4">    @break
      @case($mixCost<=38 && $mixCost>=34)   <div style="color:yellow;" class="col-md-2 col-sm-4"> @break
      @case($mixCost<34)                    <div style="color:green;" class="col-md-2 col-sm-4">  @break
      @default                              <div style="color:brown;" class="col-md-2 col-sm-4">  @endswitch
        <div class="row  justify-content-center h5 ">
          Mix Cost:
        </div>
        <div class="row  justify-content-center h3">
          {{ number_format($mixCost, 2, ',', '.') }}%
        </div>
      </div>

      <div class="col-md-2 col-sm-4 text-white">
        <div class="row-sm-4 text-center">
          Vent fiscal: ${{ number_format($totalVentasFiscal, 0, ',', '.') }}
        </div>

        <div class="row-sm-4 text-center">
          Med Elect: ${{ number_format($totalMediosElect, 0, ',', '.') }}
        </div>

        <div class="row-sm-4 text-center">
          @switch($q = $totalVentasFiscal/$totalMediosElect)
          @case($q >= 1.10) <div style="color:green"  >  @break
          @case($q >= 1.05) <div style="color:yellow;">  @break
          @case($q<1.05)    <div style="color:red;"   >  @break
          @default          <div style="color:brown;" >  @endswitch
            Dif: ${{ number_format(($totalMediosElect-$totalVentasFiscal), 0, ',', '.') }}
          </div>
        </div>
      </div>

      <div class="col-md-2 col-sm-4 text-white">
        <div class="row-sm-4 text-center">
          IVA Compra: ${{ number_format($ivaTotalPagado, 0, ',', '.') }}
        </div>

        <div class="row-sm-4 text-center">
          IVA Venta: ${{ number_format(0.21*$totalVentasFiscal, 0, ',', '.') }}
        </div>
        <div class="row-sm-4 text-center">
          @switch($q = $ivaTotalPagado-0.21*$totalVentasFiscal)
            @case($q >=0) <div style="color:red;" class="text-center">  @break
            @case($q < 0) <div style="color:green" class="text-center"> @endswitch
              Dif: ${{ number_format($q, 0, ',', '.') }}
          </div>
        </div>
      </div>

    </div>
  </div>


  <script type='text/javascript'>
  window.onload = function () {
    var element = document.getElementById("resumen");
    element.style.backgroundColor= '#202c46'; //color copado
    //element.style.backgroundColor= '#000';    //color negro del panel
  }
  </script>

@endsection
@section('javascript')
<script src="{{ asset('js/listar.js') }}" defer></script>

@endsection
@section("css")
<link rel="stylesheet" href="{{ asset('css/listar.css') }}">
@endsection
@section('tituloTabla')
  <div class="row">
    <div class="col">
      Detalle de ingresos y egresos por día.
    </div>

    @can('ver_gráficos')
    <form method="POST" action="{{ route('graficos.resumen1') }}">
      @csrf

          <input type="hidden" value='{{ $fechaInicio }}' class="form-control" id="fechaInicio" name="fechaInicio" />
          <input type="hidden" value='{{ $fechaFin }}' class="form-control" id="fechaFin" name="fechaFin" />

        <div class="col text-right">
          <button type="submit" class="btn sm-btn-block btn-secondary">Ver gráficos</a>
        </div>
      </form>
      @endcan
  </div>

  <div class="row h6 text-normal text-info">
    <div class="col">
      Del {{ \Carbon\Carbon::parse($fechaInicio)->format('d-M-y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d-M-y') }}
    </div>
  </div>

@endsection
@section("Columnas fijas")
<div class="controls">
    <div>
        <span class="text-[#333333]">Columnas fijas: <span id="fixedCount">0</span></span>
    </div>
    <div class="control-buttons">
        <button id="resetColumns">Reiniciar columnas fijas</button>
        {{-- <button id="selectFirstColumn">Fix First Day</button> --}}
        {{-- <button id="fixMultipleColumns">Fix First 10 Days</button>
        <button id="fixManyColumns">Fix First 20 Days</button> --}}
    </div>
</div>
@endsection
@section('tabla')
<div class="container">
    <div class="table-container" id="tableContainer">
        <table id="dataTable">
           <thead>
                <tr id="thead-row">
                    @php($i=0)
                    <th class="fixed-column is-sticky">Detalle / Día</th>
                    @foreach($r as $key => $dia)
                    @if($dia['egresos'][0][1] != 'Totales' && $dia['egresos'][0][1] != 'Incidencia (%)')
                    @php($i++)
                    <th data-day="{{ $key+1 }}"><a class="h7 text-center">{{ \Carbon\Carbon::parse($dia['egresos'][0][1])->locale('es')->isoFormat('dddd D-MMM')  }}</a></th>
                    @else
                    {{-- <th class="h5 text-center"><a>{{ $dia['egresos']['Fecha'] }}</a></th> --}}
                    <th class="h5 text-center" data-day="{{ $key+1 }}"><a>{{ $dia['egresos'][0][1] }}</a></th>
                    @endif
                        {{-- <th data-day="{{ $key+1 }}">{{ $key+1 }}</th> --}}
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="vanta item-animation">
                    <th class="text-white bg-info fixed-column is-sticky">Venta Electrónica</th>
                    @php($j=$i)
                    @foreach($r as $dia)
                        @if($j-- >0)
                        <td class="text-center text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{  number_format($dia['ingresos']['ventasFiscal'], 0, ',', '.') }}</a></td>
                        @else
                        <td class="h3 text-center text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{  number_format($dia['ingresos']['ventasFiscal'], 0, ',', '.') }}</a></td>
                        @endif
                    @endforeach
                </tr>
                <tr class="vanta item-animation">
                    <th class="text-white bg-info fixed-column is-sticky">Venta Efectivo MP</th>
                    @php($j=$i)
                    @foreach($r as $dia)
                        @if ($j-- > 0)
                        <td class="text-center text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ventasNoFiscal'], 0 ,',', '.') }}</a></td>
                        @else
                        <td class="h3 text-center text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ventasNoFiscal'], 0 ,',', '.') }}</a></td>
                        @endif
                    @endforeach
                </tr>
                <tr onclick="toggleElement('vanta')">
                    <th class="text-white font-italic bg-info fixed-column is-sticky">** Validador</th>
                    @php($j=$i)
                    @foreach($r as $dia)
                        <td class="text-center font-italic text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{ number_format(($dia['ingresos']['ventasNoFiscal']+$dia['ingresos']['ventasFiscal']), 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @php($u=0)
      @foreach($r as $q)
        @if(++$u == $cantidadDeRegistros-1)
          {{-- Solo analizo totales --}}

         {{--  @if($q['egresos']['Sin datos'] == 0)                  @php($mostrarSd=0)    @else --}}    @php($mostrarSd=1)      {{-- @endif--}}
         {{--  @if($q['egresos']['Alimentos'] == 0)                  @php($mostrarA=0)     @else --}}    @php($mostrarA=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Bebidas'] == 0)                    @php($mostrarB=0)     @else --}}    @php($mostrarB=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Luz'] == 0)                        @php($mostrarL=0)     @else --}}    @php($mostrarL=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Teléfono, Internet, Cable'] == 0)  @php($mostrarTel=0)   @else --}}    @php($mostrarTel=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Aysa'] == 0)                       @php($mostrarAy=0)    @else --}}    @php($mostrarAy=1)      {{-- @endif--}}
         {{--  @if($q['egresos']['Fumigación'] == 0)                 @php($mostrarF=0)     @else --}}    @php($mostrarF=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Librería'] == 0)                   @php($mostrarLi=0)    @else --}}    @php($mostrarLi=1)      {{-- @endif--}}
         {{--  @if($q['egresos']['Descartables'] == 0)               @php($mostrarD=0)     @else --}}    @php($mostrarD=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Mantenimiento'] == 0)              @php($mostrarM=0)     @else --}}    @php($mostrarM=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Equipamiento'] == 0)               @php($mostrarE=0)     @else --}}    @php($mostrarE=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Vajilla'] == 0)                    @php($mostrarV=0)     @else --}}    @php($mostrarV=1)       {{-- @endif--}}
         {{--  @if($q['egresos']['Decoración'] == 0)                 @php($mostrarDec=0)   @else --}}    @php($mostrarDec=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Art. limpieza'] == 0)              @php($mostrarArt=0)   @else --}}    @php($mostrarArt=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Viáticos'] == 0)                   @php($mostrarViat=0)  @else --}}    @php($mostrarViat=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Lavandería'] == 0)                 @php($mostrarLav=0)   @else --}}    @php($mostrarLav=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Imprenta / Gráfica'] == 0)         @php($mostrarImp=0)   @else --}}    @php($mostrarImp=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Alquiler y expensas'] == 0)        @php($mostrarAlq=0)   @else --}}    @php($mostrarAlq=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Personal Extra'] == 0)             @php($mostrarPer=0)   @else --}}    @php($mostrarPer=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['ABL'] == 0)                        @php($mostrarABL=0)   @else --}}    @php($mostrarABL=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Seguro'] == 0)                     @php($mostrarSeg=0)   @else --}}    @php($mostrarSeg=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Salarios'] == 0)                   @php($mostrarSal=0)   @else --}}    @php($mostrarSal=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Seguridad'] == 0)                  @php($mostrarSegu=0)  @else --}}    @php($mostrarSegu=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Contador'] == 0)                   @php($mostrarCont=0)  @else --}}    @php($mostrarCont=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Abogado'] == 0)                    @php($mostrarAbo=0)   @else --}}    @php($mostrarAbo=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Marketing'] == 0)                  @php($mostrarMark=0)  @else --}}    @php($mostrarMark=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Entretenimiento'] == 0)            @php($mostrarEntr=0)  @else --}}    @php($mostrarEntr=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Banco'] == 0)                      @php($mostrarBan=0)   @else --}}    @php($mostrarBan=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Autónomos'] == 0)                  @php($mostrarAut=0)   @else --}}    @php($mostrarAut=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Uniformes'] == 0)                  @php($mostrarUni=0)   @else --}}    @php($mostrarUni=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Sadaic'] == 0)                     @php($mostrarSad=0)   @else --}}    @php($mostrarSad=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Liquidación y legales'] == 0)      @php($mostrarLiq=0)   @else --}}    @php($mostrarLiq=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Sistemas'] == 0)                   @php($mostrarSist=0)  @else --}}    @php($mostrarSist=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Fee'] == 0)                        @php($mostrarFee=0)   @else --}}    @php($mostrarFee=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Comida Personal'] == 0)            @php($mostrarCom=0)   @else --}}    @php($mostrarCom=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Tarjeta Control'] == 0)            @php($mostrarTarj=0)  @else --}}    @php($mostrarTarj=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Otros'] == 0)                      @php($mostrarOtr=0)   @else --}}    @php($mostrarOtr=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Limpieza del Local'] == 0)         @php($mostrarLimp=0)  @else --}}    @php($mostrarLimp=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Recolección de basura'] == 0)      @php($mostrarRec=0)   @else --}}    @php($mostrarRec=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Fletes y Viáticos'] == 0)          @php($mostrarFle=0)   @else --}}    @php($mostrarFle=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Alarma'] == 0)                     @php($mostrarAlar=0)  @else --}}    @php($mostrarAlar=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Farmacia'] == 0)                   @php($mostrarFarm=0)  @else --}}    @php($mostrarFarm=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Tubos de Gas'] == 0)               @php($mostrarTub=0)   @else --}}    @php($mostrarTub=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Regalías'] == 0)                   @php($mostrarRegal=0) @else --}}    @php($mostrarRegal=1)   {{-- @endif--}}
         {{--  @if($q['egresos']['Facturas adicionales de IVA'] == 0)@php($mostrarFactA=0) @else --}}    @php($mostrarFactA=1)   {{-- @endif--}}
         {{--  @if($q['egresos']['Ascensores'] == 0)                 @php($mostrarAsc=0)   @else --}}    @php($mostrarAsc=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Gas'] == 0)                        @php($mostrarGas=0)   @else --}}    @php($mostrarGas=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Thinkion'] == 0)                   @php($mostrarThi=0)   @else --}}    @php($mostrarThi=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Linkedin'] == 0)                   @php($mostrarLin=0)   @else --}}    @php($mostrarLin=1)     {{-- @endif--}}
         {{--  @if($q['egresos']['Comunity'] == 0)                   @php($mostrarComu=0)  @else --}}    @php($mostrarComu=1)    {{-- @endif--}}
         {{--  @if($q['egresos']['Asesor Gastronómico'] == 0)        @php($mostrarAses=0)  @else --}}    @php($mostrarAses=1)    {{-- @endif--}}

         @if($q['ingresos']['fp1'] == 0)     @php($mostrarFp1=0)   @else     @php($mostrarFp1=1)    @endif
         @if($q['ingresos']['fp2'] == 0)     @php($mostrarFp2=0)   @else     @php($mostrarFp2=1)    @endif
         @if($q['ingresos']['fp3'] == 0)     @php($mostrarFp3=0)   @else     @php($mostrarFp3=1)    @endif
         @if($q['ingresos']['fp4'] == 0)     @php($mostrarFp4=0)   @else     @php($mostrarFp4=1)    @endif
         @if($q['ingresos']['fp5'] == 0)     @php($mostrarFp5=0)   @else     @php($mostrarFp5=1)    @endif
         @if($q['ingresos']['fp6'] == 0)     @php($mostrarFp6=0)   @else     @php($mostrarFp6=1)    @endif
         @if($q['ingresos']['fp7'] == 0)     @php($mostrarFp7=0)   @else     @php($mostrarFp7=1)    @endif
         @if($q['ingresos']['fp8'] == 0)     @php($mostrarFp8=0)   @else     @php($mostrarFp8=1)    @endif
         @if($q['ingresos']['fp9'] == 0)     @php($mostrarFp9=0)   @else     @php($mostrarFp9=1)    @endif
         @if($q['ingresos']['fp10'] == 0)    @php($mostrarFp10=0)  @else     @php($mostrarFp10=1)   @endif
         @if($q['ingresos']['egresos'] == 0) @php($mostrarEgre=0)  @else     @php($mostrarEgre=1)   @endif


         {{--  @if($q['resultados']['foodCost'] == 0)      @php($mostrarFood=0)  @else --}}  @php($mostrarFood=1)  {{-- @endif--}}
         {{--  @if($q['resultados']['beverageCost'] == 0)  @php($mostrarBeve=0)  @else --}}  @php($mostrarBeve=1)  {{-- @endif--}}
         {{--  @if($q['resultados']['mixCost'] == 0)       @php($mostrarMix=0)   @else --}}  @php($mostrarMix=1)   {{-- @endif--}}
         {{--  @if($q['resultados']['ivaPagado'] == 0)     @php($mostrarIva=0)   @else --}}  @php($mostrarIva=1)   {{-- @endif--}}


        @endif
      @endforeach

                {{-- @for($i = 0; $i < count($fp); $i++)
                @if(isset($fp[$i]->tipo))
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[$i]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp'.($i+1)], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif
                @endfor --}}

                @if(isset($fp[0]->tipo) && $mostrarFp1 )
                <tr>
                  <th class="fixed-column is-sticky">{{ $fp[0]->tipo }}</th>
                  @foreach($r as $dia)
                  <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp1'], 0 ,',', '.') }}</a></td>
                  @endforeach
                </tr>
                @endif
                {{-- Add more rows following the same pattern... --}}
                {{-- Include all your other rows, adding fixed-column class to the th element and data-day attribute to each td --}}

                {{-- <tr>
                    <th class="fixed-column is-sticky">Diferencia de Caja</th>
                    @foreach($r as $dia)
                    @if ($dia['ingresos']['diferencia_de_caja'] >= 0)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['diferencia_de_caja'], 0 ,',', '.') }}</a></td>
                    @else
                    <td class="text-center text-danger" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['diferencia_de_caja'], 0 ,',', '.') }}</a></td>
                    @endif
                    @endforeach
                </tr> --}}
                @if(isset($fp[1]->tipo) && $mostrarFp2 )
                <tr>
                  <th class="fixed-column is-sticky">{{ $fp[1]->tipo }}</th>
                  @foreach($r as $dia)
                  <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp2'], 0 ,',', '.') }}</a></td>
                  @endforeach
                </tr>
                @endif

                @if(isset($fp[2]->tipo) && $mostrarFp3 )
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[2]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp3'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif
                {{-- Continue with your original rows, adding fixed-column class and data-day attributes --}}

                @if(isset($fp[3]->tipo) && $mostrarFp4 )
                <tr>
                  <th class="fixed-column is-sticky">{{ $fp[3]->tipo }}</th>
                  @foreach($r as $dia)
                  <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp4'], 0 ,',', '.') }}</a></td>
                  @endforeach
                </tr>
                @endif

                @if(isset($fp[4]->tipo) && $mostrarFp5 )
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[4]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp5'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif

                @if(isset($fp[5]->tipo) && $mostrarFp6 )
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[5]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp6'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif

                @if(isset($fp[6]->tipo) && $mostrarFp7 )
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[6]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp7'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif

                @if(isset($fp[7]->tipo) && $mostrarFp8)
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[7]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp8'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif

                @if(isset($fp[8]->tipo) && $mostrarFp9)
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[8]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp9'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif

                @if(isset($fp[9]->tipo) && $mostrarFp10)
                <tr>
                    <th class="fixed-column is-sticky">{{ $fp[9]->tipo }}</th>
                    @foreach($r as $dia)
                    <td class="text-center" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['fp10'], 0 ,',', '.') }}</a></td>
                    @endforeach
                </tr>
                @endif
                <tr>
                    <th class="fixed-column is-sticky">Diferencia de Caja</th>
                    @foreach($r as $dia)
                    @if ($dia['ingresos']['diferencia_de_caja'] >= 0)
                    <td class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['diferencia_de_caja'], 0 ,',', '.') }}</a></td>

                    @else
                    <td class="text-center text-danger" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['diferencia_de_caja'], 0 ,',', '.') }}</a></td>

                    @endif
                    @endforeach
                </tr>
                <tr>
                    <th class="text-white font-italic bg-info fixed-column is-sticky">** Validador</th>
                    @foreach($r as $dia)
                        <td class="text-center font-italic text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{ number_format((
                          $dia['ingresos']['fp1']+
                          $dia['ingresos']['fp2']+
                          $dia['ingresos']['fp3']+
                          $dia['ingresos']['fp4']+
                          $dia['ingresos']['fp5']+
                          $dia['ingresos']['fp6']+
                          $dia['ingresos']['fp7']+
                          $dia['ingresos']['fp8']+
                          $dia['ingresos']['fp9']+
                          $dia['ingresos']['fp10']-
                          $dia['ingresos']['diferencia_de_caja']
                          ), 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th style="background-color:#d8d2dd" class="fixed-column is-sticky">Ingresos por Shows</th>
                    @foreach($r as $dia)
                    <td style="background-color:#d8d2dd" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ingreso_por_derecho_show'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th style="background-color:#d8d2dd" class="fixed-column is-sticky">Invitaciones</th>
                    @foreach($r as $dia)
                    <td style="background-color:#d8d2dd" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['cuenta_corriente'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>

                  <tr>
                    <th  style="background-color:#c48ff0" class="fixed-column is-sticky">Arena</th>
                    @foreach($r as $dia)
                    <td  style="background-color:#c48ff0" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['temple_1'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th  style="background-color:#c48ff0" class="fixed-column is-sticky">Estudio</th>
                    @foreach($r as $dia)
                    <td  style="background-color:#c48ff0" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['temple_2'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>

                  <tr>
                    <th class="text-white font-italic bg-info fixed-column is-sticky">** Validador</th>
                    @foreach($r as $dia)
                        <td class="text-center font-italic text-white bg-info" data-day="{{ $loop->iteration }}"><a> {{ number_format((
                          $dia['ingresos']['temple_2']+
                          $dia['ingresos']['temple_1']), 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th class="fixed-column is-sticky">Indice Pinta</th>
                    @foreach($r as $dia)
                    <td class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['indice_pinta'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th style="background-color:#a2bead" class="fixed-column is-sticky">Venta Alimentos</th>
                    @foreach($r as $dia)
                    <td style="background-color:#a2bead" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ventaAlimentos'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th style="background-color:#a2bead" class="fixed-column is-sticky">Venta Bebidas</th>
                    @foreach($r as $dia)
                    <td style="background-color:#a2bead" class="text-center " data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ventaBebidas'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  <tr>
                    <th class="text-white font-italic bg-info fixed-column is-sticky">** Validador</th>
                    @foreach($r as $dia)
                        <td class="text-center font-italic text-white bg-info " data-day="{{ $loop->iteration }}"><a> {{ number_format((
                          $dia['ingresos']['ventaBebidas']+
                          $dia['ingresos']['ventaAlimentos']), 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>


                  <tr>
                    <th style="background-color:#1c9448" class="h5 font-italic font-weight-bold text-white fixed-column is-sticky">Ventas Total</th>
                    @foreach($r as $dia)
                    <td style="background-color:#1c9448" class="text-center h5 font-weight-bold text-white" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['ingresos']['ventasTotales'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  @can('ver_info_sensible')
                  <tr onclick="toggleElement('toalExpensesItem')">
                    <th style="background-color:#dd4653" class="h5 font-italic font-weight-bold text-white fixed-column is-sticky">Egresos Total</th>
                    @foreach($r as $dia)
                    <td style="background-color:#dd4653" class="text-center h5 font-weight-bold text-white" data-day="{{ $loop->iteration }}"><a> {{ number_format($dia['resultados']['totalGastos'], 0 ,',', '.') }}</a></td>
                    @endforeach
                  </tr>
                  @endcan

                {{-- Expense items --}}
                @php($i=0)
                @foreach($r[0]['egresos'] as $egreso)
                  @if($i>0)
                    @if($r[0]['egresos'][$i][2] < 99)
                    <tr class="toalExpensesItem item-animation" style="display: none">
                      <th class="fixed-column is-sticky">{{ $r[0]['egresos'][$i][0] }}</th>
                      @foreach($r as $dia)
                        <td class="text-center" data-day="{{ $loop->iteration }}" ><a> {{ number_format($dia['egresos'][$i][1], 0,',','.') }}</a></td>
                      @endforeach
                    </tr>
                    @endif
                  @endif
                  @php($i++)
                @endforeach
                     {{--
                @if($rubro[1]->tipo && $mostrarA)
                <tr>
                <th>Alimentos</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Alimentos'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[2]->tipo && $mostrarB)
                <tr>
                <th>Bebidas</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Bebidas'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[17]->tipo && $mostrarAlq)
                <tr>
                <th>Alquiler y expensas</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Alquiler y expensas'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[21]->tipo && $mostrarSal)
                <tr>
                <th>Salarios & C.S.</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Salarios'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[18]->tipo && $mostrarPer)
                <tr>
                <th>Personal Extra</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Personal Extra'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[3]->tipo && $mostrarL)
                <tr>
                <th>Luz</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Luz'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[4]->tipo && $mostrarTel)
                <tr>
                <th>Tel Internet Cable Spotify</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Teléfono, Internet, Cable'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[20]->tipo && $mostrarSeg)
                <tr>
                <th>Seguro</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Seguro'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[5]->tipo && $mostrarAy)
                <tr>
                <th>Aysa</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Aysa'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[46]->tipo && $mostrarGas)
                <tr>
                <th>Gas</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Gas'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[19]->tipo && $mostrarABL)
                <tr>
                <th>ABL</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['ABL'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[7]->tipo && $mostrarLi)
                <tr>
                <th>Librería</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Librería'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[8]->tipo && $mostrarD)
                <tr>
                <th>Descartables</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Descartables'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[6]->tipo && $mostrarF)
                <tr>
                <th>Fumigación</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Fumigación'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[23]->tipo && $mostrarCont)
                <tr>
                <th>Contador</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Contador'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[24]->tipo && $mostrarAbo)
                <tr>
                <th>Abogado</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Abogado'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[31]->tipo && $mostrarLiq)
                <tr>
                <th>Liquidación y legales</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Liquidación y legales'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[25]->tipo && $mostrarMark)
                <tr>
                <th>Marketing</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Marketing'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[26]->tipo && $mostrarEntr)
                <tr>
                <th>Entretenimiento</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Entretenimiento'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[9]->tipo && $mostrarM)
                <tr>
                <th>Mantenimiento</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Mantenimiento'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[10]->tipo && $mostrarE)
                <tr>
                <th>Equipamiento</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Equipamiento'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[11]->tipo && $mostrarV)
                <tr>
                <th>Vajilla</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Vajilla'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[27]->tipo && $mostrarBan)
                <tr>
                <th>Gastos Bancarios</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Banco'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[28]->tipo && $mostrarAut)
                <tr>
                <th>Autónomos</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Autónomos'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[29]->tipo && $mostrarUni)
                <tr>
                <th>Uniformes</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Uniformes'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[12]->tipo && $mostrarDec)
                <tr>
                <th>Decoración</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Decoración'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[13]->tipo && $mostrarArt)
                <tr>
                <th>Art. limpieza</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Art. limpieza'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[47]->tipo && $mostrarThi)
                <tr>
                <th>Thinkion</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Thinkion'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[30]->tipo && $mostrarSad)
                <tr>
                <th>SADAICy AADICAPIF</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Sadaic'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[38]->tipo && $mostrarRec)
                <tr>
                <th>Recolección de Basura</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Recolección de basura'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[39]->tipo && $mostrarFle)
                <tr>
                <th>Viáticos y Fletes</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Fletes y Viáticos'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[48]->tipo && $mostrarLin)
                <tr>
                <th>Linkedin</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Linkedin'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[16]->tipo && $mostrarImp)
                <tr>
                <th>Imprenta / Gráfica</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Imprenta / Gráfica'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[42]->tipo && $mostrarTub)
                <tr>
                <th>Tubos de Gas y Garrafas </th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Tubos de Gas'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[34]->tipo && $mostrarCom)
                <tr>
                <th>Comida Personal</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Comida Personal'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[49]->tipo && $mostrarComu)
                <tr>
                <th>Comunity</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Comunity'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[50]->tipo && $mostrarAses)
                <tr>
                <th>Asesor Gastronómico</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Asesor Gastronómico'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[43]->tipo && $mostrarRegal)
                <tr>
                <th>Regalías</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Regalías'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[41]->tipo && $mostrarFarm)
                <tr>
                <th>Farmacia</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Farmacia'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif


                @if($rubro[40]->tipo && $mostrarAlar)
                <tr>
                <th>Alarma Verisure</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Alarma'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif


                @if($rubro[36]->tipo && $mostrarOtr)
                <tr>
                <th>Varios</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Otros'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif




        {{-- ESTOS SOBRAN --}}
        {{--
                @if($rubro[45]->tipo && $mostrarAsc)
                <tr>
                <th>Ascensores</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Ascensores'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[15]->tipo && $mostrarLav)
                <tr>
                <th>Lavandería</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Lavandería'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif


                @if($rubro[32]->tipo && $mostrarSist)
                <tr>
                <th>Sistemas</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Sistemas'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[33]->tipo && $mostrarFee)
                <tr>
                <th>Fee</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Fee'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[35]->tipo && $mostrarTarj)
                <tr>
                <th>Tarjeta Control</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Tarjeta Control'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[0]->tipo && $mostrarSd)
                <tr>
                <th>Sin Datos</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Sin datos'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[22]->tipo && $mostrarSegu)
                <tr>
                <th>Seguridad</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Seguridad'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif

                @if($rubro[37]->tipo && $mostrarLimp)
                <tr>
                <th>Limpieza del Local</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Limpieza del Local'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif


                @if($rubro[14]->tipo && $mostrarViat)
                <tr>
                <th>Viáticos</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Viáticos'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif
            </tbody>


                @if($rubro[44]->tipo && $mostrarFactA)
                <tr>
                <th>Facturas adicionales de IVA</th>
                @foreach($r as $dia)
                <td class="text-center"><a> {{ number_format($dia['egresos']['Facturas adicionales de IVA'], 0,',','.') }}</a></td>
                @endforeach
                </tr>
                @endif --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- @section('scripts')
  <script src="{{ mix('js/app.js') }}"></script>
@endsection
<script>
    window.diasData = @json($r);
    const theadRow = document.getElementById("thead-row");
</script> --}}
