<!-- Sidebar -->
@php
$authUser = Auth::user();
@endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Main Menu</h3>
        <!-- Collapse Button (Desktop only) -->
        <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse()" title="Collapse Sidebar">
            <i class="bi bi-chevron-left" id="collapseIcon"></i>
        </button>
    </div>
    <nav class="nav nav-pills flex-column">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="menu-icon">🏠</span>
            <span>Dashboard</span>
        </a>

        @auth
        @if(in_array($authUser->role_selected,['super_admin','asesi']))
        @include('layouts.roles.admin-sidebar')
        @elseif(in_array($authUser->role_selected,['asesor']))
        @include('layouts.roles.asesor-sidebar')
        @elseif(in_array($authUser->role_selected,['verifikator']))
        @include('layouts.roles.verifikator-sidebar')
        @elseif(in_array($authUser->role_selected,['validator']))
        @include('layouts.roles.validator-sidebar')
        @elseif(in_array($authUser->role_selected,['admin_prodi']))
        @include('layouts.roles.prodi-sidebar')
        @elseif(in_array($authUser->role_selected,['keuangan_lamdepilar']))
        @include('layouts.roles.keuangan-sidebar')
        @else
        @include('layouts.roles.default-sidebar')
        @endif
        @endauth

        <!-- Panduan Penggunaan DAISY -->
        <a href="{{ route('panduan') }}" class="nav-link {{ request()->routeIs('panduan') ? 'active' : '' }}">
            <span class="menu-icon">💬</span>
            <span>Panduan Penggunaan DAISY</span>
        </a>

        <!-- Bantuan Layanan -->
        @if(in_array($authUser->role_selected,['super_admin','validator','asesor']))
        <a href="{{ route('bantuan') }}" class="nav-link {{ request()->routeIs('bantuan') ? 'active' : '' }}">
            <span class="menu-icon">🚨</span>
            <span>Kontak Sekretariat</span>
        </a>
        @endif

        <!-- Profil & Pengaturan -->
        @if(in_array($authUser->role_selected,['super_admin']))
        <a href="#" class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}" onclick="toggleSubmenu(event, 'profil-submenu')">
            <span class="menu-icon">👤</span>
            <span>Profil & Pengaturan</span>
        </a>
        <ul class="submenu nav flex-column" id="profil-submenu">
            <li>
                <a href="{{ route('profile') }}" class="nav-link">
                    Profil Saya
                </a>
            </li>
            <li>
                <a href="{{ route('profile.password') }}" class="nav-link">
                    Ubah Password
                </a>
            </li>
            <li>
                <a href="{{ route('settings') }}" class="nav-link">
                    Preferensi
                </a>
            </li>
        </ul>
        @endif

        <!-- Keluar -->
        <a href="{{ route('logout') }}" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
            <span class="menu-icon">🚪</span>
            <span>Keluar</span>
        </a>
        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </nav>
</aside>
