<?php
session_start();
include '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['cari_transaksi'])) { 
    
    // Gunakan strtoupper agar input "b 123" jadi "B 123" (cocok dengan database)
    $keyword = mysqli_real_escape_string($koneksi, strtoupper($_POST['keyword']));
    $waktu_keluar = date('Y-m-d H:i:s');

    // Query mencari plat nomor yang statusnya masih 'masuk'
    $query = mysqli_query($koneksi, "SELECT t.*, tr.tarif_per_jam 
                                     FROM tb_transaksi t 
                                     JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                                     WHERE t.plat_nomor = '$keyword' AND t.status = 'masuk'
                                     ORDER BY t.id_transaksi DESC LIMIT 1");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $id_transaksi = $data['id_transaksi'];
        $awal  = new DateTime($data['waktu_masuk']);
        $akhir = new DateTime($waktu_keluar);
        $diff  = $awal->diff($akhir);

        // Hitung durasi jam
        $jam = $diff->h + ($diff->days * 24);
        if ($diff->i > 0 || $diff->s > 0) { $jam++; } 
        if ($jam <= 0) { $jam = 1; }

        $total_bayar = $jam * $data['tarif_per_jam'];

        // Update database: status berubah jadi 'keluar' dan biaya terisi
        $update = mysqli_query($koneksi, "UPDATE tb_transaksi SET 
                                          waktu_keluar = '$waktu_keluar', 
                                          biaya_total = '$total_bayar', 
                                          status = 'keluar' 
                                          WHERE id_transaksi = '$id_transaksi'");

        if ($update) {
            // PERBAIKAN DI SINI: Arahkan ke file struk khusus keluar
            header("location:cetak_struk_keluar.php?id=$id_transaksi");
            exit;
        } else {
            echo "Gagal update: " . mysqli_error($koneksi);
        }
    } else {
        echo "<script>alert('Data Plat Nomor $keyword tidak ditemukan atau sudah Checkout!'); window.location='transaksi_keluar.php';</script>";
    }
} else {
    header("location:transaksi_keluar.php");
    exit;
}
?>