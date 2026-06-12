@extends('layouts.pegawai-app')

@section('title', 'Scan QR Parkir Pegawai | Smart Parking')

@push('styles')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .scanner-wrapper {
            position: relative;
            max-width: 450px;
            margin: 0 auto;
        }

        #reader {
            width: 100% !important;
            border: none !important;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
            background: #000;
        }

        #reader video {
            border-radius: 24px;
            object-fit: cover;
        }

        .scan-status-badge {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
        }

        .btn-switch-cam {
            background-color: var(--bs-success) !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 50px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: 0 4px 6px rgba(25, 135, 84, 0.2) !important;
        }

        /* Input Manual Styling */
        .manual-input-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 20px;
            padding: 20px;
            margin-top: 25px;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1 text-success">Masuk Parkir Pegawai</h5>
            <p class="text-muted small">Pindai QR pada layar gate masuk (Akses Gratis)</p>
        </div>

        <div class="scanner-wrapper">
            <div id="statusBadge" class="scan-status-badge bg-white text-warning">
                <span id="statusText"><i class="bi bi-camera me-1"></i> Menyiapkan Kamera...</span>
            </div>

            <div id="reader"></div>

            <div class="text-center mt-3">
                <button id="btn-switch" class="btn btn-switch-cam d-none">
                    <i class="bi bi-camera-rotate me-2"></i> Ganti Kamera
                </button>
            </div>
        </div>

        {{-- INPUT MANUAL SECTION --}}
        <div class="manual-input-box mx-auto" style="max-width: 450px;">
            <label class="small text-muted mb-2 fs-8 d-block text-center">MASALAH SCANNING? INPUT KODE DISINI:</label>
            <div class="input-group">
                <input type="text" id="manualCode"
                    class="form-control form-control-lg rounded-pill-start border-0 shadow-none"
                    placeholder="Contoh: PKR-XXXXX" style="font-size: 0.9rem;">
                <button onclick="handleManualInput()" class="btn btn-success rounded-pill-end px-4 fw-bold">KIRIM</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-3 rounded-4 mx-auto mt-4" style="max-width: 450px;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <small class="text-muted d-block lh-1 mb-1">Kendaraan Terpilih (Pegawai)</small>
                    <span class="fw-bold text-dark d-block">{{ strtoupper($kendaraan->plat_nomor) }}</span>
                    <span class="text-muted small">{{ $kendaraan->merk }} • {{ $kendaraan->warna }}</span>
                </div>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small">Free</span>
            </div>
        </div>

        <div id="debugError" class="alert alert-danger rounded-4 small mt-3 d-none mx-auto" style="max-width: 450px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span id="errorMessage"></span>
        </div>

        <div class="d-grid mt-3 mx-auto" style="max-width: 450px;">
            <button onclick="history.back()" class="btn btn-light py-3 rounded-pill fw-bold text-muted border-0">
                <i class="bi bi-x-lg me-2"></i> Batalkan
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const KENDARAAN_ID = {{ $kendaraan->id }};
        let html5QrCode;
        let cameras = [];
        let currentCameraIndex = 0;
        let isProcessing = false;

        // --- INITIALIZE ---
        async function initScanner() {
            try {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length) {
                    cameras = devices;
                    if (cameras.length > 1) document.getElementById('btn-switch').classList.remove('d-none');

                    // Cari kamera belakang
                    const backCamIndex = cameras.findIndex(cam =>
                        cam.label.toLowerCase().includes('back') || cam.label.toLowerCase().includes('rear')
                    );
                    currentCameraIndex = backCamIndex !== -1 ? backCamIndex : 0;
                    startScanning(cameras[currentCameraIndex].id);
                } else {
                    showError("Kamera tidak ditemukan. Gunakan input kode manual.");
                    updateStatus("Kamera Off", "bg-secondary text-white", "bi-camera-video-off");
                }
            } catch (err) {
                showError("Akses kamera ditolak. Gunakan input kode manual.");
            }
        }

        async function startScanning(cameraId) {
            if (html5QrCode && html5QrCode.isScanning) await html5QrCode.stop();
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 20, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
            try {
                await html5QrCode.start(cameraId, config, (decodedText) => onScanSuccess(decodedText));
                updateStatus("Siap Scan", "bg-white text-warning", "bi-camera");
            } catch (err) {
                console.error(err);
            }
        }

        // --- HANDLING LOGIC ---
        function handleManualInput() {
            const code = document.getElementById('manualCode').value.trim();
            if (!code) return Swal.fire('Oops', 'Masukkan kode parkir terlebih dahulu', 'warning');
            onScanSuccess(code);
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;

            updateStatus("Memproses...", "bg-success text-white", "bi-hourglass-split");

            // Mengarahkan fetch ke URL/Route Store khusus Pegawai
            fetch("{{ route('pegawai.parkir.scan.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    qr_kode: decodedText,
                    kendaraan_id: KENDARAAN_ID
                })
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw data;
                    return data;
                })
                .then(res => {
                    if (res.success) {
                        if (html5QrCode) html5QrCode.stop();
                        updateStatus("Berhasil!", "bg-success text-white", "bi-check-circle-fill");
                        Swal.fire({
                            icon: 'success',
                            title: 'Gate Terbuka!',
                            text: 'Akses Pegawai dikonfirmasi. Silahkan masuk.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = res.redirect;
                        });
                    }
                })
                .catch(err => {
                    isProcessing = false;
                    let message = err.message || "Kode tidak valid atau sistem sibuk";
                    showError(message);
                    updateStatus("Gagal", "bg-danger text-white", "bi-exclamation-circle");

                    setTimeout(() => {
                        document.getElementById('debugError').classList.add('d-none');
                        updateStatus("Siap Scan", "bg-white text-warning", "bi-camera");
                    }, 4000);
                });
        }

        // --- HELPERS ---
        document.getElementById('btn-switch').addEventListener('click', async () => {
            currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
            await startScanning(cameras[currentCameraIndex].id);
        });

        function updateStatus(text, className, icon) {
            const badge = document.getElementById('statusBadge');
            const statusText = document.getElementById('statusText');
            badge.className = `scan-status-badge shadow ${className}`;
            statusText.innerHTML = `<i class="bi ${icon} me-1"></i> ${text}`;
        }

        function showError(message) {
            const el = document.getElementById('debugError');
            const msg = document.getElementById('errorMessage');
            el.classList.remove('d-none');
            msg.innerText = message;
        }

        document.addEventListener('DOMContentLoaded', initScanner);
    </script>
@endpush