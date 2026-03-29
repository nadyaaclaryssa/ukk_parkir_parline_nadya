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
    <title>Parline - Detail Laporan Gringotts</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb; /* Biru Safir */
            --primary-light: #eff6ff;
            --gringotts-gold: #c6a059; /* Emas Gringotts */
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-menu-inactive: #334155; 
            --text-sub: #94a3b8;
            --white: #ffffff;
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
            text-decoration: none; color: var(--text-menu-inactive);
            font-size: 14px; font-weight: 700;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .section-title { font-size: 24px; color: var(--text-main); font-weight: 800; margin: 0; }
        .section-subtitle { color: var(--text-main); font-size: 14px; margin-top: 5px; opacity: 0.8; }

        .btn-print {
            background: var(--primary); color: white; border: none;
            padding: 12px 22px; border-radius: 15px; font-weight: 700;
            font-size: 13px; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 10px;
        }
        .btn-print:hover { background: #1e40af; transform: translateY(-1px); }

        /* Tabel Section (Tampilan Layar) */
        .table-container { 
            background: white; border-radius: 30px; border: 1px solid #f1f5f9; 
            overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; padding: 20px 25px; 
            color: var(--text-main); font-size: 11px; font-weight: 800; 
            text-transform: uppercase; background: #fcfdfe; border-bottom: 1px solid #f1f5f9; 
        }
        td { padding: 20px 25px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f8fafc; }
        
        .badge-money { background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 12px; font-weight: 800; font-size: 13px; }
        .badge-type { background: #f1f5f9; color: var(--text-main); padding: 5px 12px; border-radius: 10px; font-size: 11px; font-weight: 700; }
        .logout-link { margin-top: auto; color: var(--text-menu-inactive); text-decoration: none; font-size: 14px; font-weight: 700; padding-left: 20px; transition: 0.3s; }

        /* Kop Surat (Default disembunyikan di layar) */
        .print-only-kop { display: none; }

        /* ===========================================
           CSS KHUSUS CETAK (PRINT) - Gringotts Edition
           =========================================== */
        @media print {
            /* 1. Sembunyikan elemen layar */
            .sidebar, .btn-print, .logout-link { display: none !important; }

            /* 2. Reset Layout Kertas */
            body { background: white !important; padding: 0 !important; margin: 10mm !important; /* Margin kertas */ }
            .app-container { 
                box-shadow: none !important; border: none !important; 
                width: 100% !important; max-width: 100% !important; 
                height: auto !important; display: block !important; overflow: visible !important;
            }
            .main-content { padding: 0 !important; background: white !important; width: 100% !important; }

            /* 3. Hadirkan Kop Surat Resmi di Cetakan */
            .print-only-kop { 
                display: block !important;
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 3px double var(--gringotts-gold); /* Garis emas ganda resmi */
            }
            .kop-logo { width: 60px; margin-bottom: 10px; }
            .kop-title { font-size: 32px; font-weight: 800; color: var(--text-main); text-transform: uppercase; margin: 0; }
            .kop-subtitle { font-size: 12px; color: var(--text-main); margin: 5px 0; }

            /* Header Teks Laporan (Disembunyikan yang versi layar) */
            .header-top { display: none !important; }

            /* 4. Tampilan Tabel Formal & Berwarna */
            .table-container { border: none !important; box-shadow: none !important; border-radius: 0 !important; margin-top: 20px; }
            
            table { width: 100%; border: none !important; /* Hapus garis kaku luar */ }
            
            /* Header Tabel (Formal tapi berwarna biru Gringotts) */
            th { 
                background-color: var(--primary) !important; /* Biru Safir */
                color: white !important; /* Teks Putih agar kontras */
                border: none !important;
                border-bottom: 2px solid var(--gringotts-gold) !important; /* Garis bawah emas */
                text-align: center;
                padding: 12px 15px !important;
                -webkit-print-color-adjust: exact; /* Paksa cetak warna latar */
                print-color-adjust: exact;
            }
            
            /* Isi Tabel (Tanpa garis kotak-kotak kaku) */
            td { 
                border: none !important;
                border-bottom: 1px solid #e2e8f0 !important; /* Garis tipis horizontal saja */
                padding: 12px 15px !important; 
                color: #000 !important; 
            }
            
            /* Tampilan Badge saat Print (Formal) */
            .badge-type { background: transparent !important; border: 1px solid #ccc !important; padding: 4px 8px !important; color: #000 !important; font-weight: bold; }
            .badge-money { background: transparent !important; padding: 0 !important; color: #000 !important; font-weight: bold; font-size: 14px !important; }
            
            /* Tambahkan Tanda Tangan */
            .main-content::after {
                content: "Diperiksa oleh,\n\n( Gringotts Goblins Team )";
                display: block;
                text-align: right;
                margin-top: 80px;
                font-weight: 600;
                white-space: pre;
                margin-right: 50px;
            }
        }
    </style>
</head>
<body>

    <div class="app-container">
        <div class="sidebar">
            <div class="logo-section">
                <img src="../parline.png" alt="Logo">
                <h2>Parline</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php"> Dashboard</a>
                <a href="detail_laporan.php" class="active"> Detail Laporan</a>
            </div>
            <a href="../logout.php" class="logout-link"> Logout</a>
        </div>

        <div class="main-content">
            <div class="print-only-kop">
                <img src="../../hogwarts-removebg-preview.png" class="kop-logo" alt="Hogwarts Gringotts Logo">
                <h1 class="kop-title">Gringotts Vault Records</h1>
                <p class="kop-subtitle">Parline Parking System - Laporan Transaksi Brankas</p>
                <p class="kop-subtitle" style="font-size: 10px; color: #64748b;">Diunduh pada: <?= date('d F Y, H:i'); ?></p>
            </div>

            <div class="header-top">
                <div>
                    <h1 class="section-title">Detail Laporan Transaksi</h1>
                    <p class="section-subtitle">Seluruh data riwayat parkir kendaraan di Cabang Ujung Berung</p>
                </div>
                <button class="btn-print" onclick="window.print()">🖨️ Cetak Laporan Resmi</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Plat Nomor</th>
                            <th>Jenis Kendaraan</th>
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
                            <td style="text-align: center;">#<?= $no++; ?></td>
                            <td style="font-weight: 800; color: var(--primary);"><?= $data['plat_nomor']; ?></td>
                            <td style="text-align: center;"><span class="badge-type"><?= strtoupper($data['jenis_kendaraan'] ?? 'Umum'); ?></span></td>
                            <td><?= date('d/m/y H:i', strtotime($data['waktu_masuk'])); ?></td>
                            <td><?= date('d/m/y H:i', strtotime($data['waktu_keluar'])); ?></td>
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