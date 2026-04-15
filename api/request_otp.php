<?php
header("Content-Type: application/json");
require_once "config.php"; // pastikan file ini berisi $conn

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// 1. Set zona waktu default PHP ke Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Set zona waktu untuk koneksi MySQL ini ke GMT+7 (WIB)
if ($conn) {
    $conn->query("SET time_zone = '+07:00'");
}
// BACA JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode([
        "status" => "error",
        "message" => "Email wajib diisi"
    ]);
    exit;
}

// CEK EMAIL DI DATABASE
$stmt = $conn->prepare("SELECT id_masyarakat FROM masyarakat WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email tidak terdaftar"
    ]);
    exit;
}

// BUAT OTP
$otp = rand(1000, 9999);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// SIMPAN OTP
$update = $conn->prepare("UPDATE masyarakat SET otp = ?, otp_expires = ? WHERE email = ?");
$update->bind_param("sss", $otp, $expires, $email);
$update->execute();

// MULAI KIRIM EMAIL
$mail = new PHPMailer(true);

try {
    // WAJIB untuk Hostinger
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;

    // akun email Hostinger Kamu
    $mail->Username = 'simpelsi@pelatihanku.pbltifnganjuk.com';
    $mail->Password = 'Simpelsi*123#';

    // Enkripsi SSL port 465
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Bypass SSL verify (WAJIB untuk Hostinger)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Timeout max 5 detik agar Android tidak timeout
    $mail->Timeout = 5;

    // PENGIRIM
    $mail->setFrom('simpelsi@pelatihanku.pbltifnganjuk.com', 'Sistem OTP SimpelSi');

    // PENERIMA
    $mail->addAddress($email);

    // ISI EMAIL
    $mail->isHTML(false);
    $mail->Subject = "Kode OTP SimpelSi";
    $mail->Body = "Kode OTP Anda adalah: $otp\nBerlaku 10 menit.";

    // KIRIM EMAIL
    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "OTP berhasil dikirim ke email.",
        "otp" => $otp // bisa dihapus untuk keamanan
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengirim email: " . $mail->ErrorInfo
    ]);
}

?>
