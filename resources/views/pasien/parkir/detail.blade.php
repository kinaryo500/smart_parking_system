@extends('layouts.pasien-app')

@section('title', 'Detail Parkir Pasien | Smart Parking')

@push('styles')
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />

<style>
    .card-detail{
        max-width:500px;
        margin:auto;
        border:none;
        box-shadow:0 10px 25px rgba(0,0,0,.05);
        border-radius:20px;
    }

    .material-symbols-rounded{
        font-size:56px;
    }

    .info-box{
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:12px 8px;
        height:100%;
    }

    .info-box small{
        font-size:.65rem;
        color:#64748b;
        text-transform:uppercase;
    }

    .info-box div{
        font-size:.9rem;
        font-weight:700;
        color:#1e293b;
    }

    .status-badge{
        font-size:.75rem;
        padding:4px 12px;
        border-radius:50px;
        font-weight:700;
    }

    .bg-light-primary{
        background-color:#e0e7ff;
        color:#4338ca;
    }

    .bg-light-success{
        background-color:#dcfce7;
        color:#15803d;
    }

    /* =========================
       NOTIFIKASI MODERN (Green Theme for Pasien)
    ========================== */

    #notifSelesai{
        display:none;
        position:fixed;
        top:20px;
        right:20px;
        width:340px;
        max-width:calc(100% - 30px);
        background:rgba(20,83,45,.95);
        backdrop-filter:blur(14px);
        border:1px solid rgba(255,255,255,.08);
        border-radius:22px;
        overflow:hidden;
        z-index:9999;
        box-shadow: 0 15px 40px rgba(0,0,0,.25);
        animation:slideIn .45s ease;
    }

    .notif-top{
        height:5px;
        width:100%;
        background:linear-gradient(90deg, #15803d, #4ade80);
    }

    .notif-content{
        display:flex;
        align-items:flex-start;
        gap:14px;
        padding:18px;
    }

    .notif-icon{
        width:52px;
        height:52px;
        min-width:52px;
        border-radius:16px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:rgba(74, 222, 128, .15);
        color:#4ade80;
        font-size:28px;
    }

    .notif-text h6{
        color:#fff;
        font-weight:700;
        margin-bottom:4px;
        font-size:.95rem;
    }

    .notif-text p{
        color:rgba(255,255,255,.72);
        margin:0;
        font-size:.82rem;
        line-height:1.45;
    }

    .notif-close{
        margin-left:auto;
        background:none;
        border:none;
        color:rgba(255,255,255,.5);
        font-size:18px;
        cursor:pointer;
        transition:.2s;
    }

    .notif-close:hover{
        color:#fff;
        transform:scale(1.1);
    }

    .notif-progress{
        height:4px;
        width:100%;
        background:rgba(255,255,255,.08);
        overflow:hidden;
    }

    .notif-progress::before{
        content:'';
        display:block;
        height:100%;
        width:100%;
        background:linear-gradient(90deg, #15803d, #86efac);
        animation:progressNotif 5s linear forwards;
    }

    @keyframes progressNotif{
        from{ width:100%; }
        to{ width:0%; }
    }

    @keyframes slideIn{
        from{
            opacity:0;
            transform: translateX(120px) scale(.92);
        }
        to{
            opacity:1;
            transform: translateX(0) scale(1);
        }
    }

    @keyframes slideOut{
        from{
            opacity:1;
            transform: translateX(0) scale(1);
        }
        to{
            opacity:0;
            transform: translateX(120px) scale(.92);
        }
    }

    .notif-hide{
        animation:slideOut .4s ease forwards;
    }

    /* =========================
       BLINK STATUS
    ========================== */

    .blink{
        animation:blinkAnim 1s infinite;
    }

    @keyframes blinkAnim{
        0%{ opacity:1; }
        50%{ opacity:.4; }
        100%{ opacity:1; }
    }

    @media(max-width:576px){
        #notifSelesai{
            top:14px;
            right:14px;
            left:14px;
            width:auto;
        }

        .notif-content{
            padding:16px;
        }

        .notif-icon{
            width:46px;
            height:46px;
            min-width:46px;
            font-size:24px;
        }
    }
</style>
@endpush

@section('content')

<audio id="notifSound">
    <source src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" type="audio/ogg">
</audio>

<div id="notifSelesai">
    <div class="notif-top"></div>
    <div class="notif-content">
        <div class="notif-icon">✓</div>
        <div class="notif-text">
            <h6>Parkir Selesai</h6>
            <p>Kendaraan telah keluar dari area parkir. Transaksi dibebaskan dari biaya.</p>
        </div>
        <button class="notif-close" onclick="closeNotif()">✕</button>
    </div>
    <div class="notif-progress"></div>
</div>

<div class="container mt-4 mb-5">
    <div class="card card-detail p-4 text-center" id="main-card">
        <div class="mb-3">
            @php
                $jenis = strtolower($transaksi->jenis_kendaraan);
            @endphp

            @if($jenis == 'motor')
                <span class="material-symbols-rounded {{ $transaksi->status == 'aktif' ? 'text-primary' : 'text-success' }}" id="iconStatus">two_wheeler</span>
            @elseif($jenis == 'mobil')
                <span class="material-symbols-rounded {{ $transaksi->status == 'aktif' ? 'text-primary' : 'text-success' }}" id="iconStatus">directions_car</span>
            @else
                <span class="material-symbols-rounded {{ $transaksi->status == 'aktif' ? 'text-primary' : 'text-success' }}" id="iconStatus">help_center</span>
            @endif

            <h5 class="fw-bold mt-2 mb-0 {{ $transaksi->status == 'aktif' ? 'text-primary' : 'text-success' }}" id="titleText">Detail Parkir Pasien</h5>

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

        <div class="row g-2 justify-content-center">
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
                            --:--
                        @else
                            {{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('H:i') }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-6 col-sm-4">
                <div class="info-box">
                    <small>Tarif/Jam</small>
                    <div>Rp 0 (Free)</div>
                </div>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        <div class="py-2">
            <small class="text-muted d-block mb-1 text-uppercase" style="font-size:.7rem;">Durasi Parkir</small>
            <div class="fw-bold fs-5" id="durasiDisplay">
                @if($transaksi->status != 'aktif')
                    {{ floor(($transaksi->total_waktu ?? 0) / 60) }} jam {{ ($transaksi->total_waktu ?? 0) % 60 }} menit
                @else
                    Menghitung...
                @endif
            </div>

            {{-- Box Biaya Dinamis (Biru saat aktif, Hijau saat selesai) --}}
            <div id="biayaBox" class="mt-4 p-4 rounded-4 {{ $transaksi->status == 'aktif' ? 'bg-primary' : 'bg-success' }} text-white">
                <small id="biayaLabel" class="d-block opacity-75 text-uppercase fw-bold" style="font-size:.7rem;">
                    {{ $transaksi->status == 'aktif' ? 'Estimasi Biaya' : 'Total Pembayaran' }}
                </small>
                <div class="fw-bold fs-3">
                    Rp <span id="biayaDisplay">0</span>
                </div>
            </div>
        </div>

        <div class="d-grid mt-4">
            <a href="{{ route('pasien.dashboard') }}" id="btnKembali" class="btn btn-outline-secondary px-5 w-100 py-2 rounded fw-bold shadow-sm">
               Kembali
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const transaksiId = "{{ $transaksi->id }}";
    const waktuMasuk = "{{ $transaksi->waktu_masuk }}";
    let statusSekarang = "{{ $transaksi->status }}";

    let liveInterval = null;
    let pollInterval = null;

    function formatDurasi(menit){
        const jam = Math.floor(menit / 60);
        const sisa = menit % 60;
        return jam > 0 ? `${jam} jam ${sisa} menit` : `${menit} menit`;
    }

    function updateLiveCounter(){
        if(statusSekarang !== 'aktif') return;

        const start = new Date(waktuMasuk).getTime();
        const now = new Date().getTime();
        const diff = Math.max(0, now - start);
        const menit = Math.floor(diff / 60000);

        document.getElementById('durasiDisplay').innerText = formatDurasi(menit);
        
        // Pasien free, biaya selalu 0
        document.getElementById('biayaDisplay').innerText = "0";
    }

    function closeNotif(){
        const notif = document.getElementById('notifSelesai');
        notif.classList.add('notif-hide');
        setTimeout(() => {
            notif.style.display = 'none';
            notif.classList.remove('notif-hide');
        }, 350);
    }

    async function syncStatus(){
        try{
            const res = await fetch(`/pasien/transaksi/status/${transaksiId}?t=${Date.now()}`);
            const data = await res.json();

            if(data.status === 'selesai' && statusSekarang !== 'selesai'){
                statusSekarang = 'selesai';

                if(liveInterval) clearInterval(liveInterval);
                if(pollInterval) clearInterval(pollInterval);

                const badge = document.getElementById('statusBadge');
                badge.className = "status-badge bg-light-success text-success blink";
                badge.innerText = "SELESAI";

                // Ubah warna ikon dan teks header menjadi hijau saat selesai
                document.getElementById('iconStatus').className = "material-symbols-rounded text-success";
                document.getElementById('titleText').className = "fw-bold mt-2 mb-0 text-success";

                // Ubah box estimasi menjadi hijau dan ganti label
                const biayaBox = document.getElementById('biayaBox');
                biayaBox.className = "mt-4 p-4 rounded-4 bg-success text-white";
                document.getElementById('biayaLabel').innerText = "TOTAL PEMBAYARAN";
                
                // Ubah tombol kembali menjadi outline hijau
                document.getElementById('btnKembali').className = "btn btn-outline-success fw-bold py-2 rounded-pill";

                document.getElementById('jamKeluar').innerText = data.waktu_keluar;
                document.getElementById('durasiDisplay').innerText = data.durasi_teks;
                document.getElementById('biayaDisplay').innerText = "0";

                document.getElementById('notifSound').play();

                if(navigator.vibrate){
                    navigator.vibrate([200,100,200]);
                }

                const notifBox = document.getElementById('notifSelesai');
                notifBox.style.display = 'block';

                setTimeout(() => { closeNotif(); }, 5000);

                Swal.fire({
                    icon: 'success',
                    title: 'Parkir Selesai!',
                    text: 'Kendaraan telah keluar dari area parkir (Bebas Biaya).',
                    timer: 5000,
                    showConfirmButton: false
                });
            }
        } catch(err){
            console.error("Polling error:", err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if(statusSekarang === 'aktif'){
            updateLiveCounter();
            liveInterval = setInterval(updateLiveCounter, 1000);
            pollInterval = setInterval(syncStatus, 4000);
        }
    });
</script>
@endpush