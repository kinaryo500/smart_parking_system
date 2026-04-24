<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Auth') | Smart Parking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0061ff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #0061ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #f0f4ff, #ffffff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-auth {
            width: 100%;
            max-width: 420px;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .08);
            border: none;
        }

        .btn-primary-custom {
            background: var(--primary-color);
            border: none;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px;
        }

        .btn-primary-custom:hover {
            background: #004ed8;
        }

        .link-primary {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .link-primary:hover {
            text-decoration: underline;
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="card card-auth p-4">


        <div class="text-center mb-4">
            <h3 class="fw-bold">
                <span class="text-primary">Smart</span> Parking
            </h3>
            <p class="text-muted mb-0">@yield('subtitle', 'Masuk ke sistem')</p>
        </div>


        @yield('content')

    </div>

</body>

</html>