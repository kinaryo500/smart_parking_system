@extends('layouts.main-app')

@include('petugas.dashboard.css.dashboard-style')

@section('content')
    <div class="container">
        <div class="row mb-2">
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-header bg-primary-subtle py-3">
                        <h4 class="fw-bold mb-1 text-dark">Monitoring Parkir</h4>
                        <p class="text-muted small mb-0">Sistem Parkir Realtime</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center py-3 px-3">
                            <div class="ms-3 flex-grow-1">
                                <div class="text-muted small">QR validasi gate masuk.</div>
                                <div class="fw-semibold text-dark mb-2"></div>

                                <a href="{{ url('/qr-show') }}" target="_blank"
                                    class="small text-primary text-decoration-none d-block mb-1">
                                    <i class="bi bi-display me-1"></i>
                                    Klik di sini untuk tampilkan QR di monitor / TV
                                </a>

                                <div class="small text-muted">
                                    URL: <span class="text-dark">{{ url('/qr-show') }}</span>
                                </div>
                            </div>

                            <div class="text-center">
                                <div class="fw-semibold mb-2">Pintu Masuk</div>
                                <div class="p-2 bg-white border rounded-3 shadow-sm d-inline-block">
                                    <div id="qr-container" style="width:90px;height:90px;"
                                        class="d-flex justify-content-center align-items-center">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                    </div>
                                </div>
                                <div id="qr-code-text" class="small text-muted mt-2"
                                    style="max-width:120px; word-break: break-all;">
                                    Memuat kode...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchPlat" class="form-control border-start-0"
                        placeholder="Cari Plat Nomor atau Kode Slot..." onkeyup="handleSearch()">
                </div>
            </div>
        </div>

        <div id="kantung-wrapper" class="mb-2">
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-2">Menghubungkan ke MQTT & Sinkronisasi Data...</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-pill me-2" style="width: 4px; height: 20px;"></div>
                    <h5 class="fw-bold text-dark fs-6 mb-0">DAFTAR KENDARAAN SEDANG PARKIR</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="py-3 text-uppercase small fw-bold">Plat Nomor</th>
                                <th class="py-3 text-uppercase small fw-bold">Jenis</th>
                                <th class="py-3 text-uppercase small fw-bold">Jam Masuk</th>
                                <th class="py-3 text-uppercase small fw-bold">Durasi (Menit)</th>
                                <th class="py-3 text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-transactions-body">
                            <tr>
                                <td colspan="5" class="py-5">Memproses data transaksi...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('petugas.dashboard.partials.modal-dashboard-transaksi')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

    <script>
        const SLOT_URL = "{{ route('petugas.slots') }}";
        const QR_URL = "{{ url('/ajax-qr-show') }}";

        let localAreas = [];
        let activeTransactions = [];
        let selectedTransaksi = null;
        let isFetching = false;
        let processingSlots = new Set();

        async function fetchJSON(url) {
            const res = await fetch(`${url}${url.includes('?') ? '&' : '?'}t=${Date.now()}`, {
                cache: "no-store"
            });
            return await res.json();
        }

        async function syncDatabase() {
            if (isFetching) return;
            isFetching = true;
            try {
                const data = await fetchJSON(SLOT_URL);
                localAreas = data.areas || [];
                activeTransactions = data.active_transactions || [];
                processingSlots.clear();
                renderSlots();
                renderTable();
            } catch (e) {
                console.error("AJAX Error", e);
            } finally {
                isFetching = false;
            }
        }

        function listenMQTT() {
            const brokerUrl = 'wss://broker.hivemq.com:8884/mqtt';
            const options = {
                clientId: 'web_dashboard_' + Math.random().toString(16).substr(2, 8),
                clean: true,
                connectTimeout: 5000,
            };

            const client = mqtt.connect(brokerUrl, options);

            client.on('connect', () => {
                client.subscribe('smartparking/univ123/slots');
            });

            client.on('message', (topic, message) => {
                try {
                    const payload = JSON.parse(message.toString());
                    updateSlotsFromMQTT(payload);
                } catch (e) { }
            });
        }

        function updateSlotsFromMQTT(mqttData) {
            let changed = false;

            if (!mqttData || typeof mqttData !== 'object') return;

            const slots = [];

            for (const [key, value] of Object.entries(mqttData)) {
                if (key === 'command' || key === 'time') continue;

                if (value && typeof value === 'object') {
                    slots.push({
                        kode: key.replace('slot_', '').toUpperCase(),
                        status: normalizeStatus(value.status),
                        jenis: (value.jenis || 'mobil').toLowerCase()
                    });
                }
            }

            if (slots.length === 0) return;

            localAreas.forEach(area => {
                if (!Array.isArray(area.slot)) return;

                area.slot.forEach(s => {
                    const incoming = slots.find(x => x.kode === s.kode);

                    if (incoming) {
                        if (s.status !== incoming.status || s.jenis !== incoming.jenis) {
                            s.status = incoming.status;
                            s.jenis = incoming.jenis;
                            changed = true;
                        }
                    }
                });
            });

            if (changed) {
                renderSlots();

                clearTimeout(window._slotSync);
                window._slotSync = setTimeout(() => {
                    syncDatabase();
                }, 1000);
            }
        } async function loadQR() {
            try {
                const data = await fetchJSON(QR_URL);
                if (data.success) {
                    const container = document.getElementById("qr-container");
                    const textDisplay = document.getElementById("qr-code-text");
                    if (textDisplay.innerText !== (data.kode ?? '-')) {
                        container.innerHTML = data.svg;
                        textDisplay.innerText = data.kode ?? '-';
                    }
                }
            } catch (e) { }
        }

        function handleSearch() {
            renderSlots();
            renderTable();
        }

        function renderSlots() {
            const wrapper = document.getElementById('kantung-wrapper');
            const search = document.getElementById('searchPlat').value.toUpperCase();

            if (localAreas.length === 0) {
                wrapper.innerHTML = `<div class="text-center text-muted py-5">Memuat data...</div>`;
                return;
            }

            let html = '';
            localAreas.forEach(k => {
                let slotHtml = '';
                k.slot.forEach(s => {
                    const nStatus = normalizeStatus(s.status);

                    const platNomor = (s.plat ?? '').toUpperCase();
                    const kodeSlot = (s.kode ?? '').toUpperCase();
                    if (search && !platNomor.includes(search) && !kodeSlot.includes(search)) return;

                    const statusClass = nStatus === 'terisi' ? 'terisi' : 'kosong';
                    const icon = s.jenis === 'motor' ? 'motorcycle' : 'directions_car';
                    const isProcessing = processingSlots.has(s.kode);

                    let displayText = nStatus === 'terisi'
                        ? `<strong class="d-block" style="font-size:0.75rem">${platNomor || 'TERISI'}</strong>`
                        : `<span class="small" style="font-size:0.7rem">KOSONG</span>`;

                    slotHtml += `
                                <div class="slot ${statusClass} ${isProcessing ? 'opacity-50 border-primary' : ''}">
                                    <span class="kode-slot">${s.kode}</span>
                                    <span class="material-icons">${icon}</span>
                                    <div class="plat-info mt-1">
                                        ${isProcessing ? `<div class="spinner-border spinner-border-sm text-primary"></div>` : displayText}
                                    </div>
                                </div>`;
                });

                html += `
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-primary">${k.nama}</h6>
                                    <span class="badge bg-light text-dark border">Kapasitas: ${k.kapasitas}</span>
                                </div>
                                <div class="card-body bg-light-subtle">
                                    <div class="slot-grid">${slotHtml}</div>
                                </div>
                            </div>`;
            });
            wrapper.innerHTML = html;
        }
        function renderTable() {
            const tbody = document.getElementById('table-transactions-body');
            const search = document.getElementById('searchPlat').value.toUpperCase();

            if (activeTransactions.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">Tidak ada kendaraan parkir.</td></tr>`;
                return;
            }

            let html = '';
            activeTransactions.forEach(t => {
                if (search && !t.plat.toUpperCase().includes(search)) return;
                html += `
                                            <tr>
                                                <td class="ps-3 fw-bold">${t.plat}</td>
                                                <td><span class="badge bg-secondary-subtle text-dark text-uppercase py-2 px-3">${t.jenis}</span></td>
                                                <td>${t.masuk}</td>
                                                <td><span class="text-primary fw-bold">${t.total_waktu}</span> mnt</td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary px-3 rounded-pill" onclick="openModal(${t.id})">
                                                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                                    </button>
                                                </td>
                                            </tr>`;
            });
            tbody.innerHTML = html;
        }

        function openModal(id) {
            const trx = activeTransactions.find(t => t.id === id);
            if (!trx) return;

            selectedTransaksi = trx;
            document.getElementById('m_plat').innerText = trx.plat;
            document.getElementById('m_jenis').innerText = trx.jenis;
            document.getElementById('m_masuk').innerText = formatTime(trx.waktu_masuk);

            const tarifPerJam = trx.tarif ?? 0;
            document.getElementById('m_tarif').innerText = 'Rp ' + formatRupiah(tarifPerJam);

            const masuk = new Date(trx.waktu_masuk);
            const sekarang = new Date();
            const totalMenit = Math.max(1, Math.floor((sekarang - masuk) / 60000));
            document.getElementById('m_durasi').innerText = formatDurasiTeks(totalMenit);

            const jumlahJam = Math.ceil(totalMenit / 60) || 1;
            document.getElementById('m_total').innerText = 'Rp ' + formatRupiah(jumlahJam * tarifPerJam);

            new bootstrap.Modal(document.getElementById('modalTransaksi')).show();
        }

        async function keluarkanKendaraan() {
            if (!selectedTransaksi) return;
            const confirm = await Swal.fire({
                title: 'Konfirmasi Keluar',
                text: `Proses pembayaran untuk ${selectedTransaksi.plat}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0061ff',
                confirmButtonText: 'Ya, Selesaikan'
            });

            if (!confirm.isConfirmed) return;

            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            try {
                const res = await fetch(`/petugas/keluar/${selectedTransaksi.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });

                const result = await res.json();
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalTransaksi')).hide();
                    await Swal.fire({ icon: 'success', title: 'Berhasil', timer: 1500, showConfirmButton: false });
                    printNota(result.data, result.settings);
                    syncDatabase();
                } else {
                    Swal.fire('Gagal', result.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal koneksi server', 'error');
            }
        }

        function printNota(data, settings = {}) {
            const totalMenit = parseInt(data.total_waktu) || 0;
            const durasiStr = formatDurasiTeks(totalMenit);
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            if (!printWindow) return;

            const html = `
                                        <html>
                                        <head>
                                            <style>
                                                @page { size: 58mm auto; margin: 0; }
                                                body { font-family: 'Courier New', monospace; width: 48mm; margin: 0 auto; padding: 10px 0; font-size: 11px; line-height: 1.2; }
                                                .center { text-align: center; }
                                                .bold { font-weight: bold; }
                                                .line { border-top: 1px dashed #000; margin: 5px 0; }
                                                .flex { display: flex; justify-content: space-between; }
                                                .header-title { font-size: 14px; margin-bottom: 2px; }
                                            </style>
                                        </head>
                                        <body>
                                            <div class="center bold header-title">${(settings.app_name || 'SMART PARKING').toUpperCase()}</div>
                                            <div class="center">${settings.lokasi_parkir || ''}</div>
                                            <div class="center">${settings.alamat || ''}</div>
                                            <div class="center">${settings.kontak || ''}</div>
                                            <div class="line"></div>
                                            <div class="flex"><span>Tgl Keluar:</span> <span>${data.waktu_keluar}</span></div>
                                            <div class="line"></div>
                                            <div class="bold" style="font-size:13px">PLAT: ${data.plat_nomor}</div>
                                            <div class="flex"><span>Masuk:</span> <span>${formatTime(data.waktu_masuk)}</span></div>
                                            <div class="flex"><span>Keluar:</span> <span>${formatTime(data.waktu_keluar)}</span></div>
                                            <div class="flex"><span>Durasi:</span> <span>${durasiStr}</span></div>
                                            <div class="line"></div>
                                            <div class="flex bold" style="font-size:13px"><span>TOTAL:</span> <span>Rp ${formatRupiah(data.total_bayar)}</span></div>
                                            <div class="line"></div>
                                            <div class="center" style="margin-top:10px">TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
                                            <div class="center">SIMPAN STRUK SEBAGAI BUKTI</div>
                                            <script>window.onload = function() { window.print(); setTimeout(() => { window.close(); }, 500); }<\/script>
                                        </body>
                                        </html>`;
            printWindow.document.write(html);
            printWindow.document.close();
        }

        function formatTime(t) {
            if (!t) return '-';
            const date = new Date(t);
            return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
        function formatRupiah(n) { return new Intl.NumberFormat('id-ID').format(n); }
        function formatDurasiTeks(m) { return `${Math.floor(m / 60)}j ${m % 60}m`; }
        function normalizeStatus(status) {
            if (!status) return 'kosong';

            status = status.toLowerCase();

            if (status === 'occupied' || status === 'terisi' || status === '1') {
                return 'terisi';
            }

            if (status === 'empty' || status === 'kosong' || status === '0') {
                return 'kosong';
            }

            return status;
        }
        document.addEventListener('DOMContentLoaded', () => {
            syncDatabase();
            loadQR();
            listenMQTT();
            setInterval(syncDatabase, 4000);
            setInterval(loadQR, 5000);
        });
    </script>
@endpush