@extends('crm.plantillas.base')

@include('crm.plantillas.rangoFechasPlantilla', ['rutaDestino' => 'graficos.resumen1'])


@section('content')
{{-- @php(dd($tipo_accidentes)) --}}
{{-- @php(dd(json_decode($tipo_accidentes[1]))) --}}
{{-- @php(dd($accidente_rama)) --}}
{{-- @php(dd($accidentes_condiciones)) --}}
{{-- @php(dd($accidentes_camino)) --}}
{{-- @php(dd($accidentes_averias)) --}}
{{-- @php(dd($anio)) --}}

@php($anio=2023)
{{-- @php(dd($costoRubroPeq)) --}}

{{-- @php($ventasDia[0]=[1,2,3]) 
@php($ventasDia[1]=[1,2,3])  --}}

{{-- @php($costoRubro[0]=[1,2,3,4]) 
@php($costoRubro[1]=[1,2,3,4])  --}}

@php($accidentes_condiciones[0]=[1,2,3]) 
@php($accidentes_condiciones[1]=[1,2,3,4]) 

@php($accidentes_iluminacion[0]=[1,2,3,4]) 
@php($accidentes_iluminacion[1]=[1,2,3,4]) 

@php($accidentes_tipo_vehiculo[0]=[1,2,3,4]) 
@php($accidentes_tipo_vehiculo[1]=[1,2,3,4]) 

@php($accidentes_visibilidad[0]=[1,2,3,4]) 
@php($accidentes_visibilidad[1]=[1,2,3,4]) 

@php($accidentes_averias[0]=[1,2,3,4]) 
@php($accidentes_averias[1]=[1,2,3,4]) 

{{-- @php($Tamanio_Grafico=1) --}}

  <div class="container-fluid">
    <div class="card card-chart">
      <div class="card-header">
        <div class="h4 font-weight-bold ">
            <i class="fa">
                <div class="row">
            Indicadores
          </div>
          <div class="row h6 text-normal text-info">
            Del {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }}
          </div>
            </i>
        </div>
      </div>
    </div>
  </div>

          <div class="container-fluid">
            <div class="fade-in">

              @if($Tamanio_Grafico==1)
                <div class="">
              @else
                <div class="row row-cols-1 row-cols-lg-2">
                {{-- <div class="card-deck cols-2"> --}}
              @endif
              {{-- <div class=""> --}}

               
                <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold">Ventas por Dia  /  {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }}
                      <div class="card-header-actions"><a class="card-header-action" href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" target="_blank"><small class="text-muted">CRM - Rest</small></a></div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-VentasPorDia"></canvas>
                      </div>
                    </div>
                  </div>
                </div>      

                <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold">Ventas diarias /  {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }} 
                      <div class="card-header-actions"><a class="card-header-action" href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" target="_blank"><small class="text-muted">CRM - Rest</small></a></div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-VentasDiarias"></canvas>
                      </div>
                    </div>
                  </div> 
                </div> 

                <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold">Incidencia de Costos por rubro  /  {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }}
                      <div class="card-header-actions"><a class="card-header-action" href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" target="_blank"><small class="text-muted">CRM - Rest</small></a></div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-IncidenciaCostoRubros"></canvas>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold"> Rubro gastos < 5%  /  {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }}
                      <div class="card-header-actions"><a class="card-header-action" href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" target="_blank"><small class="text-muted">CRM - Rest</small></a></div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-RubrosMenor5"></canvas>
                      </div>
                    </div>
                  </div>                
                </div>                
                  
                <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold"> Rubro gastos > 5% /  {{ \Carbon\Carbon::parse($fechaInicio)->locale('es')->isoFormat('D MMM Y')  }} al {{ \Carbon\Carbon::parse($fechaFin)->locale('es')->isoFormat('D MMM Y')  }}
                      <div class="card-header-actions"><a class="card-header-action" href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" target="_blank"><small class="text-muted">CRM - Rest</small></a></div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-RubrosMayor5"></canvas>
                      </div>
                    </div>
                  </div> 
                </div> 

                {{-- <div class="col">
                  <div class="card">
                    <div class="card-header font-weight-bold"> Prueba CHARTJS  
                      <div class="card-header-actions">
                        <a class="card-header-action" 
                            href="{{ route('graficos.resumen',  ['fechaInicio'=>$fechaInicio, 'fechaFin'=>$fechaFin, 'Tamanio_Grafico'=>!$Tamanio_Grafico]) }}" 
                            target="_blank"><small class="text-muted">CRM - Rest</small>
                        </a>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="c-chart-wrapper">
                        <canvas id="canvas-Pruebas"></canvas>
                      </div>
                    </div>
                  </div> 
                </div>  --}}

              </div>
            </div>
          </div>


          <div class="container-fluid">
            <div class="card card-chart">

              <div class="card-header">
                <div class="h5">
                  <i class="fa fa-align-justify"></i> Estadísticas - Seleccione otro periodo
                </div>
              </div>
              @yield('seleccionRangoFechas')
         
            </div>
          </div>

