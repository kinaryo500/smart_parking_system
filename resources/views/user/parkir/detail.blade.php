@extends('layouts.user-app')

@section('title', 'Detail Parkir | Smart Parking')

@push('styles')
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />

    <style>
        .card-detail {
            max-width: 500px;
            margin: auto;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 20px;
        }

        .material-symbols-rounded {
            font-size: 56px;
            color: var(--primary);
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 8px;
            height: 100%;
        }

        .info-box small {
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
        }

        .info-box div {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
        }

        .bg-light-primary {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .bg-light-success {
            background-color: #dcfce7;
            color: #15803d;
        }
    </style>
@endpush

@section('content')

    <div class="container mt-4 mb-5">
        <div class="card card-detail p-4 text-center" id="main-card">

            <div class="mb-3">
                @php $jenis = strtolower($transaksi->jenis_kendaraan); @endphp

                @if($jenis == 'motor')
                    <span class="material-symbols-rounded">two_wheeler</span>
                @elseif($jenis == 'mobil')
                    <span class="material-symbols-rounded">directions_car</span>
                @else
                    <span class="material-symbols-rounded">help_center</span>
                @endif

                <h5 class="fw-bold mt-2 mb-0">Detail Transaksi</h5>

                <div class="mt-1">
                    <span id="statusBadge"
                        class="status-badge {{ $transaksi->status == 'aktif' ? 'bg-light-primary text-primary' : 'bg-light-success text-success' }}">
                        {{ strtoupper($transaksi->status) }}
                    </span>
                </div>
            </div>

            <div class="bg-light rounded-3 p-2 mb-3">
                <h4 class="fw-bold mb-0" style="letter-spacing:2px">
                    {{ strtoupper($transaksi->kendaraan->plat_nomor ?? '-') }}
                </h4>
                <small class="text-muted">
                    {{ $transaksi->kendaraan->merk ?? '-' }} • {{ ucfirst($transaksi->jenis_kendaraan) }}
                </small>
            </div>
       
            <div class="row g-2">
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
                            @if($transaksi->status == 'aktif') --:-- @else
                            {{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('H:i') }} @endif
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 mx-auto">
                    <div class="info-box">
                        <small>Tarif/Jam</small>
                        <div>Rp {{ number_format($transaksi->tarif_per_jam, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-25">

            <div class="py-2">
                <small class="text-muted d-block mb-1 text-uppercase" style="font-size:0.7rem;">
                    Durasi Parkir
                </small>

                <div class="fw-bold fs-5" id="durasiDisplay">
                    @if($transaksi->status != 'aktif')
                        {{ floor(($transaksi->total_waktu ?? 0) / 60) }} jam {{ ($transaksi->total_waktu ?? 0) % 60 }} menit
                    @else
                        Menghitung...
                    @endif
                </div>

                <div id="biayaBox"
                    class="mt-4 p-4 rounded-4 {{ $transaksi->status == 'aktif' ? 'bg-primary text-white' : 'bg-success text-white' }}">
                    <small id="biayaLabel" class="d-block opacity-75 text-uppercase fw-bold" style="font-size:0.7rem;">
                        {{ $transaksi->status == 'aktif' ? 'Estimasi Biaya' : 'Total Pembayaran' }}
                    </small>

                    <div class="fw-bold fs-3">
                        Rp <span id="biayaDisplay">
                            {{ number_format($transaksi->status == 'aktif' ? 0 : ($transaksi->total_bayar ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-grid mt-4">
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary fw-bold py-2">
                    Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const transaksiId = "{{ $transaksi->id }}";
        let statusSekarang = "{{ $transaksi->status }}";
        let liveInterval;

        function startLiveCounter() {
            const start = new Date("{{ $transaksi->waktu_masuk }}").getTime();
            const tarif = {{ $transaksi->tarif_per_jam }};

            liveInterval = setInterval(() => {
                const now = new Date();
                const diff = Math.max(0, now - start);
                const menit = Math.floor(diff / 60000);
                const jam = Math.floor(menit / 60);
                const sisa = menit % 60;

                document.getElementById('durasiDisplay').innerText =
                    jam > 0 ? `${jam} jam ${sisa} menit` : `${menit} menit`;

                const tagihanJam = Math.max(1, Math.ceil(menit / 60));
                const total = tagihanJam * tarif;
                document.getElementById('biayaDisplay').innerText = total.toLocaleString('id-ID');
            }, 1000);
        }

        if (statusSekarang === 'aktif') {
            startLiveCounter();
        }

        function initEcho() {
            if (typeof window.Echo !== 'undefined') {
                // Dengarkan channel khusus ID transaksi ini
                window.Echo.channel(`transaksi.${transaksiId}`)
                    .listen('.selesai', (e) => {
                        console.log("Sinyal selesai diterima!", e);
                        syncStatus();
                    });
            } else {
                setTimeout(initEcho, 1000);
            }
        }
        initEcho();

        async function syncStatus() {
            if (statusSekarang === 'selesai') return;

            try {
                const response = await fetch(`/transaksi/status/${transaksiId}`);
                const data = await response.json();

                if (data.status === 'selesai') {
                    statusSekarang = 'selesai';
                    if (liveInterval) clearInterval(liveInterval);

                    const badge = document.getElementById('statusBadge');
                    badge.className = "status-badge bg-light-success text-success";
                    badge.innerText = "SELESAI";

                    const biayaBox = document.getElementById('biayaBox');
                    biayaBox.className = "mt-4 p-4 rounded-4 bg-success text-white";
                    document.getElementById('biayaLabel').innerText = "TOTAL PEMBAYARAN";
                    document.getElementById('jamKeluar').innerText = data.waktu_keluar;
                    document.getElementById('durasiDisplay').innerText = data.durasi_teks;
                    document.getElementById('biayaDisplay').innerText = data.total_bayar_formatted;

                    Swal.fire({
                        icon: 'success',
                        title: 'Parkir Selesai!',
                        text: 'Kendaraan telah keluar. Terima kasih!',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                console.error("Sync error:", err);
            }
        }
    </script>
@endpush