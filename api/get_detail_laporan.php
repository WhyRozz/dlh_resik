<?php
header('Content-Type: application/json');
include 'config.php';

if (!isset($_GET['id_laporan'])) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter id_laporan belum dikirim."
    ]);
    exit;
}

$id_laporan = $_GET['id_laporan'];

// Query langsung ke tabel laporan
$query = $conn->prepare("
    SELECT id AS id_laporan, nama, lokasi, keterangan, tanggal, status, foto, balasan
    FROM laporan
    WHERE id = ?
");
$query->bind_param("i", $id_laporan);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "message" => "Data laporan ditemukan.",
        "data" => $data
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Data laporan tidak ditemukan."
    ]);
}

$query->close();
$conn->close();
?>
