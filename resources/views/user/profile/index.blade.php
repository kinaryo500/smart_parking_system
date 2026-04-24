@extends('layouts.user-app')

@section('title', 'Profil Saya | Smart Parking')

@push('styles')
<style>
    .card-profile {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
        background: #fff;
    }

    .profile-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: var(--bs-primary-light, #e0ebff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        color: var(--bs-primary);
        margin: 0 auto 15px auto;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        margin-left: 4px;
    }

    .form-control[readonly] {
        background-color: #f8fafc;
        border-color: #f1f5f9;
        color: #1e293b;
    }

    .input-group-text-password {
        background: transparent;
        border-left: none;
        border-radius: 0 50rem 50rem 0 !important;
        cursor: pointer;
        color: #64748b;
        padding-right: 1.25rem;
    }

    .form-control-password {
        border-right: none;
    }

    .rounded-pill-start {
        border-radius: 50rem 0 0 50rem !important;
    }

    .strength-bar {
        height: 6px;
        border-radius: 5px;
        background: #e9ecef;
        overflow: hidden;
        margin-top: 8px;
        display: none;
    }

    .strength-bar-inner {
        height: 100%;
        width: 0%;
        transition: 0.3s;
    }

    .strength-text {
        font-size: 12px;
        margin-top: 4px;
        font-weight: 500;
    }

    .password-requirements {
        font-size: 0.75rem;
        color: #94a3b8;
        list-style: none;
        padding-left: 5px;
        margin-top: 10px;
    }

    .password-requirements li i {
        margin-right: 5px;
    }

    .requirement-met {
        color: #10b981 !important;
    }

    .text-error {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 5px;
        margin-left: 4px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">

    <div class="text-center mb-4">
        <div class="profile-icon-wrapper">
            <i class="bi bi-person-circle"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
        <p class="text-muted small">Bergabung sejak {{ $user->created_at->translatedFormat('d F Y') }}</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="card card-profile p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-person-badge text-primary me-2 fs-5"></i>
                    <h6 class="fw-bold mb-0">Informasi Dasar</h6>
                </div>
                
                <form id="formProfile" action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" value="{{ old('name', $user->name) }}" readonly>
                        @error('name') <div class="text-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" value="{{ old('email', $user->email) }}" readonly>
                        @error('email') <div class="text-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control rounded-pill px-3" value="{{ old('no_hp', $user->no_hp) }}" readonly>
                        @error('no_hp') <div class="text-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnEditProfile" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profil
                        </button>
                        <button type="submit" id="btnSaveProfile" class="btn btn-primary w-50 d-none rounded-pill fw-bold">
                            Simpan
                        </button>
                        <button type="button" id="btnCancelProfile" class="btn btn-light w-50 d-none rounded-pill fw-bold">Batal</button>
                    </div>
                </form>
            </div>

            <div class="card card-profile p-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-shield-lock text-primary me-2 fs-5"></i>
                    <h6 class="fw-bold mb-0">Keamanan</h6>
                </div>

                <form id="formPassword" action="{{ route('user.profile.update.password') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="input-group">
                            <input type="password" id="current_password" name="current_password" class="form-control rounded-pill-start px-3 form-control-password" placeholder="Masukkan password lama" readonly>
                            <span class="input-group-text input-group-text-password toggle-password" onclick="togglePass('current_password', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        @error('current_password') <div class="text-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" id="newPassword" name="password" class="form-control rounded-pill-start px-3 form-control-password" placeholder="Minimum 8 karakter" readonly>
                            <span class="input-group-text input-group-text-password toggle-password" onclick="togglePass('newPassword', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        
                        <div class="strength-bar" id="barContainer">
                            <div id="strengthBar" class="strength-bar-inner"></div>
                        </div>
                        <div id="strengthText" class="strength-text"></div>

                        <ul class="password-requirements d-none" id="passwordRules">
                            <li id="ruleLength"><i class="bi bi-circle"></i> Minimal 8 karakter</li>
                            <li id="ruleMatch"><i class="bi bi-circle"></i> Konfirmasi password cocok</li>
                        </ul>
                        @error('password') <div class="text-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" id="confirmPassword" name="password_confirmation" class="form-control rounded-pill-start px-3 form-control-password" placeholder="Ulangi password baru" readonly>
                            <span class="input-group-text input-group-text-password toggle-password" onclick="togglePass('confirmPassword', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnEditPassword" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                            <i class="bi bi-key me-2"></i>Ubah Password
                        </button>
                        <button type="submit" id="btnSavePassword" class="btn btn-primary w-50 d-none rounded-pill fw-bold">
                            Update
                        </button>
                        <button type="button" id="btnCancelPassword" class="btn btn-light w-50 d-none rounded-pill fw-bold">Batal</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePass(id, el) {
        const input = document.getElementById(id);
        const icon = el.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = "password";
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const showLoading = (formElement) => {
          const btnSubmit = formElement.querySelector('button[type="submit"]');
            btnSubmit.disabled = true;

            Swal.fire({
                title: 'Sedang Memproses',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { 
                    Swal.showLoading(); 
                }
            });
        };

        const newPassInput = document.getElementById('newPassword');
        const confirmPassInput = document.getElementById('confirmPassword');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const rulesList = document.getElementById('passwordRules');
        const barContainer = document.getElementById('barContainer');

        const validatePassword = () => {
            const val = newPassInput.value;
            const confirmVal = confirmPassInput.value;

            let strength = 0;
            if (val.length >= 8) strength++;
            if (val.match(/[A-Z]/)) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^A-Za-z0-9]/)) strength++;

            let width = (strength / 4) * 100;
            strengthBar.style.width = width + "%";

            if (val === "") {
                strengthBar.style.width = "0%";
                strengthText.innerHTML = "";
            } else if (strength <= 1) {
                strengthBar.style.background = "#ef4444";
                strengthText.innerHTML = "Lemah";
                strengthText.style.color = "#ef4444";
            } else if (strength <= 3) {
                strengthBar.style.background = "#f59e0b";
                strengthText.innerHTML = "Sedang";
                strengthText.style.color = "#f59e0b";
            } else {
                strengthBar.style.background = "#10b981";
                strengthText.innerHTML = "Kuat";
                strengthText.style.color = "#10b981";
            }

            if(val.length >= 8) {
                document.getElementById('ruleLength').classList.add('requirement-met');
                document.getElementById('ruleLength').querySelector('i').classList.replace('bi-circle', 'bi-check-circle-fill');
            } else {
                document.getElementById('ruleLength').classList.remove('requirement-met');
                document.getElementById('ruleLength').querySelector('i').classList.replace('bi-check-circle-fill', 'bi-circle');
            }

            if(confirmVal !== "" && val === confirmVal) {
                document.getElementById('ruleMatch').classList.add('requirement-met');
                document.getElementById('ruleMatch').querySelector('i').classList.replace('bi-circle', 'bi-check-circle-fill');
            } else {
                document.getElementById('ruleMatch').classList.remove('requirement-met');
                document.getElementById('ruleMatch').querySelector('i').classList.replace('bi-check-circle-fill', 'bi-circle');
            }
        };

        newPassInput.addEventListener('input', validatePassword);
        confirmPassInput.addEventListener('input', validatePassword);

        const btnEditProfile = document.getElementById('btnEditProfile');
        const btnSaveProfile = document.getElementById('btnSaveProfile');
        const btnCancelProfile = document.getElementById('btnCancelProfile');
        const inputsProfile = document.querySelectorAll('#formProfile input');

        btnEditProfile.addEventListener('click', () => {
            inputsProfile.forEach(input => input.removeAttribute('readonly'));
            btnSaveProfile.classList.remove('d-none');
            btnCancelProfile.classList.remove('d-none');
            btnEditProfile.classList.add('d-none');
        });

        btnCancelProfile.addEventListener('click', () => {
            inputsProfile.forEach(input => input.setAttribute('readonly', true));
            btnSaveProfile.classList.add('d-none');
            btnCancelProfile.classList.add('d-none');
            btnEditProfile.classList.remove('d-none');
        });

        const btnEditPassword = document.getElementById('btnEditPassword');
        const btnSavePassword = document.getElementById('btnSavePassword');
        const btnCancelPassword = document.getElementById('btnCancelPassword');
        const inputsPassword = document.querySelectorAll('#formPassword input');

        btnEditPassword.addEventListener('click', () => {
            inputsPassword.forEach(input => input.removeAttribute('readonly'));
            btnSavePassword.classList.remove('d-none');
            btnCancelPassword.classList.remove('d-none');
            btnEditPassword.classList.add('d-none');
            rulesList.classList.remove('d-none');
            barContainer.style.display = 'block';
        });

        btnCancelPassword.addEventListener('click', () => {
            inputsPassword.forEach(input => {
                input.setAttribute('readonly', true);
                input.value = '';
            });
            btnSavePassword.classList.add('d-none');
            btnCancelPassword.classList.add('d-none');
            btnEditPassword.classList.remove('d-none');
            rulesList.classList.add('d-none');
            barContainer.style.display = 'none';
            strengthText.innerHTML = "";
            validatePassword();
        });

        document.getElementById('formProfile').addEventListener('submit', function() { showLoading(this); });
        document.getElementById('formPassword').addEventListener('submit', function() { showLoading(this); });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 2500, showConfirmButton: false });
        @endif

        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'Ups!', text: 'Pastikan semua data diisi dengan benar.', confirmButtonColor: '#3085d6' });
        @endif
    });
</script>
@endpush