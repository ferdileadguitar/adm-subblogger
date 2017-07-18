<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>

    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//static.keepo.me/">
    <link rel="dns-prefetch" href="//media.keepo.me/">
    <link rel="dns-prefetch" href="//keepo.me/">

    <!-- FavIcon -->
    <link rel="Shortcut Icon" href="[[ url('favicon.ico') ]]">
    <link rel="icon" type="image/ico" href="[[ url('favicon.ico') ]]">
    <link rel="icon" type="image/x-icon" href="[[ url('favicon.ico') ]]">

    <!-- Style -->
    @stack('css')
</head>
<body>
    @include('layouts.header')

    <div class="main-body" ng-controller="app-controller">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script>var baseURL = 'http://new-admin.keepo.pyo/';</script>
    @stack('script_addons')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
    <script src="{{ asset('dist/js/vendor.js') }}"></script>
    @stack('scripts')
</body>
</html>