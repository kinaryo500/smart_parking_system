<nav class="top-navbar shadow-sm d-flex justify-content-between align-items-center px-3 py-2">

    <button class="btn btn-white border shadow-sm" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="dropdown d-flex align-items-center gap-3">

        <div class="text-end d-none d-sm-block">
            <p class="mb-0 fw-bold small">{{ auth()->user()->name }}</p>
            <p class="mb-0 text-success fw-bold" style="font-size: 10px;">
                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> {{ auth()->user()->role ?? '-' }}
            </p>
        </div>


        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff"
            class="rounded-circle border dropdown-toggle"
            width="40"
            style="cursor:pointer;"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            alt="Avatar">


        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">

            <li class="px-3 py-2">
                <div class="fw-bold">{{ auth()->user()->name }}</div>
                <small class="text-muted text-uppercase">{{ auth()->user()->role }}</small>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="{{ route('profile.index') }}">
                    <i class="bi bi-person"></i> Profil Saya
                </a>
            </li>

            <li>
                <button onclick="confirmLogout()"
                    class="dropdown-item text-danger d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </li>

        </ul>

    </div>
</nav>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Yakin ingin logout?',
        text: 'Sesi kamu akan berakhir dan harus login kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Sedang logout...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });


            let form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('logout') }}";

            let token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>