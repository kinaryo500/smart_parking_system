<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
     <title>@yield('title', 'Smart Parking System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0061ff">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.css.petugas-style')
    @stack('styles')
</head>

<body>

    @include('partials.sidebar')

    <main id="content">
        @include('partials.navbar')

        <div class="container-fluid px-4 py-3">
            @yield('content')
        </div>
    </main>

    @include('partials.scripts.main')
    
    @stack('scripts')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register("{{ asset('sw.js') }}")
                .then(function (registration) {
                    console.log('SW registered:', registration.scope);
                })
                .catch(function (error) {
                    console.log('SW failed:', error);
                });
        });
    }
    </script>

</body>
</html>