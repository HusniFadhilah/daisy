@php
$authUser = Auth::user();
$menus = [
[
'no' => 1,
'route' => 'de.pemetaan.index',
'match' => 'de.pemetaan*',
'icon' => 'bi-clock-history',
'label' => 'Pengingat Masa Akreditasi',
],
[
'no' => 2,
'route' => 'de.surat-permohonan',
'match' => 'de.surat-permohonan*',
'icon' => 'bi-envelope-paper',
'label' => 'Permohonan Akreditasi',
],
[
'no' => 3,
'route' => 'de.penerimaan-permohonan',
'match' => 'de.penerimaan-permohonan*',
'icon' => 'bi-send-check',
'label' => 'Penerimaan Permohonan Akreditasi',
],
[
'no' => 4,
'route' => 'de.penyampaian-template',
'match' => 'de.penyampaian-template*',
'icon' => 'bi-file-earmark-text',
'label' => 'Pengiriman Formulir dan Templat Dokumen',
],
[
'no' => 5,
'route' => 'de.validasi-pembayaran',
'match' => 'de.validasi-pembayaran*',
'icon' => 'bi-credit-card',
'label' => 'Validasi Pembayaran',
],
[
'no' => 6,
'route' => 'de.penerimaan-dokumen',
'match' => 'de.penerimaan-dokumen*',
'icon' => 'bi-inbox',
'label' => 'Penerimaan Dokumen',
],
[
'no' => 7,
'route' => 'de.validasi-dokumen',
'match' => 'de.validasi-dokumen*',
'icon' => 'bi-check-circle',
'label' => 'Validasi Dokumen',
],
[
'no' => 8,
'route' => 'de.pelaporan-dokumen',
'match' => 'de.pelaporan-dokumen*',
'icon' => 'bi-bar-chart-line',
'label' => 'Pelaporan Validasi Dokumen',
],
[
'no' => 9,
'route' => 'de.penugasan-ak',
'match' => 'de.penugasan-ak*',
'icon' => 'bi-person-check',
'label' => 'Penugasan Asesor AK',
],
[
'no' => 10,
'route' => 'de.validasi-ak',
'match' => 'de.validasi-ak*',
'icon' => 'bi-patch-check',
'label' => 'Validasi AK',
],
[
'no' => 11,
'route' => 'de.pelaporan-ak',
'match' => 'de.pelaporan-ak*',
'icon' => 'bi-file-earmark-bar-graph',
'label' => 'Pelaporan AK',
],
[
'no' => 12,
'route' => 'de.penugasan-al',
'match' => 'de.penugasan-al*',
'icon' => 'bi-building',
'label' => 'Penugasan Asesor AL',
],
[
'no' => 13,
'route' => 'de.pelaksanaan-al',
'match' => 'de.pelaksanaan-al*',
'icon' => 'bi-geo-alt',
'label' => 'Pelaksanaan AL & Berita Acara',
],
[
'no' => 14,
'route' => 'de.pelaporan-al',
'match' => 'de.pelaporan-al*',
'icon' => 'bi-journal-text',
'label' => 'Pelaporan AL',
],
[
'no' => 15,
'route' => 'de.penyampaian-hasil-akreditasi',
'match' => 'de.penyampaian-hasil-akreditasi*',
'icon' => 'bi-megaphone',
'label' => 'Penyampaian Hasil Akreditasi',
],
[
'no' => 16,
'route' => 'de.masa-sanggah',
'match' => 'de.masa-sanggah*',
'icon' => 'bi-hourglass-split',
'label' => 'Masa Sanggah',
],
[
'no' => 18,
'route' => 'de.penetapan-hasil-akreditasi',
'match' => 'de.penetapan-hasil-akreditasi*',
'icon' => 'bi-award',
'label' => 'Penetapan Hasil Akreditasi',
],
[
'no' => 19,
'route' => 'de.pelaporan-hasil-akreditasi',
'match' => 'de.pelaporan-hasil-akreditasi*',
'icon' => 'bi-graph-up',
'label' => 'Pelaporan Hasil Akreditasi',
],
[
'no' => 20,
'route' => 'de.penyimpanan-arsip-akreditasi',
'match' => 'de.penyimpanan-arsip-akreditasi*',
'icon' => 'bi-archive',
'label' => 'Penyimpanan Arsip Akreditasi',
],
];

$isBandingActive =
request()->routeIs('de.permohonan-banding*')
|| request()->routeIs('de.pelaksanaan-banding*')
|| request()->routeIs('de.pelaporan-banding*');
@endphp

<ul class="nav flex-column">
    {{-- render menu 1-16 --}}
    @foreach ($menus as $menu)
    @if ($menu['no'] <= 16) <li class="nav-item">
        <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['match']) ? 'active' : '' }}">
            <span class="menu-icon">
                <i class="bi {{ $menu['icon'] }}"></i>
            </span>
            <span>{{ $menu['no'] }}. {{ $menu['label'] }}</span>
        </a>
        </li>
        @endif
        @endforeach

        <!-- Banding -->
        <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'banding-submenu')">
            <span class="menu-icon">
                <i class="bi bi-arrow-repeat"></i>
            </span>
            <span>17. Banding</span>
        </a>

        <ul class="submenu nav flex-column" id="banding-submenu">
            <li>
                <a href="{{ route('de.permohonan-banding') }}" class="nav-link">
                    17.a Permohonan Banding
                </a>
            </li>
            <li>
                <a href="{{ route('de.pelaksanaan-banding') }}" class="nav-link">
                    17.b Pelaksanaan Banding
                </a>
            </li>
            <li>
                <a href="{{ route('de.pelaporan-banding') }}" class="nav-link">
                    17.c Pelaporan Banding
                </a>
            </li>
        </ul>

        {{-- render menu 20-22 --}}
        @foreach ($menus as $menu)
        @if ($menu['no'] >= 18)
        <li class="nav-item">
            <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['match']) ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi {{ $menu['icon'] }}"></i>
                </span>
                <span>{{ $menu['no'] }}. {{ $menu['label'] }}</span>
            </a>
        </li>
        @endif
        @endforeach
