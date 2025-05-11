@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
@endsection


@section('javascript')

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <!-- DataTable -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" type="text/javascript"></script>
    {{-- <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.flash.min.js" type="text/javascript"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" type="text/javascript"></script>


    <script>
        $(document).ready(function() {
            $('#TablaInteligenteDatos').DataTable(
                {
                dom: "Blfrtip",
                ordering: false,        //Para que no ordene columnas
                //responsive: true,
                pageLength : 100,
                searching: false,         //Muestra el buscador
                lengthChange: false,        //Moestra el selector de tamanio
                paging: false,
                info: false,
                fixedHeader: true,
                    buttons: [
                        // {
                        //     text: 'csv',
                        //     extend: 'csvHtml5',
                        // },
                        //{
                        //    text: 'excel',
                        //    extend: 'excelHtml5',
                        //},
                        //{
                        //    text: 'pdf',
                        //   extend: 'pdfHtml5',
                        //},
                    ],
                    bInfo: false,
                    bAutoWidth: false,
                    
                    language: {"url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json" }
                }
            );
        });
    </script>

    @yield('javaScriptDentroDeConfigDatatable')
            
@endsection