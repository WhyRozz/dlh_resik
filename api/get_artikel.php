<?php
header("Content-Type: application/json; charset=UTF-8");
include "config.php"; // Pastikan config.php berisi $conn

// Set zona waktu (Sudah benar)
date_default_timezone_set('Asia/Jakarta');
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
    exit;
}

// ❗️ PERBAIKAN: Spasi aneh dihapus dari query SQL
$sql = "SELECT id_artikel, judul, deskripsi, foto, tanggal 
        FROM artikel 
        ORDER BY tanggal DESC"; // Urutkan berdasarkan tanggal
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($id_artikel, $judul, $deskripsi, $foto_filename, $tanggal);

$response = [];

while ($stmt->fetch()) {
    
    // --- ⬇️ PERBAIKAN DI SINI ⬇️ ---
    
    // 1. Pastikan foto tidak null
    $foto_filename_safe = !empty($foto_filename) ? $foto_filename : '';

    $response[] = [
        "id_artikel" => $id_artikel,
        "judul"      => $judul,
        "deskripsi"  => $deskripsi,
        "foto"       => $foto_filename_safe, // 2. Key harus "foto" (bukan "foto_url")
                                          // 3. Isinya HANYA nama file
        "tanggal"    => $tanggal
    ];
    // --- ⬆️ AKHIR PERBAIKAN ⬆️ ---
}

if (!empty($response)) {
    echo json_encode([
        "status" => "success",
        "data"   => $response
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Data artikel tidak ditemukan"
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();
?>