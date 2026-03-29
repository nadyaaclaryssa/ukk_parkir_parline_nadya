<?php
session_start();
// Pastikan koneksi disertakan di awal agar variabel $koneksi terbaca
include '../config/koneksi.php';

// Proteksi: Cek apakah session role ada dan apakah dia admin
if(!isset($_SESSION['role']) || strtolower($_SESSION['role']) != "admin"){
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit;
}

// Ambil ID dari URL
$id = $_GET['id'];

// Proses hapus ke database
$query = mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user = '$id'");

if($query){
    // Pastikan tujuannya kembali ke halaman tabel user kamu
    echo "<script>alert('User berhasil dibasmi!'); window.location='user_daftar.php';</script>";
} else {
    echo "Gagal membasmi: " . mysqli_error($koneksi);
}
?>