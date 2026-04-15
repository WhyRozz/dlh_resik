<?php
session_start();

// Anti-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Proteksi login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Admin/login.php");
    exit;
}

require_once '../KoneksiDatabase/koneksi.php';

$mode = 'tambah';
$tps = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tps WHERE id_tps = ?");
    $stmt->execute([$id]);
    $tps = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tps) die("Data TPS tidak ditemukan.");
    $mode = 'edit';
}

// Handle simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_tps'] ?? null;
    $nama_tps = trim($_POST['nama_tps'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kapasitas = trim($_POST['kapasitas'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validasi
    $errors = [];
    if (empty($nama_tps)) $errors[] = "Nama TPS";
    if (empty($alamat)) $errors[] = "Alamat Lengkap";
    if (empty($lokasi)) $errors[] = "Koordinat GPS";

    if (!empty($errors)) {
        $error = "Harap isi field berikut: " . implode(', ', $errors) . ".";
    }

    if (!isset($error)) {
        try {
            $kapasitas = $kapasitas === '' ? null : (int)$kapasitas;
            $lokasi = $lokasi === '' ? null : $lokasi;
            $alamat = $alamat === '' ? null : $alamat;
            $keterangan = $keterangan === '' ? null : $keterangan;

            if ($mode === 'edit') {
                $stmt = $pdo->prepare("
                    UPDATE tps
                    SET nama_tps = ?, lokasi = ?, alamat = ?, kapasitas = ?, keterangan = ?
                    WHERE id_tps = ?
                ");
                $stmt->execute([$nama_tps, $lokasi, $alamat, $kapasitas, $keterangan, $id]);
                header("Location: kelolaTPS.php?sukses=edit");
            } else {
                // --- Gap Filling ---
                $nextId = null;
                $stmt_gap = $pdo->query("
                    SELECT MIN(t1.id_tps + 1) AS next_id
                    FROM tps t1
                    LEFT JOIN tps t2 ON t1.id_tps + 1 = t2.id_tps
                    WHERE t2.id_tps IS NULL
                ");
                $row = $stmt_gap->fetch(PDO::FETCH_ASSOC);

                if ($row && $row['next_id'] !== null) {
                    $nextId = (int)$row['next_id'];
                    $maxStmt = $pdo->query("SELECT MAX(id_tps) AS max_id FROM tps");
                    $maxRow = $maxStmt->fetch(PDO::FETCH_ASSOC);
                    $maxId = (int)$maxRow['max_id'];
                    if ($nextId > $maxId + 1) $nextId = null;
                }

                if ($nextId === null) {
                    $stmt = $pdo->prepare("INSERT INTO tps (nama_tps, lokasi, alamat, kapasitas, keterangan) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nama_tps, $lokasi, $alamat, $kapasitas, $keterangan]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO tps (id_tps, nama_tps, lokasi, alamat, kapasitas, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nextId, $nama_tps, $lokasi, $alamat, $kapasitas, $keterangan]);
                    $pdo->exec("ALTER TABLE tps AUTO_INCREMENT = " . ($nextId + 1));
                }
                header("Location: kelolaTPS.php?sukses=tambah");
            }
            exit;
        } catch (Exception $e) {
            $error = "Gagal menyimpan data: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?> TPS - SIMPELSI</title>
    <link rel="shortcut icon" href="../../assets/logo_simpelsi.png" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        body.fade-in .main-content { opacity: 0; transition: opacity 0.3s ease-out; }
        body.fade-in-ready .main-content { opacity: 1; }

        /* Header Desktop */
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
            text-decoration: none; display: flex; align-items: center; gap: 5px;
        }
        .header-desktop-exit:hover { background: #e6ffe6; transform: scale(1.05); }

        /* Sidebar Desktop */
        .sidebar-desktop {
            width: 250px; background: #e6e6e6;
            position: fixed; top: 60px; left: 0; bottom: 0;
            padding: 20px 0; overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1); z-index: 999;
        }
        .sidebar-desktop-menu { list-style: none; padding: 0 20px; margin: 0; }
        .menu-item {
            padding: 14px 20px; margin-bottom: 8px; background: white;
            border-radius: 10px; transition: all 0.25s ease;
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; color: #333; font-weight: 600; font-size: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .menu-item:hover { background: #f0f0f0; transform: translateX(4px); }
        .menu-item.active { background: #2e8b57; color: white; box-shadow: 0 2px 6px rgba(46,139,87,0.3); }
        .menu-icon {
            width: 32px; height: 32px; background: #2e8b57; color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .logo-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block; }
        .menu-item.active .menu-icon { background: white; color: #2e8b57; }

        /* Mobile */
        @media (max-width: 768px) {
            .header-desktop, .sidebar-desktop { display: none; }
            .main-content { margin-left: 0; padding-top: 70px; }
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
        }
        @media (min-width: 769px) {
            .navbar-mobile, .mobile-sidebar { display: none; }
        }

        .main-content { flex: 1; margin-left: 250px; padding: 80px 30px 40px; background: #f9f9f9; min-height: 100vh; }
        .content-header h2 { color: #2e8b57; font-size: 24px; margin-bottom: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 1000px; margin: 0 auto; }
        .form-title { color: #2e8b57; font-size: 20px; margin-bottom: 20px; border-bottom: 2px solid #2e8b57; padding-bottom: 8px; }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 250px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 14px; color: #555; font-weight: 500; }
        .form-input, .form-textarea { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .form-textarea { min-height: 120px; resize: vertical; }
        .alert { padding: 10px 15px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 12px; margin-top: 25px; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; }
        .btn-primary { background: #2e8b57; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }

        /* Popup */
        .popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; justify-content: center;
            align-items: center; z-index: 9999; display: none;
        }
        .popup-overlay.active { display: flex; }
        .popup-content {
            background: white; padding: 25px; border-radius: 10px; width: 400px; max-width: 90%;
            text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transform: scale(0.8); opacity: 0; transition: transform 0.3s, opacity 0.3s;
        }
        .popup-content.show { transform: scale(1); opacity: 1; }
        .popup-content.error { border-left: 5px solid #dc3545; }
        .popup-content h3 { margin: 0 0 15px; font-size: 20px; }
        .popup-content p { margin: 0 0 20px; color: #555; }
        .popup-btn {
            padding: 10px 20px; background: #2e8b57; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-weight: bold;
        }
        .popup-btn:hover { background: #226b42; }
    </style>
</head>
<body class="fade-in">
    <!-- Header & Sidebar (sama seperti sebelumnya) -->
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

    <div class="sidebar-desktop">
        <ul class="sidebar-desktop-menu">
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div>Beranda</a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div>Kelola Laporan Aduan</a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div>Kelola Artikel Edukasi</a></li>
            <li><a href="kelolaTPS.php" class="menu-item active"><div class="menu-icon">🗑️</div>Kelola Informasi TPS</a></li>
            <li><a href="kelolaAkun.php" class="menu-item"><div class="menu-icon">🔐</div>Kelola Akun</a></li>
        </ul>
    </div>

    <div class="navbar-mobile">
        <button class="navbar-mobile-menu-btn" id="menuToggle">☰</button>
        <div class="navbar-mobile-title">
            <div class="logo">
                <img src="../../assets/logo.jpg" alt="Logo SIMPELSI" class="logo-img">
            </div>
            <div>TPS</div>
        </div>
        <a href="dashboardAdmin.php" class="navbar-mobile-exit">←</a>
    </div>

    <div class="mobile-sidebar" id="mobileSidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div>Beranda</a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div>Kelola Laporan Aduan</a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div>Kelola Artikel Edukasi</a></li>
            <li><a href="kelolaTPS.php" class="menu-item active"><div class="menu-icon">🗑️</div>Kelola Informasi TPS</a></li>
            <li><a href="kelolaAkun.php" class="menu-item"><div class="menu-icon">🔐</div>Kelola Akun</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-header">
            <h2><?= $mode === 'edit' ? 'Edit Informasi TPS' : 'Tambah Informasi TPS' ?></h2>
        </div>

        <div class="form-container">
            <div class="form-title"><?= $mode === 'edit' ? 'Edit TPS' : 'Form Tambah TPS' ?></div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="tpsForm" method="POST" action="">
                <?php if ($mode === 'edit'): ?>
                    <input type="hidden" name="id_tps" value="<?= htmlspecialchars($tps['id_tps']) ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama TPS *</label>
                        <input type="text" name="nama_tps" class="form-input" value="<?= htmlspecialchars($tps['nama_tps'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Koordinat GPS (Latitude, Longitude) *</label>
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <textarea name="lokasi" class="form-textarea" placeholder="Contoh: -7.854321,112.123456"><?= htmlspecialchars($tps['lokasi'] ?? '') ?></textarea>
                            <button type="button" class="btn btn-secondary" style="height: fit-content; padding: 12px 12px;" onclick="bukaGoogleMaps()">🗺️ Pilih di Maps</button>
                        </div>
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 6px;">
                            1. Klik tombol "Pilih di Maps"<br>
                            2. Klik lokasi di Google Maps → koordinat muncul di kiri bawah<br>
                            3. Salin & tempel ke kolom di atas
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap *</label>
                        <textarea name="alamat" class="form-textarea" placeholder="Contoh: Jl. Merdeka No. 15, Kel. Beran, Kec. Nganjuk"><?= htmlspecialchars($tps['alamat'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Kapasitas (opsional)</label>
                        <input type="number" name="kapasitas" class="form-input" value="<?= htmlspecialchars($tps['kapasitas'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Keterangan (opsional)</label>
                        <textarea name="keterangan" class="form-textarea"><?= htmlspecialchars($tps['keterangan'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="kelolaTPS.php" class="btn btn-secondary">BATAL</a>
                    <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'SIMPAN PERUBAHAN' : 'SIMPAN TPS' ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Error (tetap ada untuk validasi) -->
    <div id="errorPopup" class="popup-overlay">
        <div class="popup-content error">
            <h3>Kesalahan!</h3>
            <p id="errorMessage">Terjadi kesalahan.</p>
            <button class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
        </div>
    </div>

    <script>
        const koordinatTersimpan = <?= json_encode($tps['lokasi'] ?? null) ?>;

        function bukaGoogleMaps() {
            let url = 'https://www.google.com/maps';
            if (koordinatTersimpan && /^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/.test(koordinatTersimpan)) {
                url = `https://www.google.com/maps/@${koordinatTersimpan},18z`;
            } else {
                url = 'https://www.google.com/maps/@-7.599401,111.900081,11z';
            }
            window.open(url, '_blank');
        }

        function showErrorPopup(message) {
            const popup = document.getElementById('errorPopup');
            document.getElementById('errorMessage').textContent = message;
            popup.classList.add('active');
            setTimeout(() => popup.querySelector('.popup-content').classList.add('show'), 10);
        }

        function closeErrorPopup() {
            const popup = document.getElementById('errorPopup');
            popup.querySelector('.popup-content').classList.remove('show');
            setTimeout(() => popup.classList.remove('active'), 300);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Validasi form
            document.getElementById('tpsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const nama = this.querySelector('[name="nama_tps"]').value.trim();
                const lokasi = this.querySelector('[name="lokasi"]').value.trim();
                const alamat = this.querySelector('[name="alamat"]').value.trim();
                let errors = [];
                if (!nama) errors.push('Nama TPS');
                if (!lokasi) errors.push('Koordinat GPS');
                if (!alamat) errors.push('Alamat Lengkap');
                if (errors.length) {
                    showErrorPopup(`Harap isi field berikut: ${errors.join(', ')}.`);
                } else {
                    this.submit();
                }
            });

            // Tampilkan error PHP jika ada
            <?php if (isset($error)): ?>
                showErrorPopup("<?= addslashes(htmlspecialchars($error)) ?>");
            <?php endif; ?>

            // Fade dan mobile menu (sama seperti sebelumnya)
            const body = document.body;
            setTimeout(() => body.classList.add('fade-in-ready'), 50);
            
            const menuToggle = document.getElementById('menuToggle');
            const mobileSidebar = document.getElementById('mobileSidebar');
            menuToggle?.addEventListener('click', () => {
                mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
            });
            document.addEventListener('click', (e) => {
                if (!menuToggle?.contains(e.target) && !mobileSidebar?.contains(e.target)) {
                    mobileSidebar.style.display = 'none';
                }
            });

            // Fade out saat klik link
            document.querySelectorAll('a[href]').forEach(link => {
                if (!link.href.includes('logout.php') && (link.classList.contains('btn-secondary') || link.closest('.menu-item') || link.classList.contains('header-desktop-exit'))) {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        document.querySelector('.main-content').style.opacity = '0';
                        setTimeout(() => window.location.href = link.href, 200);
                    });
                }
            });
        });
    </script>
</body>
</html>