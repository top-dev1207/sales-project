@extends('crm.plantillas.base')


@section('contenido_2')

    <div class="container-fluid">
        {{-- <div class="animated fadeIn"> --}}
            
            @yield('espacioResumen')

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                            <div class="h4 font-weight-bold ">
                                <i class="fa">
                                    @yield('tituloTabla')
                                    {{ $titulo }}
                                </i>
                            </div>
                        </div>
                        @yield('operacionesTabla')
                    </div>
                </div>

                <div class="card-body text-center">
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <p class="card-text h4">
                        {{ $texto }}
                    </p>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
        {{-- </div> --}}
    </div>

@endsection

@section('content')

<div class="container-fluid px-2">

    <div class="row justify-content-center">
        @yield('contenido_2') 
    </div>
    
    
    <div class="row justify-content-center">
        <div class="col-md-2 col-sm-3 col-xs-4 mb-3">
            <a href="{{URL::previous()}}" class="btn btn-block btn-secondary ">{{ __('Return') }}</a>
        </div>
    </div>
</div>

@endsection

@section('javascript')

@endsection




