@extends('crm.plantillas.base')

@include('crm.plantillas.rangoFechasPlantilla', ['rutaDestino' => 'graficos.resumen1'])


@section('content')
        <div class="container-fluid">
                <div class="card card-chart">

                    <div class="card-header">
                      <div class="h2">
                        <i class="fa fa-align-justify"></i> Estadísticas - Seleccione otro periodo
                      </div>

                    </div>

                    @yield('rangoFechas')

                </div>
        </div>

@endsection

@section('javascript')

  @yield('jsSeleccionFechas')

@endsection