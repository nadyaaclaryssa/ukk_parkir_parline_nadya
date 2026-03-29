<?php
session_start();
// Proteksi role petugas
if(!isset($_SESSION['role']) || $_SESSION['role'] != "petugas") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// Ambil data kendaraan yang statusnya masih 'masuk'
$query = mysqli_query($koneksi, "SELECT tb_transaksi.*, tb_area.nama_area 
         FROM tb_transaksi 
         LEFT JOIN tb_area ON tb_transaksi.id_area = tb_area.id_area
         WHERE tb_transaksi.status = 'masuk' 
         ORDER BY waktu_masuk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Petugas - Kendaraan Aktif</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #94a3b8;
            --bg-light: #f8fafc;
            --indigo-soft: #e0e7ff;
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
            text-decoration: none; color: #475569; 
            font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        
        /* Logout Button Styling */
        .btn-logout-direct {
            display: flex; align-items: center; gap: 10px;
            background: var(--indigo-soft); color: #3730a3;
            text-decoration: none; padding: 10px 18px; border-radius: 15px;
            font-size: 12px; font-weight: 800; transition: 0.3s ease;
            border: 1px solid rgba(55, 48, 163, 0.1);
        }
        .btn-logout-direct:hover { background: #3730a3; color: white; }
        .btn-logout-direct img { 
            width: 18px; 
            filter: invert(18%) sepia(48%) saturate(3651%) hue-rotate(238deg) brightness(91%) contrast(100%); 
        }
        .btn-logout-direct:hover img { filter: brightness(0) invert(1); }

        /* Table Styling */
        .table-card {
            background: white; border-radius: 35px; border: 1px solid #f1f5f9;
            overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            margin-top: 10px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            background: var(--bg-light); padding: 22px 25px; text-align: left;
            font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-sub); font-weight: 800;
        }
        td { padding: 22px 25px; border-bottom: 1px solid #f1f5f9; color: var(--text-main); font-size: 14px; font-weight: 600; }
        
        /* Lebarkan kolom plat nomor */
        .col-plat { min-width: 180px; }
        .badge-plat {
            background: #707b8f; color: white; padding: 10px 20px;
            border-radius: 12px; font-family: 'Monaco', monospace; font-size: 15px;
            letter-spacing: 2px; display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Lebarkan kolom status */
        .col-status { min-width: 150px; }
        .status-tag {
            background: #dcfce7; color: #166534; padding: 8px 16px;
            border-radius: 12px; font-size: 12px; font-weight: 800;
            display: inline-block; text-align: center;
        }

        .user-avatar { width: 40px; height: 40px; background: var(--primary); border-radius: 12px; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; }
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
                <a href="dashboard.php">Dashboard</a>
                <a href="transaksi_masuk.php">Transaksi Masuk</a>
                <a href="kendaraan_aktif.php" class="active">Kendaraan Aktif</a> 
                <a href="transaksi_keluar.php">Transaksi Keluar</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Kendaraan Aktif</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 15px;">Daftar kendaraan yang masih parkir</p>
                </div>

                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px; border-right: 1px solid #e2e8f0; padding-right: 20px;">
                        <div style="text-align: right;">
                            <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Petugas</div>
                            <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?></div>
                        </div>
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
                        </div>
                    </div>

                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="../assets/images/logout.png" alt="Logout" onerror="this.src='https://cdn-icons-png.flaticon.com/512/182/182448.png';">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <h2 style="font-size: 13px; font-weight: 800; margin-bottom: 20px; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px;">Live Traffic Data</h2>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th width="120">ID Transaksi</th>
                            <th class="col-plat">Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Area Parkir</th>
                            <th>Waktu Masuk</th>
                            <th class="col-status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) > 0) { 
                            while($row = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td style="color: var(--primary); font-size: 13px; font-weight: 800;">#<?= $row['kode_transaksi'] ?></td>
                            <td class="col-plat"><span class="badge-plat"><?= $row['plat_nomor'] ?></span></td>
                            <td><?= $row['jenis_kendaraan'] ?? '-' ?></td>
                            <td><?= $row['nama_area'] ?? $row['area'] ?? '-' ?></td>
                            <td>
                                <div style="font-size: 14px; color: var(--text-main);"><?= date('H:i', strtotime($row['waktu_masuk'])) ?></div>
                                <div style="font-size: 11px; color: var(--text-sub);"><?= date('d M Y', strtotime($row['waktu_masuk'])) ?></div>
                            </td>
                            <td class="col-status"><span class="status-tag">DALAM AREA</span></td>
                        </tr>
                        <?php } } else { ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 100px; color: var(--text-sub);">
                                <div style="font-size: 45px; margin-bottom: 15px;">🍃</div>
                                <b style="font-size: 16px;">Tidak ada kendaraan aktif saat ini.</b>
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