@endsection

@section('javascript')

  @yield('jsSeleccionFechas')


    {{-- <script src="{{ asset('js/Chart.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('js/coreui-chartjs.bundle.js') }}"> </script> --}}
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    {{-- <script src="{{ asset('js/chartjs-plugin-colorschemes.js') }}"></script>  --}}
    {{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script> --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-colorschemes@0.4.0"></script>  

  {{-- const label = @json($tipo_accidentes[0]);
  const datos = @json($tipo_accidentes[1]); --}}
    <script>
      const anios = @json($anio);

      var ctx = document.getElementById('canvas-7').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'horizontalBar',
          data: {
              labels: label,
              datasets: [
                {
                  label: anios,
                  data: datos,
                  backgroundColor: [
                      'rgba(255, 99, 132, 0.3)',
                      'rgba(54, 162, 235, 0.3)',
                      'rgba(255, 206, 86, 0.3)',
                      'rgba(75, 192, 192, 0.3)',
                      'rgba(153, 102, 255, 0.3)',
                      'rgba(255, 159, 64, 0.3)',
                      'rgba(255, 99, 132, 0.3)',
                      'rgba(54, 162, 235, 0.3)',
                      'rgba(255, 206, 86, 0.3)',
                      'rgba(75, 192, 192, 0.3)',
                      'rgba(153, 102, 255, 0.3)',
                      'rgba(255, 159, 64, 0.3)'
                  ],
                  borderColor: [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                ],
                  borderWidth: 1
                }
                

              ]
          },

      options: {
        responsive: true,
        legend: { display: false},
        plugins: {
          legend: {
            //position: 'right',
            position: 'left',
          }
        },

        maintainAspectRatio: false
      }
      });
      
    </script>   
     
  <script>
      const label_1 = @json($ventasDia[0]);
      const datos_1 = @json($ventasDia[1]);
      
      var ctx = document.getElementById('canvas-VentasPorDia').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'bar',    //Ventas por Dia
          data: {
            labels: label_1,

              datasets: [
                {
                  label:  "Ventas en $",
                  data:   datos_1,
                  plugins: {
                      legend: { position: 'top'},
                    },
                   backgroundColor: [
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)',
                     'rgba(255, 99, 132, 0.3)',
                     'rgba(54, 162, 235, 0.3)',
                     'rgba(255, 206, 86, 0.3)',
                     'rgba(75, 192, 192, 0.3)',
                     'rgba(153, 102, 255, 0.3)',
                     'rgba(255, 159, 64, 0.3)',
                     'rgba(95, 99, 2, 0.3)'
    
                   ],
                   borderColor: [
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',                  
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',                  
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',                  
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)'                  
                   ],
                   hoverBackgroundColor:      [
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',    
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',    
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)',    
                     'rgba(255, 99, 132, 1)',
                     'rgba(54, 162, 235, 1)',
                     'rgba(255, 206, 86, 1)',
                     'rgba(75, 192, 192, 1)',
                     'rgba(153, 102, 255, 1)',
                     'rgba(255, 159, 64, 1)',
                     'rgba(95, 99, 2, 1)'   

                 ],
                  borderWidth: 2

                },
              ]
          },



          options: {
            scales:{
              yAxes: [{
                ticks: {
                  beginAtZero:true
                  //min: 0,
                  //max: 100,
                  //callback: function(value){return value+ "%"}
                  },
                scaleLabel:{
                  display: true,
                  //labelString: "Porcentaje"
                },
              }]

            },
            plugins:{
              legend: { display: false}
            },
            responsive: true,
            maintainAspectRatio: false
        }
      });
      
  </script>   

