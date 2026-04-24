@extends('layouts.main-app')

@section('content')
    <div class="container">
        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm rounded overflow-hidden">
                <div class="card-body p-0 fs-6">
                    <div class="d-flex justify-content-between align-items-center px-3  py-2 bg-primary-subtle">
                        <div>
                            <h4 class="fw-bold mb-1 text-info-emphasis">Kelola Petugas</h4>
                            <p class="text-info-emphasis opacity-75 mb-0">Manajemen data petugas secara real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Tambah Petugas Baru</h5>
                    </div>
                    <div class="card-body p-4">

                        <form id="formCreatePetugas" action="{{ route('admin.petugas.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Contoh: Budi Santoso">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="budi@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor HP</label>
                                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                                    value="{{ old('no_hp') }}" placeholder="0812xxxx">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Minimal 6 karakter">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" onclick="history.back()" class="btn btn-light px-4">
                                    Batal
                                </button>

                                <button type="submit" id="btnSubmit" class="btn btn-primary px-4">
                                    <span class="spinner-border spinner-border-sm d-none" id="btnSpinner"></span>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: "{{ session('error') }}",
                confirmButtonColor: '#0d6efd'
            });
        @endif

         
        const form = document.getElementById('formCreatePetugas');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnSpinner = document.getElementById('btnSpinner');

        form.addEventListener('submit', function () {
            btnSubmit.disabled = true;
            btnSpinner.classList.remove('d-none');
            Swal.fire({
                title: 'Sedang Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    </script>
@endsection