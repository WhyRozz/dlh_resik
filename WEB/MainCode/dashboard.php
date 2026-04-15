<?php
// Tidak perlu session — ini halaman publik
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPELSI - Dashboard Umum</title>
    <link rel="shortcut icon" href="../assets/logo_simpelsi.png" type="image/x-icon">
    <style>
        /* =============== RESET & GLOBAL STYLES =============== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* =============== NAVBAR DESKTOP STYLES (Hanya Tampil di Desktop) =============== */
        .navbar-desktop {
            background: #2e8b57;
            color: white;
            padding: 12px 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #2e8b57;
        }

        .nav-logo img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .nav-title {
            font-size: 18px;
            font-weight: bold;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-menu a,
        .nav-menu .nav-login {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s, color 0.2s;
            text-align: center;
            white-space: nowrap;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #e0f0e9;
        }

        .nav-menu .nav-login {
            background: #ff6347;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }

        .nav-menu .nav-login:hover {
            background: #ff4500;
        }

        /* =============== NAVBAR MOBILE STYLES (Versi Final - Lebih Besar & Tidak Ada Celah) =============== */
        .navbar-mobile {
            background: #2e8b57;
            color: white;
            padding: 18px 20px; /* Lebih tinggi */
            width: 100%;
            display: none;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: 70px; /* Tinggi lebih besar */
        }

        .nav-mobile-menu-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 3px;
        }

        .nav-mobile-menu-btn span {
            display: block;
            width: 28px;
            height: 3px;
            background: white;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .nav-mobile-menu-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        .nav-mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        .nav-mobile-menu-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Menu slide-in dari kanan, ukuran lebih besar, tanpa celah */
        .nav-mobile-menu {
            position: fixed;
            top: 0;
            right: 0;
            width: 320px; /* Lebih lebar */
            height: 100vh;
            background: linear-gradient(135deg, #2e8b57, #1e6b3f);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 999;
            padding-top: 90px; /* Agar tidak tertutup navbar */
            padding-bottom: 20px; /* Agar tidak terlalu ketat di bawah */
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: stretch;

            /* Sembunyikan sepenuhnya saat tidak aktif */
            transform: translateX(100%);
            opacity: 0;
            visibility: hidden;
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s ease, visibility 0.3s;
        }

        .nav-mobile-menu.active {
            transform: translateX(0);
            opacity: 1;
            visibility: visible;
        }

        .nav-mobile-menu a,
        .nav-mobile-menu .nav-login {
            color: white;
            text-decoration: none;
            padding: 16px 30px; /* Lebih lega */
            font-size: 16px;
            font-weight: 500;
            display: block;
            transition: background 0.2s, transform 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-mobile-menu a:hover,
        .nav-mobile-menu .nav-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid white;
            transform: translateX(5px);
        }

        .nav-mobile-menu .nav-login {
            background: #ff6347;
            margin: 25px 30px;
            text-align: center;
            border-radius: 25px;
            font-weight: bold;
            padding: 14px 20px;
            font-size: 16px;
        }

        /* Overlay untuk tutup menu saat klik di luar */
        .nav-mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .nav-mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* =============== SECTION STYLES =============== */
        .section {
            min-height: 100vh;
            width: 100%;
            padding: clamp(30px, 5vw, 60px) clamp(15px, 4vw, 40px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background: white;
        }

        /* Padding-top untuk menghindari konten tertutup navbar */
        .section {
            padding-top: 80px; /* Default tinggi navbar + jarak */
        }

        /* Di mobile, tinggi navbar lebih kecil */
        @media (max-width: 768px) {
            .section {
                padding-top: 70px; /* Sesuaikan dengan tinggi navbar di mobile */
            }
        }

        /* =============== KONTEN UTAMA =============== */
        .content {
            max-width: 1200px;
            width: 100%;
            text-align: center;
            margin-top: 0;
        }

        h1 {
            color: #2e8b57;
            margin-bottom: 20px;
            font-size: clamp(20px, 4vw, 28px);
        }

        p {
            line-height: 1.6;
            margin-bottom: 20px;
            color: #333;
            font-size: clamp(14px, 1.8vw, 16px);
        }

        .btn-green {
            background: #2e8b57;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
            transition: background 0.2s;
            font-size: clamp(13px, 1.8vw, 15px);
        }

        .btn-green:hover {
            background: #226b42;
        }

        /* =============== HOME SECTION =============== */
        .home-content {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .home-text {
            flex: 1;
            min-width: 300px;
        }

        .home-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .home-image img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            animation: waveImage 4s ease-in-out infinite;
            transform-origin: bottom;
            display: block;
        }

        /* =============== PROFIL SECTION =============== */
        .profil-content {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .profil-text {
            flex: 1;
            min-width: 300px;
        }

        .profil-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            min-width: 300px;
        }

        .logo-large-container {
            width: 100%;
            text-align: center;
        }

        .logo-large {
            max-width: clamp(130px, 20vw, 250px);
            height: auto;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logo-small-container {
            margin-top: -10px;
            margin-left: 5px;
        }

        .logo-small {
            width: clamp(35px, 8vw, 70px);
            height: auto;
        }

        /* =============== VISI & MISI SECTION =============== */
        .visimisi-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 0 20px;
            width: 100%;
        }

        .visimisi-columns {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }

        .visimisi-column {
            flex: 1;
            min-width: 280px;
            background: #f9f9f9;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #2e8b57;
        }

        .visimisi-column h2 {
            color: #2e8b57;
            font-size: clamp(18px, 3vw, 22px);
            margin-bottom: 15px;
        }

        /* =============== FITUR SECTION =============== */
        .features {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .feature-card {
            background: #e6f2e6;
            padding: 20px;
            border-radius: 12px;
            width: 100%;
            max-width: 300px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .feature-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            /*background: #2e8b57;*/
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .feature-icon img {
            width: 100px;
            height: 100px;
        }

        .feature-title {
            font-size: 16px;
            color: #2e8b57;
            margin-bottom: 10px;
            font-weight: bold;
        }

        /* =============== JENIS SAMPAH SECTION =============== */
        .waste-types-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

        .waste-category {
            background: #e6e6e6;
            padding: 20px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .waste-category-title {
            color: #2e8b57;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .waste-items {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .waste-item {
            background: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 80px;
        }

        .waste-icon {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2e8b57;
        }

        .waste-name {
            font-weight: bold;
            color: #2e8b57;
            font-size: 12px;
        }

        /* =============== DOWNLOAD SECTION =============== */
        .download-section-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 1000px;
            gap: 40px;
        }

        .download-text {
            text-align: center;
            max-width: 600px;
        }

        .download-btn {
            background: #2e8b57;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .download-btn:hover {
            background: #226b42;
        }

        /* =============== ANIMASI =============== */
        @keyframes waveImage {
            0% {
                transform: scaleY(1) skewY(0deg);
            }
            50% {
                transform: scaleY(1.03) skewY(3deg);
            }
            100% {
                transform: scaleY(1) skewY(0deg);
            }
        }

        /* =============== FOOTER BAWAH =============== */
        .footer-bottom {
            background: #004d26;
            color: white;
            padding: 30px 20px;
            margin-top: 40px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            position: relative;
            z-index: 5;
        }

        .footer-bottom.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: space-between;
        }

        .footer-col {
            flex: 1;
            min-width: 250px;
        }

        .footer-col h3 {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .footer-col p {
            line-height: 1.6;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .footer-col .copyright {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 15px;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-col ul li {
            margin-bottom: 8px;
        }

        .footer-col ul li a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-col ul li a:hover {
            color: #ffeb3b;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .social-icons a img {
            width: 24px;
            height: 24px;
            filter: brightness(0) invert(1);
        }

        /* =============== RESPONSIVE UNTUK TABLET & HP =============== */
        @media (max-width: 768px) {
            /* Sembunyikan navbar desktop */
            .navbar-desktop {
                display: none;
            }

            /* Tampilkan navbar mobile */
            .navbar-mobile {
                display: flex;
            }

            .nav-mobile-menu.active {
                display: flex;
            }

            .nav-logo-container {
                order: 1;
            }

            .nav-mobile-menu-btn {
                order: 2;
            }

            .nav-mobile-menu {
                right: 0px;
            }

            .navbar-mobile {
                padding: 10px 15px;
            }

            .nav-logo {
                width: 30px;
                height: 30px;
            }

            .nav-title {
                font-size: 16px;
            }

            .content {
                padding: 20px;
            }

            .home-content, .profil-content {
                flex-direction: column;
                text-align: center;
            }

            .home-image, .profil-logo {
                order: -1;
            }

            .waste-types-container {
                flex-direction: column;
            }

            .waste-category {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .navbar-mobile {
                padding: 8px;
            }

            .nav-logo {
                width: 24px;
                height: 24px;
            }

            .nav-title {
                font-size: 14px;
            }

            .nav-mobile-menu {
                width: 180px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar Desktop -->
    <div class="navbar-desktop">
        <div class="nav-logo-container">
            <div class="nav-logo">
                <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/logo.jpg" alt="Logo SIMPELSI">
            </div>
            <div class="nav-title">SimpelSi</div>
        </div>
        <div class="nav-menu">
            <a href="#home">Beranda</a>
            <a href="#profil">Profil</a>
            <a href="#fitur">Fitur</a>
            <a href="#jenis">Jenis Sampah</a>
            <a href="#download">Pengaduan Laporan</a>
            <div class="nav-login" id="loginBtnDesktop">Login</div>
        </div>
    </div>

    <!-- Navbar Mobile -->
    <div class="navbar-mobile">
        <div class="nav-logo-container">
            <div class="nav-logo">
                <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/logo.jpg" alt="Logo SIMPELSI">
            </div>
        <div class="nav-title">SimpelSi</div>
        </div>
        <button class="nav-mobile-menu-btn" id="mobileMenuBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- Menu Mobile (Slide dari kanan) -->
    <div class="nav-mobile-menu" id="mobileMenu">
        <a href="#home">Beranda</a>
        <a href="#profil">Profil</a>
        <a href="#fitur">Fitur</a>
        <a href="#jenis">Jenis Sampah</a>
        <a href="#download">Pengaduan Laporan</a>
        <div class="nav-login" id="loginBtnMobile">Login</div>
    </div>

    <!-- Overlay untuk menutup menu -->
    <div class="nav-mobile-overlay" id="mobileOverlay"></div>

    <!-- Home -->
    <div class="section" id="home">
        <div class="content home-content">
            <div class="home-text">
                <h1>Halo, Sahabat SIMPELSI!</h1>
                <p>
                    SIMPELSI adalah Sistem Pelaporan Sampah Ilegal. Ayo, mari kita mulai pelaporan kita lewat laporan ini untuk menghindari dampak buruk terhadap lingkungan. Setiap tindakan kecil akan membuat perbedaan besar dalam menjaga lingkungan.
                </p>
                <a href="#profil" class="btn-green">Mulai</a>
            </div>
            <div class="home-image">
                <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/banner.png" alt="banner SIMPELSI">
            </div>
        </div>
    </div>

    <!-- Profil -->
    <div class="section" id="profil">
        <div class="content profil-content">
            <div class="profil-text">
                <h1>Profil</h1>
                <p>
                    Dinas Lingkungan Hidup merupakan instansi pemerintah yang bertugas membantu kepala daerah dalam melaksanakan urusan pemerintahan di bidang lingkungan hidup yang menjadi kewenangan Daerah dan tugas pembantuan yang diberikan pada Daerah sesuai dengan visi, misi dan program Walikota ekologisasi wilayah dalam Rencana Pembangunan Jangka Menengah Daerah.
                </p>
                <a href="#visimisi" class="btn-green">Baca Visi & Misi →</a>
            </div>
            <div class="profil-logo">
                <div class="logo-large-container">
                    <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/logo_dlh.jpg" alt="Logo Dinas Lingkungan Hidup" class="logo-large">
                </div>
                <div class="logo-small-container">
                    <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/Dlh.png" alt="DLH" class="logo-small">
                </div>
            </div>
        </div>
    </div>

    <!-- Visi & Misi -->
    <div class="section" id="visimisi">
        <div class="content visimisi-content">
            <h1>Visi & Misi</h1>
            <div class="visimisi-columns">
                <div class="visimisi-column">
                    <h2>Visi</h2>
                    <p>
                        Terwujudnya lingkungan hidup yang bersih dan sehat melalui pengelolaan sampah secara terpadu, berkelanjutan, dan partisipatif untuk mewujudkan Kabupaten Negara yang ekologis dan berkelanjutan.
                    </p>
                </div>
                <div class="visimisi-column">
                    <h2>Misi</h2>
                    <p>
                        1. Meningkatkan kesadaran masyarakat dalam pengelolaan sampah melalui edukasi dan kampanye lingkungan.<br>
                        2. Memperkuat sistem pengelolaan sampah dari hulu ke hilir secara terintegrasi.<br>
                        3. Mendorong inovasi teknologi dan partisipasi masyarakat dalam penanganan sampah.<br>
                        4. Menyediakan layanan pelaporan sampah ilegal yang mudah, cepat, dan transparan.<br>
                        5. Membangun kerjasama lintas sektor untuk mencapai target pengurangan sampah.
                    </p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 20px; width: 100%;">
                <a href="#main-footer" class="download-btn">Download APK →</a>
            </div>
        </div>
    </div>

    <!-- Fitur -->
    <div class="section" id="fitur">
        <div class="content">
            <h1>FITUR</h1>
            <p>Inovasi Fitur SIMPELSI</p>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon"><img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/lapor_sampah.png" alt="Lapor Sampah"></div>
                    <div class="feature-title">LAPOR SAMPAH ILEGAL</div>
                    <p>Bagikan foto sampah ke Aplikasi Simpelsi, dan arahkan letak sampah yang ada di sekitarmu.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/informasi_tps.png" alt="Informasi TPS"></div>
                    <div class="feature-title">INFORMASI LOKASI TPS</div>
                    <p>Simpelsi memudahkan informasi tempat tertang lokasi TPS Di Kabupaten Negara.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/artikel_edukasi.png" alt="Artikel Edukasi"></div>
                    <div class="feature-title">ARTIKEL EDUKASI</div>
                    <p>Menemukan pengumpulan sampah EcoSorted terdekat di wilayahmu.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Jenis Sampah -->
    <div class="section" id="jenis">
        <div class="content">
            <h1>JENIS SAMPAH</h1>
            <p>Berbagai jenis sampah yang dapat dilaporkan</p>
            <div class="waste-types-container">
                <!-- Organik -->
                <div class="waste-category">
                    <div class="waste-category-title">Organik</div>
                    <div class="waste-items">
                        <div class="waste-item">
                            <div class="waste-icon">📄</div>
                            <div class="waste-name">Kertas</div>
                        </div>
                        <div class="waste-item">
                            <div class="waste-icon">📦</div>
                            <div class="waste-name">Kardus</div>
                        </div>
                        <div class="waste-item">
                            <div class="waste-icon">🍎</div>
                            <div class="waste-name">Buah & Sayur</div>
                        </div>
                    </div>
                </div>

                <!-- Non-Organik -->
                <div class="waste-category">
                    <div class="waste-category-title">Non-Organik</div>
                    <div class="waste-items">
                        <div class="waste-item">
                            <div class="waste-icon">⚙️</div>
                            <div class="waste-name">Logam</div>
                        </div>
                        <div class="waste-item">
                            <div class="waste-icon">🥤</div>
                            <div class="waste-name">Plastik</div>
                        </div>
                        <div class="waste-item">
                            <div class="waste-icon">🍷</div>
                            <div class="waste-name">Kaca</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download -->
    <div class="section" id="download">
        <div class="content download-section-content">
            <div class="download-text">
                <h1>Download Aplikasi</h1>
                <p>Simpelsi adalah platform yang memudahkan pelaporan sampah ilegal dengan perangkat mobile/smartphone yang bisa diakses online.</p>
                <a href="https://drive.google.com/file/d/1RJLPEwUK9LQbHHdUTWy_asPKgr3FeJ9E/view?usp=sharing" class="download-btn">UNDUH APK</a>
            </div>
        </div>
    </div>

    <!-- FOOTER BAWAH -->
    <footer id="main-footer" class="footer-bottom">
        <div class="footer-container">
            <div class="footer-col">
                <h3>Simpelsi</h3>
                <h5>Berawal dari foto, Berakhir pada Kelestarian. Langkah kecil memberikan dampak besar pada pelestarian lingkungan.</h5>
                <h5 class="copyright">©2025 Simpelsi All rights reserved.</h5>
            </div>

            <div class="footer-col">
                <h3>Simpelsi</h3>
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#profil">Profil</a></li>
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#jenis">Jenis Sampah</a></li>
                    <li><a href="#download">Download</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Social Media</h3>
                <div class="social-icons">
                    <a href="https://www.instagram.com/dlhnganjuk/" aria-label="Instagram">
                        <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/instagram.png" alt="Instagram" width="24">
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=100076050218713" aria-label="Facebook">
                        <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/facebook.png" alt="Facebook" width="24">
                    </a>
                    <a href="https://www.youtube.com/@dlhbisa" aria-label="YouTube">
                        <img src="https://simpelsi.pbltifnganjuk.com/WEB/assets/—Pngtree—youtube logo png_3733302.png" alt="YouTube" width="24">
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Toggle Mobile Menu & Overlay
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');

        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
        });

        mobileOverlay.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
        });

        // Tutup menu saat klik link
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            });
        });

        // Login redirect for mobile
        document.getElementById('loginBtnMobile')?.addEventListener('click', function() {
            window.location.href = 'Admin/login.php';
        });

        // Login redirect for desktop
        document.getElementById('loginBtnDesktop')?.addEventListener('click', function() {
            window.location.href = 'Admin/login.php';
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Animasi Footer: Muncul saat scroll ke bawah
        const footer = document.getElementById('main-footer');

        function checkFooterVisibility() {
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (footerRect.top <= windowHeight * 0.9) {
                footer.classList.add('visible');
            }
        }

        window.addEventListener('scroll', checkFooterVisibility);
        window.addEventListener('load', checkFooterVisibility);
    </script>
</body>

</html>