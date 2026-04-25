@extends('layouts.main-app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-3">
            <div
                class="card border-info-subtle bg-primary-subtle text-info-emphasis shadow-sm rounded px-3 py-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Riwayat Transaksi Parkir</h4>
                        <small class="opacity-75">Pantau transaksi dan pendapatan parkir anda</small>
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
                            <th class="ps-4 text-muted small fw-bold">PLAT NOMOR</th>
                            <th class="text-muted small fw-bold">JENIS</th>
                            <th class="text-muted small fw-bold text-nowrap">WAKTU MASUK</th>
                            <th class="text-muted small fw-bold text-nowrap">WAKTU KELUAR</th>
                            <th class="text-muted small fw-bold text-center">DURASI</th>
                            <th class="text-muted small fw-bold text-end">TOTAL BAYAR</th>
                            <th class="text-muted small fw-bold text-center px-4">STATUS</th>
                            <th class="text-muted small fw-bold text-center px-4">AKSI</th>
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


    <script>
        const API_DATA = "{{ route('petugas.transaksi.data') }}";
        const API_SUMMARY = "{{ route('petugas.transaksi.summary') }}";

        let currentPage = 1;
        let lastUpdateDate = new Date();
        let searchTimeout = null;

        async function fetchSummary() {
            try {
                const res = await fetch(API_SUMMARY);
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
                const responseData = await res.json(); // Data pagination dari Laravel

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

            if (data.length === 0) {
                html = `<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ditemukan data transaksi.</td></tr>`;
            } else {
                data.forEach(t => {
                    const statusBadge = t.status === 'selesai' ?
                        '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 w-100">Selesai</span>' :
                        '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 w-100">Parkir</span>';

                    html += `
                                                            <tr>
                                                                <td class="ps-4"><span class="fw-bold text-dark bg-light px-2 py-1 rounded border small text-nowrap">${t.plat_nomor}</span></td>
                                                                <td class="small text-muted">${t.jenis_kendaraan}</td>
                                                                <td class="small text-nowrap">${formatTime(t.waktu_masuk)}</td>
                                                                <td class="small text-nowrap">${t.waktu_keluar ? formatTime(t.waktu_keluar) : '<span class="text-muted italic">-</span>'}</td>
                                                                <td class="text-center small">${hitungDurasi(t.total_waktu)}</td>
                                                                <td class="text-end fw-bold text-dark small">Rp ${formatRupiah(t.total_bayar)}</td>
                                                                <td class="px-4 text-center">${statusBadge}</td>
                                                           <td class="text-center px-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                onclick="printNotaFromHistory(${t.id})">
                                            <i class="bi bi-printer"></i>
                                        </button>
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
                                                    <a class="page-link" onclick="changePage(${paginator.current_page - 1})"><i class="bi bi-chevron-left"></i></a>
                                                </li>`;


            for (let i = 1; i <= paginator.last_page; i++) {
                if (i === 1 || i === paginator.last_page || (i >= paginator.current_page - 1 && i <= paginator
                    .current_page + 1)) {
                    html += `<li class="page-item ${paginator.current_page === i ? 'active' : ''}">
                                                            <a class="page-link" onclick="changePage(${i})">${i}</a>
                                                        </li>`;
                } else if (i === paginator.current_page - 2 || i === paginator.current_page + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }


            html += `<li class="page-item ${paginator.current_page === paginator.last_page ? 'disabled' : ''}">
                                                    <a class="page-link" onclick="changePage(${paginator.current_page + 1})"><i class="bi bi-chevron-right"></i></a>
                                                </li>`;

            container.innerHTML = html;
        }


        async function printNotaFromHistory(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Nota',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }

            try {
                const res = await fetch(`/nota/${id}`);
                if (!res.ok) throw new Error("Gagal terhubung ke server");

                const response = await res.json();

                if (typeof Swal !== 'undefined') Swal.close();

                if (!response.success) {
                    alert(response.message || 'Data transaksi tidak ditemukan!');
                    return;
                }

                printNota(response.data, response.settings);

            } catch (e) {
                console.error(e);
                alert('Tidak dapat mengambil data nota. Silakan coba lagi.');
            }
        }

        function printNota(data, settings = {}) {
            const totalMenit = parseInt(data.total_waktu) || 0;
            const jam = Math.floor(totalMenit / 60);
            const menit = totalMenit % 60;

            const printWindow = window.open('', '_blank', 'width=400,height=600');
            if (!printWindow) return;

            const html = `
                    <html>
                    <head>
                        <style>
                            @page { size: 58mm auto; margin: 0; }
                            body { font-family: 'Courier New', monospace; width: 58mm; margin: 0 auto; padding: 8px; font-size: 11px; color: #000; }
                            .center { text-align: center; }
                            .bold { font-weight: bold; }
                            .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                            .line { border-top: 1px dashed #000; margin: 6px 0; }
                            .row { display: flex; justify-content: space-between; margin: 2px 0; }
                            .footer { text-align: center; margin-top: 10px; font-size: 10px; }
                            .box { border: 1px solid #000; padding: 5px; margin: 5px 0; }
                        </style>
                    </head>
                    <body>
                        <div class="center title">${settings.app_name ?? 'SMART PARKING'}</div>
                        <div class="center" style="font-size:10px;">${settings.lokasi_parkir ?? '-'}</div>
                         <div class="center" style="font-size:10px; min-width: 100px:">
                                        ${settings.alamat ?? '-'}
                                    </div>
                                    <div class="center" style="font-size:10px;">
                                        ${settings.kontak ?? '-'}
                                    </div>
                        <div class="line"></div>
                        <div class="row"><span>Kode Transaksi</span><span>#${data.kode_qr}</span></div>
                        <div class="row"><span>Petugas</span><span>${data.petugas ?? '-'}</span></div>
                        <div class="line"></div>
                        <div class="box">
                            <div class="row"><span>PLAT</span><span class="bold">${data.plat_nomor}</span></div>
                            <div class="row"><span>JENIS</span><span>${data.jenis}</span></div>
                        </div>
                        <div class="line"></div>
                        <div class="row"><span>Masuk</span><span>${formatTime(data.waktu_masuk)}</span></div>
                        <div class="row"><span>Keluar</span><span>${formatTime(data.waktu_keluar)}</span></div>
                        <div class="row"><span>Durasi</span><span>${jam}j ${menit}m</span></div>
                        <div class="line"></div>
                        <div class="row bold" style="font-size:13px;">
                            <span>TOTAL</span><span>Rp ${formatRupiah(data.total_bayar)}</span>
                        </div>
                        <div class="line"></div>
                        <div class="footer">TERIMA KASIH<br>SILAKAN SIMPAN STRUK INI</div>
                        <script>
                            window.onload = function () {
                                window.print();
                                setTimeout(() => window.close(), 500);
                            }
                        <\/script>
                    </body>
                    </html>`;

            printWindow.document.write(html);
            printWindow.document.close();
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
            document.getElementById('lastUpdateText').innerText = diff < 1 ? "Baru saja" : `${diff} menit yang lalu`;
        }

        setInterval(fetchAll, 120000);
        setInterval(updateTimeText, 30000);

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

        fetchAll();
    </script>
@endsection