@extends('layouts.main-app')

@section('content')
<div class="container">

    {{-- HEADER --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle">
                        <div>
                            <h4 class="fw-bold mb-1 text-primary-emphasis">Profil Saya</h4>
                            <p class="text-primary-emphasis opacity-75 mb-0">
                                Informasi akun pengguna
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 {{-- FORM --}}
<div class="row g-3">

    {{-- AVATAR --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">

                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff"
                    class="rounded-circle border shadow-sm mb-3"
                    width="120"
                    height="120"
                    style="object-fit: cover;">

                <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>

                <span class="badge bg-primary text-uppercase px-3 py-2">
                    {{ auth()->user()->role }}
                </span>

            </div>
        </div>
    </div>

    {{-- FORM --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">

                {{-- INPUT --}}
                <div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-semibold">Nama</label>
                            <input type="text" id="name" class="form-control"
                                value="{{ auth()->user()->name }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Email</label>
                            <input type="email" id="email" class="form-control"
                                value="{{ auth()->user()->email }}" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-semibold">No HP</label>
                            <input type="text" id="no_hp" class="form-control"
                                value="{{ auth()->user()->no_hp }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Status</label>
                            <input type="text" class="form-control"
                                value="{{ auth()->user()->is_active ? 'Aktif' : 'Nonaktif' }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold">Bergabung</label>
                        <input type="text" class="form-control"
                            value="{{ auth()->user()->created_at->format('d M Y') }}" disabled>
                    </div>
                </div>

                {{-- ACTION --}}
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <button onclick="openPasswordModal()" class="btn btn-warning">
                        <i class="bi bi-lock me-1"></i> Ganti Password
                    </button>

                    <div class="d-flex gap-2">
                        <button id="btnCancel" onclick="cancelEdit()" class="btn btn-secondary d-none">
                            Batal
                        </button>

                        <button id="btnEdit" onclick="toggleEdit()" class="btn btn-primary">
                            Edit
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<style>
.password-wrapper { position: relative; }
.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}
.strength-bar { height:5px; background:#eee; margin-top:5px; }
.strength-bar-inner { height:100%; width:0%; transition:0.3s; }
.strength-text { font-size:12px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const UPDATE_API = "{{ route('profile.update') }}";
const PASSWORD_API = "{{ route('profile.change-password') }}";


let editMode = false;

function toggleEdit() {
    editMode = !editMode;

    document.querySelectorAll('#name,#email,#no_hp').forEach(i=>{
        i.disabled = !editMode;
    });

    let btn = document.getElementById('btnEdit');
    let cancel = document.getElementById('btnCancel');

    if(editMode){
        btn.innerHTML = 'Simpan';
        btn.classList.replace('btn-primary','btn-success');
        btn.onclick = confirmSave;
        cancel.classList.remove('d-none');
    }else{
        btn.innerHTML = 'Edit';
        btn.classList.replace('btn-success','btn-primary');
        btn.onclick = toggleEdit;
        cancel.classList.add('d-none');
    }
}

function cancelEdit(){ location.reload(); }

function confirmSave(){
    Swal.fire({
        title:'Simpan perubahan?',
        icon:'question',
        showCancelButton:true
    }).then(r=>{
        if(r.isConfirmed) saveProfile();
    });
}

async function saveProfile() {

    let original = {
        name: "{{ auth()->user()->name }}",
        email: "{{ auth()->user()->email }}",
        no_hp: "{{ auth()->user()->no_hp }}"
    };

    let current = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        no_hp: document.getElementById('no_hp').value
    };

    let data = {};

    Object.keys(current).forEach(key => {
        if (current[key] !== original[key]) {
            data[key] = current[key];
        }
    });

    if (Object.keys(data).length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak ada perubahan',
            text: 'Data tidak ada yang diubah'
        });
        return;
    }

    Swal.fire({
        title: 'Menyimpan Perubahan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        let res = await fetch(UPDATE_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        let result = await res.json();

        if (!res.ok) {
            showError(result.errors);
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: result.message
        });

        toggleEdit();

    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan server'
        });
    }
}


function openPasswordModal(){
    new bootstrap.Modal(document.getElementById('passwordModal')).show();
}

function togglePassword(id,el){
    let input=document.getElementById(id);
    let icon=el.querySelector('i');

    if(input.type==='password'){
        input.type='text';
        icon.classList.replace('bi-eye','bi-eye-slash');
    }else{
        input.type='password';
        icon.classList.replace('bi-eye-slash','bi-eye');
    }
}


new_password.addEventListener('input',function(){
    let val=this.value;
    let s=0;

    if(val.length>=6) s++;
    if(/[A-Z]/.test(val)) s++;
    if(/[0-9]/.test(val)) s++;
    if(/[^A-Za-z0-9]/.test(val)) s++;

    let width=(s/4)*100;
    strengthBar.style.width=width+'%';

    if(s<=1){
        strengthBar.style.background='red';
        strengthText.innerHTML='Lemah';
    }else if(s<=3){
        strengthBar.style.background='orange';
        strengthText.innerHTML='Sedang';
    }else{
        strengthBar.style.background='green';
        strengthText.innerHTML='Kuat';
    }
});

 async function changePassword() {

    let oldPass = old_password.value;
    let newPass = new_password.value;
    let confirmPass = confirm_password.value;

    if (!oldPass || !newPass || !confirmPass) {
        Swal.fire({
            icon: 'warning',
            title: 'Form belum lengkap',
            text: 'Semua field password wajib diisi'
        });
        return;
    }

    if (newPass.length < 6) {
        Swal.fire({
            icon: 'warning',
            title: 'Password lemah',
            text: 'Minimal 6 karakter'
        });
        return;
    }

    if (newPass !== confirmPass) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak cocok',
            text: 'Konfirmasi password tidak sama'
        });
        return;
    }

    Swal.fire({
        title: 'Mengubah Password...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        let res = await fetch(PASSWORD_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                old_password: oldPass,
                new_password: newPass,
                confirm_password: confirmPass
            })
        });

        let result = await res.json();

        if (!res.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: Object.values(result.errors).join('<br>')
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: result.message
        });

        bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();

    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Server bermasalah'
        });
    }
}
</script>

@endsection