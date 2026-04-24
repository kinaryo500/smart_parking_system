@extends('layouts.main-app')

@push('styles')
    <style>
        .card-stats {
            transition: transform 0.2s;
            border: none;
            border-left: 4px solid #0d6efd;
        }

        .card-stats:hover {
            transform: translateY(-5px);
        }

        .bg-light-profile {
            background-color: #f8f9fa;
            border-radius: 15px;
        }

        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6;
        }

        .profile-label {
            font-size: 0.75rem;
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 2px;
            display: block;
        }

        .profile-value {
            font-weight: 600;
            color: #212529;
        }

        .trx-count {
            font-size: 0.75rem;
            display: block;
            margin-top: 2px;
            opacity: 0.8;
        }
    </style>
@endpush

@section('content')

    <div class="container">

        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm rounded overflow-hidden">
                <div class="card-body p-0 fs-6">
                    <div class="d-flex justify-content-between align-items-center px-3  py-2 bg-primary-subtle">
                        <div>
                            <h4 class="fw-bold mb-1 text-info-emphasis">Detail Kinerja Petugas</h4>
                            <p class="text-info-emphasis opacity-75 mb-0">Pantau transaksi dan statistik pendapatan petugas
                            </p>
                        </div>
                        <a href="javascript:void(0)" onclick="history.back()"
                            class="btn btn-outline-secondary rounded px-4">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <span class="profile-label">Nama Lengkap</span>
                            <div class="profile-value p-2 bg-light-profile rounded">{{ $petugas->name }}</div>
                        </div>
                        <div class="mb-3">
                            <span class="profile-label">Email</span>
                            <div class="profile-value p-2 bg-light-profile rounded">{{ $petugas->email }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <span class="profile-label">Role</span>
                                <span
                                    class="badge bg-info bg-opacity-10 text-info px-3">{{ strtoupper($petugas->role) }}</span>
                            </div>
                            <div class="col-6">
                                <span class="profile-label">Bergabung Pada</span>
                                <div class="profile-value">{{ $petugas->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card card-stats shadow-sm p-3" style="border-left-color: #0dcaf0;">
                            <small class="text-muted fw-bold">HARI INI</small>
                            <h4 class="fw-bold mt-2 mb-0 text-info">Rp <span id="hariIni">0</span></h4>
                            <small class="text-muted trx-count"><i class="bi bi-arrow-repeat"></i> <span
                                    id="countHariIni">0</span> Transaksi</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats shadow-sm p-3" style="border-left-color: #6610f2;">
                            <small class="text-muted fw-bold">MINGGU INI</small>
                            <h4 class="fw-bold mt-2 mb-0 text-primary">Rp <span id="mingguIni">0</span></h4>
                            <small class="text-muted trx-count"><i class="bi bi-arrow-repeat"></i> <span
                                    id="countMingguIni">0</span> Transaksi</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats shadow-sm p-3" style="border-left-color: #fd7e14;">
                            <small class="text-muted fw-bold">BULAN INI</small>
                            <h4 class="fw-bold mt-2 mb-0 text-warning">Rp <span id="bulanIni">0</span></h4>
                            <small class="text-muted trx-count"><i class="bi bi-arrow-repeat"></i> <span
                                    id="countBulanIni">0</span> Transaksi</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-primary-subtle text-primary shadow-sm rounded">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">
                                Pendapatan Terfilter (<span id="countTrx">0</span> Transaksi):
                            </h6>
                            <h2 class="fw-bold mb-0">Rp <span id="totalFilter">0</span></h2>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded overflow-hidden p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="fs-5 fw-bold mb-0">Daftar Riwayat Transaksi</h4>
            </div>
            <div class="row align-items-end g-2 mb-3">
                <div class="col-md-5">
                    <label class="fw-bold small mb-2 text-muted">DARI TANGGAL</label>
                    <input type="date" id="start" class="form-control rounded-3" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-5">
                    <label class="fw-bold small mb-2 text-muted">SAMPAI TANGGAL</label>
                    <input type="date" id="end" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100 rounded-3 py-2" onclick="loadData(1)">
                        <i class="bi bi-search me-1"></i> Filter Data
                    </button>
                </div>
            </div>
            <div class="table-responsive border-top rounded">
                <table class="table table-hover table-bordered align-middle mb-0"
                    style="border-color:#e9ecef; --bs-table-hover-bg: rgba(0,0,0,0.02);">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th class="" style="width: 50px;">No</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" style="border-top:1px solid #e9ecef;"></tbody>
                </table>
            </div>

            <div class="card-footer bg-white p-3 border-top d-flex justify-content-center" id="pagination"></div>
        </div>
    </div>

    <script>
        const API = "{{ route('admin.petugas.transaksi.data', $petugas->id) }}";
        let currentPage = 1;

        async function loadData(page = 1) {
            currentPage = page;
            const start = document.getElementById('start').value;
            const end = document.getElementById('end').value;

            let url = new URL(API);
            if (start) url.searchParams.set('start', start);
            if (end) url.searchParams.set('end', end);
            url.searchParams.set('page', page);

            try {
                const res = await fetch(url);
                const result = await res.json();
                document.getElementById('hariIni').innerText = format(result.summary.hari_ini);
                document.getElementById('countHariIni').innerText = result.summary.count_hari_ini || 0;

                document.getElementById('mingguIni').innerText = format(result.summary.minggu_ini);
                document.getElementById('countMingguIni').innerText = result.summary.count_minggu_ini || 0;

                document.getElementById('bulanIni').innerText = format(result.summary.bulan_ini);
                document.getElementById('countBulanIni').innerText = result.summary.count_bulan_ini || 0;
                document.getElementById('totalFilter').innerText = format(result.summary_filter.total_filter);
                document.getElementById('countTrx').innerText = result.summary_filter.count_filter || 0;
                renderTable(result.data);
                renderPagination(result.meta);
            } catch (error) {
                console.error("Gagal memuat data:", error);
            }
        }

        function renderTable(data) {
            let html = '';
            if (!data.length) {
                html = `<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data transaksi ditemukan</td></tr>`;
            } else {
                data.forEach((t, i) => {
                    const statusBadge = t.status === 'selesai' ? 'bg-success' : 'bg-warning';
                    html += `
                                                <tr>
                                                    <td class="text-center">${(currentPage - 1) * 10 + (i + 1)}</td>
                                                    <td><span class="text-center">${t.kendaraan?.plat_nomor ?? '-'}</span></td>
                                                    <td class="text-center">
                                                        <span class="text-center" style="text-transform: capitalize;">
                                                            ${t.jenis_kendaraan}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">${t.waktu_masuk}</td>
                                                    <td class="text-center">${t.waktu_keluar ?? '<span class="text-danger">Parkir</span>'}</td>
                                                    <td class="text-center"><span class="badge ${statusBadge} bg-opacity-10 text-${t.status == 'selesai' ? 'success' : 'warning'} py-2">${t.status}</span></td>
                                                    <td class="text-end">Rp ${format(t.total_bayar)}</td>
                                                </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        }

        function renderPagination(meta) {
            if (meta.last_page <= 1) {
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            let html = '<nav><ul class="pagination pagination-sm mb-0">';
            for (let i = 1; i <= meta.last_page; i++) {
                html += `
                                                <li class="page-item ${i === meta.current_page ? 'active' : ''}">
                                                    <button class="page-link px-3" onclick="loadData(${i})">${i}</button>
                                                </li>
                                            `;
            }
            html += '</ul></nav>';
            document.getElementById('pagination').innerHTML = html;
        }

        function format(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadData(1);
        });
    </script>

@endsection