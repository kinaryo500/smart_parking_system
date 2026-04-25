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
                                {{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('H:i') }}
                            @endif
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            const transaksiId = "{{ $transaksi->id }}";
            let statusSekarang = "{{ $transaksi->status }}";
            let liveInterval = null;
            let pollInterval = null;

            function formatDurasi(menit) {
                const jam = Math.floor(menit / 60);
                const sisa = menit % 60;
                return jam > 0 ? `${jam} jam ${sisa} menit` : `${menit} menit`;
            }

            function startLiveCounter() {
                const start = new Date("{{ $transaksi->waktu_masuk }}").getTime();
                const tarif = {{ $transaksi->tarif_per_jam }};

                if (liveInterval) clearInterval(liveInterval);

                console.log("[LIVE] Counter started", { start, tarif });

                liveInterval = setInterval(() => {
                    const now = new Date().getTime();
                    const diff = Math.max(0, now - start);
                    const menit = Math.floor(diff / 60000);

                    console.log("[LIVE] Tick", { menit });

                    document.getElementById('durasiDisplay').innerText = formatDurasi(menit);

                    const tagihanJam = Math.max(1, Math.ceil(menit / 60));
                    const total = tagihanJam * tarif;

                    document.getElementById('biayaDisplay').innerText =
                        total.toLocaleString('id-ID');

                    console.log("[LIVE] Update biaya", { tagihanJam, total });
                }, 1000);
            }

            function syncStatus() {
                console.log("[SYNC] Request status transaksi:", transaksiId);

                fetch(`/transaksi/status/${transaksiId}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log("[SYNC] Response:", data);

                        if (data.status === 'selesai' && statusSekarang !== 'selesai') {
                            console.log("[SYNC] Status berubah menjadi SELESAI");

                            statusSekarang = 'selesai';

                            if (liveInterval) clearInterval(liveInterval);
                            if (pollInterval) clearInterval(pollInterval);

                            document.getElementById('statusBadge').className =
                                "status-badge bg-light-success text-success";
                            document.getElementById('statusBadge').innerText = "SELESAI";

                            document.getElementById('biayaBox').className =
                                "mt-4 p-4 rounded-4 bg-success text-white";

                            document.getElementById('biayaLabel').innerText =
                                "TOTAL PEMBAYARAN";

                            document.getElementById('jamKeluar').innerText =
                                data.waktu_keluar;

                            document.getElementById('durasiDisplay').innerText =
                                data.durasi_teks;

                            document.getElementById('biayaDisplay').innerText =
                                data.total_bayar_formatted;

                            console.log("[SYNC] UI updated to selesai");

                            Swal.fire({
                                icon: 'success',
                                title: 'Parkir Selesai!',
                                text: 'Kendaraan telah keluar.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            console.log("[SYNC] Belum selesai / tidak berubah status");
                        }
                    })
                    .catch(err => {
                        console.error("[SYNC] Error:", err);
                    });
            }

            function initRealtime() {
                console.log("[ECHO] Init realtime...");

                if (typeof window.Echo !== 'undefined') {
                    console.log("[ECHO] Connected, listening channel");

                    window.Echo.channel(`transaksi.${transaksiId}`)
                        .listen('.selesai', (e) => {
                            console.log("[ECHO] Event SELSAI diterima:", e);
                            syncStatus();
                        });

                } else {
                    console.warn("[ECHO] Not ready, retry...");
                    setTimeout(initRealtime, 1000);
                }
            }

            if (statusSekarang === 'aktif') {
                startLiveCounter();
            }

            initRealtime();

            console.log("[POLL] Start interval 5s");
            pollInterval = setInterval(() => {
                console.log("[POLL] Checking status...");
                syncStatus();
            }, 5000);
        </script>
    @endpush
@endpush