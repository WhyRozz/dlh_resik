<?php
header("Content-Type: application/json; charset=UTF-8");
include "config.php"; // Pastikan config.php berisi $conn

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
    exit;
}

// Ambil semua data dari tabel tps
$sql = "SELECT id_tps, nama_tps, lokasi, alamat, kapasitas, keterangan FROM tps";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($id_tps, $nama_tps, $lokasi, $alamat, $kapasitas, $keterangan);

$response = [];
while ($stmt->fetch()) {
    
    // Kita buat URL gambar palsu berdasarkan ID (misal: tps1.jpg, tps2.jpg)
    // Anda harus mengunggah gambar-gambar ini ke folder /uploads/ Anda
    $foto_name = "tps" . $id_tps . ".jpg"; 

    $response[] = [
        "id_tps"     => $id_tps,
        "nama_tps"   => $nama_tps,
        "lokasi"     => $lokasi, // Ini adalah koordinat/plus code
        "alamat"     => $alamat, // Ini adalah alamat lengkap
        "kapasitas"  => $kapasitas,
        "keterangan" => $keterangan,
        "foto_file"  => $foto_name // Nama file gambar
    ];
}

if (!empty($response)) {
    echo json_encode([
        "status" => "success",
        "data"   => $response
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Data TPS tidak ditemukan"
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();
?>