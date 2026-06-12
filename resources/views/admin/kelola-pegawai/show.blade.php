@extends('layouts.main-app')

@section('content')
    <div class="container">

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle">
                            <div>
                                <h4 class="fw-bold mb-1 text-info-emphasis">Kelola Pegawai</h4>
                                <p class="text-info-emphasis opacity-75 mb-0">
                                    Manajemen akun pegawai sistem
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('admin.pegawai.index') }}" class="btn btn-secondary me-2 px-3 shadow-sm">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali
                                </a>
                                <button onclick="openModalTambahKendaraan()" class="btn btn-primary px-3 shadow-sm">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Kendaraan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="p-3 bg-white shadow-sm rounded-3 border-0 h-100 small">
                    <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Profil Pegawai</h6>
                    <div class="d-flex mb-2 align-items-center">
                        <span class="text-muted flex-shrink-0" style="width: 120px;">Nama Lengkap</span>
                        <span class="fw-bold text-dark text-truncate">: {{ $pegawai->name }}</span>
                    </div>
                    <div class="d-flex mb-2 align-items-center">
                        <span class="text-muted flex-shrink-0" style="width: 120px;">Email</span>
                        <span class="fw-bold text-dark text-truncate">: {{ $pegawai->email }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted flex-shrink-0" style="width: 120px;">Nomor HP</span>
                        <span class="fw-bold text-dark">: {{ $pegawai->no_hp ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-2 h-100">
                    <div class="col-6">
                        <div
                            class="card border-0 shadow-sm px-3 py-2 bg-success-subtle text-success-emphasis h-100 d-flex justify-content-center rounded-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                    style="width: 38px; height: 38px;">
                                    <i class="bi bi-truck" style="font-size: 1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block opacity-75" style="font-size: 0.70rem;">Total Kendaraan</span>
                                    <h5 class="fw-bold mb-0" style="font-size: 1.1rem;">{{ $totalKendaraan }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div
                            class="card border-0 shadow-sm px-3 py-2 bg-info-subtle text-info-emphasis h-100 d-flex justify-content-center rounded-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                    style="width: 38px; height: 38px;">
                                    <i class="bi bi-ticket" style="font-size: 1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block opacity-75" style="font-size: 0.70rem;">Total Sesi Parkir</span>
                                    <h5 class="fw-bold mb-0" style="font-size: 1.1rem;">{{ $totalParkir }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded p-3">
            <h4 class="fs-5 fw-bold mb-3">Daftar Kendaraan</h4>
            <div class="table-responsive border rounded-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Plat Nomor</th>
                            <th>Merk</th>
                            <th>Total Sesi Parkir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kendaraanList as $index => $kendaraan)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $kendaraan['plat_nomor'] }}</td>
                                <td>{{ $kendaraan['merk'] }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                        {{ $kendaraan['total_parkir'] }} Sesi
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light border text-danger"
                                        onclick="deleteKendaraan({{ $kendaraan['id'] }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Belum ada data kendaraan terdaftar untuk pegawai ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalKendaraan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="fw-bold" id="modalTitle">Tambah Kendaraan Pegawai</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formKendaraan" action="{{ route('admin.kendaraan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="user_id" value="{{ $pegawai->id }}">

                    <div class="modal-body">
                        <div id="errorBox" class="alert alert-danger d-none p-2 mb-3 small"></div>

                        <div class="mb-3">
                            <label for="jenis" class="form-label fw-semibold">Jenis Kendaraan</label>
                            <select name="jenis" id="jenis" class="form-select" required>
                                <option value="" selected disabled>Pilih Jenis Kendaraan</option>
                                <option value="motor">Motor</option>
                                <option value="mobil">Mobil</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="plat_nomor" class="form-label fw-semibold">Plat Nomor</label>
                            <input type="text" name="plat_nomor" id="plat_nomor" class="form-control"
                                placeholder="Contoh: B 1234 XYZ" required>
                        </div>

                        <div class="mb-3">
                            <label for="merk" class="form-label fw-semibold">Merk / Model</label>
                            <input type="text" name="merk" id="merk" class="form-control"
                                placeholder="Contoh: Honda Beat / Toyota Avanza" required>
                        </div>

                        <div class="mb-3">
                            <label for="warna" class="form-label fw-semibold">Warna</label>
                            <input type="text" name="warna" id="warna" class="form-control"
                                placeholder="Contoh: Hitam Metalik" required>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const modalKendaraanEl = document.getElementById('modalKendaraan');

        function openModalTambahKendaraan() {
            document.getElementById('formKendaraan').reset();
            document.getElementById('user_id').value = "{{ $pegawai->id }}";

            const myModal = new bootstrap.Modal(modalKendaraanEl);
            myModal.show();
        }

        // Proses Tambah / Kirim Data dengan Indikator Loading, Sukses, dan Gagal
        document.getElementById('formKendaraan').addEventListener('submit', function (e) {
            e.preventDefault();

            // Tampilkan Alert Loading Proses Mengirim Data
            Swal.fire({
                title: 'Sedang Memproses',
                text: 'Mohon tunggu sebentar, sistem sedang mengirim data.',
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Data kendaraan berhasil disimpan.',
                            icon: 'success',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            bootstrap.Modal.getInstance(modalKendaraanEl).hide();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal Menyimpan',
                            text: data.message || 'Terjadi kesalahan saat memproses data.',
                            icon: 'error',
                            confirmButtonText: 'Coba Lagi'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Kesalahan Koneksi',
                        text: 'Tidak dapat terhubung ke server. Silakan coba beberapa saat lagi.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                });
        });

        // Proses Hapus Data dengan Indikator Loading, Sukses, dan Gagal
        function deleteKendaraan(id) {
            Swal.fire({
                title: 'Hapus Kendaraan?',
                text: "Data transaksi parkir terkait kendaraan ini mungkin akan terpengaruh.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    // Tampilkan Alert Loading Proses Hapus Data
                    Swal.fire({
                        title: 'Sedang Menghapus',
                        text: 'Sistem sedang menghapus data kendaraan...',
                        icon: 'info',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/admin/kendaraan/delete/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: data.message || 'Data kendaraan berhasil dihapus.',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal Dihapus',
                                    text: data.message || 'Terjadi kesalahan saat menghapus data.',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Kesalahan Server',
                                text: 'Koneksi terputus atau terjadi gangguan pada server.',
                                icon: 'error'
                            });
                        });
                }
            })
        }
    </script>
@endsection