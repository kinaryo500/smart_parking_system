@extends('layouts.main-app')

@section('content')
    <div class="container">

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle">
                            <div>
                                <h4 class="fw-bold mb-1 text-info-emphasis">Kelola Petugas</h4>
                                <p class="text-info-emphasis opacity-75 mb-0">Manajemen data petugas dan admin secara
                                    real-time</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-white bg-white px-4 shadow-sm border"
                                    data-bs-toggle="modal" data-bs-target="#modalLaporan">
                                    <i class="bi bi-printer me-2"></i>Cetak Laporan
                                </button>
                                <a href="{{ route('admin.petugas.create') }}" class="btn btn-primary px-4 shadow-sm">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Petugas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="fs-5 fw-bold mb-0">Daftar Petugas</h4>
            </div>

            <form action="{{ route('admin.petugas.index') }}" method="GET" id="filterForm"
                class="row g-2 mb-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>

                        <input type="text" name="search" id="searchInput" class="form-control bg-light border-start-0"
                            placeholder="Cari nama atau email..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

            </form>

            <div class="table-responsive border rounded-3">
                <table class="table table-hover table-bordered align-middle mb-0"
                    style="border-color:#e9ecef; --bs-table-hover-bg: rgba(0,0,0,0.02);">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width:50px;">No</th>
                            <th>Nama Petugas</th>
                            <th>Email</th>
                            <th>Kontak</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($petugas as $index => $p)
                            <tr>
                                <td class="text-center small">{{ $petugas->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-start">
                                            <div class="fw-semibold">{{ $p->name }}</div>
                                            <small class="text-muted">
                                                {{ ucfirst($p->role) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <div class="text-muted x-small d-md-none">{{ $p->email }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $p->email }}</td>
                                <td class="text-center">{{ $p->no_hp ?? '-' }}</td>
                                <td class="text-center">
                                    <span style="width: 100px;"
                                        class="badge rounded bg-{{ $p->is_active ? 'success' : 'secondary' }}-subtle text-{{ $p->is_active ? 'success' : 'secondary' }} px-3">
                                        {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm rounded-3">
                                        <a href="{{ route('admin.petugas.show', $p->id) }}"
                                            class="btn btn-sm btn-light border text-primary" title="Detail Transaksi">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.petugas.edit', $p->id) }}"
                                            class="btn btn-sm btn-light border text-warning" title="Edit Profil">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form id="toggle-form-{{ $p->id }}" action="{{ route('admin.petugas.toggle', $p->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="button" onclick="confirmToggle({{ $p->id }})"
                                                class="btn btn-sm btn-light border text-info" title="Ubah Status">
                                                <i class="bi {{ $p->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        @if ($p->role !== 'admin')
                                            <button type="button" class="btn btn-sm btn-light border text-danger"
                                                onclick="confirmDelete({{ $p->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                            <form id="delete-form-{{ $p->id }}"
                                                action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST"
                                                style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-people fs-1 opacity-25"></i></div>
                                    Data petugas tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $petugas->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLaporan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Cetak Laporan Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Tanggal Mulai</label>
                            <input type="date" id="report_start" class="form-control bg-light border-0 py-2">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Tanggal Selesai</label>
                            <input type="date" id="report_end" class="form-control bg-light border-0 py-2">
                        </div>
                        <div class="col-12 mt-4">
                            <div class="d-grid gap-2">
                                <button type="button" onclick="downloadReport('rekap')" class="btn btn-primary py-2">
                                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Download Rekap Pendapatan
                                </button>
                                <button type="button" onclick="downloadReport('all-detail')"
                                    class="btn btn-success py-2 text-white">
                                    <i class="bi bi-file-earmark-excel me-2"></i>Download Detail Per Petugas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function downloadReport(type) {
            const start = document.getElementById('report_start').value;
            const end = document.getElementById('report_end').value;
            const modalElement = document.getElementById('modalLaporan');
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();

            let url = type === 'rekap' ?
                "{{ route('admin.laporan.rekap') }}" :
                "{{ route('admin.laporan.all-detail') }}";

            url += `?start=${start}&end=${end}`;

            Swal.fire({
                title: 'Menyiapkan Laporan',
                text: 'File Excel sedang diproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    setTimeout(() => {
                        window.location.href = url;
                        Swal.close();
                    }, 1000);
                }
            });
        }
        let searchTimer;
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterForm.submit();
            }, 700);
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Petugas?',
                text: 'Data yang terhubung dengan transaksi tidak bisa dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('delete-form-' + id).submit();
                        }
                    });
                }
            });
        }

        function confirmToggle(id) {
            Swal.fire({
                title: 'Ubah Akses Petugas?',
                text: 'Status aktif akun akan segera diperbarui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('toggle-form-' + id).submit();
                }
            });
        }

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}"
            });
        @endif
    </script>
@endsection