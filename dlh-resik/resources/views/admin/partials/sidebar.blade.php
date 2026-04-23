<!-- Sidebar Desktop -->
<div class="sidebar-desktop">
    <ul class="sidebar-desktop-menu">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="menu-icon">📊</div>
                <div>Beranda</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.laporan.index') }}" class="menu-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <div class="menu-icon">📋</div>
                <div>Kelola Laporan Aduan</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.artikel.index') }}" class="menu-item {{ request()->routeIs('admin.artikel.*') ? 'active' : '' }}">
                <div class="menu-icon">📝</div>
                <div>Kelola Artikel Edukasi</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.tps.index') }}" class="menu-item {{ request()->routeIs('admin.tps.*') ? 'active' : '' }}">
                <div class="menu-icon">🗑️</div>
                <div>Kelola Informasi TPS</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.akun.index') }}" class="menu-item {{ request()->routeIs('admin.akun.*') ? 'active' : '' }}">
                <div class="menu-icon">🔐</div>
                <div>Kelola Akun</div>
            </a>
        </li>
    </ul>
</div>

<!-- Mobile Sidebar -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="menu-icon">📊</div>
                <div>Beranda</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.laporan.index') }}" class="menu-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <div class="menu-icon">📋</div>
                <div>Kelola Laporan Aduan</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.artikel.index') }}" class="menu-item {{ request()->routeIs('admin.artikel.*') ? 'active' : '' }}">
                <div class="menu-icon">📝</div>
                <div>Kelola Artikel Edukasi</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.tps.index') }}" class="menu-item {{ request()->routeIs('admin.tps.*') ? 'active' : '' }}">
                <div class="menu-icon">🗑️</div>
                <div>Kelola Informasi TPS</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.akun.index') }}" class="menu-item {{ request()->routeIs('admin.akun.*') ? 'active' : '' }}">
                <div class="menu-icon">🔐</div>
                <div>Kelola Akun</div>
            </a>
        </li>
    </ul>
</div>
