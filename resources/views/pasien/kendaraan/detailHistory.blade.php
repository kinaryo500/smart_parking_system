@extends('layouts.pasien-app')

@section('title', 'Detail Parkir Pasien | Smart Parking')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />

<style>
    .card-detail {
        max-width: 500px;
        margin: auto;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border-radius: 20px;
        overflow: hidden;
    }

    .material-symbols-rounded {
        font-size: 56px;
        color: var(--bs-primary);
    }

    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 8px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .info-box small {
        font-size: 0.65rem;
        color: #64748b;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }

    .info-box div {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 4px 14px;
        border-radius: 50px;
        font-weight: 800;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    /* Custom Color Classes */
    .bg-light-primary { background-color: #e0e7ff; color: #4338ca; }
    .bg-light-success { background-color: #dcfce7; color: #15803d; }
    
    .transition-all {
        transition: all 0.5s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-5">
    <div class="card card-detail p-4 text-center shadow-sm">

        <div class="mb-3">
            @php $jenis = strtolower($transaksi->jenis_kendaraan); @endphp

            <div class="mb-2">
                @if($jenis == 'motor')
                    <span class="material-symbols-rounded">two_wheeler</span>
                @elseif($jenis == 'mobil')
                    <span class="material-symbols-rounded">directions_car</span>
                @else
                    <span class="material-symbols-rounded">help_center</span>
                @endif
            </div>

            <h5 class="fw-bold mb-0 text-primary">Detail Transaksi Pasien</h5>

            <div class="mt-2">
                <span id="statusBadge"
                    class="status-badge transition-all {{ $transaksi->status == 'aktif' ? 'bg-light-primary text-primary' : 'bg-light-success text-success' }}">
                    {{ strtoupper($transaksi->status) }}
                </span>
            </div>
        </div>

        <div class="bg-light rounded-4 p-3 mb-4">
            <h4 class="fw-bold mb-0 text-dark" style="letter-spacing:3px">
                {{ strtoupper($transaksi->kendaraan->plat_nomor ?? '-') }}
            </h4>
            <small class="text-muted fw-medium">
                {{ $transaksi->kendaraan->merk ?? '-' }} • {{ ucfirst($transaksi->jenis_kendaraan) }}
            </small>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-6 col-sm-4">
                <div class="info-box">
                    <small>Masuk</small>
                    <div>{{ \Carbon\Carbon::parse($transaksi->waktu_masuk)->format('H:i') }}</div>
                </div>
            </div>

            <div class="col-6 col-sm-4">
                <div class="info-box">
                    <small>Tanggal</small>
                    <div>{{ \Carbon\Carbon::parse($transaksi->waktu_masuk)->format('d M Y') }}</div>
                </div>
            </div>

            <div class="col-6 col-sm-4">
                <div class="info-box">
                    <small>Keluar</small>
                    <div id="jamKeluar">
                        @if($transaksi->status == 'aktif') 
                            <span class="text-muted">--:--</span> 
                        @else 
                            {{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('H:i') }} 
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-6 col-sm-4 mx-auto mt-sm-0 mt-2">
                <div class="info-box">
                    <small>Tarif / Ketentuan</small>
                    <div class="">Rp 0 (Free)</div>
                </div>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        <div class="py-2">
            <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing: 1px;">
                Durasi Parkir
            </small>

            <div class="fw-bold fs-4 text-dark" id="durasiDisplay">
                @if($transaksi->status == 'aktif')
                    <span class="spinner-border spinner-border-sm text-secondary"></span> Menghitung...
                @else
                    {{ floor(($transaksi->total_waktu ?? 0)/60) }} jam {{ ($transaksi->total_waktu ?? 0)%60 }} menit
                @endif
            </div>

            <div id="biayaBox" 
                 class="mt-4 p-4 rounded-4 transition-all {{ $transaksi->status == 'aktif' ? 'bg-primary text-white' : 'bg-success text-white' }}">
                
                <small id="biayaLabel" class="d-block opacity-75 text-uppercase fw-bold" style="font-size:0.75rem;">
                    {{ $transaksi->status == 'aktif' ? 'Estimasi Biaya Parkir' : 'Total Pembayaran' }}
                </small>

                <div class="fw-bold fs-2 mt-1">
                    Rp <span id="biayaDisplay">0</span>
                </div>
            </div>
        </div>

        <div class="d-grid mt-4">
            <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary px-5 w-100 py-2 rounded fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const transaksiId = "{{ $transaksi->id }}";
    let currentStatus = "{{ $transaksi->status }}";
    let liveInterval;

    function formatDurasi(menit){
        const jam = Math.floor(menit / 60);
        const sisa = menit % 60;
        return jam > 0 ? `${jam} jam ${sisa} menit` : `${menit} menit`;
    }

    function startLiveCounter() {
        const start = new Date("{{ $transaksi->waktu_masuk }}").getTime();

        function update() {
            const now = new Date().getTime();
            const diff = Math.max(0, now - start);

            const menitTotal = Math.floor(diff / 60000);

            document.getElementById('durasiDisplay').innerHTML = formatDurasi(menitTotal);
            document.getElementById('biayaDisplay').innerText = "0"; 
        }

        liveInterval = setInterval(update, 1000);
        update();
    }

    if (currentStatus === 'aktif') {
        startLiveCounter();
    }

    function initEcho() {
        if (typeof window.Echo === 'undefined') {
            console.warn("Retrying Echo Connection...");
            setTimeout(initEcho, 1000);
            return;
        }

        console.log("Realtime Channel Connected");

        window.Echo.channel('slot-tracker')
            .listen('.EspHardwareCommand', (e) => {
                console.log("📡 Signal Received:", e.command);

                if (e.command === 'OPEN_GATE_EXIT' || e.command === 'UPDATE_DISPLAY_QR' || e.command.includes('GATE')) {
                    syncStatus();
                }
            });
    }

    initEcho();

    async function syncStatus() {
        if (currentStatus === 'selesai') return;

        try {
            const res = await fetch(`/pasien/transaksi/status/${transaksiId}`);
            if (!res.ok) return;

            const data = await res.json();

            if (data.status === 'selesai') {
                currentStatus = 'selesai';
                
                if (liveInterval) clearInterval(liveInterval);

                const badge = document.getElementById('statusBadge');
                badge.className = "status-badge bg-light-success text-success transition-all";
                badge.innerText = "SELESAI";

                // Ubah warna ikon header menjadi hijau
                document.querySelector('.material-symbols-rounded').style.color = "#15803d";

                const biayaBox = document.getElementById('biayaBox');
                biayaBox.className = "mt-4 p-4 rounded-4 bg-success text-white transition-all";

                document.getElementById('biayaLabel').innerText = "TOTAL PEMBAYARAN";
                document.getElementById('jamKeluar').innerText = data.waktu_keluar;
                document.getElementById('durasiDisplay').innerText = data.durasi_teks;
                
                document.getElementById('biayaDisplay').innerText = "0";

                Swal.fire({
                    icon: 'success',
                    title: 'Parkir Selesai!',
                    text: 'Sesi parkir kendaraan telah berakhir (Bebas Biaya).',
                    confirmButtonColor: '#15803d'
                });
            }
        } catch (e) {
            console.error("Sync Error:", e);
        }
    }
</script>
@endpush