<?php
header("Content-Type: application/json; charset=UTF-8");
include "config.php"; // Pastikan config.php berisi $conn

// 1. Set zona waktu default PHP ke Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Set zona waktu untuk koneksi MySQL ini ke GMT+7 (WIB)
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}

$id_masyarakat = isset($_GET['id_masyarakat']) ? $_GET['id_masyarakat'] : null;

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
    exit;
}

if ($id_masyarakat) {
    // --- ⬇️ INI ADALAH PERBAIKANNYA ⬇️ ---
    // Kita tambahkan "AND status != 'Ditarik'"
    // Ini akan mengambil semua laporan KECUALI yang statusnya "Ditarik"
    $sql = "SELECT id, id_masyarakat, nama, lokasi, keterangan, status, foto, tanggal, created_at 
            FROM laporan 
            WHERE id_masyarakat = ? AND status != 'Ditarik' 
            ORDER BY created_at DESC";
    // --- ⬆️ AKHIR PERBAIKAN ⬆️ ---
    
    $stmt = $conn->prepare($sql);
    
    // Bind sebagai "s" (String) sesuai solusi kita sebelumnya
    $stmt->bind_param("s", $id_masyarakat); 
    
} else {
    // Admin (tanpa ID) masih bisa melihat semua, termasuk yang ditarik
    $sql = "SELECT id, id_masyarakat, nama, lokasi, keterangan, status, foto, tanggal, created_at 
            FROM laporan ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();

// Gunakan bind_result
$stmt->bind_result($id_laporan, $id_masy, $nama, $lokasi, $keterangan, $status, $foto_filename, $tanggal, $created_at);

$response = [];
while ($stmt->fetch()) {
    
    $foto_filename_safe = !empty($foto_filename) ? $foto_filename : '';

    $response[] = [
        "id_laporan"     => $id_laporan,
        "nama"           => $nama,
        "lokasi"         => $lokasi,
        "keterangan"     => $keterangan,
        "tanggal"        => $tanggal,
        "foto"           => $foto_filename_safe, 
        "status_laporan" => $status,
        "created_at"     => $created_at
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
        "message" => "Anda belum memiliki laporan"
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();
?>