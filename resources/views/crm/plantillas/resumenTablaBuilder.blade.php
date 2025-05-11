@extends('crm.plantillas.encuadreVolver')

@yield('configuracionTabla')


@section('contenido_2')


    <div class="container-fluid ">
        <div class="animated fadeIn "  >

            @yield('espacioResumen')

            <div  class="card"   >
                <div class="card-header" >
                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                            <div class="h4 font-weight-bold ">
                                <i class="fa">
                                    @yield('tituloTabla')
                                </i>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                                <i class="fa">
                                    @yield('operacionesTabla')
                                </i>
                        </div>

                    </div>

                </div>


                <div class="card-body">
                    @yield('modal')
                    @yield('modal2')
                    <div class="row">
                        <div class="table-responsive ">
                            <table id="TablaInteligenteDatos" class="display nowrap text-center" style=" width:100%">
                                @yield('tabla')
                            </table>
                        </div>
                    </div>
                    @yield('pieTabla')
                </div>
            </div>
        </div>
    </div>

@endsection

