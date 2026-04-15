<?php
// Konfigurasi koneksi database di Awardspace
$servername = "127.0.0.1:3306";
$username = "u137138991_simpelsi";
$password = "Simpelsi2025";
$dbname = "u137138991_simpelsi";

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// echo "Koneksi berhasil!"; // (opsional untuk test)
?>
