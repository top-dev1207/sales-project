<!DOCTYPE html>

<html lang="en">
  <head>
    {{-- <base href="./"> --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CRM REST">
    <meta name="author" content="Mauricio Lucero">
    {{-- <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,crm"> --}}
    <title>CRM - REST</title>


    @yield('css')

    <!-- Icons-->
    {{-- <link href="{{ asset('css/free.min.css') }}" rel="stylesheet"> <!-- icons -->
    <link href="{{ asset('css/flag-icon.min.css') }}" rel="stylesheet"> <!-- icons --> --}}
    <!-- Main styles for this application-->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <!-- Global site tag (gtag.js) - Google Analytics-->
    {{-- <script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-118965717-3"></script> --}}
    {{-- <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());
      // Shared ID
      gtag('config', 'UA-118965717-3');
      // Bootstrap ID
      gtag('config', 'UA-118965717-5');
    </script> --}}

    <link href="{{ asset('css/coreui-chartjs.css') }}" rel="stylesheet">

  </head>
  <body class="c-app flex-row align-items-center">

    @yield('content')

    <script src="{{ asset('js/coreui.bundle.min.js') }}"></script>

    @yield('javascript')

  </body>
</html>