</ul>

@if(in_array($authUser->role_selected,['super_admin']))
<!-- Penugasan Banding -->
<a href="{{ route('banding') }}" class="nav-link {{ request()->routeIs('banding') ? 'active' : '' }}">
    <span class="menu-icon">🤝</span>
    <span>Penugasan Banding</span>
</a>
<a href="#" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}" onclick="toggleSubmenu(event, 'pengajuan-submenu')">
    <span class="menu-icon">📋</span>
    <span>Master Data</span>
    {{-- <span class="badge bg-success menu-badge">{{ $penugasanAktif ?? 1 }}</span> --}}
</a>
<!-- Pedoman AK -->
<a href="{{ route('pedoman') }}" class="nav-link {{ request()->routeIs('pedoman') ? 'active' : '' }}">
    <span class="menu-icon">❓</span>
    <span>Pedoman AK</span>
</a>

<!-- Dokumen Adm. AL -->
<a href="#" class="nav-link" onclick="toggleSubmenu(event, 'dokumen-submenu')">
    <span class="menu-icon">📁</span>
    <span>Dokumen Adm. AL</span>
</a>
<ul class="submenu nav flex-column" id="dokumen-submenu">
    <li>
        <a href="{{ route('dokumen.panduan') }}" class="nav-link">
            Panduan Asesmen
        </a>
    </li>
    <li>
        <a href="{{ route('dokumen.instrumen') }}" class="nav-link">
            Instrumen Akreditasi
        </a>
    </li>
    <li>
        <a href="{{ route('dokumen.template') }}" class="nav-link">
            Templat Penilaian
        </a>
    </li>
    <li>
        <a href="{{ route('dokumen.surat') }}" class="nav-link">
            Surat Tugas
        </a>
    </li>
</ul>
@endif

@if(in_array($authUser->role_selected,['super_admin']))
<a href="#" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}" onclick="toggleSubmenu(event, 'pengajuan-submenu')">
    <span class="menu-icon">📋</span>
    <span>Master Data</span>
    {{-- <span class="badge bg-success menu-badge">{{ $penugasanAktif ?? 1 }}</span> --}}
</a>
<ul class="submenu nav flex-column" id="pengajuan-submenu">
    <li>
        <!-- Manajemen Pengguna -->
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <span class="menu-icon">👥</span>
            <span>Kelola Pengguna</span>
        </a>
    </li>
    <li>
        <!-- Manajemen Indikator -->
        <a href="#" class="nav-link {{ request()->routeIs('kriteria.*') || request()->routeIs('elemen-standar.*') || request()->routeIs('indikator.*') ? 'active' : '' }}" onclick="toggleSubmenu(event, 'indikator-submenu')">
            <span class="menu-icon">📋</span>
            <span>Manajemen Indikator</span>
        </a>
    </li>
    <li>
        <ul class="submenu nav flex-column" id="indikator-submenu">
            <li>
                <a href="{{ route('kriteria.index') }}" class="nav-link {{ request()->routeIs('kriteria.*') ? 'active' : '' }}">
                    Kriteria
                </a>
            </li>
            <li>
                <a href="{{ route('elemen-standar.index') }}" class="nav-link {{ request()->routeIs('elemen-standar.*') ? 'active' : '' }}">
                    Elemen Standar
                </a>
            </li>
            <li>
                <a href="{{ route('indikator.index') }}" class="nav-link {{ request()->routeIs('indikator.*') && !request()->routeIs('indikator-penilaian.*') ? 'active' : '' }}">
                    Indikator
                </a>
            </li>
        </ul>
    </li>

    <li>
        <!-- Univ & Prodi -->
        <a href="{{ route('master-data.index') }}" class="nav-link {{ request()->routeIs('master-data.*') || request()->routeIs('universities.*') || request()->routeIs('study-programs.*') ? 'active' : '' }}">
            <span class="menu-icon">🏫</span>
            <span>Univ & Prodi</span>
        </a>

        <!-- Indikator Penilaian -->
        <a href="{{ route('indikator-penilaian.index') }}" class="nav-link {{ request()->routeIs('indikator-penilaian.*') ? 'active' : '' }}">
            <span class="menu-icon">📊</span>
            <span>Indikator Penilaian</span>
        </a>
        <!-- Bobot Penilaian -->
        <a href="{{ route('bobot-penilaian.index') }}" class="nav-link {{ request()->routeIs('bobot-penilaian.*') ? 'active' : '' }}">
            <span class="menu-icon">⚖️</span>
            <span>Bobot Penilaian</span>
        </a>
    </li>
</ul>
@endif
