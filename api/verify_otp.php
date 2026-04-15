<?php
// --- KONFIGURASI DATABASE ---
$servername = "127.0.0.1:3306";
$username = "u137138991_simpelsi";
$password = "Simpelsi2025";
$dbname = "u137138991_simpelsi";

// ----------------------------

header('Content-Type: application/json');
$response = [];

// 1. Baca data JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (empty($data->email) || empty($data->otp)) {
    $response['status'] = 'error'; $response['message'] = 'Email dan Kode OTP wajib diisi.';
    echo json_encode($response); die();
}

// 2. Buat Koneksi
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $response['status'] = 'error'; $response['message'] = 'Koneksi database gagal.';
    echo json_encode($response); die();
}

// 3. Cek OTP di database
// Kita cek apakah email & otp cocok, DAN belum kedaluwarsa (NOW() < otp_expires)
$sql = "SELECT id_masyarakat FROM masyarakat WHERE email = ? AND otp = ? AND otp_expires > NOW()";
$stmt = $conn->prepare($sql);

// Bind parameter: "ss" karena email dan otp adalah String
$stmt->bind_param("ss", $data->email, $data->otp);
$stmt->execute();
// ❗️ PERBAIKAN: Menggunakan store_result() dan bind_result() untuk kompatibilitas hosting
$stmt->store_result(); 

if ($stmt->num_rows == 1) {
    // OTP Benar dan valid

    // 4. Clear OTP dari database (untuk keamanan, setelah diverifikasi)
    $sql_clear = "UPDATE masyarakat SET otp = NULL, otp_expires = NULL WHERE email = ?";
    $stmt_clear = $conn->prepare($sql_clear);
    $stmt_clear->bind_param("s", $data->email);
    $stmt_clear->execute();
    $stmt_clear->close();
    
    $response['status'] = 'success';
    $response['message'] = 'Kode OTP berhasil diverifikasi.';
} else {
    // OTP Salah atau sudah kedaluwarsa
    $response['status'] = 'error';
    $response['message'] = 'Kode OTP salah atau sudah kedaluwarsa.';
}

$stmt->close();
$conn->close();
echo json_encode($response, JSON_PRETTY_PRINT);
?>