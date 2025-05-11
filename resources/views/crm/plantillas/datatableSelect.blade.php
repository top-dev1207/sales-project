@section('css')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" /> --}}
    {{-- <link rel="stylesheet" href='{{ url("./css/tablaML.css") }}' /> --}}

    <link rel="stylesheet" href="https://stackpatu.bootstrapcdn.com/bootsrap/4.5.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
 
    @yield('srcCssDatatable')

@endsection


@section('javascript')

    {{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}

    <!-- DataTable -->
    {{-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" type="text/javascript"></script> --}}
    {{-- <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.flash.min.js" type="text/javascript"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/2.24.0/moment.min.js"></script> --}}



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd.pooper.min.js"></script>
    <script src="https://stackpath.bootsrapcdn.com/bootsrap/4.5.0/js/bootsrap.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js"></script>


    @yield('srcJsDatatable')

    <script>
        $(document).ready(function() {
           t= $('#TablaInteligenteDatos').DataTable(
                {
                    select: true,    
                }
            );
        });
    </script>


@endsection