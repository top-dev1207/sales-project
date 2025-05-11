@extends('crm.errorBase')

@section('content')

    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="clearfix">
              <h3 class="float-left display-4 mr-4">404</h3>
              <h4 class="pt-3">Ups! Houston...</h4>
              <p class="text-muted">No encontramos lo que buscas.</p>
              <a class="btn btn-primary btn-md btn-primary offset-md-2" href="{{ url('/')}}" role="button" >
                Inicio
              </a>
          </div>
        </div>
     </div>
    </div>

@endsection

@section('javascript')

@endsection