@php
$authUser = auth()->user();
@endphp

<!-- Top Navigation Bar -->
<nav class="navbar top-navbar">
    <div class="container-fluid px-4">
        <div class="navbar-brand">
            <!-- Mobile Menu Toggle (only visible on mobile) -->
            <button class="menu-toggle btn" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <!-- Logo (Desktop - full logo) -->
            <div class="brand-logo brand-logo-desktop">
                <a href="{{ route('home') }}" class="d-flex text-link">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" width="120px" class="img-fluid">
                </a>
            </div>

            <!-- Logo (Mobile - square logo) -->
            <div class="brand-logo brand-logo-mobile">
                <a href="{{ route('home') }}" class="d-flex text-link">
                    <img src="{{ asset('assets/images/logo-square.png') }}" alt="DAISY" class="img-fluid">
                </a>
            </div>

            <!-- Brand Text (hidden on mobile) -->
            <div class="brand-text">
                <h1>DAISY</h1>
                <p>DEPILAR Accreditation Information System</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Notification Icon -->
            {{-- <div class="notification-icon" onclick="showNotifications()">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-badge">{{ $notificationCount ?? 5 }}</span>
        </div> --}}
        @include('layouts.template.role-switcher')
        <!-- User Menu Dropdown -->
        <div class="dropdown">
            <div class="user-menu" data-bs-toggle="dropdown">
                <div class="user-avatar">{{ strtoupper(substr($authUser->name ?? 'M', 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $authUser->name ?? 'Dr. Eng. Maryono, ST., MT' }}</div>
                    <div class="user-role">{{ $authUser->role ? $authUser->role_alias : 'LAMDEPILAR' }}</div>
                </div>
                <i class="bi bi-chevron-down"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <i class="bi bi-person me-2"></i>Profil Saya
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.password') }}">
                        <i class="bi bi-key me-2"></i>Ubah Password
                    </a>
                </li>
                {{-- <li>
                        <a class="dropdown-item" href="{{ route('settings') }}">
                <i class="bi bi-gear me-2"></i>Pengaturan
                </a>
                </li> --}}
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
    </div>
</nav>
