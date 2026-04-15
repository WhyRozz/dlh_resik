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

// Daftar nama bulan
$bulan_list = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Ambil input filter
$selected_tahun = $_GET['tahun'] ?? date('Y');
$selected_bulan = $_GET['bulan'] ?? date('n');

// Validasi input
$selected_tahun = (int)$selected_tahun;
$selected_bulan = (int)$selected_bulan;

if ($selected_bulan < 1 || $selected_bulan > 12) $selected_bulan = (int)date('n');
if ($selected_tahun < 2000 || $selected_tahun > (int)date('Y') + 1) $selected_tahun = (int)date('Y');

// Buat WHERE clause berdasarkan tahun & bulan
$where = "WHERE (
    (tanggal IS NOT NULL AND YEAR(tanggal) = $selected_tahun AND MONTH(tanggal) = $selected_bulan)
    OR
    (tanggal IS NULL AND YEAR(created_at) = $selected_tahun AND MONTH(created_at) = $selected_bulan)
)";

// Ambil total laporan
$stmt = $pdo->query("SELECT COUNT(*) FROM laporan $where");
$total = $stmt->fetchColumn();

// Ambil jumlah per status
$stmt = $pdo->prepare("SELECT status, COUNT(*) as total FROM laporan $where GROUP BY status");
$stmt->execute();
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['total'];
}

$diproses = $status_counts['Diproses'] ?? 0;
$diterima = $status_counts['Diterima'] ?? 0;
$ditolak = $status_counts['Ditolak'] ?? 0;

$belum_diproses = $diproses;
$selesai_diproses = $diterima;

// === PERBAIKAN: GANTI 7 QUERY JADI 1 QUERY ===
$dates_range = [];
$date_labels = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates_range[] = $date;
    $date_labels[] = date('D', strtotime($date));
}

