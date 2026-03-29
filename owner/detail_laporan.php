<?php
session_start();
if($_SESSION['role'] != "owner") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE status='keluar' ORDER BY waktu_keluar DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline - Parkiring online</title>
    <link rel="icon" href="../assets/images/parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb; 
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #475569;
            --indigo-soft: #e0e7ff;
            --gringotts-gold: #c6a059;
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            margin: 0; 
            background: linear-gradient(135deg, var(--grad-1) 0%, var(--grad-2) 100%);
            background-attachment: fixed;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; padding: 20px;
        }

        .app-container {
            width: 100%; max-width: 1400px; height: 92vh;
            background: white; border-radius: 50px;
            display: flex; overflow: hidden;
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.15);
        }

        /* Sidebar */
        .sidebar {
            width: 280px; background: white;
            padding: 40px 25px; display: flex; flex-direction: column;
            border-right: 1px solid #f0f4f8;
        }

        .logo-section { display: flex; align-items: center; gap: 15px; padding: 0 10px; margin-bottom: 40px; }
        .logo-section img { width: 45px; height: 45px; border-radius: 12px; }
        .logo-section h2 { font-size: 20px; margin: 0; color: var(--text-main); font-weight: 800; }

        .nav-menu { flex-grow: 1; }
        .nav-menu a {
            display: flex; align-items: center; gap: 12px; padding: 14px 20px;
            text-decoration: none; color: var(--text-sub);
            font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        /* Main Content */
        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        /* Header Area */
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }

        /* User Nav & Logout Style (SAMA DENGAN DASHBOARD) */
        .user-nav-wrapper { display: flex; align-items: center; gap: 15px; }
        
        .btn-print {
            background: var(--primary); color: white; border: none;
            padding: 10px 20px; border-radius: 15px; font-weight: 700;
            font-size: 12px; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-print:hover { background: #1e40af; transform: translateY(-2px); }

        .btn-logout-direct {
            display: flex; align-items: center; gap: 10px;
            background: var(--indigo-soft); color: #3730a3;
            text-decoration: none; padding: 10px 18px; border-radius: 15px;
            font-size: 12px; font-weight: 800; transition: 0.3s ease;
            border: 1px solid rgba(55, 48, 163, 0.1);
        }
        .btn-logout-direct:hover { background: #3730a3; color: white; transform: translateY(-2px); }
        
        /* Filter icon biar warnanya biru indigo pas awal */
        .btn-logout-direct img { 
            width: 18px; 
            filter: invert(18%) sepia(48%) saturate(3651%) hue-rotate(238deg) brightness(91%) contrast(100%); 
        }
        .btn-logout-direct:hover img { filter: brightness(0) invert(1); }

        /* Table Aesthetic */
        .table-container { 
            background: white; border-radius: 35px; border: 1px solid #f1f5f9; 
            overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; padding: 22px 25px; 
            color: var(--text-main); font-size: 11px; font-weight: 800; 
            text-transform: uppercase; background: #fcfdfe; border-bottom: 1px solid #f1f5f9; 
        }
        td { padding: 20px 25px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f8fafc; }
        tr:last-child td { border-bottom: none; }
        
        .badge-money { background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 12px; font-weight: 800; font-size: 13px; }
        .badge-type { background: #f1f5f9; color: var(--text-sub); padding: 5px 12px; border-radius: 10px; font-size: 11px; font-weight: 700; }

        .print-only-kop { display: none; }

        @media print {
            .sidebar, .user-nav-wrapper { display: none !important; }
            body { background: white !important; padding: 0 !important; margin: 10mm !important; }
            .app-container { box-shadow: none !important; border: none !important; width: 100% !important; height: auto !important; display: block !important; }
            .main-content { padding: 0 !important; background: white !important; }
            .print-only-kop { display: block !important; text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px double var(--gringotts-gold); }
            .kop-logo { width: 60px; margin-bottom: 10px; }
            .kop-title { font-size: 32px; font-weight: 800; color: var(--text-main); text-transform: uppercase; margin: 0; }
            .header-top { display: none !important; }
            th { background-color: var(--primary) !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="app-container">
        <div class="sidebar">
            <div class="logo-section">
                <img src="../assets/images/parline.png" alt="Logo">
                <h2>Parline</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php"> Dashboard</a>
                <a href="detail_laporan.php" class="active"> Detail Laporan</a>
            </div>
        </div>

        <div class="main-content">
            <div class="print-only-kop">
                <img src="../assets/images/parline.png" class="kop-logo" alt="Logo">
                <h1 class="kop-title">Laporan Parline</h1>
                <p class="kop-subtitle">Parline Parking System - Laporan Transaksi Resmi</p>
                <p class="kop-subtitle" style="font-size: 10px;">Diunduh pada: <?= date('d F Y, H:i'); ?></p>
            </div>

            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Detail Laporan</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 14px;">Riwayat transaksi kendaraan Cabang Ujung Berung.</p>
                </div>
                
                <div class="user-nav-wrapper">
                    <button class="btn-print" onclick="window.print()">
                        <span>🖨️</span> Cetak Laporan
                    </button>
                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="../assets/images/logout.png" alt="Logout" onerror="this.src='https://cdn-icons-png.flaticon.com/512/182/182448.png';">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center;">No</th>
                            <th>Plat Nomor</th>
                            <th style="text-align: center;">Jenis Kendaraan</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Total Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($data = mysqli_fetch_array($query)) { 
                        ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-sub); font-weight: 600;">#<?= $no++; ?></td>
                            <td style="font-weight: 800; color: var(--primary);"><?= $data['plat_nomor']; ?></td>
                            <td style="text-align: center;"><span class="badge-type"><?= strtoupper($data['jenis_kendaraan'] ?? 'Umum'); ?></span></td>
                            <td style="color: var(--text-sub);"><?= date('d/m/y H:i', strtotime($data['waktu_masuk'])); ?></td>
                            <td style="color: var(--text-sub);"><?= date('d/m/y H:i', strtotime($data['waktu_keluar'])); ?></td>
                            <td>
                                <span class="badge-money">
                                    Rp <?= number_format($data['biaya_total'], 0, ',', '.'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>