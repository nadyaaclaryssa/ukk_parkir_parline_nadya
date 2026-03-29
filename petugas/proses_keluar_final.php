<?php
session_start();
include '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if($_SESSION['role'] != "petugas") { header("location:../index.php"); exit; }

if (isset($_POST['bayar'])) {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_POST['id_transaksi']);
    $total_bayar  = mysqli_real_escape_string($koneksi, $_POST['total_bayar']);
    $waktu_keluar = date('Y-m-d H:i:s');
    $petugas_nama = $_SESSION['nama']; 

    $query = "UPDATE tb_transaksi SET 
              waktu_keluar = '$waktu_keluar', 
              biaya_total  = '$total_bayar', 
              status       = 'keluar',
              petugas      = '$petugas_nama' 
              WHERE id_transaksi = '$id_transaksi'";

    if (mysqli_query($koneksi, $query)) {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap' rel='stylesheet'>
            <style>
                body { font-family: 'Plus Jakarta Sans', sans-serif; }
                .swal2-popup { border-radius: 40px !important; padding: 2.5rem !important; }
                .lunas-badge {
                    display: inline-block;
                    border: 3px solid #22c55e;
                    color: #22c55e;
                    padding: 4px 15px;
                    border-radius: 12px;
                    font-weight: 800;
                    font-size: 14px;
                    text-transform: uppercase;
                    margin-bottom: 15px;
                    letter-spacing: 2px;
                    transform: rotate(-5deg);
                }
                .price-tag {
                    color: #2563eb;
                    font-size: 24px;
                    font-weight: 800;
                    display: block;
                    margin-top: 5px;
                }
            </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'Transaksi Berhasil!',
                    html: `
                        <div class='lunas-badge'>LUNAS</div>
                        <div style='color: #64748b; font-size: 14px;'>Total yang dibayarkan:</div>
                        <span class='price-tag'>Rp " . number_format($total_bayar, 0, ',', '.') . "</span>
                        <div style='margin-top: 15px; color: #94a3b8; font-size: 13px;'>Silakan kendaraan meninggalkan area.</div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Selesai & Cetak',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'transaksi_keluar.php';
                    }
                });
            </script>
        </body>
        </html>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>