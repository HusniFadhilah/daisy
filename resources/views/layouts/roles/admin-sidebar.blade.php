<!-- Penawaran Asesmen -->
<a href="{{ route('asesmen.index') }}" class="nav-link {{ request()->routeIs('asesmen.index') ? 'active' : '' }}">
    <span class="menu-icon">📨</span>
    <span>Penawaran Asesmen</span>
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
<a href="{{ route('ak.berkas') }}" class="nav-link {{ request()->routeIs('ak.berkas') ? 'active' : '' }}">
    <span class="menu-icon">📝</span>
    <span>Proses AK</span>
</a>
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

<!-- Proses AL -->
<a href="{{ route('al.jadwal') }}" class="nav-link {{ request()->routeIs('al.jadwal') ? 'active' : '' }}">
     <span class="menu-icon">🏢</span>
    <span>Proses AL</span>
</a>
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

<!-- Penugasan Banding -->
<a href="{{ route('banding') }}" class="nav-link {{ request()->routeIs('banding') ? 'active' : '' }}">
    <span class="menu-icon">🤝</span>
    <span>Penugasan Banding</span>
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
