<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/topbox.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{asset('css/topbox.css')}}" rel="stylesheet"/>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>


            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @if(config('shopify-app.appbridge_enabled'))
        <script src="https://unpkg.com/@shopify/app-bridge@0.8.2/index.js"></script>
        <script src="https://unpkg.com/@shopify/app-bridge@0.8.2/actions.js"></script>
        <script>
            var AppBridge = window['app-bridge'];
            var createApp = AppBridge.default;
            var actions = window['app-bridge']['actions'];
            var Button = actions.Button;
            var TitleBar = actions.TitleBar;

            var app = createApp({
                apiKey: '{{ config('shopify-app.api_key') }}',
                shopOrigin: '{{ \Illuminate\Support\Facades\Auth::user()->name }}',
                forceRedirect: true,
            });
            var button = Button.create(app, {label: 'Help'});

            TitleBar.create(app, {
                buttons: {
                    primary: button,
                },
            });
            button.subscribe(Button.Action.CLICK, data => {
                window.open('https://help.fuznet.com/en/category/go-back-8sa7tu/','_blank');
            });


        </script>

        @include('shopify-app::partials.flash_messages')
    @endif
    <script>
        $(document).ready(function() {
            $('.lightbox').topbox();
        });
    </script>
</body>
</html>
