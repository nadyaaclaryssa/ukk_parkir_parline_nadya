<?php
session_start();
include '../config/koneksi.php';

$id = $_GET['id'];
// Ambil data lengkap dengan JOIN untuk memunculkan jenis kendaraan dan nama petugas
$query = mysqli_query($koneksi, "SELECT t.*, tr.jenis_kendaraan, u.nama_lengkap 
                                 FROM tb_transaksi t 
                                 JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                                 JOIN tb_user u ON t.id_user = u.id_user 
                                 WHERE t.id_transaksi = '$id'");
$d = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Keluar - Parline</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; margin: auto; padding: 20px; border: 1px solid #eee; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .content { margin-top: 15px; font-size: 14px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .footer { text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; font-size: 12px; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">PARLINE</h2>
        <small>Parking System</small>
    </div>

    <div class="content">
        <div class="row"><span>ID Trans</span> <span>#<?= $d['id_transaksi'] ?></span></div>
        <div class="row"><span>Plat</span> <span><?= $d['plat_nomor'] ?></span></div>
        <div class="row"><span>Tipe</span> <span><?= $d['jenis_kendaraan'] ?></span></div>
        <hr style="border: 0.5px dashed #000;">
        <div class="row"><span>Masuk</span> <span><?= date('d/m H:i', strtotime($d['waktu_masuk'])) ?></span></div>
        <div class="row"><span>Keluar</span> <span><?= date('d/m H:i', strtotime($d['waktu_keluar'])) ?></span></div>
        <hr style="border: 0.5px dashed #000;">
        <div class="row" style="font-weight: bold;"><span>TOTAL</span> <span>Rp <?= number_format($d['biaya_total'], 0, ',', '.') ?></span></div>
    </div>

    <div class="footer">
        <p>Petugas: <?= $d['nama_lengkap'] ?></p>
        <p>Terima Kasih Atas Kunjungan Anda</p>
    </div>

    <button class="btn-print" onclick="window.print()" style="width:100%; margin-top:20px; padding:10px; cursor:pointer;">Cetak Struk</button>
    
    <script>
        // Otomatis cetak saat halaman terbuka
        window.print();
        // Kembali ke halaman transaksi setelah print dialog ditutup
        window.onafterprint = function() {
            window.location.href = 'transaksi_keluar.php';
        };
    </script>
</body>
</html>