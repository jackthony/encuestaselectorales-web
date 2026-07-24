<!doctype html>
<html lang="es" class="scroll-smooth">
<head>
    @include('partials.head')
    @stack('head')
</head>
<body class="antialiased font-sans flex min-h-screen flex-col">
    @include('partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    <script src="{{ asset('assets/js/share-actions.js') }}?v={{ filemtime(public_path('assets/js/share-actions.js')) }}"></script>
    @stack('scripts')
</body>
</html>
