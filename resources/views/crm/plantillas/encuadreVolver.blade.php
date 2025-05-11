@extends('crm.plantillas.base')

@section('content')

<div class="container-fluid px-2">

    <div class="row justify-content-center">
        @yield('contenido_2')
    </div>


    <div class="row justify-content-center">
        <div class="col-md-2 col-sm-3 col-xs-4 mb-3">
            <a href="{{URL::previous()}}" class="btn btn-block btn-secondary">{{ __('Return') }}</a>
        </div>
    </div>
</div>

@endsection


@section('javascript')

@endsection
