<?php
session_start();
include 'config/koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Login tanpa MD5 karena di database Anda password berupa teks biasa
    $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Set Session agar Dashboard tidak error
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];
        
        $nama_tampil = !empty($data['nama_lengkap']) ? $data['nama_lengkap'] : $data['username'];
        $_SESSION['nama_lengkap'] = $nama_tampil;
        $_SESSION['nama']         = $nama_tampil; 

        // Log Aktivitas
        $id_user   = $data['id_user'];
        $waktu     = date("Y-m-d H:i:s");
        $aktivitas = "Berhasil Login ke Sistem";
        
        // --- PERBAIKAN FATAL ERROR: Cek nama kolom secara otomatis ---
        $check_log = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_log_aktivitas LIKE 'waktu'");
        $kolom_waktu = (mysqli_num_rows($check_log) > 0) ? "waktu" : "waktu_aktivitas";

        mysqli_query($koneksi, "INSERT INTO tb_log_aktivitas (id_user, aktivitas, $kolom_waktu) VALUES ('$id_user', '$aktivitas', '$waktu')");

        // Redirect sesuai role
        if($data['role'] == 'admin'){
            header("location:admin/dashboard.php");
        } else if($data['role'] == 'petugas'){
            header("location:petugas/dashboard.php");
        } else {
            header("location:owner/dashboard.php");
        }
        exit;
    } else {
        echo "<script>alert('Gagal! Username atau Password Salah.'); window.location='index.php';</script>";
    }
}
?>