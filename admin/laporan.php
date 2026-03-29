<?php
session_start();
include '../config/koneksi.php';

if($_SESSION['role'] != "petugas") { header("location:../index.php"); exit; }
$nama_petugas = $_SESSION['nama_lengkap'] ?? 'Petugas';

// Ambil data transaksi yang sudah selesai
$query = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE status='keluar' ORDER BY waktu_keluar DESC");

// Hitung total pendapatan
$total_duit = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE status='keluar'");
$total = mysqli_fetch_assoc($total_duit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan - Hogwarts Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: #f8fafc; }
        .sidebar { width: 260px; height: 100vh; background: #8f3434; color: white; position: fixed; padding: 20px 0; }
        .sidebar a { display: block; color: rgba(255,255,255,0.8); padding: 14px 25px; text-decoration: none; }
        .sidebar a.active { background: #782626; color: white; font-weight: 600; border-radius: 0 50px 50px 0; margin-right: 20px; }
        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .table-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; color: #64748b; text-transform: uppercase; font-size: 12px; }
        .summary-box { background: #8f3434; color: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: inline-block; min-width: 300px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="text-align:center; padding: 20px;"><h2 style="font-size: 14px;">HOGWARTS PARKIR</h2></div>
        <a href="dashboard.php">🏠 Monitoring Area</a>
        <a href="transaksi_masuk.php">🚗 Kendaraan Masuk</a>
        <a href="transaksi_keluar.php">💸 Kendaraan Keluar</a>
        <a href="laporan.php" class="active">📊 Laporan Harian</a>
        <a href="../auth/logout.php" style="margin-top:auto; color:#ffb1b1;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Laporan Pendapatan</h1>
        
        <div class="summary-box">
            <p style="margin:0; opacity:0.8;">Total Pendapatan Hari Ini</p>
            <h2 style="margin:5px 0 0 0;">Rp <?= number_format($total['total'] ?? 0, 0, ',', '.') ?></h2>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Waktu Keluar</th>
                        <th>Plat Nomor</th>
                        <th>Jenis</th>
                        <th>Biaya</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($row['waktu_keluar'])) ?></td>
                        <td><b><?= $row['plat_nomor'] ?></b></td>
                        <td><?= ucfirst($row['jenis_kendaraan']) ?></td>
                        <td>Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?></td>
                        <td><?= $row['petugas'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>