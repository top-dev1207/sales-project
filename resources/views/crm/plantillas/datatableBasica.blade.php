@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    @yield('srcCssDatatable')

@endsection


@section('javascript')

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <!-- DataTable -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" type="text/javascript"></script>

    @yield('srcJsDatatable')

    <script>
        $(document).ready(function() {
           t= $('#TablaInteligenteDatos').DataTable(
                {
                        
                }
            );
        });
    </script>

@endsection