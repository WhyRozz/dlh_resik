<?php
// Load koneksi database — menghasilkan variabel $pdo
require_once '../KoneksiDatabase/koneksi.php';

// Pastikan $pdo tersedia
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("❌ Koneksi PDO tidak ditemukan. Pastikan koneksi.php menghasilkan \$pdo.");
}

echo "<pre style='font-family:monospace; background:#095E0D; color:#20A726; padding:15px;'>";
echo "🚀 Memulai migrasi password ke hash (menggunakan PDO)...\n";
echo "🔍 Target: tabel <strong>admin</strong>\n\n";

try {
    // Ambil admin yang password-nya masih plain text (panjang < 60)
    $stmt = $pdo->prepare("SELECT id_admin, email, password FROM admin WHERE CHAR_LENGTH(password) < 60");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated = 0;

    foreach ($admins as $admin) {
        $id_admin = $admin['id_admin'];
        $email = $admin['email'];
        $plain = $admin['password'];

        // Hash password
        $hashed = password_hash($plain, PASSWORD_DEFAULT);
        if (!$hashed) {
            echo "⚠️ Gagal hash password untuk ID $id_admin (Email: $email)\n";
            continue;
        }

        // Update ke database
        $update = $pdo->prepare("UPDATE admin SET password = :hash WHERE id_admin = :id");
        $update->bindParam(':hash', $hashed, PDO::PARAM_STR);
        $update->bindParam(':id', $id_admin, PDO::PARAM_INT);
        $exec = $update->execute();

        if ($exec) {
            echo "✅ ID $id_admin | Email: $email → Password di-hash.\n";
            $updated++;
        } else {
            echo "❌ Gagal update ID $id_admin.\n";
        }
    }

    echo "\n📊 Selesai. Total: <strong>$updated</strong> password di-hash.\n";
    echo "🔒 Semua password sekarang aman dalam bentuk hash (bcrypt).\n";
    
} catch (PDOException $e) {
    die("❌ Error database: " . $e->getMessage());
}

echo "</pre>";

// 🧹 Opsional: auto-hapus diri setelah sukses
// if ($updated > 0) { unlink(__FILE__); }
?>