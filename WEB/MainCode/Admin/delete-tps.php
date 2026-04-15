<?php
require_once '../KoneksiDatabase/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tps WHERE id_tps = ?");
        $stmt->execute([$id]);
        header("Location: kelolaTPS.php?hapus=1");
        exit;
    } catch (Exception $e) {
        die("Gagal menghapus data: " . htmlspecialchars($e->getMessage()));
    }
} else {
    header("Location: kelolaTPS.php");
    exit;
}
?>