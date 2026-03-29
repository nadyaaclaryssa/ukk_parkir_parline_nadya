<?php 
session_start();
// Menghapus semua variabel session
session_unset();
// Menghancurkan session
session_destroy();

// Tambahkan pesan logout berhasil agar user tahu mereka sudah keluar
header("location:index.php?pesan=logout");
exit; // Wajib pakai exit setelah header redirect
?>