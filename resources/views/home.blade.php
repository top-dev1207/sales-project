{{-- @extends('layouts.app') --}}
@extends('crm.plantillas.base')

@php
    session()->forget('urlOriginal');      //Para borrar la sesión actual
@endphp


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header h4">CRM - Sistema de Gestión - {{ env('NOMBRE_LOCAL', 'miAdministrador') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="div text-center">
                        {{-- <img class="img-fluid" src="{{ url( env('LOGO_PAGINA_PPAL', 'images/crm2.jpg') ) }}"  alt="{{ env('NOMBRE_LOCAL') }}"> --}}
                        <img class="img-fluid" src="{{ url( env('LOGO_PAGINA_PPAL', 'images/crm2.jpg') ) }}"  alt="{{ env('NOMBRE_LOCAL') }}">
                        
                    <hr>
                    </div>
                    <div class="div h5 text-center">
                        Hola <strong>{{auth()->user()->name  }}</strong>. Has iniciado sesión!
                    </div>
                    <div class="div h5 text-center">
                        tu mail es: {{auth()->user()->email  }}
                    </div>
                    <div class="div h5 text-center">
                        y perteneces a: <strong>{{auth()->user()->area->nombre  }}</strong>
                    </div>
                    <hr>
                    <div class="div h5 text-center">
                        <-- Selecciona una opción del panel izquierdo
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
