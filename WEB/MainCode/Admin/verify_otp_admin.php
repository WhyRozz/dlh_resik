<?php
// verify_otp_admin.php — versi untuk struktur folder Hostinger-mu

require_once __DIR__ . '/../KoneksiDatabase/koneksi.php'; // ⬅️ naik 1 level ke MainCode/

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$input = file_get_contents('php://input');
$data = json_decode($input);

if (empty($data->email) || empty($data->otp)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email dan OTP wajib diisi.']);
    exit();
}

if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid.']);
    exit();
}

if (!preg_match('/^\d{4}$/', $data->otp)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'OTP harus 4 digit angka.']);
    exit();
}

try {
    $now = gmdate('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        SELECT id_admin 
        FROM admin 
        WHERE email = ? 
          AND otp = ? 
          AND otp_expires > ?
    ");
    $stmt->execute([$data->email, $data->otp, $now]);

    if ($stmt->rowCount() === 1) {
        $clear = $pdo->prepare("UPDATE admin SET otp = NULL, otp_expires = NULL WHERE email = ?");
        $clear->execute([$data->email]);
        echo json_encode(['status' => 'success', 'message' => 'OTP valid.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kode OTP salah atau telah kadaluarsa.']);
    }

} catch (Exception $e) {
    error_log("verify_otp_admin error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
}
?>