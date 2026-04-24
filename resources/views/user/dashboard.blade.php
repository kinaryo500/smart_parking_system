@extends('layouts.user-app')
@section('title', 'Dashboard | Smart Parking')

@push('styles')
<style>
    .fab {
        position: fixed;
        bottom: 90px; 
        right: 20px;
        width: 56px;
        height: 56px;
        background: #0061ff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 15px rgba(0, 97, 255, 0.4);
        border: none;
        z-index: 1000;
        transition: transform 0.2s;
    }
    .fab:active { transform: scale(0.9); }
    
    .parkir-card {
        border-left: 4px solid #0061ff !important;
        transition: transform 0.2s;
    }
    .parkir-card:active { transform: scale(0.98); }

    .bg-light-custom {
        background-color: #f8f9fa;
        border-radius: 15px;
    }

    .loading-overlay {
        pointer-events: none;
        opacity: 0.6;
    }
</style>
@endpush

@section('content')

<div class="container mt-3">
    <div class="row g-2 text-center">
        @php
            $stats = [
                ['label' => 'Parkir Aktif', 'val' => $parkirAktif->count()],
                ['label' => 'Kendaraan', 'val' => $kendaraan->count()],
                ['label' => 'Total Pengeluaran', 'val' => 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'), 'class' => 'text-danger', 'full' => true]
            ];
        @endphp

        @foreach($stats as $s)
            <div class="{{ isset($s['full']) ? 'col-12' : 'col-6' }}">
                <div class="card p-2 shadow-sm border-0">
                    <small class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">{{ $s['label'] }}</small>
                    <div class="fw-bold {{ $s['class'] ?? '' }}">{{ $s['val'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Kendaraan Sedang Parkir</h6>
        <span class="badge bg-primary rounded-pill">{{ $parkirAktif->count() }}</span>
    </div>

    @forelse($parkirAktif as $p)
        <div class="card p-3 mb-3 shadow-sm border-0 parkir-card" 
             data-start="{{ $p->waktu_masuk }}" 
             data-tarif="{{ $p->tarif_per_jam }}">

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">
                        {{ strtoupper($p->kendaraan->jenis ?? 'Kendaraan') }} • {{ $p->kendaraan->plat_nomor }}
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-stopwatch me-1"></i>
                        Durasi: <span class="run-time fw-semibold text-dark">0m</span>
                    </small>
                </div>

                <div class="text-end">
                    <div class="fw-bold text-primary fs-5">
                        Rp <span class="run-cost">0</span>
                    </div>

                    <a href="{{ route('user.dashboard.parkir.detail', $p->id) }}"
                       class="btn btn-sm btn-primary mt-2 rounded-pill px-3">
                        Detail
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="bi bi-p-circle text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="text-muted mt-2">Tidak ada kendaraan yang sedang parkir.</p>
        </div>
    @endforelse
</div>

<button class="fab" data-bs-toggle="modal" data-bs-target="#modalParkir">
    <i class="bi bi-plus-lg"></i>
</button>

<div class="modal fade" id="modalParkir" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <form id="formParkir">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Mulai Parkir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="btn-close-modal"></button>
                </div>

                <div id="modalBodyContainer" class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Kendaraan Anda</label>
                        <select name="kendaraan_id" id="selectKendaraan" class="form-select border-0 bg-light">
                            <option value="">-- Gunakan Kendaraan Terdaftar --</option>
                            @foreach($kendaraanTersedia as $k)
                                <option value="{{ $k->id }}" data-jenis="{{ $k->jenis }}">
                                    {{ strtoupper($k->plat_nomor) }} • {{ $k->merk }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center my-3">
                        <hr class="opacity-25">
                        <span class="badge bg-light text-muted fw-normal">ATAU INPUT MANUAL</span>
                    </div>

                    <div class="card card-body border-0 bg-light-custom shadow-none p-3">
                        <div class="mb-2">
                            <label class="small fw-medium">Jenis Kendaraan</label>
                            <select name="jenis" id="jenisBaru" class="form-select border-0">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="motor">Motor</option>
                                <option value="mobil">Mobil</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="small fw-medium">Plat Nomor</label>
                            <input type="text" name="plat_nomor" id="platBaru" class="form-control border-0" placeholder="Contoh: WA 1234 VF">
                        </div>

                        <div class="row g-2">
                            <div class="col-7">
                                <label class="small fw-medium">Merk</label>
                                <input type="text" name="merk" id="merkBaru" class="form-control border-0" placeholder="Cth: Yamaha">
                            </div>
                            <div class="col-5">
                                <label class="small fw-medium">Warna</label>
                                <input type="text" name="warna" id="warnaBaru" class="form-control border-0" placeholder="Hitam">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label small fw-semibold">Estimasi Tarif</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light text-muted">Rp</span>
                            <input type="text" id="tarifDisplay" class="form-control border-0 bg-light fw-bold" readonly placeholder="0">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted rounded-pill px-4" data-bs-dismiss="modal" id="btn-cancel">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary rounded-pill px-5 fw-bold">Mulai Parkir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function updateAllTimers() {
        document.querySelectorAll('.parkir-card').forEach(card => {
            const startStr = card.dataset.start;
            if (!startStr) return;

            const start = new Date(startStr).getTime();
            const tarif = parseInt(card.dataset.tarif || 0);
            const diff = Math.max(0, Math.floor((Date.now() - start) / 1000));

            const totalMenit = Math.floor(diff / 60);
            const jam = Math.floor(totalMenit / 60);
            const menit = totalMenit % 60;

            const tagihanJam = Math.max(1, Math.ceil(totalMenit / 60));
            const biaya = tagihanJam * tarif;

            const timeEl = card.querySelector('.run-time');
            const costEl = card.querySelector('.run-cost');

            if (timeEl) {
                timeEl.innerText = jam > 0 ? `${jam}j ${menit}m` : `${menit}m`;
            }
            if (costEl) {
                costEl.innerText = biaya.toLocaleString('id-ID');
            }
        });
    }

    setInterval(updateAllTimers, 1000);
    updateAllTimers();
    const tarifMap = {
        @foreach($tarifs as $t)
            "{{ strtolower($t->nama) }}": {{ $t->tarif_per_jam }},
        @endforeach
    };

    function setTarif(jenis) {
        const val = tarifMap[jenis?.toLowerCase().trim()] || 0;
        const display = document.getElementById('tarifDisplay');
        if (display) display.value = val ? `${val.toLocaleString('id-ID')} / jam` : '0';
    }

    const selectKen = document.getElementById('selectKendaraan');
    const inputJenis = document.getElementById('jenisBaru');
    const inputPlat = document.getElementById('platBaru');
    const inputMerk = document.getElementById('merkBaru');
    const inputWarna = document.getElementById('warnaBaru');
    const formParkir = document.getElementById('formParkir');
    const modalBody = document.getElementById('modalBodyContainer');


    selectKen?.addEventListener('change', function() {
        if (this.value) {
            inputJenis.value = ""; inputPlat.value = "";
            inputMerk.value = ""; inputWarna.value = "";
            setTarif(this.options[this.selectedIndex].dataset.jenis);
        }
    });

    inputJenis?.addEventListener('change', function() {
        if (this.value) {
            selectKen.value = "";
            setTarif(this.value);
        }
    });

    formParkir?.addEventListener('submit', function(e) {
        e.preventDefault();

        const isRegistered = selectKen.value;
        const isNewComplete = inputJenis.value && inputPlat.value;

        if (!isRegistered && !isNewComplete) {
            return Swal.fire('Perhatian', 'Pilih kendaraan atau isi data kendaraan baru (Jenis & Plat)!', 'warning');
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: "Mulai sesi parkir sekarang?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0061ff',
            confirmButtonText: 'Ya, Mulai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitData();
            }
        });
    });

    function submitData() {
        const formData = new FormData(formParkir);
        const btnSubmit = document.getElementById('btnSubmit');

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Loading...`;
        modalBody.classList.add('loading-overlay');

        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch("{{ route('parkir.store') }}", {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                // Jika error (misal duplicate plat)
                throw new Error(data.message || 'Terjadi kesalahan sistem');
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Silakan menuju halaman scan.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.message,
                confirmButtonColor: '#0061ff'
            });
        })
        .finally(() => {
            // Reset Loading State
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `Mulai Parkir`;
            modalBody.classList.remove('loading-overlay');
        });
    }
</script>
@endpush