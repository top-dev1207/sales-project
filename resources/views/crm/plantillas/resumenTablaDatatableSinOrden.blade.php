@extends('crm.plantillas.encuadreVolver')

@include('crm.plantillas.datatableSinOrdenExcel')  



@section('contenido_2')


    <div class="container-fluid">
        <div class="animated fadeIn">
            
            @yield('espacioResumen')

            <div class="card">
                <div class="card-header">
                    <div class="h4 font-weight-bold ">
                        <i class="fa">
                            @yield('tituloTabla')
                        </i>
                    </div>
                </div>


                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            {{-- <table id="TablaInteligenteDatos" class="table-responsive display compact text-center" style="width:100%"> --}}
                            <table id="TablaInteligenteDatos" class="table-responsive display compact" style="width:100%">
                                @yield('tabla')                                    
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

