<?php
include 'config.php';
header('Content-Type: application/json');
$response = [];
// 1. Set zona waktu default PHP ke Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Set zona waktu untuk koneksi MySQL ini ke GMT+7 (WIB)
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}
// 1. Validasi
if (empty($_POST['id_laporan']) || empty($_POST['id_masyarakat'])) {
    $response['status'] = 'error'; $response['message'] = 'ID Laporan/Pengguna tidak ada.';
    echo json_encode($response); die();
}
if ($conn->connect_error) { /*... error ...*/ }

// 2. Ambil data teks
$id_laporan = $_POST['id_laporan'];
$id_masyarakat = $_POST['id_masyarakat'];
$nama = $_POST['nama'] ?? '';
$lokasi = $_POST['lokasi'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');

// 3. Cek Batas Waktu 1 Jam
$sql_check = "SELECT foto FROM laporan 
              WHERE id = ? AND id_masyarakat = ? 
              AND created_at > (NOW() - INTERVAL 1 HOUR)";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_laporan, $id_masyarakat);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    $response['status'] = 'error';
    $response['message'] = 'Gagal update: Laporan tidak ditemukan atau sudah lewat 1 jam.';
    echo json_encode($response); die();
}
$row = $result->fetch_assoc();
$foto_lama = $row['foto'];
$stmt_check->close();

// 4. Proses Foto (JIKA ADA FOTO BARU)
$foto_baru = $foto_lama; // Default adalah foto lama
$ada_foto_baru = isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK;

if ($ada_foto_baru) {
    $target_dir  = "uploads/"; 
    $file_name   = time() . "_" . basename($_FILES['foto']['name']);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        // Foto baru berhasil diupload, kita akan gunakan nama file baru ini
        $foto_baru = $file_name;
        // Hapus foto lama
        if (!empty($foto_lama) && file_exists("uploads/" . $foto_lama)) {
            unlink("uploads/" . $foto_lama);
        }
    } else {
        $response['status'] = 'error'; $response['message'] = 'Gagal upload foto baru.';
        echo json_encode($response); die();
    }
}

// 5. Update Database
$query = "UPDATE laporan SET nama=?, lokasi=?, keterangan=?, tanggal=?, foto=? 
          WHERE id=? AND id_masyarakat=?";
$stmt = $conn->prepare($query);
// "sssssii" = 5 string, 2 integer
$stmt->bind_param("sssssii", $nama, $lokasi, $keterangan, $tanggal, $foto_baru, $id_laporan, $id_masyarakat);

if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Laporan berhasil diperbarui.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Gagal memperbarui database: ' . $stmt->error;
}
$stmt->close();
$conn->close();
echo json_encode($response, JSON_PRETTY_PRINT);
?>