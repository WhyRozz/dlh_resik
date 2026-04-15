<?php
include 'config.php';
header('Content-Type: application/json');

// 1. Zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}

$response = [];
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// Validasi awal
if (empty($data->id_laporan) || empty($data->id_masyarakat)) {
    $response = [
        'status' => 'error',
        'message' => 'ID Laporan atau ID Pengguna tidak ada.'
    ];
    echo json_encode($response); exit;
}

if ($conn->connect_error) {
    $response = [
        'status' => 'error',
        'message' => 'Koneksi database gagal.'
    ];
    echo json_encode($response); exit;
}

$id_laporan = $data->id_laporan;
$id_masyarakat = $data->id_masyarakat;
$status_baru = "Ditarik"; // status tujuan

// --- 1. Ambil data laporan ---
$sql_check = "SELECT id, id_masyarakat, status, created_at 
              FROM laporan WHERE id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $id_laporan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        'status' => 'error',
        'message' => 'Laporan tidak ditemukan.'
    ];
    echo json_encode($response); exit;
}

$row = $result->fetch_assoc();

// --- 2. Validasi kepemilikan ---
if ($row['id_masyarakat'] != $id_masyarakat) {
    $response = [
        'status' => 'error',
        'message' => 'Laporan bukan milik Anda.'
    ];
    echo json_encode($response); exit;
}

$status = strtolower($row['status']);
$created_at = strtotime($row['created_at']);
$now = time();
$diff = $now - $created_at;

// --- 3. Logika izin hapus/edit sesuai kondisi ---
$bolehTarik = false;

// ≤ 1 jam
if ($diff <= 3600) {
    $bolehTarik = true;
}
// Status diproses → boleh juga
elseif ($status === 'diproses') {
    $bolehTarik = true;
}
// Status ditolak → boleh dihapus (ditarik)
elseif ($status === 'ditolak') {
    $bolehTarik = true;
}
// Status diterima → tidak boleh
elseif ($status === 'diterima') {
    $bolehTarik = false;
}

if (!$bolehTarik) {
    $response = [
        'status' => 'error',
        'message' => 'Laporan tidak bisa ditarik (status tidak diizinkan).'
    ];
    echo json_encode($response); exit;
}

// --- 4. Update status jadi "Ditarik" ---
$sql_update = "UPDATE laporan SET status = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ss", $status_baru, $id_laporan);

if ($stmt_update->execute()) {
    $response = [
        'status' => 'success',
        'message' => 'Laporan berhasil ditarik.',
        'data' => [
            'id_laporan' => $id_laporan,
            'status_lama' => $status,
            'status_baru' => $status_baru
        ]
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Gagal memperbarui status laporan.'
    ];
}

$stmt_update->close();
$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>
