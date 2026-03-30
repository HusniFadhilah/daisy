<!-- Penawaran Asesmen -->
<a href="{{ route('penawaran') }}" class="nav-link {{ request()->routeIs('penawaran*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-send-check"></i></span>
    <span>Penawaran Asesmen</span>
</a>

<!-- Proses AK -->
<a href="{{ route('validator.borang.index') }}" class="nav-link {{ request()->routeIs('validator.borang*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-clipboard-check"></i></span>
    <span>Validasi Dokumen</span>
</a>
<a href="{{ route('pelaporan.indexDokumen') }}" class="nav-link {{ request()->routeIs('pelaporan.indexDokumen') || request()->routeIs('pelaporan.borang.*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-file-earmark-diff"></i></span>
    <span>Pelaporan Validasi Dokumen</span>
</a>

<!-- Validasi AK -->
@if(Route::has('ak.validasi.index'))
<a href="{{ route('ak.validasi.index') }}" class="nav-link {{ request()->routeIs('ak.validasi*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-patch-check"></i></span>
    <span>Validasi AK</span>
    {{-- @if(isset($penugasanAktif) && $penugasanAktif > 0)
    <span class="badge bg-warning menu-badge">{{ $penugasanAktif }}</span>
    @endif --}}
</a>
@endif

<a href="{{ route('pelaporan.indexValidasiAK') }}" class="nav-link {{ request()->routeIs('pelaporan.indexValidasiAK') || request()->routeIs('pelaporan.validasiAk*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
    <span>Pelaporan AK</span>
</a>
<!-- Validasi AL (if exists) -->
@if(Route::has('al.validasi.index'))
<a href="{{ route('al.validasi.index') }}" class="nav-link {{ request()->routeIs('al.validasi*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-journal-text"></i></span>
    <span>Validasi AL</span>
</a>
@endif
<a href="{{ route('pelaporan.indexAL') }}" class="nav-link {{ request()->routeIs('pelaporan.indexAL') || request()->routeIs('pelaporan.al.*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-journal-text"></i></span>
    <span>Pelaporan AL</span>
</a>

<a href="#" class="nav-link {{ request()->routeIs('ak_banding.validasi*') || request()->routeIs('pelaporan.banding.*') || request()->routeIs('ak_banding.pelaporan*') ? 'active' : '' }}" onclick="toggleSubmenu(event, 'banding-submenu')">
    <span class="menu-icon">
        <i class="bi bi-arrow-repeat"></i>
    </span>
    <span>Banding</span>
</a>

<ul class="submenu nav flex-column" id="banding-submenu">
    <li>
        <a href="{{ route('ak_banding.validasi.index') }}" class="nav-link {{ request()->routeIs('ak_banding.validasi*') ? 'active' : '' }}">
            Validasi AK Banding
        </a>
    </li>
    <li>
        <a href="{{ route('pelaporan.banding.indexValidasiAK') }}" class="nav-link {{ request()->routeIs('pelaporan.banding.indexValidasiAK*') || request()->routeIs('pelaporan.banding.validasiAk*') ? 'active' : '' }}">
            Pelaporan AK Banding
        </a>
    </li>
    <li>
        <a href="{{ route('pelaporan.banding.indexAL') }}" class="nav-link {{ request()->routeIs('pelaporan.banding.indexAL*') || request()->routeIs('pelaporan.banding.al*') ? 'active' : '' }}">
            Pelaporan AL Banding
        </a>
    </li>
</ul>
