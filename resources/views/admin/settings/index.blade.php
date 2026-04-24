@extends('layouts.main-app')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="container">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle">
                            <div>
                                <h4 class="fw-bold mb-1 text-primary-emphasis">Pengaturan Sistem</h4>
                                <p class="text-primary-emphasis opacity-75 mb-0">
                                    Konfigurasi Smart Parking
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded p-4">
            <div id="errorBox" class="alert alert-danger d-none"></div>
            
            <div class="row">
                <div class="col-md-6 d-flex flex-column">
                    <div class="mb-3">
                        <label class="fw-semibold">Nama Aplikasi</label>
                        <input type="text" id="app_name" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold">Lokasi Parkir</label>
                        <input type="text" id="lokasi_parkir" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold">Alamat</label>
                        <input type="text" id="alamat" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold">Kontak</label>
                        <input type="text" id="kontak" class="form-control" disabled>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-semibold">Latitude</label>
                            <input type="text" id="latitude" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold">Longitude</label>
                            <input type="text" id="longitude" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button id="btnCancel" onclick="cancelEdit()" class="btn btn-secondary d-none px-4">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </button>

                        <button id="btnEdit" onclick="toggleEdit()" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-pencil me-2"></i>Edit
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="fw-semibold">Lokasi Map</label>
                    <div id="map" style="height:410px; border-radius:12px; border: 1px solid #dee2e6; z-index: 1;"></div>
                </div>

            </div> 
        </div>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const GET_API = "{{ route('admin.setting.data') }}";
        const SAVE_API = "{{ route('admin.setting.update') }}";

        let map;
        let marker;
        let editMode = false;

        async function loadSetting() {
            try {
                const res = await fetch(GET_API);
                const result = await res.json();
                let data = result.data;

                app_name.value = data.app_name ?? '';
                lokasi_parkir.value = data.lokasi_parkir ?? '';
                alamat.value = data.alamat ?? '';
                kontak.value = data.kontak ?? '';
                latitude.value = data.latitude ?? '';
                longitude.value = data.longitude ?? '';

                initMap(data.latitude, data.longitude);
            } catch (e) {
                console.error("Gagal load data:", e);
                initMap(); 
            }
        }

        function initMap(lat, lng) {
            lat = parseFloat(lat) || -8.5203;
            lng = parseFloat(lng) || 140.4185;

            if (map != undefined) { map.remove(); }

            map = L.map('map').setView([lat, lng], 15);

            const road = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            road.addTo(map);
            L.control.layers({ "Jalan": road, "Satelit": satellite }).addTo(map);

            marker = L.marker([lat, lng], { draggable: false }).addTo(map);

            map.on('click', function (e) {
                if (!editMode) return;
                let newLat = e.latlng.lat;
                let newLng = e.latlng.lng;
                marker.setLatLng([newLat, newLng]);
                latitude.value = newLat;
                longitude.value = newLng;
            });

            setTimeout(() => { map.invalidateSize(); }, 400);
        }

        function toggleEdit() {
            editMode = !editMode;
            
            document.querySelectorAll('input').forEach(i => {
                i.disabled = !editMode;
            });
            if (marker.dragging) {
                editMode ? marker.dragging.enable() : marker.dragging.disable();
            }

            let btn = document.getElementById('btnEdit');
            let btnCancel = document.getElementById('btnCancel');

            if (editMode) {
                btn.innerHTML = '<i class="bi bi-save me-2"></i>Simpan';
                btn.classList.replace('btn-primary', 'btn-success');
                btn.onclick = confirmSave;
                
                btnCancel.classList.remove('d-none');
            } else {
                btn.innerHTML = '<i class="bi bi-pencil me-2"></i>Edit';
                btn.classList.replace('btn-success', 'btn-primary');
                btn.onclick = toggleEdit;
                
                btnCancel.classList.add('d-none');
            }
        }


        function cancelEdit() {
            loadSetting();
            editMode = true; 
            toggleEdit();
        }

        function confirmSave() {
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: 'Pastikan data lokasi sudah benar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) { saveSetting(); }
            });
        }

        async function saveSetting() {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let data = {
                app_name: app_name.value,
                lokasi_parkir: lokasi_parkir.value,
                alamat: alamat.value,
                kontak: kontak.value,
                latitude: latitude.value,
                longitude: longitude.value
            };

            try {
                let res = await fetch(SAVE_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
                let result = await res.json();
                if (result.success) {
                    Swal.fire('Berhasil', 'Pengaturan berhasil diperbarui', 'success');
                    toggleEdit();
                } else { throw new Error(); }
            } catch (e) {
                Swal.fire('Gagal', 'Gagal menghubungi server', 'error');
            }
        }

        document.addEventListener("DOMContentLoaded", loadSetting);
    </script>
@endsection