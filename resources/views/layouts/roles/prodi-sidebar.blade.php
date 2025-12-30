<!-- Penawaran Asesmen -->
<a href="{{ route('pengajuan') }}" class="nav-link {{ request()->routeIs('pengajuan*') ? 'active' : '' }}">
    <span class="menu-icon">📋</span>
    <span>Pengajuan Akreditasi</span>
</a>

<!-- Proses AK -->
{{-- <a href="" class="nav-link">
    <span class="menu-icon">📝</span>
    <span>Proses AK</span>
</a>

<!-- Proses AL -->
<a href="{{ route('al.jadwal') }}" class="nav-link {{ request()->routeIs('al.jadwal') ? 'active' : '' }}">
<span class="menu-icon">🏢</span>
<span>Proses AL</span>
</a> --}}

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
