<!-- Header Desktop -->
<div class="header-desktop">
    <div class="header-desktop-title">
        <div class="header-desktop-logo">
            <img src="{{ asset('assets/logo.jpg') }}" alt="Logo SIMPELSI" class="logo-img">
        </div>
        <div>
            <div style="font-size: 18px; font-weight: bold;">@yield('page-title', 'Beranda')</div>
            <div style="font-size: 12px; opacity: 0.9;">ADMIN</div>
        </div>
    </div>
    <button class="header-desktop-exit" id="logoutBtn">
        <img src="{{ asset('assets/icon_keluar.png') }}" alt="Logout" style="width: 20px; height: 20px;">
        KELUAR
    </button>
</div>

<!-- Navbar Mobile -->
<div class="navbar-mobile">
    <button class="navbar-mobile-menu-btn" id="menuToggle">☰</button>
    <div class="navbar-mobile-title">
        <div class="logo"><img src="{{ asset('assets/logo.jpg') }}" alt="Logo" class="logo-img"></div>
        <div>@yield('page-title-mobile', 'BERANDA')</div>
    </div>
    <button class="navbar-mobile-exit" id="logoutBtnMobile">
        <img src="{{ asset('assets/keluar.png') }}" alt="Logout" style="width: 20px; height: 20px;">
    </button>
</div>

<!-- Popup Logout -->
<div id="popupLogout" class="popup-overlay">
    <div class="popup-content">
        <h3>Apakah Yakin Ingin Keluar?</h3>
        <button class="popup-btn yes" onclick="logout()">Iya</button>
        <button class="popup-btn no" onclick="closePopup()">Tidak</button>
    </div>
</div>
