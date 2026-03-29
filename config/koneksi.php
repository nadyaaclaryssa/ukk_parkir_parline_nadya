<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ukk_parkir"; 

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Hanya tampilkan pesan jika koneksi GAGAL
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// JANGAN ada echo di sini agar layout dashboard tidak berantakan
?>