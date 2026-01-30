<!-- Permohonan Akreditasi -->
<a href="{{ route('pengajuan') }}" class="nav-link {{ request()->routeIs('pengajuan*') ? 'active' : '' }}">
    <span class="menu-icon">
        <i class="bi bi-file-earmark-text"></i>
    </span>
    <span>Permohonan Akreditasi</span>
</a>

<!-- Penerimaan Permohonan Akreditasi -->
<a href="{{ route('prodi.penerimaan-permohonan') }}" class="nav-link {{ request()->routeIs('prodi.penerimaan-permohonan*') ? 'active' : '' }}">
    <span class="menu-icon">
        <i class="bi bi-envelope-check"></i>
    </span>
    <span>Penerimaan Permohonan Akreditasi</span>
</a>

<!-- Template Dokumen -->
<a href="{{ route('pedoman') }}" class="nav-link {{ request()->routeIs('pedoman') ? 'active' : '' }} text-secondary" style="color: #ddd">
    <span class="menu-icon text-secondary">
        <i class="bi bi-folder2-open"></i>
    </span>
    <span class="text-secondary">Template Dokumen</span>
</a>

<!-- Permohonan Banding -->
<a href="{{ route('banding') }}" class="nav-link {{ request()->routeIs('banding') ? 'active' : '' }}">
    <span class="menu-icon">
        <i class="bi bi-arrow-left-right"></i>
    </span>
    <span>Permohonan Banding</span>
</a>
