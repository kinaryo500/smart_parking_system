@extends('layouts.main-app')

@section('content')

    <style>
        .badge-admin {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-petugas {
            background: #ecfeff;
            color: #0891b2;
        }
    </style>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-3">
            <div
                class="card border-info-subtle bg-primary-subtle text-info-emphasis shadow-sm rounded px-3 py-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Riwayat Transaksi Parkir</h4>
                        <small class="opacity-75">Pantau transaksi dan pendapatan parkir</small>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-white text-info border py-2 px-3 small fw-normal">
                            <i class="bi bi-clock-history me-1"></i>
                            Update terakhir: <span id="lastUpdateText">Baru saja</span>
                        </span>

                        <button class="btn btn-info btn-sm rounded-3 shadow-sm px-3 d-flex align-items-center"
                            onclick="fetchAll()" id="btnRefresh">

                            <i class="bi bi-arrow-clockwise me-2" id="iconRefresh"></i>
                            <span id="textRefresh">Refresh Data</span>

                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded p-3 bg-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-cash-stack text-success"></i>
                        </div>
                        <small class="text-muted fw-medium">Hari Ini</small>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Rp <span id="hariIni">0</span></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded p-3 bg-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-calendar-event text-primary"></i>
                        </div>
                        <small class="text-muted fw-medium">Minggu Ini</small>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Rp <span id="mingguIni">0</span></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded p-3 bg-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-graph-up-arrow text-warning"></i>
                        </div>
                        <small class="text-muted fw-medium">Bulan Ini</small>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Rp <span id="bulanIni">0</span></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded p-3 bg-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-dark bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-car-front text-dark"></i>
                        </div>
                        <small class="text-muted fw-medium">Kendaraan Hari Ini</small>
                    </div>
                    <h5 class="fw-bold text-dark mb-0"><span id="totalHari">0</span> <small
                            class="text-muted fs-6 fw-normal">Unit</small></h5>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded overflow-hidden bg-white">
            <div class="px-3 py-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="fs-5 fw-bold mb-0">Daftar Transaksi</h4>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="search" class="form-control border-0 bg-light"
                                placeholder="Cari plat nomor..." onkeyup="debounceSearch()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="filterStatus" class="form-select border-0 bg-light" onchange="resetAndFetch()">
                            <option value="all">Semua Status</option>
                            <option value="aktif">Sedang Parkir</option>
                            <option value="selesai">Sudah Keluar</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive px-2">
                <table class="table table-hover align-middle mb-0 border rounded">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted small fw-bold">OPERATOR</th>
                            <th class="ps-4 text-muted small fw-bold">PLAT NOMOR</th>
                            <th class="text-muted small fw-bold">JENIS</th>
                            <th class="text-muted small fw-bold text-nowrap">WAKTU MASUK</th>
                            <th class="text-muted small fw-bold text-nowrap">WAKTU KELUAR</th>
                            <th class="text-muted small fw-bold text-center">DURASI</th>
                            <th class="text-muted small fw-bold text-end">TOTAL BAYAR</th>
                            <th class="text-muted small fw-bold text-center px-4">STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="border-top-0">
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div id="pageInfo" class="small text-muted fw-medium"></div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 shadow-sm" id="paginationControl"></ul>
                </nav>
            </div>
        </div>

    </div>

    <style>
        .spin {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .card {
            transition: all 0.3s ease;
        }

        .table thead th {
            letter-spacing: 0.05rem;
            font-size: 0.75rem;
        }

        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
            background-color: #f1f3f5;
        }

        .pagination .page-link {
            color: #495057;
            border: none;
            margin: 0 2px;
            border-radius: 6px !important;
            cursor: pointer;
        }

        .pagination .active .page-link {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .pagination .disabled .page-link {
            background-color: transparent;
            color: #ced4da;
            cursor: not-allowed;
        }
    </style>

    <script>
        const API_DATA = "{{ route('admin.transaksi.data') }}";
        const API_SUMMARY = "{{ route('admin.transaksi.summary') }}";

        let currentPage = 1;
        let lastUpdateDate = new Date();
        let searchTimeout = null;
        async function fetchSummary() {
            try {
                const res = await fetch(API_SUMMARY);

                if (!res.ok) throw new Error("Gagal fetch summary");

                const data = await res.json();

                document.getElementById('hariIni').innerText = formatRupiah(data.hari_ini);
                document.getElementById('mingguIni').innerText = formatRupiah(data.minggu_ini);
                document.getElementById('bulanIni').innerText = formatRupiah(data.bulan_ini ?? 0);
                document.getElementById('totalHari').innerText = data.total_hari;

            } catch (e) {
                console.error("Summary error:", e);
            }
        }


        async function fetchTableData(page = 1) {
            const icon = document.getElementById('iconRefresh');
            const btn = document.getElementById('btnRefresh');
            const search = document.getElementById('search').value;
            const status = document.getElementById('filterStatus').value;

            icon.classList.add('spin');
            btn.classList.add('disabled');

            try {
                const url = new URL(API_DATA);
                url.searchParams.append('page', page);
                url.searchParams.append('search', search);
                url.searchParams.append('status', status);

                const res = await fetch(url);


                if (!res.ok) throw new Error("Response bukan JSON / error server");

                const responseData = await res.json();

                renderTable(responseData);
                currentPage = responseData.current_page;
                lastUpdateDate = new Date();
                updateTimeText();

            } catch (e) {
                console.error("Fetch data error:", e);
            } finally {
                setTimeout(() => {
                    icon.classList.remove('spin');
                    btn.classList.remove('disabled');
                }, 500);
            }
        }

        function renderTable(paginator) {
            const data = paginator.data;
            let html = '';

            if (!data || data.length === 0) {
                html = `<tr><td colspan="8" class="text-center py-5 text-muted">Tidak ditemukan data transaksi.</td></tr>`;
            } else {
                data.forEach(t => {

                    const statusBadge = t.status === 'selesai'
                        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 w-100">Selesai</span>'
                        : '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 w-100">Parkir</span>';

                    const initial = t.petugas && t.petugas !== '-'
                        ? t.petugas.charAt(0).toUpperCase()
                        : '?';

                    const avatarColor = t.role === 'admin'
                        ? 'bg-primary text-white'
                        : 'bg-info text-white';

                    html += `
                    <tr>
                        <td class="ps-4 small fw-semibold text-dark">
                            <div class="d-flex align-items-center gap-2">

                                <div class="d-flex flex-column">
                                    <span>${t.petugas}</span>
                                    <small class="text-muted">
                                        ${t.role === 'admin' ? 'Admin' : 'Petugas'}
                                    </small>
                                </div>

                            </div>
                        </td>

                        <td class="ps-4">
                            <span class="fw-bold text-dark bg-light px-2 py-1 rounded border small text-nowrap">
                                ${t.plat_nomor}
                            </span>
                        </td>

                        <td class="small text-muted">${t.jenis_kendaraan}</td>

                        <td class="small text-nowrap">
                            ${formatTime(t.waktu_masuk)}
                        </td>

                        <td class="small text-nowrap">
                            ${t.waktu_keluar ? formatTime(t.waktu_keluar) : '<span class="text-muted">-</span>'}
                        </td>

                        <td class="text-center small">
                            ${hitungDurasi(t.total_waktu)}
                        </td>

                        <td class="text-end fw-bold text-dark small">
                            Rp ${formatRupiah(t.total_bayar)}
                        </td>

                        <td class="px-4 text-center">
                            ${statusBadge}
                        </td>
                    </tr>
                `;
                });
            }

            document.getElementById('tableBody').innerHTML = html;

            document.getElementById('pageInfo').innerText =
                `Menampilkan ${paginator.from ?? 0} - ${paginator.to ?? 0} dari ${paginator.total} data`;

            renderPagination(paginator);
        }


        function renderPagination(paginator) {
            const container = document.getElementById('paginationControl');
            if (paginator.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';

            html += `<li class="page-item ${paginator.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" onclick="changePage(${paginator.current_page - 1})">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>`;

            for (let i = 1; i <= paginator.last_page; i++) {
                if (i === 1 || i === paginator.last_page || (i >= paginator.current_page - 1 && i <= paginator.current_page + 1)) {
                    html += `<li class="page-item ${paginator.current_page === i ? 'active' : ''}">
                            <a class="page-link" onclick="changePage(${i})">${i}</a>
                        </li>`;
                } else if (i === paginator.current_page - 2 || i === paginator.current_page + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            html += `<li class="page-item ${paginator.current_page === paginator.last_page ? 'disabled' : ''}">
                    <a class="page-link" onclick="changePage(${paginator.current_page + 1})">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>`;

            container.innerHTML = html;
        }


        function fetchAll() {
            fetchSummary();
            fetchTableData(currentPage);
        }

        function resetAndFetch() {
            currentPage = 1;
            fetchTableData(1);
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                resetAndFetch();
            }, 500);
        }

        function changePage(page) {
            if (page < 1) return;
            fetchTableData(page);
        }

        function updateTimeText() {
            const diff = Math.floor((new Date() - lastUpdateDate) / 60000);
            document.getElementById('lastUpdateText').innerText =
                diff < 1 ? "Baru saja" : `${diff} menit yang lalu`;
        }

        function formatTime(t) {
            if (!t) return '-';
            const d = new Date(t);
            return d.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            }).replace('.', ':');
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka || 0);
        }

        function hitungDurasi(menit) {
            if (!menit || menit < 0) return '-';
            const jam = Math.floor(menit / 60);
            const sisa = menit % 60;
            return jam > 0 ? `${jam}j ${sisa}m` : `${sisa}m`;
        }

        setInterval(fetchAll, 120000);
        setInterval(updateTimeText, 30000);

        fetchAll();
    </script>
@endsection