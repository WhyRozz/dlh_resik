<?php

// --- UBAH BAGIAN INI SESUAI PENGATURAN DATABASE ANDA ---

$servername = "127.0.0.1:3306";
$username = "u137138991_simpelsi";
$password = "Simpelsi2025";
$dbname = "u137138991_simpelsi";

// ---------------------------------------------------------


// Variabel untuk menyimpan hasil pengecekan
$status_class = ""; // Ini akan diisi dengan 'success' atau 'error'
$status_message = "";
$error_details = "";

// Mencoba membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Mengecek status koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal
    $status_class = "error";
    $status_message = "Koneksi GAGAL!";
    $error_details = "<strong>Detail Error:</strong> " . $conn->connect_error;
} else {
    // Jika koneksi berhasil
    $status_class = "success";
    $status_message = "Koneksi BERHASIL!";
    $error_details = "Berhasil terhubung ke database '<strong>" . $dbname . "</strong>' di server <strong>" . $servername . "</strong>.";
}

// Menutup koneksi
$conn->close();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Koneksi Database</title>
    
    <style>
        /* Mengatur dasar halaman */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Memastikan halaman penuh */
            margin: 0;
            color: #333;
        }

        /* Kotak utama di tengah */
        .container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            border-top: 5px solid #007bff; /* Garis biru di atas */
        }

        h1 {
            color: #111;
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 1.8em;
        }

        /* Kotak yang menampilkan status */
        .status-box {
            padding: 20px;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            margin-top: 20px;
            border: 1px solid transparent;
        }

        /* * Kelas CSS ini akan ditambahkan oleh PHP 
         * tergantung hasil koneksi
         */

        /* Tampilan jika Sukses (Hijau) */
        .status-box.success {
            background-color: #e0f8e9; /* Latar hijau muda */
            border-color: #5cb85c;     /* Garis hijau */
            color: #3d7c3d;            /* Teks hijau tua */
        }

        /* Tampilan jika Gagal (Merah) */
        .status-box.error {
            background-color: #f9e3e3; /* Latar merah muda */
            border-color: #d9534f;     /* Garis merah */
            color: #a94442;            /* Teks merah tua */
        }

        /* Teks untuk detail error/sukses */
        .details {
            margin-top: 20px;
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
            word-wrap: break-word; /* Agar pesan error panjang tidak merusak layout */
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Status Koneksi Database</h1>
        
        <div class="status-box <?php echo $status_class; ?>">
            <?php echo $status_message; ?>
        </div>

        <div class="details">
            <?php echo $error_details; ?>
        </div>
    </div>

</body>
</html>