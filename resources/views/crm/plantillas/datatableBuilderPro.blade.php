@section('css')
    
    @yield('srcCssDatatable')
    
    <!-- DataTable Builder -->
    {{-- <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.3/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.4/sr-1.4.1/datatables.min.css" rel="stylesheet"> --}}

    {{-- Lastone --}}


    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.1.4/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.4/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.8.0/sp-2.3.2/sl-2.0.5/sr-1.4.1/datatables.min.css" rel="stylesheet">

    {{-- <style>
        /* Centrar el contenido de la tabla */
        #TablaInteligenteDatos {
            width: 80%; /* Ajusta el ancho según sea necesario */
            margin: 0 auto; /* Centra la tabla en su contenedor */
        }

        #TablaInteligenteDatos td {
            text-align: center; /* Centra horizontalmente el texto */
            vertical-align: middle; /* Centra verticalmente el texto */
            padding: 10px; /* Espaciado interno para mejorar la legibilidad */
            border: 0px solid #ddd; /* Bordes de las celdas */
        }
        #TablaInteligenteDatos th {
            text-align: center !important; /* Centra horizontalmente el texto */
            vertical-align: middle !important; /* Centra verticalmente el texto */
            padding: 10px; /* Espaciado interno para mejorar la legibilidad */
            border: 10px solid #ddd; /* Bordes de las celdas */
        }
      
        /* #TablaInteligenteDatos th {
            background-color: #96b4e0; // Color de fondo del encabezado 
        } 
        */
    </style> --}}


@endsection


@section('javascript')

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    
    <!-- DataTable Builder -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.3/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.4/sr-1.4.1/datatables.min.js"></script> --}}

        {{-- lastone --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.1.4/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.4/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.8.0/sp-2.3.2/sl-2.0.5/sr-1.4.1/datatables.min.js"></script>


    @yield('srcJsDatatable')

    <script>
        $(document).ready(function() {
            t= $('#TablaInteligenteDatos').DataTable(
                {
                    @yield('configuracion Datatable')
                }
            );
        });
    </script>

@endsection