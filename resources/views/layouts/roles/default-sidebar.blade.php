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
