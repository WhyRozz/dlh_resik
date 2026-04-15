<?php
include 'config.php'; // Pastikan config.php berisi $conn
header('Content-Type: application/json');

// 1. Set zona waktu default PHP ke Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Set zona waktu untuk koneksi MySQL ini ke GMT+7 (WIB)
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Gunakan metode POST"]);
    exit;
}

// 1. Ambil data
$id_masyarakat = $_POST['id_masyarakat'] ?? null;
$nama          = $_POST['nama'] ?? '';
$lokasi        = $_POST['lokasi'] ?? '';
$keterangan    = $_POST['keterangan'] ?? '';
$tanggal       = $_POST['tanggal'] ?? date('Y-m-d');
$status        = "Diproses";

// Validasi data
if (!$id_masyarakat) {
    echo json_encode(["status" => "error", "message" => "ID masyarakat tidak ditemukan"]);
    exit;
}
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] != UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "File foto tidak ditemukan"]);
    exit;
}
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    exit;
}

// 2. Proses file foto (asumsi 'uploads' di dalam 'api')
$target_dir  = "uploads/"; 
$file_name   = time() . "_" . basename($_FILES['foto']['name']);
$target_file = $target_dir . $file_name;

// 3. Pindahkan file
if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
    
    // --- ⬇️ INI ADALAH PERBAIKAN KRITIS ⬇️ ---
    
    // 4. Simpan ke database menggunakan Prepared Statements
    $query = "INSERT INTO laporan (id_masyarakat, nama, lokasi, keterangan, status, foto, tanggal)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);

    // "i" = integer untuk id_masyarakat
    $stmt->bind_param("issssss", $id_masyarakat, $nama, $lokasi, $keterangan, $status, $file_name, $tanggal);

    // 5. Eksekusi
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Laporan berhasil dikirim"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database: " . $stmt->error]);
    }
    $stmt->close();
    
    // --- ⬆️ AKHIR PERBAIKAN ⬆️ ---
    
} else {
    echo json_encode(["status" => "error", "message" => "Gagal upload foto. Cek izin folder 'uploads'."]);
}

$conn->close();
?>