<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Keepo') }}</title>

    <!-- Style -->
    <link href="{{ asset('dist/css/login.css') }}" rel="stylesheet">

</head>
<body>
    <div class="main-body">
        <!-- Modal -->
        <div class="mdl mdl-popup mdl-open">
            <div class="mdl-component <@ processing ? 'processing' : '' @>" ng-controller="login-controller">
                <div class="mdl-body">
                    <div class="error bg-danger" ng-show="error"><@ error @></div>

                    <form  method="POST" action="{{ route('login') }}">
                        <div class="hidden">
                            <input type="submit">
                            <input type="hidden" name="redirect">
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></div>
                                <input type="text" name="username" ng-model="username" placeholder="Username" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></div>
                                <input type="password" name="password" ng-model="password" placeholder="Password" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <footer class="mdl-footer">
                    <a ng-click="submit()"><span class="glyphicon glyphicon-repeat spinner"></span>Login</a>
                </footer>

                <div class="mdl-overlay"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>var baseURL = "{{ config('app.url') }}";</script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
    <script src="{{ asset('dist/js/vendor.js') }}"></script>
    <script src="{{ asset('dist/js/login.js') }}"></script>
</body>
</html>