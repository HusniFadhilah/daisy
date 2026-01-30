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
'label' => 'Pengiriman Formulir dan Template Dokumen',
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
'label' => 'Penerimaan Draft Dokumen',
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
'no' => 17,
'route' => 'upps.pelaksanaan-banding',
'match' => 'upps.pelaksanaan-banding*',
'icon' => 'bi-arrow-repeat',
'label' => 'Pelaksanaan Banding',
],
[
'no' => 18,
'route' => 'upps.pelaporan-banding',
'match' => 'upps.pelaporan-banding*',
'icon' => 'bi-clipboard-data',
'label' => 'Pelaporan Banding',
],
[
'no' => 19,
'route' => 'upps.penetapan-hasil-akreditasi',
'match' => 'upps.penetapan-hasil-akreditasi*',
'icon' => 'bi-award',
'label' => 'Penetapan Hasil Akreditasi',
],
[
'no' => 20,
'route' => 'upps.pelaporan-hasil-akreditasi',
'match' => 'upps.pelaporan-hasil-akreditasi*',
'icon' => 'bi-graph-up',
'label' => 'Pelaporan Hasil Akreditasi',
],
[
'no' => 21,
'route' => 'upps.penyimpanan-arsip-pelaksanaan-akreditasi',
'match' => 'upps.penyimpanan-arsip-pelaksanaan-akreditasi*',
'icon' => 'bi-archive',
'label' => 'Penyimpanan Arsip Akreditasi',
],
];
@endphp

<!-- Permohonan Akreditasi -->
<a href="{{ route('pengajuan') }}" class="nav-link {{ request()->routeIs('pengajuan*') ? 'active' : '' }}">
    <span class="menu-icon">
        <i class="bi bi-file-earmark-text"></i>
    </span>
    <span>Progress Permohonan Akreditasi</span>
</a>

@foreach ($menus as $menu)
<li class="nav-item">
    <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['match']) ? 'active' : '' }}">
        <span class="menu-icon">
            <i class="bi {{ $menu['icon'] }}"></i>
        </span>
        <span>{{ $menu['no'] }}. {{ $menu['label'] }}</span>
    </a>
</li>
@endforeach
