@extends('crm.plantillas.base')

@include('crm.plantillas.rangoFechasPlantilla', ['rutaDestino' => 'resultados.calcular'])

@section('content')
        <div class="container-fluid">
                <div class="card card-chart">


                    <div class="card-header">
                      <div class="h2">
                        <i class="fa fa-align-justify"></i> Resultados de Gestión.
                      </div>

                    </div>

                    @yield('seleccionRangoFechas')
                    <a href="/resumenVer">Ver gráficos</a>
                </div>
        </div>

@endsection

@section('javascript')

  @yield('jsSeleccionFechas')

@endsection


