<nav id="sidebar">
    <div class="sidebar-header">
        <h5 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
            <i class="bi bi-p-square-fill"></i> SMART PARKING
        </h5>
    </div>

    <div class="py-3 flex-grow-1">

        @if(auth()->user()->role === 'petugas')

            <a href="{{ route('petugas.dashboard') }}"
                class="nav-link {{ request()->routeIs('petugas.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Monitoring
            </a>

            <a href="{{ route('petugas.riwayat') }}"
                class="nav-link {{ request()->routeIs('petugas.riwayat*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Riwayat Transaksi
            </a>
        @endif

        @if(auth()->user()->role === 'admin')

            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Monitoring
            </a>

            <a href="{{ route('admin.riwayat') }}"
                class="nav-link {{ request()->routeIs('admin.riwayat*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Riwayat Transaksi
            </a>


            <a href="{{ route('admin.tarif.index') }}"
                class="nav-link {{ request()->routeIs('admin.tarif.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Kelola Tarif
            </a>

            <a href="{{ route('admin.petugas.index') }}"
                class="nav-link {{ request()->routeIs('admin.petugas.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Kelola Petugas
            </a>

            <a href="{{ route('admin.user.index') }}"
                class="nav-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Kelola User
            </a>

            <a href="{{ route('admin.setting.index') }}"
                class="nav-link {{ request()->routeIs('admin.setting.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Pengaturan
            </a>

        @endif

    </div>

    <div class="p-3 border-top">
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button" onclick="confirmLogout()"
                class="nav-link text-danger p-0 border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-left"></i> Logout Sistem
            </button>
        </form>
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

                document.getElementById('logoutForm').submit();
            }
        });
    }
</script>