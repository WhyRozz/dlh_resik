<?php
// Ambil parameter dari URL
$file = $_GET['file'] ?? '';
$tipe = $_GET['tipe'] ?? 'laporan'; // Default ke 'laporan' jika 'tipe' tidak ada

// 1. Validasi nama file
if (empty($file)) {
    http_response_code(400);
    echo "Nama file tidak disediakan.";
    exit;
}

// 2. Keamanan: Cegah directory traversal
$file_name = basename($file);

// 3. Tentukan path folder berdasarkan 'tipe'
// __DIR__ adalah folder /api/ tempat skrip ini berada
$base_dir = __DIR__; 
$file_path = '';

// --- ⬇️ PERBAIKAN DI SINI ⬇️ ---
if ($tipe == 'artikel') {
    // Untuk artikel, cari di /api/uploads/artikel/
    $file_path = $base_dir . '/uploads/artikel/' . $file_name;
} else {
    // Untuk laporan (default), cari di /api/uploads/
    $file_path = $base_dir . '/uploads/' . $file_name;
}
// --- ⬆️ AKHIR PERBAIKAN ⬆️ ---


// 4. Cek apakah file ada
if (!file_exists($file_path)) {
    http_response_code(404);
    echo "File tidak ditemukan di: " . $file_path; // Pesan debug
    exit;
}

// 5. Tentukan tipe MIME (cara Anda sudah bagus)
$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
switch ($ext) {
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    case 'gif':
        header('Content-Type: image/gif');
        break;
    default:
        header('Content-Type: application/octet-stream');
        break;
}

// 6. Set header lain dan tampilkan gambar
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>