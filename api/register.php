<?php

/**
 * File API: register.php
 * Menerima data JSON (nama, email, password) dari Android,
 * mendaftarkan pengguna baru ke tabel 'masyarakat'.
 * * ✅ KEAMANAN DITINGKATKAN: Password disimpan dalam bentuk hash (Bcrypt).
 */

// 1. Set Header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- KONFIGURASI DATABASE ---
// ⚠️ PENTING: Jangan gunakan variabel $password untuk password DB agar tidak bingung.
$servername = "127.0.0.1:3306";
$db_username = "u137138991_simpelsi"; // Ubah nama variabel
$db_password = "Simpelsi2025"; // Ubah nama variabel
$dbname = "u137138991_simpelsi";

$response = [];

// 2. Baca data JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// 3. Validasi data input
if (empty($data->nama) || empty($data->email) || empty($data->password)) {
    $response['status'] = 'error';
    $response['message'] = 'Semua data wajib diisi.';
    echo json_encode($response);
    die();
}

// 4. Buat Koneksi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// 5. Cek Koneksi
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Koneksi database gagal: ' . $conn->connect_error;
    echo json_encode($response);
    die();
}

// 6. Cek Apakah Email Sudah Terdaftar
$sql_check = "SELECT id_masyarakat FROM masyarakat WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $data->email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Email sudah ada
    $response['status'] = 'error';
    $response['message'] = 'Email ini sudah terdaftar. Silakan gunakan email lain.';
    echo json_encode($response);
    $stmt_check->close();
    $conn->close();
    die();
}
$stmt_check->close();


// 7. Masukkan Pengguna Baru ke Database

// --- ✅ PERBAIKAN KEAMANAN: HASHING PASSWORD ---

// Gunakan password_hash() untuk membuat hash dari password input.
// PASSWORD_DEFAULT saat ini menggunakan algoritma Bcrypt yang kuat.
$hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

$sql_insert = "INSERT INTO masyarakat (nama, email, password) VALUES (?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// ⚠️ PENTING: Bind variabel $hashed_password, BUKAN $data->password asli.
$stmt_insert->bind_param("sss", $data->nama, $data->email, $hashed_password);

// --- AKHIR PERBAIKAN KEAMANAN ---


if ($stmt_insert->execute()) {
    // Registrasi berhasil
    $response['status'] = 'success';
    $response['message'] = 'Registrasi berhasil! Silakan login.';
} else {
    // Registrasi gagal
    $response['status'] = 'error';
    $response['message'] = 'Registrasi gagal: ' . $stmt_insert->error;
}

// 8. Tutup Koneksi
$stmt_insert->close();
$conn->close();

// 9. Tampilkan Hasil
echo json_encode($response, JSON_PRETTY_PRINT);

?>