<script>
  const label_8 = @json($ventasDia[0]);
  const datos_8 = @json($ventasDia[1]);
  const anios_8 = @json($anio);
  
  var ctx = document.getElementById('canvas-VentasDiarias').getContext('2d');
  var myChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: label_8,

        datasets: [
          {
            label:  "Ventas $",
            data:   datos_8,
            fill: false,
            borderWidth: 2,
            borderColor: 'rgb(75, 192, 192)',
            //tension: 0.1
            
          }
        ]
      },
      
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top'}
        },
        elements: {
          drawActiveElementsOnTop: true,
          xAxisID: true,
          yAxisID: true,

        },
    //legend: {display:false},
    //title: { display: true, text:'Ventas $'},
    maintainAspectRatio: false
  }
});
  
</script>    




  <script>
      const label_3 = @json($costoRubroGrande[0]);
      const datos_3 = @json($costoRubroGrande[1]);
      const anios_3 = @json($anio);
      
      var ctx = document.getElementById('canvas-RubrosMayor5').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'pie', //Accidentes según Estado del pavimento
          plugins: [ChartDataLabels],

          data: {
            labels: label_3,

            datasets: [
              {
                label:  anios_3,
                data:   datos_3,

                backgroundColor: [
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)'
                ],

                borderWidth: 1
                }

              ]
          },

      options: {
        responsive: true,
      
        plugins: {
          legend: {
            position: 'right',
          }
        },

        maintainAspectRatio: false
      }
    });
      
  </script>  
  
  <script>
      const label_Peq4 = @json($costoRubroPeq[0]);
      const datos_Peq4 = @json($costoRubroPeq[1]);
      const anios_Peq4 = @json($anio);
      
      var ctx = document.getElementById('canvas-RubrosMenor5').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'pie',    //Accidente por tipo de camino
          data: {
            labels: label_Peq4,

            datasets: [
              {
                label:  anios_Peq4,
                data:   datos_Peq4,

                backgroundColor: [
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                ],

                borderWidth: 1
                }

              ]
          },

          plugins: [ChartDataLabels],
      options: {
      
        responsive: true,
      
        plugins: {
          legend: {
            position: 'right',
          }
        },

        maintainAspectRatio: false
      }
    });
      
  </script>   

  <script>
      const label_4 = @json($costoRubro[0]);
      const datos_4 = @json($costoRubro[1]);
      const anios_4 = @json($anio);
      
      var ctx = document.getElementById('canvas-IncidenciaCostoRubros').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'pie',    //Accidente por tipo de camino
          plugins: [ChartDataLabels],

          data: {
            labels: label_4,

            datasets: [
              {
                label:  anios_4,
                data:   datos_4,

                backgroundColor: [
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                ],

                borderWidth: 1
                }

              ]
          },

      options: {
      
        responsive: true,
      
        plugins: {
          legend: {
            position: 'right',
          }
        },

        maintainAspectRatio: false
      }
    });
      
  </script>   


 
  <script>
      const label_5 = @json($accidentes_iluminacion[0]);
      const datos_5 = @json($accidentes_iluminacion[1]);
      const anios_5 = @json($anio);
      
      var ctx = document.getElementById('canvas-ml-5').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: label_5,

            datasets: [
              {
                label:  anios_5,
                data:   datos_5,

                backgroundColor: [
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                ],

                borderWidth: 1
                }

              ]
          },

      options: {
        // Elements options apply to all of the options unless overridden in a dataset
        // In this case, we are setting the border of each horizontal bar to be 2px wide
        // scales:{
        //   yAxes: [{
        //     ticks: {
        //       beginAtZero:true
        //     }
        //   }]
        // },
      
        responsive: true,
      
        plugins: {
          legend: {
            position: 'right',
          }
        },

        //maintainAspectRatio: false
        maintainAspectRatio: false
      }
    });
      
  </script>   

  <script>
      const label_6 = @json($accidentes_tipo_vehiculo[0]);
      const datos_6 = @json($accidentes_tipo_vehiculo[1]);
      const anios_6 = @json($anio);
      
      var ctx = document.getElementById('canvas-ml-4').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'bar',    //Accidentes por Tipo de Vehículo
          data: {
            labels: label_6,

            datasets: [
              {
                label:  anios_6,
                data:   datos_6,

                backgroundColor: [
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)',
                    'rgba(255, 99, 132, 0.3)',
                    'rgba(54, 162, 235, 0.3)',
                    'rgba(255, 206, 86, 0.3)',
                    'rgba(75, 192, 192, 0.3)',
                    'rgba(153, 102, 255, 0.3)',
                    'rgba(255, 159, 64, 0.3)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                  ],
                  hoverBackgroundColor:      [
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)',
                      'rgba(255, 99, 132, 1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                ],

                borderWidth: 1
              }
              ]



              
          },

          




      options: {

        responsive: true,
        legend: { display: false},
        plugins: {
          legend: {
            position: 'right',
          },
          datalabels: {
              color: 'white',
              display: function(context) {
                        return context.dataset.data[context.dataIndex] > 15;
                      },
              font: {
                weight: 'bold'
              },
              formatter: Math.round
              }
        },

        //maintainAspectRatio: false
        maintainAspectRatio: false
      }
    });
      
  </script>  
  
  
  <script>
    
      var ctx = document.getElementById('canvas-Pruebas').getContext('2d');
      var chart = new Chart(ctx, {

      type: 'line',
      data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [1, 2, 3].map(function(i) {
          return {
            label: 'Dataset ' + i,
            data: [0, 0, 0, 0, 0, 0, 0].map(Math.random),
            fill: false
          };
        })
      },
      options: {
        plugins: {
          colorschemes: {
            scheme: 'brewer.Paired12'
          }
        }
      }
    });
      
  </script>   
  
  <script>
    
      const label_7 = @json($accidentes_visibilidad[0]);
      const datos_7 = @json($accidentes_visibilidad[1]);
      const anios_7 = @json($anio);
      
      var ctx = document.getElementById('canvas-Pruebas2').getContext('2d');
      var myChart = new Chart(ctx, {

          plugins: [ChartDataLabels],
          type: 'bar',
          data: {
            labels: label_7,

            datasets: [
              {
                label:  anios_7,
                data:   datos_7,

                 backgroundColor: [ '#ffffcc', '#ffeda0', '#fed976', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c', '#bd0026', '#800026'],
                //     'rgba(255, 99, 132, 0.3)',
                //     'rgba(54, 162, 235, 0.3)',
                //     'rgba(255, 206, 86, 0.3)',
                //     'rgba(75, 192, 192, 0.3)',
                //     'rgba(153, 102, 255, 0.3)',
                //     'rgba(255, 159, 64, 0.3)',
                //     'rgba(255, 99, 132, 0.3)',
                //     'rgba(54, 162, 235, 0.3)',
                //     'rgba(255, 206, 86, 0.3)',
                //     'rgba(75, 192, 192, 0.3)',
                //     'rgba(153, 102, 255, 0.3)',
                //     'rgba(255, 159, 64, 0.3)'
                //   ],
                   borderColor: [ '#ffffcc', '#ffeda0', '#fed976', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c', '#bd0026', '#800026'],
                //     'rgba(255, 99, 132, 1)',
                //     'rgba(54, 162, 235, 1)',
                //     'rgba(255, 206, 86, 1)',
                //     'rgba(75, 192, 192, 1)',
                //     'rgba(153, 102, 255, 1)',
                //     'rgba(255, 159, 64, 1)',
                //     'rgba(255, 99, 132, 1)',
                //     'rgba(54, 162, 235, 1)',
                //     'rgba(255, 206, 86, 1)',
                //     'rgba(75, 192, 192, 1)',
                //     'rgba(153, 102, 255, 1)',
                //     'rgba(255, 159, 64, 1)'
                //   ],
                   hoverBackgroundColor:      ['#ffffcc', '#ffeda0', '#fed976', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c', '#bd0026', '#800026'],
                //       'rgba(255, 99, 132, 1)',
                //       'rgba(54, 162, 235, 1)',
                //       'rgba(255, 206, 86, 1)',
                //       'rgba(75, 192, 192, 1)',
                //       'rgba(153, 102, 255, 1)',
                //       'rgba(255, 159, 64, 1)',
                //       'rgba(255, 99, 132, 1)',
                //       'rgba(54, 162, 235, 1)',
                //       'rgba(255, 206, 86, 1)',
                //       'rgba(75, 192, 192, 1)',
                //       'rgba(153, 102, 255, 1)',
                //       'rgba(255, 159, 64, 1)'
                // ],
                 borderWidth: 1
              }
              ]
          },

          options: {

            responsive: true,
            legend: { display: false},
            maintainAspectRatio: false
          }
    });
      
  </script>   



@endsection