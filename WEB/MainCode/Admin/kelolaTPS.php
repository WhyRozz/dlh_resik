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

// Load koneksi database
require_once '../KoneksiDatabase/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola TPS - SIMPELSI</title>
    <link rel="shortcut icon" href="../../assets/logo_simpelsi.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        /* Header Desktop */
        .header-desktop {
            width: 100%;
            background: #2e8b57;
            color: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header-desktop-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-desktop-logo {
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

        .header-desktop-exit {
            background: white;
            color: #2e8b57;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .header-desktop-exit:hover {
            background: #e6ffe6;
            transform: scale(1.05);
        }

        /* Sidebar Desktop */
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
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .menu-item.active .menu-icon {
            background: white;
            color: #2e8b57;
        }

        /* Navbar Mobile */
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

        /* Dropdown Mobile Sidebar */
        .mobile-sidebar {
            display: none;
            position: fixed;
            top: 60px;
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

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 80px 30px 40px;
            background: #f9f9f9;
            min-height: 100vh;
            opacity: 1;
            transition: opacity 0.25s ease-in-out;
        }

        .main-content.fade-out {
            opacity: 0;
        }

        .content-header {
            margin-bottom: 20px;
        }

        .content-header h2 {
            color: #2e8b57;
            font-size: 24px;
        }

        .search-bar {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        .table-title {
            color: #2e8b57;
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 2px solid #2e8b57;
            padding-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            font-size: 13px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .btn-add {
            background: #2e8b57; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: bold; font-size: 14px; margin-top: 20px;
            display: inline-flex; align-items: center; gap: 8px;
            transition: background 0.2s;
        }
        .btn-add:hover { background: #226b42; }

        .header-artikel {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

        /* === POPUP KONFIRMASI HAPUS === */
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
            display: none;
            pointer-events: none;
        }
        .popup-overlay.active {
            display: flex;
            pointer-events: auto;
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
        .popup-btns {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .popup-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s;
        }
        .popup-btn.cancel {
            background: #6c757d;
            color: white;
        }
        .popup-btn.cancel:hover {
            background: #5a6268;
        }
        .popup-btn.confirm {
            background: #dc3545;
            color: white;
        }
        .popup-btn.confirm:hover {
            background: #c82333;
        }

        /* ✅ POPUP SUKSES */
        .popup-content.success { 
            border-left: 5px solid #28a745; 
        }

        @media (max-width: 768px) {
            .header-desktop, .sidebar-desktop { display: none; }
            .main-content { margin-left: 0; padding-top: 70px; }
            th, td { padding: 10px 8px; font-size: 13px; }
            .btn-action { width: 28px; height: 28px; font-size: 12px; }
            .footer-buttons { flex-direction: column; gap: 10px; }
        }

        @media (max-width: 480px) {
            .table-container { padding: 15px; }
            .table-title { font-size: 18px; }
            .btn-footer { width: 100%; justify-content: center; }
        }

        @media (min-width: 769px) {
            .navbar-mobile, .mobile-sidebar { display: none; }
        }
    </style>
</head>

<body class="fade-in">
    <!-- Header & Sidebar (tidak diubah) -->
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
        <a href="dashboardAdmin.php" class="header-desktop-exit">
            <span>←</span> KEMBALI
        </a>
    </div>

    <div class="sidebar-desktop">
        <ul class="sidebar-desktop-menu">
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div><div>Beranda</div></a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div><div>Kelola Laporan Aduan</div></a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div><div>Kelola Artikel Edukasi</div></a></li>
            <li><a href="kelolaTPS.php" class="menu-item active"><div class="menu-icon">🗑️</div><div>Kelola Informasi TPS</div></a></li>
            <li><a href="kelolaAkun.php" class="menu-item"><div class="menu-icon">🔐</div><div>Kelola Akun</div></a></li>
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
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div><div>Beranda</div></a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div><div>Kelola Laporan Aduan</div></a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div><div>Kelola Artikel Edukasi</div></a></li>
            <li><a href="kelolaTPS.php" class="menu-item active"><div class="menu-icon">🗑️</div><div>Kelola Informasi TPS</div></a></li>
            <li><a href="kelolaAkun.php" class="menu-item"><div class="menu-icon">🔐</div><div>Kelola Akun</div></a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-header">
            <h2>Kelola TPS</h2>
        </div>

        <div class="search-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="Cari TPS berdasarkan nama atau lokasi...">
        </div>

        <div class="table-container">
            <div class="header-artikel">
                <div class="table-title">Daftar Informasi TPS</div>
                    <a href="form-tps.php" class="btn-add">
                        <span>➕</span> BUAT INFO TPS
                    </a>
                </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAMA TPS</th>
                        <th>LOKASI</th>
                        <th>KAPASITAS</th>
                        <th>KETERANGAN</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody id="tpsTableBody">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT * FROM tps ORDER BY id_tps ASC");
                        $tpsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($tpsList as $tps): ?>
                            <tr>
                                <td><?= htmlspecialchars($tps['id_tps']) ?></td>
                                <td><?= htmlspecialchars($tps['nama_tps']) ?></td>
                                <td>
                                    <?php if (!empty($tps['lokasi']) && preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $tps['lokasi'])): ?>
                                        <a href="https://maps.google.com/maps?q=<?= urlencode($tps['lokasi']) ?>" target="_blank" style="color: #2e8b57; text-decoration: none;">🗺️ Lihat di Maps</a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($tps['lokasi'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($tps['kapasitas'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(substr($tps['keterangan'] ?? '', 0, 30)) ?><?= strlen($tps['keterangan'] ?? '') > 30 ? '...' : '' ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="form-tps.php?id=<?= $tps['id_tps'] ?>" class="btn-action btn-edit">✏️</a>
                                        <button class="btn-action btn-delete" onclick="konfirmasiHapus(<?= $tps['id_tps'] ?>)">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach;

                        if (empty($tpsList)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">Belum ada data TPS.</td>
                            </tr>
                        <?php endif;
                    } catch (Exception $e) {
                        echo '<tr><td colspan="6" style="color: red; text-align: center;">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popup Konfirmasi Hapus (tetap ada) -->
    <div id="confirmPopup" class="popup-overlay">
        <div class="popup-content">
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus data TPS ini?</p>
            <div class="popup-btns">
                <button class="popup-btn cancel" onclick="closeConfirmPopup()">Batal</button>
                <button class="popup-btn confirm" onclick="hapusTPS()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- ✅ POPUP SUKSES -->
    <div id="successPopup" class="popup-overlay">
        <div class="popup-content success">
            <h3>Berhasil!</h3>
            <p id="successMessage">Data TPS telah diperbarui.</p>
            <button class="popup-btn" style="background: #28a745;" onclick="closeSuccessPopup()">Tutup</button>
        </div>
    </div>

    <script>
        // === POPUP SUKSES ===
        function showSuccessPopup(message) {
            document.getElementById('successMessage').textContent = message;
            const popup = document.getElementById('successPopup');
            popup.classList.add('active');
            setTimeout(() => {
                popup.querySelector('.popup-content').classList.add('show');
            }, 10);
        }

        function closeSuccessPopup() {
            const popup = document.getElementById('successPopup');
            popup.querySelector('.popup-content').classList.remove('show');
            setTimeout(() => {
                popup.classList.remove('active');
            }, 300);
        }

        // Konfirmasi hapus (tidak diubah)
        let idYangAkanDihapus = null;
        function konfirmasiHapus(id) {
            idYangAkanDihapus = id;
            const popup = document.getElementById('confirmPopup');
            popup.classList.add('active');
            setTimeout(() => popup.querySelector('.popup-content').classList.add('show'), 10);
        }

        function closeConfirmPopup() {
            const popup = document.getElementById('confirmPopup');
            popup.querySelector('.popup-content').classList.remove('show');
            setTimeout(() => {
                popup.classList.remove('active');
                idYangAkanDihapus = null;
            }, 300);
        }

        function hapusTPS() {
            if (idYangAkanDihapus === null) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete-tps.php';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = idYangAkanDihapus;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        // === DETEKSI SUKSES DARI URL ===
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const mainContent = document.getElementById('mainContent');
            const menuToggle = document.getElementById('menuToggle');
            const mobileSidebar = document.getElementById('mobileSidebar');

            // Fade-in
            setTimeout(() => body.classList.add('fade-in-ready'), 50);

            // Mobile menu
            menuToggle?.addEventListener('click', () => {
                mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
            });
            document.addEventListener('click', (e) => {
                if (!menuToggle?.contains(e.target) && !mobileSidebar?.contains(e.target)) {
                    mobileSidebar.style.display = 'none';
                }
            });

            // Fade-out navigasi
            document.querySelectorAll('a[href]').forEach(link => {
                if (!link.href.includes('logout.php') && 
                    (link.classList.contains('btn-create') || 
                     link.closest('.menu-item') || 
                     link.classList.contains('header-desktop-exit'))) {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        mainContent.style.opacity = '0';
                        setTimeout(() => window.location.href = link.href, 200);
                    });
                }
            });

            // === 🔑 DETEKSI PARAMETER SUKSES ===
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('sukses')) {
                const type = urlParams.get('sukses');
                let message = 'Operasi berhasil.';
                if (type === 'tambah') {
                    message = 'Data TPS berhasil ditambahkan.';
                } else if (type === 'edit') {
                    message = 'Data TPS berhasil diperbarui.';
                }
                showSuccessPopup(message);
                // Hapus parameter dari URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Pencarian
            document.getElementById('searchInput').addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                document.querySelectorAll('#tpsTableBody tr').forEach(row => {
                    const nama = row.cells[1]?.textContent.toLowerCase() || '';
                    const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
                    row.style.display = (nama.includes(query) || lokasi.includes(query)) ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>