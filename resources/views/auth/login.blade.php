@extends('layouts.auth-app')

@section('title', 'Login')
@section('subtitle', 'Masuk ke sistem')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: @json(session('error')),
                confirmButtonColor: '#0061ff'
            });
        </script>
    @endif

    <style>
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 18px;
        }
    </style>

    <form action="{{ route('login.process') }}" method="POST" id="loginForm">
        @csrf


        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>


        <div class="mb-2">
            <label class="form-label">Password</label>

            <div class="password-wrapper">
                <input type="password" name="password" id="password" class="form-control" required>

                <span class="toggle-password" onclick="togglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
        </div>

        <div class="mb-3 text-end">
            <a href="{{ route('password.request') }}" class="link-primary small">
                Lupa password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mb-3">
            Masuk
        </button>
    </form>

    <div class="text-center">
        <small class="text-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="link-primary">Daftar sekarang</a>
        </small>
    </div>

    <script>


        function togglePassword(id, el) {
            const input = document.getElementById(id);
            const icon = el.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }


        document.getElementById('loginForm').addEventListener('submit', function (e) {

            let email = document.querySelector('input[name="email"]').value;
            let password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap!',
                    text: 'Email dan password wajib diisi',
                    confirmButtonColor: '#0061ff'
                });
                return;
            }

            Swal.fire({
                title: 'Sedang Login...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        });

    </script>

@endsection