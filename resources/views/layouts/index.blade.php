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

        var app = createApp({
            apiKey: '{{ config('shopify-app.api_key') }}',
            shopOrigin: '{{ \Illuminate\Support\Facades\Auth::user()->name }}',
            forceRedirect: true,
        });


    </script>

{{--    @include('shopify-app::partials.flash_messages')--}}
@endif
</body>
</html>
