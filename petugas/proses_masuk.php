<?php
session_start();
include '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['simpan'])) {
    // 1. Ambil data dari form & sanitize
    $plat_nomor = mysqli_real_escape_string($koneksi, strtoupper($_POST['plat_nomor']));
    $id_tarif   = mysqli_real_escape_string($koneksi, $_POST['id_tarif']);
    $jenis      = mysqli_real_escape_string($koneksi, strtoupper($_POST['jenis_kendaraan'])); 
    $id_area    = mysqli_real_escape_string($koneksi, $_POST['id_area']); 
    
    // 2. Cari NAMA AREA berdasarkan ID Area (untuk cadangan teks di kolom 'area')
    $q_area    = mysqli_query($koneksi, "SELECT nama_area FROM tb_area WHERE id_area = '$id_area'");
    $data_area = mysqli_fetch_assoc($q_area);
    $nama_area = $data_area['nama_area'];

    // 3. Ambil NAMA petugas dari session
    $nama_petugas = $_SESSION['nama'] ?? 'Petugas'; 
    
    // 4. Buat Data Otomatis
    $kode_transaksi = "TRX-" . date('YmdHis');
    $waktu_masuk    = date('Y-m-d H:i:s');
    $status         = "masuk";

    // 5. QUERY: Memasukkan data ke tb_transaksi
    // Pastikan kolom 'id_area' sudah ada di tabel tb_transaksi kamu di HeidiSQL
    $query = "INSERT INTO tb_transaksi 
              (id_tarif, id_area, kode_transaksi, status, petugas, jenis_kendaraan, waktu_masuk, plat_nomor, area) 
              VALUES 
              ('$id_tarif', '$id_area', '$kode_transaksi', '$status', '$nama_petugas', '$jenis', '$waktu_masuk', '$plat_nomor', '$nama_area')";

    if (mysqli_query($koneksi, $query)) {
        $last_id = mysqli_insert_id($koneksi);
        // Redirect ke cetak struk
        header("location:cetak_struk.php?id=$last_id");
        exit;
    } else {
        // Jika error, akan muncul pesan di sini
        die("Error Database: " . mysqli_error($koneksi));
    }
} else {
    header("location:transaksi_masuk.php");
    exit;
}
?>