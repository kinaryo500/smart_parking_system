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
                            <button onclick="openModal()" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Pegawai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded p-3">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="fs-5 fw-bold mb-0">Daftar Pegawai</h4>
            </div>

            <div class="row g-2 align-items-stretch mb-2">
                <div class="col-md-9">
                    <div class="input-group h-100">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search" class="form-control bg-light border-start-0"
                            placeholder="Cari nama, email, atau nomor HP pegawai...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select id="status" class="form-select bg-light h-100">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive border rounded-3">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th style="min-width: 250px;">Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="fw-bold" id="modalTitle">Tambah Pegawai</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="errorBox" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="pegawai_id">

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" id="name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" id="no_hp" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="password" class="form-control">
                        <small class="text-muted" id="passwordHint"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="is_active" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button onclick="savePegawai()" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const API = "{{ route('admin.pegawai.data') }}";
        const STORE = "{{ route('admin.pegawai.store') }}";
        const UPDATE = "{{ url('admin/pegawai/update') }}";
        const DELETE_URL = "{{ url('admin/pegawai/delete') }}";
        const TOGGLE_URL = "{{ url('admin/pegawai/toggle') }}";
        const SHOW_URL = "{{ url('admin/pegawai') }}"; // 1. Tambahkan baris ini agar url show detail terdefinisi

        let editMode = false;
        let pegawaiDataList = []; 

        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('status');
        const tableBody = document.getElementById('tableBody');
        const errorBox = document.getElementById('errorBox');

        const pegawaiIdInput = document.getElementById('pegawai_id');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const noHpInput = document.getElementById('no_hp');
        const passwordInput = document.getElementById('password');
        const isActiveInput = document.getElementById('is_active');
        const modalTitle = document.getElementById('modalTitle');
        const passwordHint = document.getElementById('passwordHint');

        async function loadData() {
            try {
                const res = await fetch(`${API}?search=${searchInput.value}&status=${statusFilter.value}`);
                const result = await res.json();
                pegawaiDataList = result.data || [];
                renderTable(pegawaiDataList);
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Gagal mengambil data', 'error');
            }
        }

        function renderTable(data) {
            let html = '';

            if (data.length === 0) {
                html = `<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data pegawai ditemukan.</td></tr>`;
            } else {
                data.forEach((p, i) => {
                    html += `
                    <tr class="text-center">
                        <td>${i + 1}</td>
                        <td class="text-start">${p.name}</td>
                        <td>${p.email}</td>
                        <td>${p.no_hp ?? '-'}</td>

                        <td>
                            <span style="width:100px;"
                                class="badge py-2 rounded bg-${p.is_active ? 'success' : 'secondary'}-subtle 
                                text-${p.is_active ? 'success' : 'secondary'} px-3">
                                ${p.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </td>

                        <td>
                            <div class="btn-group">
                                ${/* 2. Tambahkan tombol Detail / Show di bawah ini */''}
                                <a href="${SHOW_URL}/${p.id}"
                                    class="btn btn-sm btn-light border text-primary"
                                    data-bs-toggle="tooltip"
                                    title="Detail pegawai">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <button onclick="editPegawai(${p.id})"
                                    class="btn btn-sm btn-light border text-warning"
                                    data-bs-toggle="tooltip"
                                    title="Edit pegawai">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button onclick="togglePegawai(${p.id})"
                                    class="btn btn-sm btn-light border text-info"
                                    data-bs-toggle="tooltip"
                                    title="Aktif / Nonaktifkan pegawai">
                                    <i class="bi ${p.is_active ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
                                </button>

                                <button onclick="deletePegawai(${p.id})"
                                    class="btn btn-sm btn-light border text-danger"
                                    data-bs-toggle="tooltip"
                                    title="Hapus pegawai">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
            }

            tableBody.innerHTML = html;
            initTooltip();
        }

        function initTooltip() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        }

        function clearError() {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.form-select').forEach(el => el.classList.remove('is-invalid'));
        }

        function showValidation(errors) {
            let html = '';
            for (let key in errors) {
                html += errors[key].join('<br>') + '<br>';
                let field = document.getElementById(key);
                if (field) field.classList.add('is-invalid');
            }
            errorBox.innerHTML = html;
            errorBox.classList.remove('d-none');
        }

        function openModal() {
            editMode = false;
            clearError();

            modalTitle.innerText = "Tambah Pegawai";
            passwordHint.innerText = "Wajib diisi minimal 6 karakter";

            pegawaiIdInput.value = '';
            nameInput.value = '';
            emailInput.value = '';
            noHpInput.value = '';
            passwordInput.value = '';
            isActiveInput.value = 1;

            let myModal = new bootstrap.Modal(document.getElementById('modalForm'));
            myModal.show();
        }

        function editPegawai(id) {
            editMode = true;
            clearError();

            const p = pegawaiDataList.find(user => user.id == id);

            if (!p) {
                Swal.fire('Error', 'Data pegawai tidak ditemukan di cache lokal', 'error');
                return;
            }

            modalTitle.innerText = "Edit Pegawai";
            passwordHint.innerText = "Kosongkan jika tidak ingin mengubah password";

            pegawaiIdInput.value = p.id;
            nameInput.value = p.name;
            emailInput.value = p.email;
            noHpInput.value = p.no_hp ?? '';
            passwordInput.value = '';
            isActiveInput.value = p.is_active ? 1 : 0;

            let myModal = new bootstrap.Modal(document.getElementById('modalForm'));
            myModal.show();
        }

        async function savePegawai() {
            clearError();

            Swal.fire({
                title: 'Menyimpan Data...',
                text: 'Sistem sedang memproses data pegawai.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                let data = {
                    name: nameInput.value,
                    email: emailInput.value,
                    no_hp: noHpInput.value,
                    is_active: isActiveInput.value
                };

                if (passwordInput.value) data.password = passwordInput.value;

                let url = editMode ? `${UPDATE}/${pegawaiIdInput.value}` : STORE;
                let method = editMode ? 'PUT' : 'POST';

                let res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                let result = await res.json();

                if (!res.ok) {
                    Swal.close();
                    showValidation(result.errors || {});
                    return;
                }

                if (!result.success) {
                    Swal.fire('Gagal', result.message || 'Terjadi kesalahan', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message
                });

                const modalEl = document.getElementById('modalForm');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                loadData();

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghubungi server'
                });
            }
        }

        function deletePegawai(id) {
            Swal.fire({
                title: 'Hapus Pegawai?',
                text: 'Data tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(async (r) => {
                if (!r.isConfirmed) return;

                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const res = await fetch(`${DELETE_URL}/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await res.json();

                    if (result.success) {
                        Swal.fire('Berhasil', 'Pegawai berhasil dihapus', 'success');
                        loadData();
                    } else {
                        Swal.fire('Gagal', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal menghapus pegawai', 'error');
                }
            });
        }

        function togglePegawai(id) {
            Swal.fire({
                title: 'Ubah Status?',
                text: 'Status pegawai akan diubah.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then(async r => {
                if (!r.isConfirmed) return;

                try {
                    const res = await fetch(`${TOGGLE_URL}/${id}`, {
                        method: 'PUT',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await res.json();

                    if (result.success) {
                        Swal.fire('Berhasil', result.message, 'success');
                        loadData();
                    } else {
                        Swal.fire('Gagal', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal mengubah status', 'error');
                }
            });
        }

        searchInput.addEventListener('input', loadData);
        statusFilter.addEventListener('change', loadData);
        document.addEventListener("DOMContentLoaded", () => {
            loadData();
        });
    </script>
@endpush