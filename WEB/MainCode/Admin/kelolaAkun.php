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
$message = '';
$error = '';

// 🔐 Kunci enkripsi — ganti dengan string 32-byte acak di production
define('ENCRYPTION_KEY', 'SIMPELSI_2025_ADMIN_ENCRYPTION_KEY_STRONG_512');

// 🔐 Enkripsi & Dekripsi AES-256-CBC
function encrypt_pass($plaintext, $key = ENCRYPTION_KEY) {
    if (empty($plaintext)) return null;
    $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options = 0, $iv);
    return base64_encode($iv . $ciphertext);
}
function decrypt_pass($ciphertext, $key = ENCRYPTION_KEY) {
    if (empty($ciphertext)) return null;
    $ciphertext = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
    $iv = substr($ciphertext, 0, $ivlen);
    $ciphertext = substr($ciphertext, $ivlen);
    $decrypted = openssl_decrypt($ciphertext, $cipher, $key, $options = 0, $iv);
    return $decrypted === false ? null : $decrypted;
}

// ✅ HANDLE AJAX: get_password (placeholder ••••••••)
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_password') {
    $id_admin = (int)($_POST['id_admin'] ?? 0);
    $stmt = $pdo->prepare("SELECT password FROM admin WHERE id_admin = ?");
    $stmt->execute([$id_admin]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode(['status' => 'success', 'password' => '••••••••']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Akun tidak ditemukan.']);
    }
    exit;
}

// ✅ HANDLE AJAX: get_password_raw → baca dari password_encrypted (tanpa hapus)
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_password_raw') {
    $id_admin = (int)($_POST['id_admin'] ?? 0);
    $stmt = $pdo->prepare("SELECT password_encrypted FROM admin WHERE id_admin = ?");
    $stmt->execute([$id_admin]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['password_encrypted'])) {
        $decrypted = decrypt_pass($row['password_encrypted']);
        $passwordToDisplay = $decrypted ?: '••••••••';
    } else {
        $passwordToDisplay = '••••••••';
    }
    echo json_encode(['status' => 'success', 'password' => $passwordToDisplay]);
    exit;
}

// PROSES SIMPAN/EDIT/HAPUS
if ($_POST['action'] ?? '' === 'simpan') {
    $id_admin = $_POST['id'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (empty($password) && !$id_admin) {
        $error = "Password wajib diisi untuk akun baru.";
    } else {
        $stmt = $pdo->prepare("SELECT id_admin FROM admin WHERE email = ? AND id_admin != ?");
        $stmt->execute([$email, $id_admin ?: 0]);
        if ($stmt->fetch()) {
            $error = "Email sudah terdaftar.";
        } else {
            if ($id_admin) {
                // ✅ EDIT — UPDATE password & password_encrypted (jangan NULL!)
                $sql = "UPDATE admin SET email = ?";
                $params = [$email];
                if (!empty($password) && $password !== '••••••••') {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $encryptedPassword = encrypt_pass($password); // ← enkripsi password baru
                    if (!$hashedPassword || !$encryptedPassword) {
                        $error = "Gagal membuat hash/enkripsi password.";
                    } else {
                        $sql .= ", password = ?, password_encrypted = ?"; // ← simpan keduanya
                        $params[] = $hashedPassword;
                        $params[] = $encryptedPassword;
                    }
                }
                $sql .= " WHERE id_admin = ?";
                $params[] = $id_admin;
                if (!$error) {
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute($params)) {
                        $message = "Akun berhasil diperbarui.";
                    } else {
                        $error = "Gagal memperbarui akun.";
                    }
                }
            } else {
                // ✅ TAMBAH — isi password + password_encrypted
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $encryptedPassword = encrypt_pass($password);
                if (!$hashedPassword || !$encryptedPassword) {
                    $error = "Gagal membuat hash/enkripsi password.";
                } else {
                    $stmt_count = $pdo->query("SELECT COUNT(*) FROM admin");
                    $total = $stmt_count->fetchColumn();
                    if ($total >= 3) {
                        $error = "Jumlah akun admin sudah mencapai batas maksimal (3).";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO admin (email, password, password_encrypted) VALUES (?, ?, ?)");
                        if ($stmt->execute([$email, $hashedPassword, $encryptedPassword])) {
                            $message = "Akun berhasil ditambahkan.";
                        } else {
                            $error = "Gagal menambah akun.";
                        }
                    }
                }
            }
        }
    }
}

