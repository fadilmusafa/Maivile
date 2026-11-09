@php
    $authUser = auth()->user();

    // Menu hanya memuat halaman yang benar-benar sudah ada. Menu baru ditambahkan di tahap berikutnya.
    if ($authUser->isAdmin()) {
        $menu = [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill'],
            ['route' => 'admin.transactions.index', 'match' => 'admin.transactions.*', 'label' => 'Antrean Pengajuan', 'icon' => 'bi-inbox-fill'],
            ['route' => 'admin.histories.index', 'match' => 'admin.histories.*', 'label' => 'Riwayat Status', 'icon' => 'bi-clock-history'],
            ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'label' => 'Laporan', 'icon' => 'bi-file-earmark-bar-graph-fill'],
            ['route' => 'admin.documents.index', 'match' => 'admin.documents.*', 'label' => 'Repositori Dokumen', 'icon' => 'bi-folder-fill'],
            ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Pengguna', 'icon' => 'bi-people-fill'],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Kategori Dokumen', 'icon' => 'bi-tags-fill'],
            ['route' => 'admin.service-types.index', 'match' => 'admin.service-types.*', 'label' => 'Jenis Layanan', 'icon' => 'bi-ui-checks-grid'],
        ];
    } else {
        $menu = [
            ['route' => 'student.dashboard', 'match' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill'],
            ['route' => 'student.documents.index', 'match' => 'student.documents.*', 'label' => 'Repositori Dokumen', 'icon' => 'bi-folder-fill'],
            ['route' => 'student.loan.create', 'match' => 'student.loan.*', 'label' => 'Peminjaman Rapor', 'icon' => 'bi-journal-bookmark-fill'],
            ['route' => 'student.legalization.create', 'match' => 'student.legalization.*', 'label' => 'Legalisir', 'icon' => 'bi-patch-check-fill'],
            ['route' => 'student.diploma.create', 'match' => 'student.diploma.*', 'label' => 'Pengambilan Ijazah', 'icon' => 'bi-mortarboard-fill'],
            ['route' => 'student.transactions.index', 'match' => 'student.transactions.*', 'label' => 'Riwayat Pengajuan', 'icon' => 'bi-clock-history'],
        ];
    }

    $menu[] = ['route' => 'profile.edit', 'match' => 'profile.*', 'label' => 'Profil', 'icon' => 'bi-person-circle'];
@endphp

<aside class="offcanvas-lg offcanvas-start mv-sidebar" tabindex="-1" id="mvSidebar" aria-labelledby="mvSidebarLabel">
    <div class="offcanvas-header">
        <span class="mv-logo" id="mvSidebarLabel">Maivile</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#mvSidebar" aria-label="Tutup menu"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3">
        <a href="{{ route('home') }}" class="mv-logo d-none d-lg-block px-2 pt-2 mb-4">Maivile</a>

        <nav aria-label="Menu utama">
            <ul class="mv-nav">
                @foreach ($menu as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <li>
                        <a href="{{ route($item['route']) }}" class="{{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mt-auto pt-4 border-top border-secondary-subtle">
            <div class="px-2 mb-3">
                <div class="fw-semibold text-truncate">{{ $authUser->name }}</div>
                <small class="text-secondary">{{ $authUser->roleLabel() }}</small>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</aside>
