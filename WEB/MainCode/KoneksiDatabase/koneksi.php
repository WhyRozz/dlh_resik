<?php
// File: MainCode/KoneksiDatabase/koneksi.php

$host = '127.0.0.1'; // GANTI dengan host yang sesuai
$dbname = 'u137138991_simpelsi';
$user = 'u137138991_simpelsi';         // GANTI dengan username database
$pass = 'Simpelsi2025';    // GANTI dengan password database
$port = '3306';                   // biasanya 3306
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>