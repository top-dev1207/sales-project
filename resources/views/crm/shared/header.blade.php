

    <div class="c-wrapper">
      <header class="c-header c-header-dark c-header-fixed c-header-with-subheader ">
          <button class="c-header-toggler c-class-toggler d-lg-none mr-auto"
              type="button" data-target="#sidebar" data-class="c-sidebar-show">
              <span class="c-header-toggler-icon"></span>
          </button>

        {{-- <a class="animate__animated animate__heartBeat c-header-brand " href="#">
          <div class="h1 animate__animated animate__pulse c-header-brand" ><strong> CRM</strong> </div>
        </a> --}}

          <button class="c-header-toggler c-class-toggler ml-3 d-md-down-none"
            type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
            <span class="c-header-toggler-icon"></span>
          </button>

          <a class="d-none d-sm-block animate__animated animate__pulse c-header-brand animate__delay-0.5s animate__slower animate__infinite">
            <div class="h1 c-header-brand" ><strong> CRM</strong> </div>
          </a>
          <a class="d-block d-sm-none animate__animated animate__pulse c-header-brand animate__delay-0.5s animate__slower animate__infinite">
            <div class="h4 c-header-brand mt-2" ><strong> CRM</strong> </div>
          </a>

          <a class="c-header-brand d-none d-lg-block d-xl-block">
            <div class="h1 c-header-brand" ><strong> &nbsp&nbsp&nbsp</strong> </div>
          </a>

          <a class="animate__animated animate__fadeIn c-header-brand c-header-brand  d-none d-xl-block">
            <div class="h2 c-header-brand " > Sistema de Gestión</div>
          </a>

        <a class="c-header-brand d-lg-block d-xl-block">
          <div class="h1 c-header-brand" ><strong>&nbsp</strong> </div>
        </a>
        <a class="c-header-brand d-none d-lg-block d-xl-block">
          <div class="h1 c-header-brand" ><strong> &nbsp&nbsp&nbsp</strong> </div>
        </a>


      <ul class="c-header-nav ml-auto mr-4">


          <li class="c-header-nav-item mx-2">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
              <svg class="c-icon">
                <use xlink:href="{{ url('assets/icons/free/free.svg#cil-search') }}"></use>
              </svg>
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">
              <div class="dropdown-header bg-light py-2" style="color:#ffffff;">
                <strong>Funciones</strong>
              </div>

              <a class="dropdown-item" href="{{ url('/resultados/consultar')}}" >
                <svg class="c-icon mr-2">
                  <use xlink:href="{{  url('/icons/sprites/free.svg#cil-find-in-page') }}"></use>
                </svg>
                Gastos
              </a>
             <a class="dropdown-item" href="{{ route('resultados.anual',['anio' => env('ANIO_RESULTADOS','2025')])}}">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{  url('/icons/sprites/free.svg#cil-find-in-page') }}"></use>
                </svg>
                Resultados
              </a>

            </div>
          </li>





        </ul>



    </header>
