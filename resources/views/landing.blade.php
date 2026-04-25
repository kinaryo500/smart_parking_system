<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Smart Parking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0061ff">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0061ff;
            --accent-color: #60efff;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            overflow: hidden;
            background: radial-gradient(circle at top right, #eaf1ff, #ffffff);
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            width: 500px;
            height: 500px;
            background: var(--accent-color);
            filter: blur(140px);
            opacity: .25;
            top: -100px;
            right: -120px;
            z-index: -1;
        }

        body::after {
            content: "";
            position: fixed;
            width: 400px;
            height: 400px;
            background: var(--primary-color);
            filter: blur(160px);
            opacity: .15;
            bottom: -120px;
            left: -120px;
            z-index: -1;
        }

        #preloader {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100dvh;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #e5e7eb;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .navbar {
            backdrop-filter: blur(18px);
            background: rgba(255,255,255,.7) !important;
            border-bottom: 1px solid rgba(0,0,0,.05);
            padding: 18px 0;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 90px 15px;
        }

        .hero-card {
            background: rgba(255,255,255,.75);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.5);
            border-radius: 22px;
            padding: 50px 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,.08);
            max-width: 720px;
            margin: auto;
        }

        .hero h1 {
            font-size: 2.4rem;
            font-weight: 800;
        }

        .hero p {
            color: #64748b;
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 14px 26px;
            font-weight: 700;
            transition: .3s;
            text-decoration: none;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            background: #004ed8;
            color: #fff;
        }

        .btn-outline-custom {
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 26px;
            font-weight: 700;
            background: rgba(255,255,255,.6);
            transition: .3s;
        }

        .btn-outline-custom:hover {
            background: white;
            transform: translateY(-2px);
        }

        .btn-uniform {
            min-width: 220px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 576px) {
            .hero h1 { font-size: 1.7rem; }
            .hero-card { padding: 35px 20px; }
            .btn-uniform { width: 100%; }
        }
    </style>
</head>

<body>

<div id="preloader">
    <div class="spinner"></div>
    <small class="text-muted fw-bold" style="letter-spacing: 1px;">
        MEMUAT SISTEM...
    </small>
</div>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <span class="navbar-brand fw-bold fs-4">
            <span class="text-primary">Smart</span> Parking
        </span>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="hero-card" data-aos="fade-up">
            <h1 class="mb-3">Sistem Parkir Modern</h1>
            <p class="mb-4">
                Kelola akses & slot parkir secara otomatis.
            </p>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('login') }}" class="btn btn-primary-custom btn-uniform">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
                <button id="installPwaBtn" class="btn btn-outline-custom btn-uniform d-none">
                    <i class="bi bi-download"></i> Install App
                </button>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
AOS.init({ once: true });

const hidePreloader = () => {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        preloader.style.opacity = '0';
        preloader.style.visibility = 'hidden';
        document.body.style.overflow = 'auto';
    }
};

window.addEventListener('load', hidePreloader);
setTimeout(hidePreloader, 3000);

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(console.log);
}

let deferredPrompt;
const installBtn = document.getElementById('installPwaBtn');

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

window.addEventListener('beforeinstallprompt', (e) => {
    if (isStandalone()) return;
    e.preventDefault();
    deferredPrompt = e;
    installBtn.classList.remove('d-none');
});

installBtn.addEventListener('click', async () => {
    if (!deferredPrompt) return;

    Swal.fire({
        title: 'Pasang Aplikasi?',
        text: 'Instal Smart Parking untuk akses lebih cepat.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0061ff',
        confirmButtonText: 'Ya, Instal',
        cancelButtonText: 'Nanti'
    }).then(async (result) => {
        if (result.isConfirmed) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                installBtn.classList.add('d-none');
            }
            deferredPrompt = null;
        }
    });
});
</script>

</body>
</html>