
@section('seleccionRangoFechas')
    <div class="card-body">
        <form method="POST" action="{{ route($rutaDestino) }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <strong>Indique periodo o rango de fechas para la consulta:</strong>
            </div>
        </div>

        
        <div class="container">


            <div class="row">
            
                <div class="col-sm-3">
                    <div class="label">Mes</div>
                    <select class="form-control" name="mes" id="mes" onchange="javascript:calcularFecha()">
                    {{-- <option value='20'>Enero 2024</option> --}}
                    <option value='01'>Enero</option>
                    <option value='02'>Febrero</option>
                    <option value='03'>Marzo</option>
                    <option value='04'>Abril</option>
                    <option value='05'>Mayo</option>
                    <option value='06'>Junio</option>
                    <option value='07'>Julio</option>
                    <option value='08'>Agosto</option>
                    <option value='09'>Septiembre</option>
                    <option value='10'>Octubre</option>
                    <option value='11'>Noviembre</option>
                    <option value='12'>Diciembre</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="label">Año</div>
                    <select class="form-control" name="anio" id="anio" onchange="javascript:calcularFecha()">
                    {{-- <option value='20'>Enero 2024</option> --}}
                    <option value='24'>2024</option>
                    <option value='25'>2025</option>
                    {{-- <option value='0424'>2026</option>
                    <option value='0524'>2027</option>
                    <option value='0624'>2028</option>
                    <option value='0724'>2029</option> --}}
                    </select>
                </div>

            <div class="col-sm-3">
                <div class="label">Fecha de inicio</div>  
                <input type="date" value='{{  \Carbon\Carbon::now()->format('Y-m-d') }}' class="form-control" id="fechaInicio" name="fechaInicio" />
            </div>
            <div class="col-sm-3">
                <div class="label">Fecha de fin</div>  
                <input type="date" value='{{  \Carbon\Carbon::now()->format('Y-m-d') }}' class="form-control" id="fechaFin" name="fechaFin" />
            </div>
            </div>

            <hr>

            <div class="row">
            <div class="col-md-6 mt-3">
                <button class="btn btn-block btn-primary" type="submit"> Consultar </button>
            </div>
            
            <div class="col-md-6 mt-3">
                <a href="{{URL::previous()}}" class="btn btn-block btn-secondary">{{ __('Return') }}</a>
            </div>
            </div>
        </div>
        </form>
    </div> 
@endsection


@section('jsSeleccionFechas')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript">
    $(document).ready(                  function(){ inicializarFecha()});
        


    function inicializarFecha(){

        date = new Date();      //Actual
        
        anio = date.getFullYear()-2000;
        //console.log(anio);
        
        mes = date.getMonth()+1;
        mes = mes.toString().padStart(2, '0');
        //console.log('Mes actual: '+mes);
        //console.log('Día actual: '+date.getDate());
        //console.log('Año actual: '+date.getFullYear());

        let ult = new Date(date.getYear(),date.getMonth()+1,0);
        //console.log('Último día del mes: '+ult.getDate());

        let hoy = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        let ultimoDiaMes = new Date(date.getFullYear(), date.getMonth(), new Date(date.getYear(),date.getMonth()+1,0).getDate());
        let primerDiaMes = new Date(date.getFullYear(), date.getMonth(), 1);
        
        hoy = hoy.toISOString().slice(0, 10);
        ultimoDiaMes = ultimoDiaMes.toISOString().slice(0, 10);
        primerDiaMes = primerDiaMes.toISOString().slice(0, 10);

        //console.log(hoy);
        //console.log(ultimoDiaMes);
        //console.log(primerDiaMes);

        //let p =document.getElementById("periodo");
        let m =document.getElementById("mes");
        let a =document.getElementById("anio");

        document.getElementById("fechaInicio").value = primerDiaMes;
        document.getElementById("fechaFin").value = ultimoDiaMes;

        //let periodo = ""+mes+anio;
        //console.log("periodo: "+periodo);
        //p.value = periodo;
        m.value = mes;
        a.value = anio;

    }

    function calcularFecha(){

        //let p =document.getElementById("periodo");
        let m =document.getElementById("mes");
        let a =document.getElementById("anio");
        
        //const indiceOpcionSeleccionado = p.options.selectedIndex; 
        const indiceOpcionSeleccionado = m.options.selectedIndex; 
        const indiceOpcionSeleccionado2 = a.options.selectedIndex; 
        //rango = p.options[indiceOpcionSeleccionado].value;

        //console.log(indiceOpcionSeleccionado);
        //console.log(indiceOpcionSeleccionado2);

        mes         = m.options[indiceOpcionSeleccionado].value;
        anioCorto   = a.options[indiceOpcionSeleccionado2].value;

        //console.log(mes);
        //console.log(anioCorto);

        anio = Number(anioCorto)+2000;

        //console.log(mes);
        //console.log(anio);

        let ultimoDiaMes = new Date(anio, mes-1, new Date(anio,mes,0).getDate());
        let primerDiaMes = new Date(anio, mes-1, 1);

        ultimoDiaMes = ultimoDiaMes.toISOString().slice(0, 10);
        primerDiaMes = primerDiaMes.toISOString().slice(0, 10);

        //console.log(ultimoDiaMes);
        //console.log(primerDiaMes);

        document.getElementById("fechaInicio").value = primerDiaMes;
        document.getElementById("fechaFin").value = ultimoDiaMes;


    }
    </script>

 

@endsection