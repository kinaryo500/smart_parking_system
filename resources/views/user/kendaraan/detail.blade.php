@extends('layouts.user-app')

@section('title', 'Detail Kendaraan | Smart Parking')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
<style>
    .card-main {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        max-width: 600px;
        margin: 0 auto 20px auto;
    }

    .material-symbols-rounded {
        font-size: 56px;
        color: var(--primary);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #334155;
        max-width: 600px;
        margin: 24px auto 12px auto;
    }

    .riwayat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        max-width: 600px;
        margin: 0 auto 12px auto;
        transition: transform 0.2s;
    }

    .riwayat-card:active {
        transform: scale(0.98);
    }

    .btn-detail {
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    
    <div class="card card-main p-4 text-center">
        @php $jenis = strtolower($kendaraan->jenis); @endphp

        <div class="mb-3">
            @if($jenis == 'motor')
                <span class="material-symbols-rounded">two_wheeler</span>
            @elseif($jenis == 'mobil')
                <span class="material-symbols-rounded">directions_car</span>
            @else
                <span class="material-symbols-rounded">help_center</span>
            @endif
        </div>

        <h5 class="fw-bold mb-1">{{ strtoupper($kendaraan->plat_nomor) }}</h5>
        <p class="text-muted small mb-0">
            {{ ucfirst($kendaraan->jenis) }} • {{ $kendaraan->merk }} • {{ $kendaraan->warna }}
        </p>
    </div>

    <div class="section-title">Parkir Aktif</div>

    @forelse($parkirAktif as $i => $p)
        <div class="card riwayat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-start">
                    <div class="fw-bold text-dark mb-1">
                        {{ strtoupper($p->kendaraan->jenis) }} • {{ $p->kendaraan->plat_nomor }}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i> <span id="time-{{ $i }}">0 menit</span> 
                        <span class="mx-1">•</span>
                        <span class="text-primary fw-semibold">Rp <span id="cost-{{ $i }}">0</span></span>
                    </div>
                </div>
                
                <div>
                    <a href="{{ route('user.kendaraan.detail-history', $p->id) }}" class="btn btn-outline-primary btn-detail">
                        Detail
                    </a>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            (function() {
                const start{{ $i }} = new Date("{{ $p->waktu_masuk }}").getTime();
                const tarif{{ $i }} = {{ $p->tarif_per_jam }};

                function updateTime{{ $i }}() {
                    const now = Date.now();
                    const diff = Math.floor((now - start{{ $i }}) / 1000);
                    const totalMenit = Math.floor(diff / 60);

                    const jam = Math.floor(totalMenit / 60);
                    const menit = totalMenit % 60;

                    const jamTagih = Math.max(1, Math.ceil(totalMenit / 60)); 
                    const total = jamTagih * tarif{{ $i }};

                    let waktuText = jam > 0 ? `${jam} jam ${menit} menit` : `${menit} menit`;

                    document.getElementById('time-{{ $i }}').innerText = waktuText;
                    document.getElementById('cost-{{ $i }}').innerText = total.toLocaleString('id-ID');
                }
                updateTime{{ $i }}();
                setInterval(updateTime{{ $i }}, 10000); 
            })();
        </script>
        @endpush
    @empty
        <div class="card card-main p-4 text-center border-0 shadow-none bg-light">
            <small class="text-muted">Tidak ada parkir aktif saat ini.</small>
        </div>
    @endforelse

    <!-- RIWAYAT PARKIR -->
    <div class="section-title">Riwayat Parkir</div>

    @forelse($transaksiRiwayat as $t)
        @php
            $durasi = $t->waktu_keluar
                ? \Carbon\Carbon::parse($t->waktu_masuk)->diff(\Carbon\Carbon::parse($t->waktu_keluar))
                : null;
        @endphp

        <div class="card riwayat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-start">
                    <div class="fw-bold text-dark mb-1">
                        {{ \Carbon\Carbon::parse($t->waktu_masuk)->translatedFormat('d M Y, H:i') }}
                    </div>
                    <div class="text-muted small">
                        @if($durasi)
                            {{ $durasi->h > 0 ? $durasi->h . 'j ' : '' }}{{ $durasi->i }}m
                        @else
                            Sedang Parkir
                        @endif
                        <span class="mx-1">•</span>
                        <span class="fw-medium">Rp {{ number_format($t->total_bayar ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <a href="{{ route('user.kendaraan.detail-history', $t->id) }}" class="btn btn-outline-secondary btn-detail">
                        Detail
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-main p-4 text-center border-0 shadow-none bg-light">
            <small class="text-muted">Belum ada riwayat parkir.</small>
        </div>
    @endforelse

        <div class="card card-main p-3 mt-3 text-center">
            <div class="fw-bold text-dark mb-1">Total Parkir Terbayar</div>
            <div class="text-primary fw-semibold fs-5">
                Rp {{ number_format($totalSemua, 0, ',', '.') }}
            </div>
        </div>

    <!-- BACK BUTTON -->
    <div class="text-center mt-4 mb-5">
        <a href="javascript:history.back()" class="btn btn-primary px-5 w-100 py-2 rounded fw-bold shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>
@endsection