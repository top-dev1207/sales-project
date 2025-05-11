@extends('crm.plantillas.encuadreVolver')

@yield('configuracionTabla')


@section('contenido_2')


    <div class="container-fluid">
        <div class="animated fadeIn" style="background: #153261">
            @yield("contenidoEspacioResumen")
            @yield('espacioResumen')

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                            <div class="h4 font-weight-bold ">
                                <i class="fa">
                                    @yield('tituloTabla')
                                </i>
                            </div>
                        </div>

                        @yield('operacionesTabla')

                    </div>

                </div>


                <div class="card-body">
                    @yield('modal')
                    @yield('modal2')
                    @yield("Columnas fijas")
                    <div class="row">
                        <div class="table-responsive ">
                            {{-- <table id="TablaInteligenteDatos" class="table-responsive table-hover table-striped display compact text-center" style="width:100%"> --}}
                                @yield('tabla')
                            {{-- </table> --}}
                        </div>
                    </div>
                    @yield('pieTabla')
                </div>
            </div>
        </div>
    </div>

@endsection

