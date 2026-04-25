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

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --primary: #0061ff;
            --bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            padding-top: 70px;
            padding-bottom: 90px;
        }

        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 72px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1050;
        }

        .bottom-nav a {
            text-decoration: none;
            color: #94a3b8;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bottom-nav a.active {
            color: var(--primary)
        }

        .swal-icon-small {
            font-size: 10px !important;
            height: 40px !important;
            width: 40px !important;
        }

        .swal-title-small {
            font-size: 1rem !important;
        }

        .swal-text-small {
            font-size: 0.85rem !important;
        }
    </style>
    @stack('styles')
</head>

<body>

    <div class="app-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0">Smart Parking</h6>
            <small class="text-muted">Halo, {{ Auth::user()->name }}</small>
        </div>
        <form id="formLogout" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button" id="btnLogout" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

    <main>
        @yield('content')
    </main>

    <nav class="bottom-nav">
        <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard*') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i><span>Home</span>
        </a>
        <a href="{{ route('user.kendaraan.index') }}"
            class="{{ request()->routeIs('user.kendaraan.*') ? 'active' : '' }}">
            <i class="bi bi-car-front"></i><span>Kendaraan</span>
        </a>
        <a href="{{ route('user.profile') }}" class="{{ request()->routeIs('user.profile') ? 'active' : '' }}">
            <i class="bi bi-person"></i><span>Profil</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @vite(['resources/js/app.js'])

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            console.log("App initialized.");
        });

        // Logout logic
        const btnLogout = document.getElementById('btnLogout');
        if (btnLogout) {
            btnLogout.onclick = function () {
                Swal.fire({
                    title: 'Keluar',
                    text: "Apakah Anda Ingin Keluar ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya',
                    customClass: {
                        title: 'swal-title-small',
                        htmlContainer: 'swal-text-small',
                        icon: 'swal-icon-small'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formLogout').submit();
                    }
                });
            };
        }
    </script>
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
    @stack('scripts')
</body>

</html>