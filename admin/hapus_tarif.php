<?php
include '../config/koneksi.php';
$id = $_GET['id'];
// Kita pakai DELETE karena databasemu sensitif relasi
mysqli_query($koneksi, "DELETE FROM tb_tarif WHERE id_tarif='$id'");
header("location:tarif_parkir.php");
?>