// HAPUS (setelah OTP)
if (isset($_POST['hapus_id_confirmed'])) {
    $hapus_id = (int)$_POST['hapus_id_confirmed'];
    $stmt = $pdo->prepare("DELETE FROM admin WHERE id_admin = ?");
    if ($stmt->execute([$hapus_id])) {
        $message = "Akun berhasil dihapus.";
    } else {
        $error = "Gagal menghapus akun.";
    }
}

// Ambil semua akun
$stmt_all = $pdo->prepare("
    SELECT * FROM admin 
    ORDER BY 
        CASE WHEN email = 'simpelsi2025@gmail.com' THEN 0 ELSE 1 END,
        id_admin ASC
");
$stmt_all->execute();
$admins = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
$total_admins = count($admins);
$akun_utama = null;
$tambahan = [];
foreach ($admins as $a) {
    if ($a['email'] === 'simpelsi2025@gmail.com') {
        $akun_utama = $a;
    } else {
        $tambahan[] = $a;
    }
}
if (!$akun_utama && !empty($admins)) {
    $akun_utama = array_shift($admins);
    $tambahan = $admins;
}

// ✅ Auto-create kolom password_encrypted jika belum ada
try {
    $pdo->query("SELECT password_encrypted FROM admin LIMIT 1");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'password_encrypted') !== false) {
        $pdo->exec("ALTER TABLE admin ADD COLUMN password_encrypted TEXT NULL AFTER password");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - SIMPELSI</title>
    <link rel="shortcut icon" href="../../assets/logo_simpelsi.png" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; display: flex; min-height: 100vh; }
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
        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
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
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .menu-item:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        .menu-item.active {
            background: #2e8b57;
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(46,139,87,0.3);
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
            box-shadow: 0 2px 6px rgba(46,139,87,0.3);
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
        /* Accounts Grid */
        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .account-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            position: relative;
            transition: transform 0.2s;
        }
        .account-card:hover {
            transform: translateY(-3px);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .card-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .badge-default {
            background: #20A726;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-tambah {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .account-info {
            margin-bottom: 15px;
        }
        .account-info label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }
        .account-info span {
            font-weight: bold;
            color: #2e8b57;
        }
        .btn-group {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary { background: #2e8b57; color: white; }
        .btn-primary:hover { background: #226b43; }
        .btn-outline { background: transparent; border: 1px solid #2e8b57; color: #2e8b57; }
        .btn-outline:hover { background: #e6f2e6; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .slot-tambah {
            background: #f0f9f0;
            border: 2px dashed #20A726;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .slot-tambah:hover {
            background: #e6f7e6;
            border-color: #095E0D;
        }
        .slot-title {
            font-size: 16px;
            font-weight: bold;
            color: #20A726;
            margin-bottom: 10px;
        }
        .slot-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }
        .slot-btn {
            background: #20A726;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .slot-btn:hover {
            background: #095E0D;
        }
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        .form-section h3 {
            color: #2e8b57;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
        }
        .form-note {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: #2e8b57;
        }
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .modal-body label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .modal-body input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-otp {
            background: #20A726;
            color: white;
            border: none;
        }
        .btn-cancel {
            background: #f0f0f0;
            color: #333;
            border: none;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
        }
        /* Eye Icon Styling */
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;
            height: 20px;
            z-index: 10;
        }
        .toggle-password img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .toggle-password:hover img {
            filter: brightness(0.8);
        }
        @media (max-width: 768px) {
            .header-desktop, .sidebar-desktop {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
            .accounts-grid {
                grid-template-columns: 1fr;
            }
            .account-card {
                padding: 15px;
            }
            .card-title {
                font-size: 14px;
            }
            .badge-default, .badge-tambah {
                font-size: 8px;
                padding: 2px 6px;
            }
            .btn {
                font-size: 11px;
                padding: 5px 10px;
            }
            .form-section {
                padding: 20px;
            }
            .form-section h3 {
                font-size: 16px;
            }
            .form-group label {
                font-size: 13px;
            }
            .form-control {
                font-size: 13px;
            }
            .form-actions {
                gap: 8px;
            }
            .modal-content {
                padding: 20px;
            }
            .modal-title {
                font-size: 16px;
            }
            .modal-body input {
                font-size: 14px;
            }
            .btn-small {
                font-size: 12px;
                padding: 6px 12px;
            }
        }
        @media (max-width: 480px) {
            .navbar-mobile-title {
                font-size: 14px;
            }
            .navbar-mobile-title .logo {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            .mobile-sidebar .menu-item {
                font-size: 13px;
                padding: 10px 12px;
            }
            .table-container {
                padding: 15px;
            }
            .table-title {
                font-size: 18px;
            }
            .btn-footer {
                width: 100%;
                justify-content: center;
            }
        }
        @media (min-width: 769px) {
            .navbar-mobile, .mobile-sidebar {
                display: none;
            }
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
                <div style="font-size: 18px; font-weight: bold;">Kelola Akun</div>
                <div style="font-size: 12px; opacity: 0.9;">ADMIN</div>
            </div>
        </div>
        <a href="dashboardAdmin.php" class="header-desktop-exit">
            <span>←</span> KEMBALI
        </a>
    </div>
    <!-- Sidebar Desktop -->
    <div class="sidebar-desktop">
        <ul class="sidebar-desktop-menu">
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div>Beranda</a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div>Kelola Laporan Aduan</a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div>Kelola Artikel Edukasi</a></li>
            <li><a href="kelolaTPS.php" class="menu-item"><div class="menu-icon">🗑️</div>Kelola Informasi TPS</a></li>
            <li><a href="kelolaAkun.php" class="menu-item active"><div class="menu-icon">🔐</div>Kelola Akun</a></li>
        </ul>
    </div>
    <!-- Navbar Mobile -->
    <div class="navbar-mobile">
        <button class="navbar-mobile-menu-btn" id="menuToggle">☰</button>
        <div class="navbar-mobile-title">
            <div class="logo">
                <img src="../../assets/logo.jpg" alt="Logo SIMPELSI" class="logo-img">
            </div>
            <div>Kelola Akun</div>
        </div>
        <a href="dashboardAdmin.php" class="navbar-mobile-exit">←</a>
    </div>
    <!-- Dropdown Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboardAdmin.php" class="menu-item"><div class="menu-icon">📊</div>Beranda</a></li>
            <li><a href="kelolaLaporan.php" class="menu-item"><div class="menu-icon">📋</div>Kelola Laporan Aduan</a></li>
            <li><a href="kelolaArtikel.php" class="menu-item"><div class="menu-icon">📝</div>Kelola Artikel Edukasi</a></li>
            <li><a href="kelolaTPS.php" class="menu-item"><div class="menu-icon">🗑️</div>Kelola Informasi TPS</a></li>
            <li><a href="kelolaAkun.php" class="menu-item active"><div class="menu-icon">🔐</div>Kelola Akun</a></li>
        </ul>
    </div>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content-header">
            <h2>Kelola Akun Admin (<?= $total_admins ?>/3)</h2>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="accounts-grid">
            <!-- AKUN UTAMA -->
            <div class="account-card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🔒</span> Akun Utama
                    </div>
                    <span class="badge-default">DEFAULT</span>
                </div>
                <div class="account-info">
                    <label>Email:</label>
                    <span><?= htmlspecialchars($akun_utama ? $akun_utama['email'] : 'Belum dibuat') ?></span>
                </div>
                <div class="account-info">
                    <label>Password:</label>
                    <span><?= $akun_utama ? '••••••••' : '—' ?></span>
                </div>
                <div class="btn-group">
                    <?php if ($akun_utama): ?>
                        <button class="btn btn-outline"
                            onclick="requestOTPForAction('edit', <?= $akun_utama['id_admin'] ?>, '<?= htmlspecialchars($akun_utama['email'], ENT_QUOTES) ?>')">
                            Edit
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary" onclick="createDefaultAccount()">
                            Buat Akun Utama
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- SLOT TAMBAHAN -->
            <?php for ($i = 0; $i < 2; $i++): ?>
                <?php if (isset($tambahan[$i])): $a = $tambahan[$i]; ?>
                    <div class="account-card">
                        <div class="card-header">
                            <div class="card-title">
                                <span>👤</span> Akun Tambahan
                            </div>
                            <span class="badge-tambah">TAMBAHAN</span>
                        </div>
                        <div class="account-info">
                            <label>Email:</label>
                            <span><?= htmlspecialchars($a['email']) ?></span>
                        </div>
                        <div class="account-info">
                            <label>Password:</label>
                            <span>••••••••</span>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline"
                                onclick="requestOTPForAction('edit', <?= $a['id_admin'] ?>, '<?= htmlspecialchars($a['email'], ENT_QUOTES) ?>')">
                                Edit
                            </button>
                            <button class="btn btn-danger"
                                onclick="requestOTPForAction('delete', <?= $a['id_admin'] ?>, '<?= htmlspecialchars($a['email'], ENT_QUOTES) ?>')">
                                Hapus
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="slot-tambah" onclick="showAddForm()">
                        <div class="slot-title">➕ Tambah Akun Baru</div>
                        <div class="slot-desc">Buat akun admin cadangan untuk login alternatif.</div>
                        <button class="slot-btn">Tambah Akun</button>
                    </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <!-- FORM TAMBAH/EDIT -->
        <div class="form-section" id="formSection" style="display:none;">
            <h3 id="formTitle">Tambah Akun Baru</h3>
            <form method="POST" id="accountForm">
                <input type="hidden" name="action" value="simpan">
                <input type="hidden" name="id" id="formIdAdmin" value="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="off">
                        <span class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility()">
                            <img src="../../assets/hide.png" alt="Hide Password" id="eyeIconImg">
                        </span>
                    </div>
                </div>
                <div class="form-note">
                    Untuk edit: biarkan kosong jika tidak ingin ganti password.
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <!-- MODAL: Batas Akun -->
    <div class="modal" id="limitModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">⚠️ Batas Akun Tercapai</div>
                <span class="close-modal" onclick="closeModal('limitModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p><strong>Jumlah akun admin sudah mencapai batas maksimal (3).</strong></p>
                <p>Silakan <strong>hapus salah satu akun tambahan</strong> terlebih dahulu jika ingin menambah akun baru.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-small btn-otp" onclick="closeModal('limitModal')">Mengerti</button>
            </div>
        </div>
    </div>
    <!-- MODAL: Kirim OTP -->
    <div class="modal" id="otpRequestModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="otpModalTitle">Kirim Kode OTP</div>
                <span class="close-modal" onclick="closeModal('otpRequestModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>Kode OTP akan dikirim ke email berikut:</p>
                <div style="background:#f0f9f0; padding:10px; border-radius:6px; font-weight:bold; color:#20A726;" id="targetEmailDisplay">
                    email@domain.com
                </div>
                <button type="button" class="btn-small btn-otp" onclick="sendOTPToTarget()" style="margin-top:15px;">
                    Kirim Kode OTP Sekarang
                </button>
                <div id="otpRequestStatus" style="margin-top:10px;"></div>
            </div>
        </div>
    </div>
    <!-- MODAL: Verifikasi OTP -->
    <div class="modal" id="otpVerifyModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Masukkan Kode OTP</div>
                <span class="close-modal" onclick="closeModal('otpVerifyModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>Masukkan kode 4 digit yang dikirim ke <span id="otpTargetEmail" style="font-weight:bold;">email@domain.com</span>.</p>
                <label for="otpInput">Kode OTP</label>
                <input type="text" id="otpInput" maxlength="4" placeholder="1234" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                <div id="otpVerifyStatus"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-small btn-cancel" onclick="closeModal('otpVerifyModal')">Batal</button>
                <button type="button" class="btn-small btn-otp" onclick="verifyOTP()">Verifikasi</button>
            </div>
        </div>
    </div>
    <script>
        let currentAction = '';
        let currentIdAdmin = null;
        let currentEmail = '';
        let isEditing = false;

        // ✅ Toggle password visibility — selalu panggil get_password_raw saat edit
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const img = document.getElementById('eyeIconImg');
            if (input.type === "password") {
                if (isEditing) {
                    const id_admin = document.getElementById('formIdAdmin').value;
                    if (!id_admin) {
                        alert('ID admin tidak ditemukan.');
                        return;
                    }
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'ajax=get_password_raw&id_admin=' + encodeURIComponent(id_admin)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            input.type = "text";
                            input.value = data.password;
                            img.src = "../../assets/show.png";
                        } else {
                            alert('Gagal: ' + (data.message || 'Error'));
                        }
                    })
                    .catch(() => alert('Gagal menghubungi server.'));
                } else {
                    input.type = "text";
                    img.src = "../../assets/show.png";
                }
            } else {
                input.type = "password";
                img.src = "../../assets/hide.png";
            }
        }

        // ✅ Reset tanpa hapus encrypted (sesuai permintaan)
        function resetForm() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('formIdAdmin').value = '';
            document.getElementById('eyeIconImg').src = '../../assets/hide.png';
            document.getElementById('password').type = 'password';
            isEditing = false;
        }

        function showAddForm() {
            const total = <?= $total_admins ?>;
            if (total >= 3) {
                document.getElementById('limitModal').style.display = 'flex';
            } else {
                resetForm();
                document.getElementById('formTitle').textContent = 'Tambah Akun Baru';
                document.getElementById('formSection').style.display = 'block';
                document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
                isEditing = false;
            }
        }

        function createDefaultAccount() {
            resetForm();
            document.getElementById('formTitle').textContent = 'Buat Akun Utama';
            document.getElementById('email').value = 'simpelsi2025@gmail.com';
            document.getElementById('password').value = 'Admin123';
            document.getElementById('formSection').style.display = 'block';
            document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
            isEditing = false;
        }

        function requestOTPForAction(action, id_admin, email) {
            currentAction = action;
            currentIdAdmin = id_admin;
            currentEmail = email;
            document.getElementById('targetEmailDisplay').textContent = email;
            document.getElementById('otpTargetEmail').textContent = email;
            let title = 'Verifikasi ';
            if (action === 'edit') title += 'Edit Akun';
            else if (action === 'delete') title += 'Hapus Akun';
            document.getElementById('otpModalTitle').textContent = title;
            document.getElementById('otpRequestModal').style.display = 'flex';
        }

        function sendOTPToTarget() {
            fetch('request_otp_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: currentEmail })
            })
            .then(res => res.json())
            .then(data => {
                const statusDiv = document.getElementById('otpRequestStatus');
                if (data.status === 'success' || data.status === 'success_dev') {
                    let msg = `<div style="color:#20A726;">✅ OTP dikirim ke ${currentEmail}</div>`;
                    if (data.status === 'success_dev') {
                        msg = `<div style="background:#e6f7e6; padding:8px; border-radius:4px; color:#095E0D;">[DEV] Gunakan kode: <strong>${data.otp}</strong></div>`;
                    }
                    statusDiv.innerHTML = msg;
                    setTimeout(() => {
                        document.getElementById('otpRequestModal').style.display = 'none';
                        document.getElementById('otpVerifyModal').style.display = 'flex';
                        if (data.otp) {
                            document.getElementById('otpInput').value = data.otp;
                        }
                        document.getElementById('otpInput').focus();
                    }, 800);
                } else {
                    statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
                }
            })
            .catch(() => {
                document.getElementById('otpRequestStatus').innerHTML = '<div class="alert-error">❌ Gagal kirim OTP.</div>';
            });
        }

        function verifyOTP() {
            const otp = document.getElementById('otpInput').value.trim();
            if (!otp || otp.length !== 4 || isNaN(otp)) {
                document.getElementById('otpVerifyStatus').innerHTML = '<div class="alert-error">OTP harus 4 digit angka.</div>';
                return;
            }
            fetch('verify_otp_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: currentEmail, otp: otp })
            })
            .then(res => res.json())
            .then(data => {
                const statusDiv = document.getElementById('otpVerifyStatus');
                if (data.status === 'success') {
                    statusDiv.innerHTML = `<div style="color:#20A726;">✅ OTP valid. Memproses...</div>`;
                    setTimeout(() => {
                        closeModal('otpVerifyModal');
                        executeAction();
                    }, 600);
                } else {
                    statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
                }
            })
            .catch(() => {
                document.getElementById('otpVerifyStatus').innerHTML = '<div class="alert-error">❌ Kesalahan jaringan.</div>';
            });
        }

        function executeAction() {
            if (currentAction === 'edit') {
                document.getElementById('formIdAdmin').value = currentIdAdmin;
                document.getElementById('email').value = currentEmail;
                document.getElementById('formTitle').textContent = 'Edit Akun';
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'ajax=get_password&id_admin=' + encodeURIComponent(currentIdAdmin)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('password').value = data.password;
                        isEditing = true;
                    } else {
                        alert('⚠️ ' + (data.message || 'Gagal memuat password.'));
                        document.getElementById('password').value = '';
                        isEditing = false;
                    }
                })
                .catch(() => {
                    alert('⚠️ Gagal menghubungi server.');
                    document.getElementById('password').value = '';
                    isEditing = false;
                })
                .finally(() => {
                    document.getElementById('formSection').style.display = 'block';
                    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
                });
            } else if (currentAction === 'delete') {
                if (confirm(`Yakin hapus akun ${currentEmail}?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'hapus_id_confirmed';
                    input.value = currentIdAdmin;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['limitModal', 'otpRequestModal', 'otpVerifyModal'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m && m.style.display === 'flex') m.style.display = 'none';
                });
            }
        });

        document.getElementById('accountForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value.trim();
            if (!isEditing && !password) {
                e.preventDefault();
                alert('Password wajib diisi untuk akun baru.');
                document.getElementById('password').focus();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const mainContent = document.getElementById('mainContent');
            const menuToggle = document.getElementById('menuToggle');
            const mobileSidebar = document.getElementById('mobileSidebar');
            menuToggle.addEventListener('click', function() {
                mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
            });
            document.addEventListener('click', function(event) {
                const isClickInsideNav = menuToggle.contains(event.target);
                const isClickInsideSidebar = mobileSidebar.contains(event.target);
                if (!isClickInsideNav && !isClickInsideSidebar) {
                    mobileSidebar.style.display = 'none';
                }
            });
            setTimeout(() => {
                body.classList.add('fade-in-ready');
            }, 50);
            document.querySelectorAll('.menu-item a, .header-desktop-exit').forEach(link => {
                if(link.href.includes('logout.php')) return;
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.href;
                    mainContent.style.opacity = '0';
                    setTimeout(() => {
                        window.location.href = url;
                    }, 200);
                });
            });
        });
    </script>
</body>
</html>