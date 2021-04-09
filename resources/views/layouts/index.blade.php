<!DOCTYPE html>
<html>
@include('inc.header')
<body>
<div id="page-container" class="sidebar-o sidebar-dark enable-page-overlay side-scroll page-header-fixed">
    @include('layouts.sidebar')
    <main id="main-container">
        @include('inc.message')
        @yield('content')
    </main>

    @include('inc.footer')
    <div class="pre-loader">
        <div class="loader">
        </div>
    </div>
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
        var Toast=actions.Toast;

        var app = createApp({
            apiKey: '{{ config('shopify-app.api_key') }}',
            shopOrigin: '{{ \Illuminate\Support\Facades\Auth::user()->name }}',
            forceRedirect: true,
        });
        var button = Button.create(app, {label: 'View Free Google Shopping Clicks'});

        TitleBar.create(app, {
            buttons: {
                primary: button,
            },
        });
        button.subscribe(Button.Action.CLICK, data => {
            window.open('https://merchants.google.com/mc/reporting/dashboard/','_blank');
        });
        var msg = '{{\Illuminate\Support\Facades\Session::get('msg')}}';
        var error='{{\Illuminate\Support\Facades\Session::get('error')}}';
        if(msg!=='')
        {
            const toastOptions = {
                message: msg,
                duration: 3000,
            };
            const toastNotice = Toast.create(app, toastOptions);
            toastNotice.dispatch(Toast.Action.SHOW);
        }
        if(error!=='')
        {
            const toastOptions = {
                message: msg,
                duration: 3000,
                isError: true
            };
            const toastNotice = Toast.create(app, toastOptions);
            toastNotice.dispatch(Toast.Action.SHOW);
        }


    </script>

{{--    @include('shopify-app::partials.flash_messages')--}}
@endif
</body>
</html>
