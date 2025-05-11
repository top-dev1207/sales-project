@section('css')
    
    @yield('srcCssDatatable')
    
    <!-- DataTable Builder -->
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.3/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.4/sr-1.4.1/datatables.min.css" rel="stylesheet">

@endsection


@section('javascript')

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    
    <!-- DataTable Builder -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/af-2.7.0/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/cr-2.0.3/date-1.5.3/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/rg-1.5.0/rr-1.5.0/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.4/sr-1.4.1/datatables.min.js"></script>

    @yield('srcJsDatatable')

    <script>
        $(document).ready(function() {
            $('#TablaInteligenteDatos').DataTable(
                {
                    @yield('configuracion Datatable')
                }
            );
        });
    </script>

@endsection