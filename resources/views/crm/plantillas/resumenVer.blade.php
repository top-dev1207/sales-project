<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CRM CPBA Ceamse">
    <meta name="author" content="Mauricio Lucero">

    <title>CRM - REST</title>

    @yield('css')

    <!-- Icons-->
    {{--
    <link href="{{ asset('css/free.min.css') }}" rel="stylesheet"> <!-- icons -->
    <link href="{{ asset('css/flag-icon.min.css') }}" rel="stylesheet"> <!-- icons --> --}}

    <!-- Main styles for this application-->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.8.1"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/es.js"></script>
</head>

<body class="c-app">

    {{-- @include('crm.shared.nav-admin') --}}
    {{--Mauricio--}}
   <div id="resumenVer"></div>

    {{-- <!-- CoreUI and necessary plugins--> --}}
    <script src="{{ asset('js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('js/coreui-utils.js') }}"></script>
    <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>

</body>

</html>
