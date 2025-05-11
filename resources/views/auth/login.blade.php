@extends('crm.plantillas.authBase')

@section('content')

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card-group">
        <div class="card ">
          <img class="img-fluid " src="{{ url ('/images/crm2.jpg') }}" alt="CRM">

          <div class="card-body">
            <x-auth-session-status class="mb-4 text-danger" :status="session('status')" />

            <x-auth-validation-errors class="mb-4  text-danger" :errors="$errors" />

            <p class="text-muted">Ingrese sus credenciales</p>
            <form method="POST" action="{{ route('login') }}">
              @csrf
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <svg class="c-icon">
                      <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-user"></use>
                    </svg>
                  </span>
                </div>
                <input class="form-control" type="text" placeholder="{{ __('E-Mail Address') }}" name="email"
                  value="{{ old('email') }}" required autofocus>
              </div>
              <div class="input-group mb-4">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <svg class="c-icon">
                      <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-lock-locked"></use>
                    </svg>
                  </span>
                </div>
                <input class="form-control" type="password" placeholder="{{ __('Password') }}" name="password" required>
              </div>
              <div class="row">
                <div class="col-6">
                  <button class="btn btn-primary px-4" type="submit">{{ __('Login') }}</button>
                </div>
            </form>

            <div class="col-6 text-right">
              <a href="{{ route('password.request') }}" class="btn btn-link px-0">{{ __('Forgot Your Password?') }}</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card text-white bg-primary py-5 d-md-down-none " style="width:44%">
        {{-- <div class="card text-white " style="width:44%"> --}}
          <div class="card-body bg-primary ">
            {{-- <div class="row justify-content-center align-items-center text-center pt-5"> --}}
              <div class="bg-primary h-75">
                <div class="container d-flex align-items-centered justify-content-center flex-column text-center h-100">
                    <h1><strong>Sistema CRM</strong></h1>  
                    <h3><strong>Restaurante</strong></h3>
                    <p>Ingrese sus credenciales provistas para el sistema</p>
                  </div>

                {{-- @can('desarrollar')
                @if (Route::has('password.request'))
                <a href="{{ route('register') }}" class="btn btn-secondary active mt-3">{{ __('Register') }}</a>
                @endif
                @endcan --}}
              </div>
              
                <div class="d-flex align-items-center justify-content-center bg-primary borde-danger h-25">
                  <img class="img-fluid text-center" width="200" height="56"
                    src="{{ url (env('LOGO_INICIO_LOCAL', 'images/crm2.jpg')) }}" alt=' env("NOMBRE_LOCAL") '>
                    {{-- src="{{ url ('images/isologotipo-horizontal-negro.png') }}" alt="Temple"> --}}
                </div> 
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @endsection

  @section('javascript')

  @endsection