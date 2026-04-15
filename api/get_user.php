<?php

/**
 * File API: get_masyarakat.php (Nama file disarankan diubah)
 * Mengambil semua data pengguna dari tabel 'masyarakat' dan menampilkannya sebagai JSON.
 */

// 1. Set Header ke JSON
// Memberi tahu browser atau aplikasi bahwa data yang dikirim adalah format JSON.
header('Content-Type: application/json');

// (Opsional) Mengizinkan akses dari domain lain (CORS)
header('Access-Control-Allow-Origin: *');


// --- KONFIGURASI DATABASE (Sudah diisi sesuai info Anda) ---


$servername = "127.0.0.1:3306";
$username = "u137138991_simpelsi";
$password = "Simpelsi2025";
$dbname = "u137138991_simpelsi";
// ---------------------------------------------------------


// Array untuk respons JSON
$response = [];

// 2. Buat Koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. Cek Koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal, buat respons error
    $response['status'] = 'error';
    $response['message'] = 'Koneksi database gagal: ' . $conn->connect_error;
    
    // Tampilkan JSON error dan hentikan skrip
    echo json_encode($response);
    die();
}


// --- ⬇️ PERUBAHAN DI SINI ⬇️ ---

// 4. Buat SQL Query
// Mengambil data dari tabel 'masyarakat'
// PENTING: Kita TIDAK menyertakan kolom 'password' dan 'google_id'
$sql = "SELECT id_masyarakat, nama, email, no_telpon, created_at FROM masyarakat";

// 5. Eksekusi Query
$result = $conn->query($sql);

// 6. Proses Hasil
if ($result) {
    // Siapkan array kosong untuk menampung data masyarakat
    $data_masyarakat = [];
    
    // Ambil setiap baris data
    while ($row = $result->fetch_assoc()) {
        // Masukkan data baris ke array $data_masyarakat
        $data_masyarakat[] = $row;
    }
    
    // Buat respons sukses
    $response['status'] = 'success';
    $response['data'] = $data_masyarakat; // <-- Menggunakan variabel data masyarakat

} else {
    // Jika query SQL gagal
    $response['status'] = 'error';
    $response['message'] = 'Query gagal: ' . $conn->error;
}

// --- ⬆️ AKHIR PERUBAHAN ⬆️ ---


// 7. Tampilkan Hasil sebagai JSON
// JSON_PRETTY_PRINT membuat output JSON lebih mudah dibaca (opsional)
echo json_encode($response, JSON_PRETTY_PRINT);

// 8. Tutup Koneksi
$conn->close();

?>