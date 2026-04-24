@extends('layouts.auth-app')

@section('title', 'Register')
@section('subtitle', 'Daftar akun baru')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Daftar!',
                text: @json($errors->first()),
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

        .strength-bar {
            height: 6px;
            border-radius: 5px;
            background: #e9ecef;
            overflow: hidden;
            margin-top: 6px;
        }

        .strength-bar-inner {
            height: 100%;
            width: 0%;
            transition: 0.3s;
        }

        .strength-text {
            font-size: 12px;
            margin-top: 4px;
        }
    </style>

    <form action="{{ route('register.process') }}" method="POST" id="registerForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">No. HP</label>
            <input type="text" name="no_hp" class="form-control" required value="{{ old('no_hp') }}">
        </div>

        
        <div class="mb-3">
            <label class="form-label">Password</label>

            <div class="password-wrapper">
                <input type="password" name="password" id="password" class="form-control" required>

                <span class="toggle-password" onclick="togglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </span>
            </div>

            <div class="strength-bar">
                <div id="strengthBar" class="strength-bar-inner"></div>
            </div>
            <div id="strengthText" class="strength-text"></div>
        </div>


        <div class="mb-4">
            <label class="form-label">Konfirmasi Password</label>

            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="password_confirm" class="form-control" required>

                <span class="toggle-password" onclick="togglePassword('password_confirm', this)">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100">
            Daftar
        </button>
    </form>

    <div class="text-center mt-3">
        <small class="text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="link-primary">Login</a>
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


        const password = document.getElementById('password');
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');

        password.addEventListener('input', function () {
            const val = password.value;
            let strength = 0;

            if (val.length >= 6) strength++;
            if (val.match(/[A-Z]/)) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^A-Za-z0-9]/)) strength++;

            let width = (strength / 4) * 100;
            bar.style.width = width + "%";

            if (strength <= 1) {
                bar.style.background = "red";
                text.innerHTML = "Lemah";
                text.style.color = "red";
            }
            else if (strength == 2 || strength == 3) {
                bar.style.background = "orange";
                text.innerHTML = "Sedang";
                text.style.color = "orange";
            }
            else {
                bar.style.background = "green";
                text.innerHTML = "Kuat";
                text.style.color = "green";
            }
        });

        document.getElementById('registerForm').addEventListener('submit', function (e) {

            let name = document.querySelector('input[name="name"]').value;
            let email = document.querySelector('input[name="email"]').value;
            let hp = document.querySelector('input[name="no_hp"]').value;
            let pass = document.getElementById('password').value;
            let confirm = document.getElementById('password_confirm').value;

            if (!name || !email || !hp || !pass || !confirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap!',
                    text: 'Semua field wajib diisi',
                    confirmButtonColor: '#0061ff'
                });
                return;
            }

            if (pass !== confirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password tidak cocok!',
                    text: 'Pastikan password dan konfirmasi sama',
                    confirmButtonColor: '#0061ff'
                });
                return;
            }

            Swal.fire({
                title: 'Membuat akun...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        });

    </script>

@endsection