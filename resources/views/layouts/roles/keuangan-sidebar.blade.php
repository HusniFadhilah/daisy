<!-- Penawaran Asesmen -->
<a href="{{ route('keuangan.pembayaran.index') }}" class="nav-link {{ request()->routeIs('keuangan.pembayaran*') ? 'active' : '' }}">
    <span class="menu-icon">📋</span>
    <span>Verifikasi Pembayaran</span>
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
