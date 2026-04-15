<?php
$servername = "127.0.0.1:3306";
$db_username = "u137138991_simpelsi";
$db_password = "Simpelsi2025";
$dbname = "u137138991_simpelsi";

header('Content-Type: application/json');
$response = [];

// 1. Baca data JSON dari aplikasi Android
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (empty($data->email) || empty($data->new_password)) {
    $response['status'] = 'error';
    $response['message'] = 'Data tidak lengkap.';
    echo json_encode($response); die();
}

// Buat koneksi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Koneksi database gagal: ' . $conn->connect_error;
    echo json_encode($response); die();
}

$email = $data->email;
$new_password_input = $data->new_password;

// --------------------------------------------------------
// 2. Ambil password lama dari database
// --------------------------------------------------------
$sql_old = "SELECT password FROM masyarakat WHERE email = ?";
$stmt_old = $conn->prepare($sql_old);
$stmt_old->bind_param("s", $email);
$stmt_old->execute();
$result_old = $stmt_old->get_result();

if ($result_old->num_rows == 0) {
    $response['status'] = 'error';
    $response['message'] = 'Email tidak ditemukan.';
    echo json_encode($response); die();
}

$row = $result_old->fetch_assoc();
$old_password_hash = $row['password'];

// --------------------------------------------------------
// 3. Bandingkan password lama vs password baru
//    (new_password plaintext dibanding dengan hash lama)
// --------------------------------------------------------
if (password_verify($new_password_input, $old_password_hash)) {
    $response['status'] = 'error';
    $response['message'] = 'Password baru tidak boleh sama dengan password lama.';
    echo json_encode($response); die();
}

// --------------------------------------------------------
// 4. Hash password baru
// --------------------------------------------------------
$hashed_password = password_hash($new_password_input, PASSWORD_DEFAULT);

// --------------------------------------------------------
// 5. Update password baru
// --------------------------------------------------------
$sql_update = "UPDATE masyarakat SET password = ?, updated_at = NOW() WHERE email = ?";
$stmt_update = $conn->prepare($sql_update);

if ($stmt_update === false) {
    $response['status'] = 'error';
    $response['message'] = 'Gagal mempersiapkan statement update: ' . $conn->error;
    echo json_encode($response); die();
}

$stmt_update->bind_param("ss", $hashed_password, $email);

if ($stmt_update->execute()) {
    if ($stmt_update->affected_rows > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Password berhasil diubah.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Password gagal diperbarui.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Gagal menyimpan password baru: ' . $stmt_update->error;
}

$stmt_update->close();
$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>