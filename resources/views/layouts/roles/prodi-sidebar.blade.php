<!-- Penawaran Asesmen -->
<a href="{{ route('pengajuan') }}" class="nav-link {{ request()->routeIs('pengajuan*') ? 'active' : '' }}">
    <span class="menu-icon">📋</span>
    <span>Permohonan Akreditasi</span>
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
<!-- Pedoman AK -->
<a href="{{ route('pedoman') }}" class="nav-link {{ request()->routeIs('pedoman') ? 'active' : '' }} text-secondary" style="color: #ddd">
    <span class="menu-icon text-secondary">📁</span>
    <span class="text-secondary">Template Dokumen</span>
</a>

<!-- Penugasan Banding -->
<a href="{{ route('banding') }}" class="nav-link {{ request()->routeIs('banding') ? 'active' : '' }}">
    <span class="menu-icon">🤝</span>
    <span>Permohonan Banding</span>
</a>
