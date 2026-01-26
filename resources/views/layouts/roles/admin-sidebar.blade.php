@php
$authUser = Auth::user();
@endphp

<!-- Penawaran Asesmen -->
<li class="nav-item">
    <a href="{{ route('de.pemetaan.index') }}" class="nav-link {{ request()->routeIs('de.pemetaan*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-clock-history"></i></span>
        <span>1. Pengingat Masa Akreditasi</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.surat-permohonan') }}" class="nav-link {{ request()->routeIs('de.surat-permohonan*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-envelope-paper"></i></span>
        <span>2. Surat Permohonan dari PS</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penyampaian-template') }}" class="nav-link {{ request()->routeIs('de.penyampaian-template*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-file-earmark-text"></i></span>
        <span>3. Penyampaian Template LED+Suplemen dan LKPS</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.validasi-pembayaran') }}" class="nav-link {{ request()->routeIs('de.validasi-pembayaran*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-credit-card"></i></span>
        <span>4. Validasi Pembayaran</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penerimaan-dokumen') }}" class="nav-link {{ request()->routeIs('de.penerimaan-dokumen*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-inbox"></i></span>
        <span>5. Penerimaan Draft LED+Suplemen dan LKPS</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.validasi-dokumen') }}" class="nav-link {{ request()->routeIs('de.validasi-dokumen*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-check-circle"></i></span>
        <span>6. Validasi LED+Suplemen dan LKPS</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaporan-dokumen') }}" class="nav-link {{ request()->routeIs('de.pelaporan-dokumen*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-bar-chart-line"></i></span>
        <span>7. Pelaporan Validasi LED+Suplemen dan LKPS</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penugasan-ak') }}" class="nav-link {{ request()->routeIs('de.penugasan-ak*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-person-check"></i></span>
        <span>8. Penugasan Asesor AK</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.validasi-ak') }}" class="nav-link {{ request()->routeIs('de.validasi-ak*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-patch-check"></i></span>
        <span>9. Validasi AK</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaporan-ak') }}" class="nav-link {{ request()->routeIs('de.pelaporan-ak*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
        <span>10. Pelaporan AK</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penugasan-al') }}" class="nav-link {{ request()->routeIs('de.penugasan-al*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-building"></i></span>
        <span>11. Penugasan Asesor AL</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaksanaan-al') }}" class="nav-link {{ request()->routeIs('de.pelaksanaan-al*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-geo-alt"></i></span>
        <span>12. Pelaksanaan AL & Berita Acara</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaporan-al') }}" class="nav-link {{ request()->routeIs('de.pelaporan-al*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-journal-text"></i></span>
        <span>13. Pelaporan AL</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penyampaian-hasil-akreditasi') }}" class="nav-link {{ request()->routeIs('de.penyampaian-hasil-akreditasi*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-megaphone"></i></span>
        <span>14. Penyampaian Hasil Akreditasi</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.masa-sanggah') }}" class="nav-link {{ request()->routeIs('de.masa-sanggah*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-hourglass-split"></i></span>
        <span>15. Masa Sanggah</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaksanaan-banding') }}" class="nav-link {{ request()->routeIs('de.pelaksanaan-banding*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-arrow-repeat"></i></span>
        <span>16. Pelaksanaan Banding</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaporan-banding') }}" class="nav-link {{ request()->routeIs('de.pelaporan-banding*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-clipboard-data"></i></span>
        <span>17. Pelaporan Banding</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penetapan-hasil-akreditasi') }}" class="nav-link {{ request()->routeIs('de.penetapan-hasil-akreditasi*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-award"></i></span>
        <span>18. Penetapan Hasil Akreditasi</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.pelaporan-hasil-akreditasi') }}" class="nav-link {{ request()->routeIs('de.pelaporan-hasil-akreditasi*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-graph-up"></i></span>
        <span>19. Pelaporan Hasil Akreditasi</span>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi') }}" class="nav-link {{ request()->routeIs('de.penyimpanan-arsip-pelaksanaan-akreditasi*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="bi bi-archive"></i></span>
        <span>20. Penyimpanan Arsip Akreditasi</span>
    </a>
</li>
{{-- <a href="{{ route('asesmen.index') }}" class="nav-link {{ request()->routeIs('asesmen*') ? 'active' : '' }}">
<span class="menu-icon">📨</span>
<span>Asesmen</span>
</a> --}}

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

{{-- <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'ak-submenu')">
    <span class="menu-icon">📝</span>
    <span>Proses AK</span>
    <span class="badge bg-warning menu-badge">{{ $prosesAK ?? 1 }}</span>
</a>
<ul class="submenu nav flex-column" id="ak-submenu">
    <li>
        <a href="{{ route('ak.berkas') }}" class="nav-link">
            Berkas Penilaian
        </a>
    </li>
    <li>
        <a href="{{ route('ak.split') }}" class="nav-link">
            Cek Split Nilai
        </a>
    </li>
    <li>
        <a href="{{ route('ak.upload') }}" class="nav-link">
            Upload Hasil Penilaian
        </a>
    </li>
    <li>
        <a href="{{ route('ak.validasi') }}" class="nav-link">
            Status Validasi
        </a>
    </li>
</ul> --}}

{{-- <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'al-submenu')">
    <span class="menu-icon">🏢</span>
    <span>Proses AL</span>
</a>
<ul class="submenu nav flex-column" id="al-submenu">
    <li>
        <a href="{{ route('al.jadwal') }}" class="nav-link">
Jadwal Visitasi
</a>
</li>
<li>
    <a href="{{ route('al.dokumen') }}" class="nav-link">
        Dokumen AL
    </a>
</li>
<li>
    <a href="{{ route('al.upload') }}" class="nav-link">
        Upload Hasil AL
    </a>
</li>
<li>
    <a href="{{ route('al.laporan') }}" class="nav-link">
        Laporan Asesmen
    </a>
</li>
</ul> --}}

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
            Template Penilaian
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