$placeholders = str_repeat('?,', count($dates_range) - 1) . '?';
$stmt = $pdo->prepare("
    SELECT DATE(COALESCE(tanggal, created_at)) as report_date, COUNT(*) as total
    FROM laporan
    WHERE DATE(COALESCE(tanggal, created_at)) IN ($placeholders)
    GROUP BY report_date
");
$stmt->execute($dates_range);

$result_map = [];
while ($row = $stmt->fetch()) {
    $result_map[$row['report_date']] = (int)$row['total'];
}

$counts = [];
foreach ($dates_range as $date) {
    $counts[] = $result_map[$date] ?? 0;
}
$dates = $date_labels;
// === AKHIR PERBAIKAN ===

// Laporan terbaru (4 terakhir)
$stmt = $pdo->query("
    SELECT lokasi AS alamat, status, created_at 
    FROM laporan 
    ORDER BY created_at DESC 
    LIMIT 4
");
$recent_reports = $stmt->fetchAll();

// Ambil daftar tahun unik dari database untuk dropdown
$stmt = $pdo->query("SELECT DISTINCT YEAR(COALESCE(tanggal, created_at)) as tahun FROM laporan ORDER BY tahun DESC");
$tahun_options = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($tahun_options)) {
    $tahun_options = [date('Y')];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIMPELSI</title>
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
            /* Agar gambar tidak terdistorsi */
            border-radius: 50%;
            /* Tetap bulat */
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
            /* Lebih tinggi dari konten */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        /* Dropdown Mobile - Default: Sembunyikan */
        .mobile-sidebar {
            display: none;
            /* <-- Baris ini diubah dari display: block menjadi display: none */
            position: fixed;
            top: 60px;
            /* Sesuaikan dengan tinggi navbar */
            left: 0;
            width: 100%;
            background: #e6e6e6;
            /* <-- Warna latar sidebar mobile */
            z-index: 1000;
            padding: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            /* Warna latar item */
            border-radius: 0;
            border-radius: 8px;
            margin: 0 10px 4px 10px;
            /* Gaya untuk item mobile */
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

        /* Warna item aktif di sidebar mobile */
        .mobile-sidebar .menu-item.active {
            background: #2e8b57;
            /* Warna latar item aktif */
            color: white;
            box-shadow: 0 2px 6px rgba(46, 139, 87, 0.3);
        }

        .mobile-sidebar .menu-item.active .menu-icon {
            background: white;
            /* Warna ikon di item aktif */
            color: #2e8b57;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            /* Margin untuk sidebar desktop */
            padding: 80px 30px 40px;
            /* Padding untuk header desktop */
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

        .stats-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .stats-header h3 {
            color: #2e8b57;
            font-size: 18px;
        }

        .filter-controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-controls label {
            font-size: 12px;
            color: #2e8b57;
            margin-bottom: 2px;
        }

        .filter-controls select,
        .filter-controls button,
        .filter-controls a {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            border: 1px solid #ccc;
        }

        .filter-controls button {
            background: #2e8b57;
            color: white;
            border: none;
            cursor: pointer;
        }

        .filter-controls button:hover {
            background: #226b43;
        }

        .filter-controls a {
            background: #e6e6e6;
            color: #333;
            text-decoration: none;
            display: inline-block;
        }

        .filter-controls a:hover {
            background: #ddd;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2e8b57;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .trend-chart {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .trend-chart h4 {
            color: #2e8b57;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .mini-bar-chart {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            height: 110px;
            gap: 5px;
        }

        .bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            font-size: 10px;
            color: #666;
            width: 14.28%;
        }

        .bar-value {
            font-weight: bold;
            color: #2e8b57;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .bar-label {
            margin-top: 5px;
            white-space: nowrap;
            font-size: 11px;
        }

        .bar {
            width: 24px;
            background: #2e8b57;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px 3px 0 0;
            transition: all 0.2s;
            font-size: 10px;
        }

        .bar:hover {
            background: #226b43;
            transform: scaleY(1.05);
        }

        .recent-reports {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .recent-reports h4 {
            color: #2e8b57;
            margin-bottom: 10px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #e6f2e6;
            font-weight: bold;
        }

        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-green {
            background: #d4edda;
            color: #155724;
        }

        .status-yellow {
            background: #fff3cd;
            color: #856404;
        }

        .status-red {
            background: #f8d7da;
            color: #721c24;
        }

        /* RESPONSIF: Mobile */
        /* 1. Sembunyikan elemen Desktop di layar kecil */
        @media (max-width: 768px) {

            .header-desktop,
            .sidebar-desktop {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding-top: 70px;
                /* Sesuaikan dengan tinggi navbar mobile */
            }

            /* Penyesuaian layout konten untuk mobile */
            .stats-header {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls select,
            .filter-controls button,
            .filter-controls a {
                width: 100%;
                box-sizing: border-box;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .mini-bar-chart {
                height: 100px;
            }

            .bar {
                width: 18px;
            }

            .bar-label {
                font-size: 10px;
            }
        }

        /* 2. Sembunyikan elemen Mobile di layar lebar */
        @media (min-width: 769px) {

            .navbar-mobile,
            .mobile-sidebar {
                display: none;
            }
        }

        /* Popup Konfirmasi Logout */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: popIn 0.3s ease;
        }

        .popup-content h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #2e8b57;
        }

        .popup-content p {
            margin: 0 0 15px 0;
            color: #555;
        }

        .popup-btn {
            margin: 0 5px;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .popup-btn.yes {
            background: #2e8b57;
            color: white;
        }

        .popup-btn.no {
            background: #dc3545;
            /* Merah */
            color: white;
        }

        .popup-btn:hover {
            opacity: 0.9;
        }

        @keyframes popIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="fade-in">
    <!-- Header Desktop (Tampil di Laptop/PC) -->
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
        <button class="header-desktop-exit" id="logoutBtn">
    <img src="../../assets/icon_keluar.png" alt="Logout Icon" style="width: 20px; height: 20px;">
    KELUAR
</button>
    </div>

    <!-- Sidebar Desktop (Tampil di Laptop/PC) -->
    <div class="sidebar-desktop">
        <ul class="sidebar-desktop-menu">
            <li>
                <a href="dashboardAdmin.php" class="menu-item active">
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
                <a href="kelolaArtikel.php" class="menu-item">
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

    <!-- Navbar Mobile (Tampil di HP) -->
    <div class="navbar-mobile">
        <button class="navbar-mobile-menu-btn" id="menuToggle">☰</button>
        <div class="navbar-mobile-title">
            <div class="logo">
                <img src="../../assets/logo.jpg" alt="Logo SIMPELSI" class="logo-img">
            </div>
            <div>BERANDA</div>
        </div>
        <button class="header-desktop-exit" id="logoutBtnMobile">
        <img src="/WEB/assets/keluar.png" alt="Logout Icon" style="width: 20px; height: 20px;">
    </div>

    <!-- Dropdown Mobile Sidebar (Tampil di HP saat tombol diklik) -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <ul class="sidebar-menu">
            <li>
                <a href="dashboardAdmin.php" class="menu-item active">
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
                <a href="kelolaArtikel.php" class="menu-item">
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

    <!-- Popup Konfirmasi Logout -->
    <div id="popupLogout" class="popup-overlay">
        <div class="popup-content">
            <h3>Apakah Yakin Ingin Keluar?</h3>
            <button class="popup-btn yes" onclick="logout()">Iya</button>
            <button class="popup-btn no" onclick="closePopup()">Tidak</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content-header">
            <h2>Statistik Laporan</h2>
        </div>

        <div class="stats-container">
            <div class="stats-header">
                <h3>Statistik Laporan – <?= htmlspecialchars($bulan_list[$selected_bulan]) ?> <?= $selected_tahun ?></h3>
                <div class="filter-controls">
                    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label for="tahun">Tahun:</label>
                            <select name="tahun" id="tahun">
                                <?php foreach ($tahun_options as $thn): ?>
                                    <option value="<?= $thn ?>" <?= $thn == $selected_tahun ? 'selected' : '' ?>><?= $thn ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bulan">Bulan:</label>
                            <select name="bulan" id="bulan">
                                <?php foreach ($bulan_list as $num => $nama): ?>
                                    <option value="<?= $num ?>" <?= $num == $selected_bulan ? 'selected' : '' ?>><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Tampilkan</button>
                        <a href="dashboardAdmin.php">Reset</a>
                    </form>
                </div>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-number"><?= $total ?></div>
                    <div class="stat-label">Total Laporan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $selesai_diproses ?></div>
                    <div class="stat-label">Selesai Diproses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $belum_diproses ?></div>
                    <div class="stat-label">Belum Diproses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $ditolak ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>

            <div class="trend-chart">
                <h4>Trend Laporan (7 Hari Terakhir)</h4>
                <div class="mini-bar-chart">
                    <?php
                    $max_count = max($counts) ?: 1;
                    ?>
                    <?php foreach ($dates as $i => $day_abbr):
                        $full_date = date('Y-m-d', strtotime("-" . (6 - $i) . " days"));
                        $count = $counts[$i];
                        $height = ($count / $max_count) * 80;
                        if ($height < 10) $height = 10;
                    ?>
                        <div class="bar-container">
                            <div class="bar-value"><?= $count ?></div>
                            <div class="bar" style="height: <?= $height ?>px;"></div>
                            <div class="bar-label"><?= date('d M', strtotime($full_date)) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="recent-reports">
                <h4>Laporan Terbaru</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Alamat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reports as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($r['created_at']))) ?></td>
                                <td><?= htmlspecialchars($r['alamat']) ?></td>
                                <td>
                                    <?php
                                    $status_class = match ($r['status']) {
                                        'Diterima' => 'status-green',
                                        'Diproses' => 'status-yellow',
                                        'Ditolak' => 'status-red',
                                        default => 'status-yellow'
                                    };
                                    ?>
                                    <span class="status <?= $status_class ?>"><?= htmlspecialchars($r['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script untuk toggle sidebar mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const mainContent = document.getElementById('mainContent');
            const menuToggle = document.getElementById('menuToggle');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const logoutBtn = document.getElementById('logoutBtn');
            const logoutBtnMobile = document.getElementById('logoutBtnMobile');
            const popup = document.getElementById('popupLogout');

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

            // Tampilkan popup logout
            logoutBtn.addEventListener('click', function() {
                popup.style.display = 'flex';
            });

            logoutBtnMobile.addEventListener('click', function() {
                popup.style.display = 'flex';
            });

            // Tutup popup jika klik di luar popup
            document.addEventListener('click', function(event) {
                const isClickInsidePopup = popup.contains(event.target);
                const isClickLogoutBtn = logoutBtn.contains(event.target) || logoutBtnMobile.contains(event.target);

                if (!isClickInsidePopup && !isClickLogoutBtn) {
                    popup.style.display = 'none';
                }
            });

            // Fade-in saat halaman dimuat
            setTimeout(() => {
                body.classList.add('fade-in-ready');
            }, 50);

            // Fade-out saat navigasi (termasuk link dalam sidebar mobile)
            document.querySelectorAll('.menu-item a, .filter-controls a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    mainContent.style.opacity = '0';
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 200);
                });
            });

            // Form submit (filter)
            document.querySelectorAll('.filter-controls form').forEach(form => {
                form.addEventListener('submit', function() {
                    mainContent.style.opacity = '0';
                });
            });
        });

        function logout() {
            window.location.href = '../../MainCode/Admin/logout.php';
        }

        function closePopup() {
            document.getElementById('popupLogout').style.display = 'none';
        }
    </script>
</body>

</html>