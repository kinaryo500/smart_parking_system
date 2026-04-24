@extends('layouts.auth-app')

@section('title', 'Lupa Password')
@section('subtitle', 'Masukkan email untuk reset password')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json($errors->first()),
                confirmButtonColor: '#0061ff'
            });
        </script>
    @endif

    @if (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('status')),
                confirmButtonColor: '#0061ff'
            });
        </script>
    @endif

    <form id="forgotForm" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mb-3" id="submitBtn">
            Kirim Link Reset
        </button>
    </form>

    <div class="text-center">
        <a href="{{ route('login') }}" class="link-primary">
            Kembali ke login
        </a>
    </div>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', function (e) {
            let email = document.querySelector('input[name="email"]').value;

            if (!email) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Email kosong!',
                    text: 'Silakan masukkan email terlebih dahulu',
                });
                return;
            }

            Swal.fire({
                title: 'Mengirim Link...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    </script>

@endsection