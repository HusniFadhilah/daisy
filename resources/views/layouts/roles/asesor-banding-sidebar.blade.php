<!-- Penawaran Asesmen -->
<a href="{{ route('penawaran') }}" class="nav-link {{ request()->routeIs('penawaran*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-send-check"></i></span>
    <span>Penawaran Asesmen</span>
    @if(isset($penawaranBaru) && $penawaranBaru > 0)
    <span class="badge bg-danger menu-badge">{{ $penawaranBaru }}</span>
    @endif
</a>

{{-- <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'penawaran-submenu')">
    <span class="menu-icon">📨</span>
    <span>Penawaran Asesmen</span>
    <span class="badge bg-danger menu-badge">{{ $penawaranBaru ?? 2 }}</span>
</a>
<ul class="submenu nav flex-column" id="penawaran-submenu">
    <li>
        <a href="{{ route('asesmen.index') }}" class="nav-link">
            Daftar Penawaran Baru
        </a>
    </li>
    <li>
        <a href="{{ route('penawaran.riwayat') }}" class="nav-link">
            Riwayat Penawaran
        </a>
    </li>
</ul> --}}

<!-- Penugasan Asesmen -->
{{-- <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'penugasan-submenu')">
    <span class="menu-icon">📋</span>
    <span>Penugasan Asesmen</span>
    <span class="badge bg-success menu-badge">{{ $penugasanAktif ?? 1 }}</span>
</a>
<ul class="submenu nav flex-column" id="penugasan-submenu">
    <li>
        <a href="{{ route('penugasan.aktif') }}" class="nav-link">
            Penugasan Aktif
        </a>
    </li>
    <li>
        <a href="{{ route('penugasan.selesai') }}" class="nav-link">
            Penugasan Selesai
        </a>
    </li>
    <li>
        <a href="{{ route('penugasan.riwayat') }}" class="nav-link">
            Riwayat Penugasan
        </a>
    </li>
</ul> --}}

<!-- Proses AK -->
@if(Route::has('ak_banding.berkas'))
<a href="{{ route('ak_banding.berkas') }}" class="nav-link {{ request()->routeIs('ak_banding*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-patch-check"></i></span>
    <span>Proses AK Banding</span>
    {{-- @if(isset($prosesAK) && $prosesAK > 0)
    <span class="badge bg-warning menu-badge">{{ $prosesAK }}</span>
    @endif --}}
</a>
@endif

<!-- Proses AL -->
@if(Route::has('al_banding.berkas'))
<a href="{{ route('al_banding.berkas') }}" class="nav-link {{ request()->routeIs('al_banding*') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-building"></i></span>
    <span>Proses AL Banding</span>
</a>
@endif

<!-- Penugasan Banding -->
{{-- @if(Route::has('banding'))
<a href="{{ route('banding') }}" class="nav-link {{ request()->routeIs('banding') ? 'active' : '' }}">
<span class="menu-icon">🤝</span>
<span>Penugasan Banding</span>
</a>
@endif --}}

<!-- Pedoman AK -->
{{-- @if(Route::has('pedoman'))
<a href="{{ route('pedoman') }}" class="nav-link {{ request()->routeIs('pedoman') ? 'active' : '' }}">
<span class="menu-icon"><i class="bi bi-question-circle"></i></span>
<span>Pedoman AK Banding</span>
</a>
<a href="{{ route('pedoman') }}" class="nav-link {{ request()->routeIs('pedoman') ? 'active' : '' }}">
    <span class="menu-icon"><i class="bi bi-question-diamond"></i></span>
    <span>Pedoman AL Banding</span>
</a>
@endif --}}

<!-- Dokumen Adm. AL -->
{{-- @if(Route::has('dokumen.panduan') || Route::has('dokumen.instrumen'))
<a href="#" class="nav-link" onclick="toggleSubmenu(event, 'dokumen-submenu')">
    <span class="menu-icon"><i class="bi bi-journal-text"></i></span>
    <span>Dokumen Adm. AL Banding</span>
</a>
<ul class="submenu nav flex-column" id="dokumen-submenu">
    @if(Route::has('dokumen.panduan'))
    <li>
        <a href="{{ route('dokumen.panduan') }}" class="nav-link">
Panduan Asesmen Banding
</a>
</li>
@endif
@if(Route::has('dokumen.instrumen'))
<li>
    <a href="{{ route('dokumen.instrumen') }}" class="nav-link">
        Instrumen Akreditasi
    </a>
</li>
@endif
@if(Route::has('dokumen.template'))
<li>
    <a href="{{ route('dokumen.template') }}" class="nav-link">
        Templat Penilaian
    </a>
</li>
@endif
@if(Route::has('dokumen.surat'))
<li>
    <a href="{{ route('dokumen.surat') }}" class="nav-link">
        Surat Tugas
    </a>
</li>
@endif
</ul>
@endif --}}
