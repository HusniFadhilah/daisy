@php
$authUser = Auth::user();
$menus = [
[
'no' => 1,
'route' => 'upps.pengingat-akreditasi',
'match' => 'upps.pengingat-akreditasi*',
'icon' => 'bi-clock-history',
'label' => 'Pengingat Masa Akreditasi',
],
[
'no' => 2,
'route' => 'upps.surat-permohonan',
'match' => 'upps.surat-permohonan*',
'icon' => 'bi-envelope-paper',
'label' => 'Permohonan Akreditasi',
],
[
'no' => 3,
'route' => 'upps.penerimaan-permohonan',
'match' => 'upps.penerimaan-permohonan*',
'icon' => 'bi-send-check',
'label' => 'Penerimaan Permohonan Akreditasi',
],
[
'no' => 4,
'route' => 'upps.penyampaian-template',
'match' => 'upps.penyampaian-template*',
'icon' => 'bi-file-earmark-text',
'label' => 'Formulir Pembayaran dan Template Dokumen',
],
[
'no' => 5,
'route' => 'upps.validasi-pembayaran',
'match' => 'upps.validasi-pembayaran*',
'icon' => 'bi-credit-card',
'label' => 'Validasi Pembayaran',
],
[
'no' => 6,
'route' => 'upps.penerimaan-dokumen',
'match' => 'upps.penerimaan-dokumen*',
'icon' => 'bi-inbox',
'label' => 'Pengiriman Dokumen',
],
[
'no' => 7,
'route' => 'upps.validasi-dokumen',
'match' => 'upps.validasi-dokumen*',
'icon' => 'bi-check-circle',
'label' => 'Validasi Dokumen',
],
[
'no' => 8,
'route' => 'upps.pelaporan-dokumen',
'match' => 'upps.pelaporan-dokumen*',
'icon' => 'bi-bar-chart-line',
'label' => 'Pelaporan Validasi Dokumen',
],
[
'no' => 9,
'route' => 'upps.penugasan-ak',
'match' => 'upps.penugasan-ak*',
'icon' => 'bi-person-check',
'label' => 'Penugasan Asesor AK',
],
[
'no' => 10,
'route' => 'upps.validasi-ak',
'match' => 'upps.validasi-ak*',
'icon' => 'bi-patch-check',
'label' => 'Validasi AK',
],
[
'no' => 11,
'route' => 'upps.pelaporan-ak',
'match' => 'upps.pelaporan-ak*',
'icon' => 'bi-file-earmark-bar-graph',
'label' => 'Pelaporan AK',
],
[
'no' => 12,
'route' => 'upps.penugasan-al',
'match' => 'upps.penugasan-al*',
'icon' => 'bi-building',
'label' => 'Penugasan Asesor AL',
],
[
'no' => 13,
'route' => 'upps.pelaksanaan-al',
'match' => 'upps.pelaksanaan-al*',
'icon' => 'bi-geo-alt',
'label' => 'Pelaksanaan AL & Berita Acara',
],
[
'no' => 14,
'route' => 'upps.pelaporan-al',
'match' => 'upps.pelaporan-al*',
'icon' => 'bi-journal-text',
'label' => 'Pelaporan AL',
],
[
'no' => 15,
'route' => 'upps.penyampaian-hasil-akreditasi',
'match' => 'upps.penyampaian-hasil-akreditasi*',
'icon' => 'bi-megaphone',
'label' => 'Penyampaian Hasil Akreditasi',
],
[
'no' => 16,
'route' => 'upps.masa-sanggah',
'match' => 'upps.masa-sanggah*',
'icon' => 'bi-hourglass-split',
'label' => 'Masa Sanggah',
],
[
'no' => 18,
'route' => 'upps.penetapan-hasil-akreditasi',
'match' => 'upps.penetapan-hasil-akreditasi*',
'icon' => 'bi-award',
'label' => 'Penetapan Hasil Akreditasi',
],
[
'no' => 19,
'route' => 'upps.pelaporan-hasil-akreditasi',
'match' => 'upps.pelaporan-hasil-akreditasi*',
'icon' => 'bi-graph-up',
'label' => 'Pelaporan Hasil Akreditasi',
],
[
'no' => 20,
'route' => 'upps.penyimpanan-arsip-pelaksanaan-akreditasi',
'match' => 'upps.penyimpanan-arsip-pelaksanaan-akreditasi*',
'icon' => 'bi-archive',
'label' => 'Penyimpanan Arsip Pelaksanaan Akreditasi',
],
];
// biar submenu "Banding" otomatis terbuka kalau salah satu route-nya aktif
$isBandingActive =
request()->routeIs('upps.permohonan-banding*')
|| request()->routeIs('upps.pelaksanaan-banding*')
|| request()->routeIs('upps.pelaporan-banding*');
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
                <a href="{{ route('upps.permohonan-banding') }}" class="nav-link">
                    17.a Permohonan Banding
                </a>
            </li>
            <li>
                <a href="{{ route('upps.pelaksanaan-banding') }}" class="nav-link">
                    17.b Pelaksanaan Banding
                </a>
            </li>
            <li>
                <a href="{{ route('upps.pelaporan-banding') }}" class="nav-link">
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
