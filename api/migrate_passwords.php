<?php
/**
 * Script Migrasi Password
 * ⚠️ HANYA JALANKAN SEKALI UNTUK MENGUBAH PASSWORD LAMA MENJADI HASH ⚠️
 *
 * Script ini akan:
 * 1. Membaca semua user.
 * 2. Mengambil password plaintext mereka.
 * 3. Melakukan hashing pada password tersebut.
 * 4. Mengupdate kembali ke database.
 */

header('Content-Type: text/plain; charset=utf-8');

// --- KONFIGURASI DATABASE ---
$servername = "127.0.0.1:3306";
$db_username = "u137138991_simpelsi"; // Ganti nama variabel agar tidak bingung
$db_password = "Simpelsi2025"; // Ganti nama variabel agar tidak bingung
$dbname = "u137138991_simpelsi";
// ----------------------------

// 1. Buat Koneksi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

echo "Memulai migrasi password...\n\n";

// 2. Ambil semua user dan password plaintext mereka
// HANYA ambil user yang passwordnya belum di-hash.
// Cara sederhana mendeteksinya adalah hash Bcrypt selalu diawali dengan '$2y$'.
// Jika password TIDAK dimulai dengan '$2y$', kita anggap itu plaintext.
$sql = "SELECT id_masyarakat, email, password FROM masyarakat WHERE password NOT LIKE '$2y$%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $count = 0;
    $updated = 0;

    // Siapkan statement update di luar loop untuk efisiensi
    $update_sql = "UPDATE masyarakat SET password = ? WHERE id_masyarakat = ?";
    $stmt_update = $conn->prepare($update_sql);

    while($row = $result->fetch_assoc()) {
        $count++;
        $id = $row['id_masyarakat'];
        $email = $row['email'];
        $plain_password = $row['password'];

        // 3. Lakukan Hashing
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

        echo "Memproses User ID: $id ($email)...\n";
        // echo " - Password Lama: $plain_password\n"; // Hapus komentar untuk debug (jangan di produksi)
        // echo " - Password Baru (Hash): " . substr($hashed_password, 0, 20) . "...\n";

        // 4. Update ke Database
        $stmt_update->bind_param("si", $hashed_password, $id);
        
        if ($stmt_update->execute()) {
            echo " -> BERHASIL di-update.\n";
            $updated++;
        } else {
            echo " -> GAGAL update: " . $stmt_update->error . "\n";
        }
        echo "--------------------------------------------------\n";
    }

    $stmt_update->close();
    echo "\nMigrasi Selesai.\n";
    echo "Total user diproses: $count\n";
    echo "Total user berhasil di-update: $updated\n";

} else {
    echo "Tidak ada user dengan password plaintext yang ditemukan.\n";
    echo "Semua password tampaknya sudah di-hash.\n";
}

$conn->close();
?>