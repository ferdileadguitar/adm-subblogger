<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="fb:app_id" content="1555190841439591">

    <title>{{ $pageTitle }}</title>

    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//static.keepo.me/">
    <link rel="dns-prefetch" href="//media.keepo.me/">
    <link rel="dns-prefetch" href="//keepo.me/">

    <!-- FavIcon -->
    <link rel="Shortcut Icon" href="{{ url('favicon.ico') }}">
    <link rel="icon" type="image/ico" href="{{ url('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

    <!-- Style -->
    @stack('css')
</head>
<body>
    @include('layouts.header')

    <div class="main-body" ng-controller="app-controller">
        @yield('content')
    </div>

    <!-- Scripts -->
    <!-- <script>var baseURL = 'http://new-admin.keepo.pyo/';</script> -->
    <script>var baseURL = "{{ config('app.url') }}";</script>
    @stack('script_addons')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
    <!-- <script
      src="https://code.jquery.com/jquery-3.2.1.min.js"
      integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
      crossorigin="anonymous"></script>
    <script
      src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
      integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
      crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"></script> -->

    <script src="{{ asset('dist/js/vendor.js') }}"></script>
    <script type="text/javascript">
        window.admin = {!! $adminUser !!};
    </script>
    @stack('scripts')
    @stack('json')

    <!-- Facebook -->
    @include('layouts.fb-scripts')
</body>
</html>