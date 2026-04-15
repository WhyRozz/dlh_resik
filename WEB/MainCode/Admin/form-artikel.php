<?php
session_start();

// Anti-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Proteksi login
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../Admin/login.php");
    exit;
}
require_once '../KoneksiDatabase/koneksi.php';

// Inisialisasi variabel
$id = null;
$judul = '';
$deskripsi = '';
$tanggal = date('Y-m-d\TH:i');
$fotoLama = '';

// Jika edit
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM artikel WHERE id_artikel = ?");
    $stmt->execute([$id]);
    $artikel = $stmt->fetch();
    if ($artikel) {
        $judul = htmlspecialchars($artikel['judul'], ENT_QUOTES, 'UTF-8');
        $deskripsi = $artikel['deskripsi'];
        $tanggal = date('Y-m-d\TH:i', strtotime($artikel['tanggal']));
        $fotoLama = $artikel['foto'];
    } else {
        die("Artikel tidak ditemukan.");
    }
}

// Handle simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';

    if (empty($judul) || empty($deskripsi) || empty($tanggal)) {
        $error = "Judul, deskripsi, dan tanggal wajib diisi!";
    } else {
        $fotoNama = $fotoLama;
        if (!empty($_FILES['foto']['name'])) {
            // ✅ SESUAIKAN PATH SESUAI STRUKTUR FISIK:
            // Jika: MainCode/Admin/form-artikel.php
            // Maka: ../../../api/uploads/artikel/
            $targetDir = "../../../api/uploads/artikel/";

            // Buat folder jika belum ada
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    $error = "Gagal membuat folder uploads. Pastikan permission 'api/uploads/artikel/' = 755.";
                }
            }

            $fileExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Format gambar tidak didukung! Gunakan: JPG, JPEG, PNG, GIF.";
            } else {
                // ✅ HAPUS FOTO LAMA DARI SERVER (jika ada & beda dari yang baru)
                if ($fotoLama && $fotoLama !== $fotoNama && file_exists($targetDir . $fotoLama)) {
                    unlink($targetDir . $fotoLama);
                }

                $fotoNama = uniqid('artikel_') . '.' . $fileExt;
                $targetFile = $targetDir . $fotoNama;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                    $error = "Gagal upload gambar. Periksa: 1) Ukuran file ≤ 2MB, 2) Permission folder = 755.";
                }
            }
        }

        if (empty($error)) {
            try {
                if ($id) {
                    $stmt = $pdo->prepare("UPDATE artikel SET judul = ?, deskripsi = ?, tanggal = ?, foto = ? WHERE id_artikel = ?");
                    $stmt->execute([$judul, $deskripsi, $tanggal, $fotoNama, $id]);
                    $pesan = "Artikel berhasil diperbarui!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO artikel (judul, deskripsi, tanggal, foto) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$judul, $deskripsi, $tanggal, $fotoNama]);
                    $pesan = "Artikel berhasil disimpan!";
                }
                header("Location: kelolaArtikel.php?pesan=" . urlencode($pesan));
                exit;
            } catch (Exception $e) {
                $error = "Gagal menyimpan ke database: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Tambah' ?> Artikel - SIMPELSI</title>
    <link rel="shortcut icon" href="../../assets/logo_simpelsi.png" type="image/x-icon">
    <style>
        /* --- Gaya Umum --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        /* Fade-in saat halaman load */
        body.fade-in .main-content {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        body.fade-in-ready .main-content {
            opacity: 1;
        }

        /* --- Header Desktop --- */
        .header-desktop {
            width: 100%; background: #2e8b57; color: white;
            padding: 12px 30px; display: flex; justify-content: space-between;
            align-items: center; position: fixed; top: 0; left: 0; z-index: 1000;
        }
        .header-desktop-title { display: flex; align-items: center; gap: 10px; }
        .header-desktop-logo {
            width: 40px; height: 40px; border-radius: 50%;
            background: white; display: flex; align-items: center;
            justify-content: center; font-weight: bold; color: #2e8b57;
        }
        .header-desktop-exit {
            background: white; color: #2e8b57; padding: 6px 12px;
            border-radius: 5px; font-size: 12px; font-weight: bold;
            cursor: pointer; display: flex; align-items: center; gap: 5px;
            text-decoration: none;
        }
        .header-desktop-exit:hover {
            background: #e6ffe6;
            transform: scale(1.05);
        }

        /* --- Sidebar Desktop --- */
        .sidebar-desktop {
            width: 250px;
            background: #e6e6e6;
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            padding: 20px 0;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            z-index: 999;
            display: flex;
            flex-direction: column;
        }
        .sidebar-desktop-menu {
            list-style: none;
            padding: 0 20px;
            margin: 0;
            flex: 1;
        }
        .menu-item {
            padding: 14px 20px;
            margin-bottom: 8px;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .menu-item:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        .menu-item.active {
            background: #2e8b57;
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(46, 139, 87, 0.3);
        }
        .menu-icon {
            width: 32px;
            height: 32px;
            background: #2e8b57;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        /* Gaya khusus untuk logo gambar */
		.logo-img {
    		width: 100%;
    		height: 100%;
    		object-fit: cover; /* Agar gambar tidak terdistorsi */
    		border-radius: 50%; /* Tetap bulat */
    		display: block;
		}
            
        .menu-item.active .menu-icon {
            background: white;
            color: #2e8b57;
        }

        /* --- Navbar Mobile --- */
        .navbar-mobile {
            width: 100%;
            background: #2e8b57;
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1001;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-mobile-menu-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
        }
        .navbar-mobile-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        .navbar-mobile-title .logo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2e8b57;
            font-weight: bold;
            font-size: 14px;
        }
        .navbar-mobile-exit {
            background: white;
            color: #2e8b57;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        .navbar-mobile-exit:hover {
            background: #e6ffe6;
        }

        /* --- Dropdown Mobile Sidebar --- */
        .mobile-sidebar {
            display: none; /* Sembunyikan secara default */
            position: fixed;
            top: 60px; /* Sesuaikan dengan tinggi navbar */
            left: 0;
            width: 100%;
            background: #e6e6e6;
            z-index: 1000;
            padding: 10px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-height: calc(100vh - 60px);
            overflow-y: auto;
        }
        .mobile-sidebar .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .mobile-sidebar .menu-item {
            padding: 12px 15px;
            margin-bottom: 4px;
            background: white;
            border-radius: 0;
            border-radius: 8px;
            margin: 0 10px 4px 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .mobile-sidebar .menu-item:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        .mobile-sidebar .menu-item.active {
            background: #2e8b57;
            color: white;
            box-shadow: 0 2px 6px rgba(46, 139, 87, 0.3);
        }
        .mobile-sidebar .menu-item.active .menu-icon {
            background: white;
            color: #2e8b57;
        }

        .main-content {
            flex: 1; margin-left: 250px; padding: 80px 30px 30px;
            background: white; /* Warna latar utama */
            min-height: 100vh;
            opacity: 1;
            transition: opacity 0.25s ease-in-out;
        }
        .main-content.fade-out {
            opacity: 0;
        }
        .content-header h2 {
            color: #2e8b57; font-size: 24px; margin-bottom: 20px;
        }
        .form-container {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            max-width: 800px; margin: 0 auto;
        }
        .form-title {
            color: #2e8b57; font-size: 20px; margin-bottom: 20px;
            border-bottom: 2px solid #2e8b57; padding-bottom: 8px;
        }
        .form-row {
            display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .form-group { flex: 1; min-width: 250px; }
        .form-label {
            display: block; margin-bottom: 8px; font-size: 14px;
            color: #555; font-weight: 500;
        }
        .form-input, .form-textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 6px; font-size: 14px;
        }
        .form-textarea { min-height: 120px; resize: vertical; }
        .upload-area {
            border: 2px dashed #ccc; border-radius: 8px; padding: 20px;
            text-align: center; background: #fafafa; cursor: pointer;
            position: relative; overflow: hidden;
        }
        .upload-area:hover {
            border-color: #2e8b57; background: #f0f9f4;
        }
        .upload-area input[type="file"] {
            position: absolute; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }
        .upload-icon { font-size: 36px; color: #888; margin-bottom: 10px; }
        .upload-text { color: #666; font-size: 14px; }
        .upload-preview { margin-top: 15px; display: <?= $fotoLama ? 'block' : 'none' ?>; }
        .upload-preview img {
            max-width: 100%; max-height: 150px; border-radius: 6px;
            object-fit: contain; border: 1px solid #ddd;
        }
        .action-buttons {
            display: flex; gap: 12px; margin-top: 25px;
            justify-content: flex-start;
        }
        .btn {
            padding: 10px 20px; border: none; border-radius: 6px;
            cursor: pointer; font-weight: bold; font-size: 14px;
        }
        .btn-primary { background: #2e8b57; color: white; }
        .btn-primary:hover { background: #226b42; }
        .btn-secondary { background: #6c757d; color: white; text-decoration: none; text-align: center; display: inline-block; }
        .btn-secondary:hover { background: #5a6268; }

        /* --- RESPONSIF: Mobile --- */
        @media (max-width: 768px) {
            /* Sembunyikan elemen desktop */
            .header-desktop, .sidebar-desktop {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding-top: 70px; /* Sesuaikan dengan tinggi navbar mobile */
            }
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
        }
        /* --- RESPONSIF: Mobile Lebar --- */
        @media (max-width: 480px) {
            .form-container { padding: 15px; }
            .form-title { font-size: 18px; }
            .action-buttons { flex-direction: column; }
        }
        /* --- RESPONSIF: Desktop (Sembunyikan elemen mobile) --- */
        @media (min-width: 769px) {
            .navbar-mobile, .mobile-sidebar {
                display: none;
            }
        }

        /* === POPUP ERROR === */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none; /* Sembunyikan secara default */
            pointer-events: none; /* Agar bisa klik ke elemen di belakang jika overlay tidak aktif */
        }
        .popup-overlay.active {
            display: flex;
            pointer-events: auto; /* Aktifkan saat ditampilkan */
        }
        .popup-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transform: scale(0.8);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .popup-content.error { border-left: 5px solid #dc3545; } /* Warna merah untuk error */
        .popup-content.show {
            transform: scale(1);
            opacity: 1;
        }
        .popup-content h3 {
            margin: 0 0 15px 0;
            font-size: 20px;
            color: #333;
        }
        .popup-content p {
            margin: 0 0 20px 0;
            color: #555;
            font-size: 15px;
        }
        .popup-btn {
            padding: 10px 20px;
            background: #2e8b57;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .popup-btn:hover {
            background: #226b42;
        }

    </style>
</head>
<body class="fade-in">

    <!-- Header Desktop -->
    <div class="header-desktop">
        <div class="header-desktop-title">
            <div class="header-desktop-logo">
                <img src="../../assets/logo.jpg" alt="Logo SIMPELSI" class="logo-img">
            </div>
            <div>
                <div style="font-size: 18px; font-weight: bold;">Beranda</div>
                <div style="font-size: 12px; opacity: 0.9;">ADMIN</div>
            </div>
        </div>
        <a href="dashboardAdmin.php" class="header-desktop-exit"><span>←</span> KEMBALI</a>
    </div>

    <!-- Sidebar Desktop -->
    <div class="sidebar-desktop">
        <ul class="sidebar-desktop-menu">
            <li>
                <a href="dashboardAdmin.php" class="menu-item">
                    <div class="menu-icon">📊</div>
                    <div>Beranda</div>
                </a>
            </li>
            <li>
                <a href="kelolaLaporan.php" class="menu-item">
                    <div class="menu-icon">📋</div>
                    <div>Kelola Laporan Aduan</div>
                </a>
            </li>
            <li>
                <a href="kelolaArtikel.php" class="menu-item active">
                    <div class="menu-icon">📝</div>
                    <div>Kelola Artikel Edukasi</div>
                </a>
            </li>
            <li>
                <a href="kelolaTPS.php" class="menu-item">
                    <div class="menu-icon">🗑️</div>
                    <div>Kelola Informasi TPS</div>
                </a>
            </li>
            <li>
                <a href="kelolaAkun.php" class="menu-item">
                    <div class="menu-icon">🔐</div>
                    <div>Kelola Akun</div>
                </a>
            </li>
        </ul>
    </div>

    <!-- Navbar Mobile -->
    <div class="navbar-mobile">
        <button class="navbar-mobile-menu-btn" id="menuToggle">☰</button>
        <div class="navbar-mobile-title">
            <div class="logo">
                <img src="../../assets/logo.jpg" alt="Logo SIMPELSI" class="logo-img">
            </div>
            <div>ARTIKEL</div>
        </div>
        <a href="dashboardAdmin.php" class="navbar-mobile-exit">←</a>
    </div>

    <!-- Dropdown Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <ul class="sidebar-menu">
            <li>
                <a href="dashboardAdmin.php" class="menu-item">
                    <div class="menu-icon">📊</div>
                    <div>Beranda</div>
                </a>
            </li>
            <li>
                <a href="kelolaLaporan.php" class="menu-item">
                    <div class="menu-icon">📋</div>
                    <div>Kelola Laporan Aduan</div>
                </a>
            </li>
            <li>
                <a href="kelolaArtikel.php" class="menu-item active">
                    <div class="menu-icon">📝</div>
                    <div>Kelola Artikel Edukasi</div>
                </a>
            </li>
            <li>
                <a href="kelolaTPS.php" class="menu-item">
                    <div class="menu-icon">🗑️</div>
                    <div>Kelola Informasi TPS</div>
                </a>
            </li>
            <li>
                <a href="kelolaAkun.php" class="menu-item">
                    <div class="menu-icon">🔐</div>
                    <div>Kelola Akun</div>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content-header">
            <h2><?= $id ? 'Edit' : 'Tambah' ?> Artikel Edukasi</h2>
        </div>

        <div class="form-container">
            <div class="form-title"><?= $id ? 'Edit' : 'Tambah' ?> Artikel</div>

            <!-- Tambahkan id="artikelForm" untuk referensi JS -->
            <form id="artikelForm" method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Upload Foto</label>
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">📁</div>
                            <div class="upload-text">Klik untuk upload foto artikel</div>
                            <input type="file" id="fotoInput" name="foto" accept="image/*" onchange="previewImage(event)">
                            <div class="upload-preview" id="uploadPreview">
                                <?php if ($fotoLama): ?>
                                    <!-- ✅ TAMPILKAN FOTO DARI URL YANG BENAR -->
                                    <img src="/api/uploads/artikel/<?= htmlspecialchars($fotoLama) ?>" alt="Foto artikel">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Artikel</label>
                        <!-- Hapus atribut required -->
                        <input type="text" class="form-input" name="judul" value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Publikasi</label>
                        <!-- Hapus atribut required -->
                        <input type="datetime-local" class="form-input" name="tanggal" value="<?= $tanggal ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Deskripsi Artikel</label>
                        <!-- Hapus atribut required -->
                        <textarea class="form-textarea" name="deskripsi"><?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="kelolaArtikel.php" class="btn btn-secondary">BATAL</a>
                    <button type="submit" class="btn btn-primary"><?= $id ? 'PERBARUI' : 'SIMPAN' ?> ARTIKEL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Notifikasi Error -->
    <div id="errorPopup" class="popup-overlay">
        <div class="popup-content error">
            <h3>Kesalahan!</h3>
            <p id="errorMessage">Terjadi kesalahan.</p>
            <div style="margin-top: 15px;">
                <button class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
            </div>
        </div>
    </div>

<script>
    document.getElementById('uploadArea').addEventListener('click', function() {
        document.getElementById('fotoInput').click();
    });

    function previewImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const preview = document.getElementById('uploadPreview');
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        preview.innerHTML = '';
        preview.appendChild(img);
        preview.style.display = 'block';
    }

    // Fungsi untuk menampilkan popup error
    function showErrorPopup(message) {
        const popup = document.getElementById('errorPopup');
        const messageElement = document.getElementById('errorMessage');
        messageElement.textContent = message;
        popup.classList.add('active');
        setTimeout(() => {
            popup.querySelector('.popup-content').classList.add('show');
        }, 10);
    }

    // Fungsi untuk menutup popup error
    function closeErrorPopup() {
        const popup = document.getElementById('errorPopup');
        popup.querySelector('.popup-content').classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
        }, 300);
    }

    // Cek apakah ada pesan error PHP untuk ditampilkan SEGERA
    <?php if (isset($error)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showErrorPopup("<?= addslashes(htmlspecialchars($error, ENT_QUOTES, 'UTF-8')) ?>");
    });
    <?php endif; ?>

    // Sisanya dari script DOMContentLoaded seperti sebelumnya
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const mainContent = document.getElementById('mainContent');
        const menuToggle = document.getElementById('menuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const artikelForm = document.getElementById('artikelForm');

        // Toggle sidebar mobile
        menuToggle.addEventListener('click', function() {
            mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
        });

        // Tutup sidebar jika klik di luar sidebar
        document.addEventListener('click', function(event) {
            const isClickInsideNav = menuToggle.contains(event.target);
            const isClickInsideSidebar = mobileSidebar.contains(event.target);

            if (!isClickInsideNav && !isClickInsideSidebar) {
                mobileSidebar.style.display = 'none';
            }
        });

        // Fade-in saat halaman dimuat
        setTimeout(() => {
            body.classList.add('fade-in-ready');
        }, 50);

        // Terapkan fade out saat klik link internal (kecuali logout)
        document.querySelectorAll('.menu-item a, .header-desktop-exit').forEach(link => {
            if(link.href.includes('logout.php')) return; // Lewati link logout
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                mainContent.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = url;
                }, 200);
            });
        });

        // Tambahkan fade-out untuk tombol batal
        const btnBatal = document.querySelector('a.btn-secondary');
        if (btnBatal) {
            btnBatal.addEventListener('click', function(e) {
                e.preventDefault();
                mainContent.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = this.href;
                }, 200);
            });
        }

        // Tambahkan event listener untuk validasi manual sebelum submit
        artikelForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Cegah submit default

            // Ambil nilai dari input
            const judul = artikelForm.querySelector('input[name="judul"]').value.trim();
            const tanggal = artikelForm.querySelector('input[name="tanggal"]').value.trim();
            const deskripsi = artikelForm.querySelector('textarea[name="deskripsi"]').value.trim();

            // Validasi manual
            let errors = [];
            if (judul === '') {
                errors.push('Judul Artikel');
            }
            if (tanggal === '') {
                errors.push('Tanggal Publikasi');
            }
            if (deskripsi === '') {
                errors.push('Deskripsi Artikel');
            }

            if (errors.length > 0) {
                // Tampilkan popup error
                showErrorPopup(`Harap isi field berikut: ${errors.join(', ')}.`);
            } else {
                // Submit form secara manual
                artikelForm.submit();
            }
        });
    });
</script>
</body>
</html>