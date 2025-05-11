<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">

<div class="c-sidebar-brand c-sidebar-dark">
  {{-- <img class="c-sidebar-brand-full img-fluid" src="{{ url ('images/isologotipo-horizontal-blanco.png') }}" alt="Temple"> --}}
  <img class="c-sidebar-brand-full img-fluid" src="{{ url (env('LOGO_LOCAL', 'images/crm2.jpg')) }}" alt=' env("NOMBRE_LOCAL") '>
  {{-- <img class="c-sidebar-brand-minimized img-fluid" src="{{ url ('images/iso-cuadrado-blanco.png') }}"  alt="Temple"> --}}
  <img class="c-sidebar-brand-minimized img-fluid" src="{{ url (env('MINI_LOGO_LOCAL', 'images/crm2.jpg')) }}" alt='{{ env("NOMBRE_LOCAL") }} '>
</div>


  <nav class="c-sidebar-nav">
      @csrf
      <li class="c-sidebar-nav-item">
        <a class="c-sidebar-nav-link" href="{{ url('/')}}">
          <svg class="c-sidebar-nav-icon">
            {{-- <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-speedometer')}}"></use> --}}
            <use xlink:href="{{ url('/icons/sprites/free.svg#cil-home')}}"></use>
          </svg> CRM - Panel de control
          <!-- <span class="badge badge-info">NEW</span> -->
        </a>
      </li>


      {{--/**************************GESTION DE RECLAMOS******************************/ --}}
      @can('ver_reclamos')
        <li class="c-sidebar-nav-title">Gestión de Reclamos</li>

          <li class="c-sidebar-nav-dropdown">
            <a class="c-sidebar-nav-dropdown-toggle" href="#">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/free/free.svg#cil-burn')}}"></use>
              </svg> Reclamos</a>

          <ul class="c-sidebar-nav-dropdown-items">

            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('cliente.buscar', ['mje'=> 1 ]) }}">
              {{-- <a class="c-sidebar-nav-link" href="{{ route('reclamo.crear', ['clicod' => 999] ) }}"> --}}
              {{-- <a class="c-sidebar-nav-link" href="{{ action('ReclamosController@formulario_crear', ['clicod' => 122] ) }}"> --}}
                  <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('assets/icons/free/free.svg#cil-plus') }}"></use></svg>
                Nuevo Reclamo
              </a>
            </li>


            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('reclamos.listar.resolver') }}">
                <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-puzzle')}}"></use></svg>
                Reclamos para resolver
              </a>
            </li>



            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('reclamos.listar.delegados') }}">
                <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-share-alt')}}"></use></svg>
                Mis Reclamos delegados
              </a>
            </li>

            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('reclamos.listar.mios') }}">
                <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-list-high-priority')}}"></use></svg>
                Todos mis Reclamos
              </a>
            </li>

            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('reclamos.listar.abiertos') }}">
                <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-list')}}"></use></svg>
                Todos los Reclamos abiertos
              </a>
            </li>

            <li class="c-sidebar-nav-item">
              <a class="c-sidebar-nav-link" href="{{ route('reclamos.listar') }}">
                <svg class="c-sidebar-nav-icon"> <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-history')}}"></use></svg>
                Histórico de Reclamos
              </a>
            </li>


          </ul>
        </li>
      @endcan



      {{--/************************** PROVEEDORES ******************************/ --}}
        {{-- @canany(['ver_proveedores','crear_proveedores'])

          <li class="c-sidebar-nav-title">Proveedores</li> --}}

          {{-- <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/Proveedores/crear_old">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-garage"></use>
              </svg> Nuevo Accidente old</a>
          </li> --}}
          {{-- <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/Proveedores/listar_old')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-spreadsheet"></use>
              </svg> Todos los Proveedores old</a>
            </li> --}}
          {{-- @can('crear_proveedores')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/proveedores/crear')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-address-book')}}"></use>
                </svg> Nuevo Proveedor</a>
            </li>
          @endcan --}}
            {{-- <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/proveedores/listar_paginado')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-spreadsheet"></use>
                </svg> Últimos Proveedores</a>
            </li> --}}
          {{-- @can('ver_proveedores')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/proveedores/listar')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-people')}}"></use>
                </svg> Todos los Proveedores</a>
            </li>
          @endcan --}}
            {{-- <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/proveedores/resumen')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-vertical-align-bottom1')}}"></use>
              </svg> Resumen</a>
            </li> --}}
          {{-- @endcan --}}
        {{-- @endcan --}}

      {{--/***************************************************************/ --}}

      {{--/************************** CAJAS ******************************/ --}}
        {{-- @hasanyrole([ 'Caja chica Arena|Caja chica Templo|Caja Gerente Arena|Caja mayor Arena
                      |Caja mayor Templo|Caja Administración|Caja Administración USD|Caja chica Administración
                      |Caja Gerencia Administración|Caja Gerencia Administración USD']) --}}
        @canany(['Caja chica Arena','Caja chica Templo','Caja Gerente Arena','Caja mayor Arena
                      ','Caja mayor Templo','Caja Administración','Caja Administración USD','Caja chica Administración
                      ','Caja Gerencia Administración','Caja Gerencia Administración USD'])

          <li class="c-sidebar-nav-title">Cajas</li>

          <li class="c-sidebar-nav-dropdown">
            <a class="c-sidebar-nav-dropdown-toggle" href="#">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/icons/sprites/free.svg#cil-dollar')}}"></use>
                {{-- cil-dollar --}}
              </svg> Cajas</a>

          <ul class="c-sidebar-nav-dropdown-items">


          @can('ver_saldo_caja')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{  route('cajas.resumen')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-bank')}}"></use>
                </svg> Saldos </a>
            </li>
          @endcan

          @canany(['transferencias_full','aliviar_caja_mayor','aliviar_caja_chica'])
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('cajas.transferencia')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-loop')}}"></use>
                </svg> Transferencias </a>
            </li>
          @endcanany


          @can('realizar_arqueos')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('cajas.arqueo')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-calculator')}}"></use>
                </svg> Arqueo </a>
            </li>
          @endcan

          </ul>

        @endcanany
        {{-- @endhasanyrole --}}
      {{--/******************************************************************/ --}}


      {{--/************************** VENTAS ******************************/ --}}
        @can('ver_ventas')
        <li class="c-sidebar-nav-title">Cierres Diarios</li>

        <li class="c-sidebar-nav-dropdown">
            <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-chart-line')}}"></use>
            </svg> Ventas</a>

        <ul class="c-sidebar-nav-dropdown-items">

        {{-- <li class="c-sidebar-nav-title">Ventas</li> --}}

            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/ventas/crear')}}">
                <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-notes')}}"></use>
                </svg> Nueva Venta</a>
            </li>

            @can('validar_ventas')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/ventas/a_validar')}}">
                <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-task')}}"></use>
                </svg> Para validar</a>
            </li>
            @endcan

            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/ventas/listar')}}">
            <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-vertical-align-bottom1')}}"></use>
            </svg> Todas las Ventas</a>
            </li>
            </ul>

        @endcan

      {{--/********************************************************/ --}}


      {{--/************************** FACTURAS ******************************/ --}}
        @can('ver_facturas')

          <li class="c-sidebar-nav-title">Facturas / Remitos</li>

          <li class="c-sidebar-nav-dropdown">
            <a class="c-sidebar-nav-dropdown-toggle" href="#">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/icons/sprites/free.svg#cil-barcode')}}"></use>
              </svg> Facturas</a>

          <ul class="c-sidebar-nav-dropdown-items">

          {{-- <li class="c-sidebar-nav-title">Facturas</li>--}}


            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/facturas/crear')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-notes')}}"></use>
                </svg> Nueva Factura</a>
            </li>
            @cannot('validar_facturas')
              <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('Facturas.listar') }}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-list')}}"></use>
                </svg> Mis Facturas</a>
              </li>
            @endcan

            @can('validar_facturas')
              <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('facturas.para.validar') }}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-task')}}"></use>
                </svg> Para Validar</a>
              </li>
            @endcan

            @if(auth()->user()->can('validar_facturas_N2'))
            @else
              <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('facturas.para.verificar') }}">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-task')}}"></use>
                  </svg> Para Verificar</a>
              </li>
            @endif

            @can('validar_facturas')

            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/facturas/pagar')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-list')}}"></use>
                </svg> Para Pagar</a>
            </li>
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('facturas.proximas.pagar')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-paperclip')}}"></use>
              </svg> Próximos a vencer</a>
            </li>
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('facturas.deuda.proveedor')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/icons/sprites/free.svg#cil-tags')}}"></use>
              </svg> Deuda por Proveedor</a>
            </li>
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/facturas/historial')}}">
                <svg class="c-sidebar-nav-icon">
                  <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-watch')}}"></use>
                </svg> Historial </a>
            </li>
            @endcan

          </ul>

        @endcan
      {{--/*******************************************************************/ --}}



        {{--/************************** ESTADISTICAS ******************************/ --}}
        @canany(['ver_estadísticas','ver_gráficos'])

          <li class="c-sidebar-nav-title">Estadísticas</li>

          @can('ver_estadísticas')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ url('/resultados/consultar')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/icons/sprites/free.svg#cil-bar-chart')}}"></use>
              </svg> Gastos </a>
            </li>
            @can('ver_info_sensible')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('resultados.anual',['anio' => env('ANIO_RESULTADOS','2025')])}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/icons/sprites/free.svg#cil-calendar')}}"></use>
              </svg> Resultados </a>
            </li>
            @endcan
          @endcan

          @can('ver_gráficos')
            <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('graficos.resumen', ['Tamanio_Grafico'=>0])}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-chart-pie')}}"></use>
              </svg> Gráficos</a>
            </li>
          @endcan

        @endcan

        {{--/************************** DESARROLLO ******************************/ --}}
        @can('desarrollar')
          <li class="c-sidebar-nav-title">Desarrollo</li>

            <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link" href="{{ url('/debug')}}">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ url('/assets/icons/coreui/free-symbol-defs.svg#cui-drop1')}}"></use>
              </svg> Debug
            </a>
          </li>
        @endcan

      </nav>
      <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
    </div>
