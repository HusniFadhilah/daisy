<!-- Penawaran Asesmen -->
<a href="{{ route('penawaran') }}" class="nav-link {{ request()->routeIs('penawaran*') ? 'active' : '' }}">
    <span class="menu-icon">✉️</span> <!-- ganti dari 📨 ke ✉️ -->
    <span>Penawaran Asesmen</span>
</a>

<!-- Proses AK -->
<a href="{{ route('validator.borang.index') }}" class="nav-link {{ request()->routeIs('validator.borang*') ? 'active' : '' }}">
    <span class="menu-icon">🖊️</span>
    <span>Validasi Dokumen</span>
</a>
<a href="{{ route('pelaporan.indexDokumen') }}" class="nav-link {{ request()->routeIs('pelaporan.indexDokumen') || request()->routeIs('pelaporan.borang.*') ? 'active' : '' }}">
    <span class="menu-icon">📋</span>
    <span>Pelaporan Validasi Dokumen</span>
</a>

<!-- Validasi AK -->
@if(Route::has('ak.validasi.index'))
<a href="{{ route('ak.validasi.index') }}" class="nav-link {{ request()->routeIs('ak*') ? 'active' : '' }}">
    <span class="menu-icon">☑️</span>
    <span>Validasi AK</span>
    {{-- @if(isset($penugasanAktif) && $penugasanAktif > 0)
    <span class="badge bg-warning menu-badge">{{ $penugasanAktif }}</span>
    @endif --}}
</a>
@endif

<a href="{{ route('pelaporan.indexValidasiAK') }}" class="nav-link {{ request()->routeIs('pelaporan.indexValidasiAK') ? 'active' : '' }}">
    <span class="menu-icon">📝</span>
    <span>Pelaporan AK</span>
</a>
<!-- Validasi AL (if exists) -->
@if(Route::has('al.validasi.index'))
<a href="{{ route('al.validasi.index') }}" class="nav-link {{ request()->routeIs('al.validasi*') ? 'active' : '' }}">
    <span class="menu-icon">✅</span>
    <span>Validasi AL</span>
</a>
@endif
<a href="{{ route('pelaporan.indexAL') }}" class="nav-link {{ request()->routeIs('pelaporan.indexAL') ? 'active' : '' }}">
    <span class="menu-icon">📨</span>
    <span>Pelaporan AL</span>
</a>
