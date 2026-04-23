@extends('layouts.app')

@section('title', 'SIMPELSI - Dashboard Umum')

@section('content')
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
                <img src="{{ asset('assets/banner.png') }}" alt="banner SIMPELSI">
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
                    <img src="{{ asset('assets/logo_dlh.jpg') }}" alt="Logo Dinas Lingkungan Hidup" class="logo-large">
                </div>
                <div class="logo-small-container">
                    <img src="{{ asset('assets/Dlh.png') }}" alt="DLH" class="logo-small">
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
                    <div class="feature-icon">
                        <img src="{{ asset('assets/lapor_sampah.png') }}" alt="Lapor Sampah">
                    </div>
                    <div class="feature-title">LAPOR SAMPAH ILEGAL</div>
                    <p>Bagikan foto sampah ke Aplikasi Simpelsi, dan arahkan letak sampah yang ada di sekitarmu.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('assets/informasi_tps.png') }}" alt="Informasi TPS">
                    </div>
                    <div class="feature-title">INFORMASI LOKASI TPS</div>
                    <p>Simpelsi memudahkan informasi tempat tertang lokasi TPS Di Kabupaten Negara.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('assets/artikel_edukasi.png') }}" alt="Artikel Edukasi">
                    </div>
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
                <a href="https://drive.google.com/file/d/1RJLPEwUK9LQbHHdUTWy_asPKgr3FeJ9E/view?usp=sharing" class="download-btn" target="_blank">UNDUH APK</a>
            </div>
        </div>
    </div>
@endsection
