@extends('layouts.main-app')

@section('content')
    <div class="container">

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle">
                            <div>
                                <h4 class="fw-bold mb-1 text-success-emphasis">Kelola Tarif</h4>
                                <p class="text-success-emphasis opacity-75 mb-0">
                                    Manajemen tarif parkir
                                </p>
                            </div>
                            <button onclick="openModal()" class="btn btn-success px-4 shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Tarif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="fs-5 fw-bold mb-0">Daftar Tarif Yang Berlaku Saat Ini</h4>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="search" class="form-control bg-light border-start-0"
                    placeholder="Cari nama tarif...">
            </div>
            <div class="table-responsive border rounded-3">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Tarif / Jam</th>
                            <th>Tarif Maksimal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalForm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                <div class="modal-header border-0">
                    <h5 class="fw-bold" id="modalTitle">Tambah Tarif</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div id="errorBox" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="tarif_id">

                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" id="nama" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Tarif per Jam</label>
                        <input type="number" id="tarif_per_jam" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Tarif Maksimal</label>
                        <input type="number" id="tarif_maksimal" class="form-control">
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button onclick="saveData()" class="btn btn-success">Simpan</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const API = "{{ route('admin.tarif.data') }}";
        const STORE = "{{ route('admin.tarif.store') }}";
        const UPDATE = "{{ url('admin/tarif') }}";
        const DELETE_URL = "{{ url('admin/tarif') }}";

        let editMode = false;
        let dataList = [];

        const searchInput = document.getElementById('search');
        const tableBody = document.getElementById('tableBody');
        const errorBox = document.getElementById('errorBox');

        const idInput = document.getElementById('tarif_id');
        const namaInput = document.getElementById('nama');
        const perJamInput = document.getElementById('tarif_per_jam');
        const maksimalInput = document.getElementById('tarif_maksimal');

        async function loadData() {
            const res = await fetch(`${API}?search=${searchInput.value}`);
            const result = await res.json();
            dataList = result.data || [];
            renderTable(dataList);
        }

        function renderTable(data) {
            let html = '';
            data.forEach((d, i) => {
                html += `
            <tr class="text-center">
                <td>${i + 1}</td>
                <td class="text-start">${d.nama}</td>
                <td>Rp ${parseInt(d.tarif_per_jam).toLocaleString()}</td>
                <td>Rp ${parseInt(d.tarif_maksimal).toLocaleString()}</td>
                <td>
                    <div class="btn-group">
                        <button onclick="editData(${i})"
                            class="btn btn-sm btn-light border text-warning">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button onclick="deleteData(${d.id})"
                            class="btn btn-sm btn-light border text-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
            });
            tableBody.innerHTML = html;
        }

        function openModal() {
            editMode = false;
            clearError();

            idInput.value = '';
            namaInput.value = '';
            perJamInput.value = '';
            maksimalInput.value = '';

            new bootstrap.Modal(document.getElementById('modalForm')).show();
        }

        function editData(index) {
            editMode = true;
            clearError();

            const d = dataList[index];

            idInput.value = d.id;
            namaInput.value = d.nama;
            perJamInput.value = d.tarif_per_jam;
            maksimalInput.value = d.tarif_maksimal;

            new bootstrap.Modal(document.getElementById('modalForm')).show();
        }

        function clearError() {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
        }

        async function saveData() {
            clearError();

            let data = {
                nama: namaInput.value,
                tarif_per_jam: perJamInput.value,
                tarif_maksimal: maksimalInput.value
            };

            let url = editMode ? `${UPDATE}/${idInput.value}` : STORE;
            let method = editMode ? 'PUT' : 'POST';

            let res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            let result = await res.json();

            if (!res.ok) {
                errorBox.innerHTML = Object.values(result.errors).join('<br>');
                errorBox.classList.remove('d-none');
                return;
            }

            Swal.fire('Berhasil', result.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalForm')).hide();
            loadData();
        }

        function deleteData(id) {
            Swal.fire({
                title: 'Hapus?',
                icon: 'warning',
                showCancelButton: true
            }).then(async r => {
                if (!r.isConfirmed) return;

                let res = await fetch(`${DELETE_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                let result = await res.json();

                if (result.success) {
                    Swal.fire('Berhasil', 'Data dihapus', 'success');
                    loadData();
                }
            });
        }

        searchInput.addEventListener('input', loadData);
        document.addEventListener("DOMContentLoaded", loadData);
    </script>
@endsection