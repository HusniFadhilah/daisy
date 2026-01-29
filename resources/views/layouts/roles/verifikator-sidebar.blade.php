<!-- Verifikasi AK -->
@if(Route::has('ak.verifikasi.index'))
<a href="{{ route('ak.verifikasi.index') }}" class="nav-link {{ request()->routeIs('ak.verifikasi*') ? 'active' : '' }}">
    <span class="menu-icon">✅</span>
    <span>Verifikasi AK</span>
    @if(isset($penugasanAktif) && $penugasanAktif > 0)
    <span class="badge bg-danger menu-badge">{{ $penugasanAktif }}</span>
    @endif
</a>
@endif

<!-- Verifikasi AL -->
@if(Route::has('al.verifikasi.index'))
<a href="{{ route('al.verifikasi.index') }}" class="nav-link {{ request()->routeIs('al.verifikasi*') ? 'active' : '' }}">
    <span class="menu-icon">✅</span>
    <span>Verifikasi AL</span>
</a>
@endif

<!-- Laporan -->
@if(Route::has('laporan.verifikator'))
<a href="{{ route('laporan.verifikator') }}" class="nav-link {{ request()->routeIs('laporan*') ? 'active' : '' }}">
    <span class="menu-icon">📊</span>
    <span>Laporan</span>
</a>
@endif

<!-- Histori Verifikasi -->
@if(Route::has('histori.verifikasi'))
<a href="{{ route('histori.verifikasi') }}" class="nav-link {{ request()->routeIs('histori*') ? 'active' : '' }}">
    <span class="menu-icon">📜</span>
    <span>Histori Validasi</span>
</a>
@endif

<!-- Statistik -->
@if(Route::has('statistik.verifikator'))
<a href="{{ route('statistik.verifikator') }}" class="nav-link {{ request()->routeIs('statistik*') ? 'active' : '' }}">
    <span class="menu-icon">📈</span>
    <span>Statistik</span>
</a>
@endif

<!-- Pedoman AK -->
@if(Route::has('pedoman.ak'))
<a href="{{ route('pedoman.ak') }}" class="nav-link {{ request()->routeIs('pedoman*') ? 'active' : '' }}">
    <span class="menu-icon">📘</span>
    <span>Pedoman AK</span>
</a>
@endif

<!-- Dokumen Administrasi AL -->
@if(Route::has('dokumen.panduan') || Route::has('dokumen.instrumen') || Route::has('dokumen.template') || Route::has('dokumen.surat'))
<a href="#" class="nav-link" onclick="toggleSubmenu(event, 'dokumen-submenu')">
    <span class="menu-icon">📁</span>
    <span>Dokumen Adm. AL</span>
</a>
<ul class="submenu nav flex-column" id="dokumen-submenu">
    @if(Route::has('dokumen.panduan'))
    <li>
        <a href="{{ route('dokumen.panduan') }}" class="nav-link">
            Panduan Asesmen
        </a>
    </li>
    @endif
    @if(Route::has('dokumen.instrumen'))
    <li>
        <a href="{{ route('dokumen.instrumen') }}" class="nav-link">
            Instrumen Asesmen
        </a>
    </li>
    @endif
    @if(Route::has('dokumen.template'))
    <li>
        <a href="{{ route('dokumen.template') }}" class="nav-link">
            Template Laporan
        </a>
    </li>
    @endif
    @if(Route::has('dokumen.surat'))
    <li>
        <a href="{{ route('dokumen.surat') }}" class="nav-link">
            Surat & Keputusan
        </a>
    </li>
    @endif
</ul>
@endif
