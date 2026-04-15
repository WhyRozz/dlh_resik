<?php
/**
 * File API: login.php
 * Memverifikasi login dari tabel 'masyarakat'.
 *
 * ✅ KEAMANAN DITINGKATKAN: Password diverifikasi menggunakan password_verify()
 */

// --- 1. Set Header ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- KONFIGURASI DATABASE ---
$servername = "127.0.0.1:3306";
$username = "u137138991_simpelsi";
$password_db = "Simpelsi2025"; // Ganti nama variabel agar tidak bingung dengan password user
$dbname = "u137138991_simpelsi";
// ----------------------------

$response = [];

// --- 3. Baca Data JSON ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// --- 4. Validasi Input ---
if (empty($data->email) || empty($data->password)) {
    $response['status']  = 'error';
    $response['message'] = 'Email dan password tidak boleh kosong.';
    echo json_encode($response);
    die();
}

// --- 5. Buat Koneksi ke Database ---
$conn = new mysqli($servername, $username, $password_db, $dbname);

// --- 6. Cek Koneksi ---
if ($conn->connect_error) {
    $response['status']  = 'error';
    $response['message'] = 'Koneksi database gagal: ' . $conn->connect_error;
    echo json_encode($response);
    die();
}

// --- 7. Buat SQL Query ---
$sql = "SELECT id_masyarakat, nama, email, password FROM masyarakat WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $response['status']  = 'error';
    $response['message'] = 'Gagal mempersiapkan statement: ' . $conn->error;
    echo json_encode($response);
    die();
}

$stmt->bind_param("s", $data->email);

// --- 8. Eksekusi Query ---
$stmt->execute();
$result = $stmt->get_result();

// --- 9. Proses Hasil ---
if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $hashed_password_from_db = $user['password'];

    // --- 10. ✅ Verifikasi Password dengan Hash ---
    // Bandingkan password mentah dari input ($data->password)
    // dengan hash yang ada di database ($hashed_password_from_db)
    if (password_verify($data->password, $hashed_password_from_db)) {
        $response['status']  = 'success';
        $response['message'] = 'Login berhasil!';
        $response['data'] = [
            'id_masyarakat' => $user['id_masyarakat'],
            'nama'          => $user['nama'],
            'email'         => $user['email']
        ];
    } else {
        // Password salah (hash tidak cocok)
        $response['status']  = 'error';
        $response['message'] = 'Email atau password salah.';
    }
} else {
    // Email tidak ditemukan
    $response['status']  = 'error';
    $response['message'] = 'Email atau password salah.';
}

// --- 11. Tampilkan Hasil ---
echo json_encode($response, JSON_PRETTY_PRINT);

// --- 12. Tutup Koneksi ---
$stmt->close();
$conn->close();
?>