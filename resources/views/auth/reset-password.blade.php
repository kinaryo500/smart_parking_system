@extends('layouts.auth-app')

@section('title', 'Reset Password')
@section('subtitle', 'Buat password baru kamu')

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

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
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

<form method="POST" action="{{ route('password.update') }}" id="resetForm">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ request()->email }}">

    <div class="mb-3">
        <label class="form-label">Password Baru</label>

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

    <button type="submit" class="btn btn-primary-custom w-100 mb-3">
        Reset Password
    </button>
</form>

<div class="text-center">
    <a href="{{ route('login') }}" class="link-primary">
        Kembali ke login
    </a>
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

document.getElementById('resetForm').addEventListener('submit', function (e) {

    let pass = document.getElementById('password').value;
    let confirm = document.getElementById('password_confirm').value;

    if (!pass || !confirm) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Data belum lengkap!',
            text: 'Password dan konfirmasi wajib diisi',
            confirmButtonColor: '#0061ff'
        });
        return;
    }

    if (pass !== confirm) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password tidak sama!',
            text: 'Pastikan password dan konfirmasi sama',
            confirmButtonColor: '#0061ff'
        });
        return;
    }

    Swal.fire({
        title: 'Memproses Reset Password...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
});

</script>

@endsection