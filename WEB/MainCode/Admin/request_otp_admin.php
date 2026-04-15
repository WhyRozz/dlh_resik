<?php
// request_otp_admin.php — versi untuk struktur folder Hostinger-mu

require_once __DIR__ . '/../KoneksiDatabase/koneksi.php'; // ⬅️ naik 1 level ke MainCode/

// Load PHPMailer (folder phpmailer ada di dalam Admin/)
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$input = file_get_contents('php://input');
$data = json_decode($input);

if (empty($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email tidak valid atau tidak diisi.']);
    exit();
}

try {
    // Cek apakah email terdaftar sebagai admin
    $stmt = $pdo->prepare("SELECT id_admin FROM admin WHERE email = ?");
    $stmt->execute([$data->email]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email tidak terdaftar sebagai admin.']);
        exit;
    }

    // Generate OTP 4 digit
    $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $expires = gmdate('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Simpan ke database
    $update = $pdo->prepare("UPDATE admin SET otp = ?, otp_expires = ? WHERE email = ?");
    $update->execute([$otp, $expires, $data->email]);

    // ✉️ KIRIM EMAIL VIA PHPMAILER (SMTP Hostinger)
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'simpelsi@pelatihanku.pbltifnganjuk.com'; // ganti jika beda
    $mail->Password   = 'Simpelsi*123#';                          // ganti jika beda
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port       = 465;

    // ⚠️ WAJIB untuk Hostinger
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->Timeout = 5; // detik

    // Pengirim & penerima
    $mail->setFrom('simpelsi@pelatihanku.pbltifnganjuk.com', 'SIMPELSI Admin');
    $mail->addAddress($data->email);

    // Email body
    $mail->isHTML(false);
    $mail->Subject = '[SIMPELSI] Kode Verifikasi Admin (4 Digit)';
    $mail->Body    = "Halo Admin,\n\nKode OTP Anda:\n\n    $otp\n\nBerlaku selama 5 menit.\n\n— SIMPELSI";

    // Kirim
    if ($mail->send()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP 4 digit telah dikirim ke ' . $data->email
        ]);
    } else {
        // Jika gagal kirim, tampilkan error detail (untuk debugging)
        throw new Exception($mail->ErrorInfo);
    }

} catch (Exception $e) {
    error_log("request_otp_admin error: " . $e->getMessage());
    
    // ❗ Untuk development: tampilkan OTP di response (aman selama internal)
    if (isset($otp)) {
        echo json_encode([
            'status' => 'success_dev',
            'message' => '[DEBUG] Gagal kirim email. Gunakan kode berikut:',
            'otp' => $otp
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengirim OTP: ' . substr($e->getMessage(), 0, 100)
        ]);
    }
}
?>