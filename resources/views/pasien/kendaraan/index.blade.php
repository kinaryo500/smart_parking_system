@extends('layouts.pasien-app')

@section('title', 'Kendaraan Pasien | Smart Parking')

@section('content')

    <style>
        .fab {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0061ff, #3b82f6);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 10px 25px rgba(0, 97, 255, 0.4);
            transition: all 0.2s ease;
            z-index: 1050;
        }

        .fab:hover {
            transform: scale(1.08);
            color: white;
        }

        .fab.hide {
            opacity: 0;
            transform: scale(0.8);
            pointer-events: none;
        }
    </style>

    <div class="container mt-4">

        <div class="text-center mb-5">
            <div class="bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3"> 
                <i class="bi bi-car-front-fill text-primary fs-3"></i> 
            </div>
            <h4 class="fw-bold mb-1">Kendaraan Pasien</h4>
            <div class="mb-2">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill small fw-semibold"> 
                    <i class="bi bi-patch-check-fill me-1"></i> Tarif: Berlaku Sesuai Ketentuan Parkir
                </span>
            </div>
            <p class="text-muted small">{{ $kendaraan->count() }} Kendaraan terdaftar</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                @forelse($kendaraan as $k)
                    <div class="card p-3 mb-3 border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">
                                    {{ strtoupper($k->jenis) }} - {{ $k->plat_nomor }}
                                </div>
                                <small class="text-muted">{{ $k->merk }} • {{ $k->warna }}</small>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-sm btn-warning rounded-circle text-white" data-bs-toggle="modal"
                                    data-bs-target="#modalEditKendaraan{{ $k->id }}" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <a href="{{ route('pasien.kendaraan.detail', $k->id) }}"
                                    class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalEditKendaraan{{ $k->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                <form action="{{ route('pasien.kendaraan.update', $k->id) }}" method="POST" class="form-update-kendaraan">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="fw-bold">Edit Kendaraan Pasien</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Jenis Kendaraan</label>
                                            <select name="jenis" class="form-select border-0 bg-light" required>
                                                <option value="motor" {{ $k->jenis == 'motor' ? 'selected' : '' }}>Motor</option>
                                                <option value="mobil" {{ $k->jenis == 'mobil' ? 'selected' : '' }}>Mobil</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Plat Nomor</label>
                                            <input type="text" name="plat_nomor" class="form-control border-0 bg-light"
                                                value="{{ $k->plat_nomor }}" placeholder="B 1234 ABC" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Merk</label>
                                            <input type="text" name="merk" class="form-control border-0 bg-light"
                                                value="{{ $k->merk }}" placeholder="Contoh: Honda Vario" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Warna</label>
                                            <input type="text" name="warna" class="form-control border-0 bg-light"
                                                value="{{ $k->warna }}" placeholder="Contoh: Hitam" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light text-center border-0 shadow-sm py-4">
                        <i class="bi bi-info-circle d-block mb-2 fs-4 text-muted"></i>
                        <span class="text-muted">Belum ada kendaraan pasien terdaftar</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <button class="fab shadow" data-bs-toggle="modal" data-bs-target="#modalTambahKendaraan">
        <i class="bi bi-plus-lg"></i>
    </button>

    <div class="modal fade" id="modalTambahKendaraan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <form action="{{ route('pasien.kendaraan.store') }}" method="POST" id="formTambahKendaraan">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Tambah Kendaraan Pasien</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis Kendaraan</label>
                            <select name="jenis" class="form-select border-0 bg-light" required>
                                <option value="">-- Pilih --</option>
                                <option value="motor">Motor</option>
                                <option value="mobil">Mobil</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Plat Nomor</label>
                            <input type="text" name="plat_nomor" class="form-control border-0 bg-light"
                                placeholder="B 1234 ABC" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Merk</label>
                            <input type="text" name="merk" class="form-control border-0 bg-light"
                                placeholder="Contoh: Honda Vario" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Warna</label>
                            <input type="text" name="warna" class="form-control border-0 bg-light"
                                placeholder="Contoh: Hitam" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button> 
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fab = document.querySelector('.fab');
            const modals = document.querySelectorAll('.modal');

            modals.forEach(modal => {
                modal.addEventListener('show.bs.modal', () => fab.classList.add('hide'));
                modal.addEventListener('hidden.bs.modal', () => fab.classList.remove('hide'));
            });

            const formTambah = document.querySelector('#formTambahKendaraan');
            if (formTambah) {
                formTambah.addEventListener('submit', function () {
                    Swal.fire({
                        title: 'Menyimpan Data',
                        text: 'Mohon tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                });
            }

            const formsUpdate = document.querySelectorAll('.form-update-kendaraan');
            formsUpdate.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Update',
                        text: "Apakah Anda yakin ingin mengubah data kendaraan ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0061ff',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Update!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'rounded-pill px-4',
                            cancelButton: 'rounded-pill px-4'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memperbarui Data',
                                text: 'Mohon tunggu...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            form.submit();
                        }
                    });
                });
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#0061ff',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33'
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Data Tidak Valid',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#d33'
                });
            @endif
        });
    </script>
@endsection