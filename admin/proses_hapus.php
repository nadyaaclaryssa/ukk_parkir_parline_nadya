<?php
session_start();
// Proteksi: Jangan biarkan orang luar atau role lain menghapus data
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("location:../index.php");
    exit;
}

include '../config/koneksi.php';

// Gunakan mysqli_real_escape_string untuk mencegah SQL Injection
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Proses Hapus
$query = mysqli_query($koneksi, "DELETE FROM tb_transaksi WHERE id_transaksi = '$id'");

if($query) {
    header("location:dashboard.php?status=hapus_berhasil");
} else {
    header("location:dashboard.php?status=hapus_gagal");
}
exit;
?>