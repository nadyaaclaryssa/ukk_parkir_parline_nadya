<?php
session_start();
// Proteksi role petugas
if($_SESSION['role'] != "petugas") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

// --- LOGIKA STATISTIK UTAMA ---
$kendaraan_masuk_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'");
$kendaraan_masuk = mysqli_fetch_assoc($kendaraan_masuk_query)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Petugas - Dashboard</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb; 
            --grad-bg-1: #d4e9f7; 
            --grad-bg-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #94a3b8;
            --indigo-mature: #3730a3; 
            --indigo-soft: #e0e7ff; 
            --danger: #ef4444;
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            margin: 0; 
            background: linear-gradient(135deg, var(--grad-bg-1) 0%, var(--grad-bg-2) 100%);
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

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; display: flex; flex-direction: column; }

        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px;
        }

        .user-nav-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-stack {
            text-align: right;
            border-left: 1px solid #f1f5f9;
            padding-left: 15px;
        }

        .user-avatar { 
            width: 42px; height: 42px; 
            background: var(--primary); 
            border-radius: 12px; color: white; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 800;
            font-size: 16px;
        }

        .btn-logout-direct {
            display: flex; align-items: center; gap: 10px;
            background: var(--indigo-soft); 
            color: var(--indigo-mature);
            text-decoration: none; padding: 10px 18px; border-radius: 15px;
            font-size: 12px; font-weight: 800; transition: 0.3s ease;
            border: 1px solid rgba(55, 48, 163, 0.1);
        }

        .btn-logout-direct:hover { background: var(--indigo-mature); color: white; }
        .btn-logout-direct img { 
            width: 18px; 
            filter: invert(18%) sepia(48%) saturate(3651%) hue-rotate(238deg) brightness(91%) contrast(100%); 
        }

        /* --- PERBAIKAN GRID (PAS DI LAYAR) --- */
        .area-grid { 
            display: flex; 
            flex-wrap: nowrap; /* Tetap satu baris */
            gap: 20px; 
            width: 100%;
        }

        .area-card { 
            flex: 1; /* MEMBAGI RATA RUANG (OTOMATIS MENGECIL) */
            min-width: 0; /* Mencegah card meluap jika teks kepanjangan */
            background: white; padding: 30px 15px; border-radius: 40px; 
            border: 1px solid #f1f5f9; text-align: center; transition: 0.3s;
        }

        .area-card .count { 
            font-size: 48px; font-weight: 800; 
            color: var(--indigo-mature); 
            margin: 5px 0; display: block; 
        }

        /* Tombol Check-In WARNA AWAL */
        .btn-checkin { 
            background: var(--primary); 
            color: white; text-decoration: none; 
            padding: 10px 20px; border-radius: 15px; 
            font-size: 12px; font-weight: 700; 
            display: inline-block; margin-top: 15px; transition: 0.3s; 
            width: 100%; /* Tombol menyesuaikan lebar card */
            max-width: 180px;
        }
        .btn-checkin:hover { transform: translateY(-3px); opacity: 0.9; }

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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="transaksi_masuk.php">Transaksi Masuk</a>
                <a href="kendaraan_aktif.php">Kendaraan Aktif</a> 
                <a href="transaksi_keluar.php">Transaksi Keluar</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 28px; font-weight: 800; color: var(--text-main); margin: 0;">Status Parkir</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 15px;">Monitor Kapasitas Real-time</p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Petugas</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
                    </div>
                    <a href="../logout.php" class="btn-logout-direct">
                        <img src="logout.png" alt="Exit">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <h2 style="font-size: 11px; font-weight: 800; margin-bottom: 20px; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px;">Parking Areas Status</h2>
            
            <div class="area-grid">
                <?php
                $q_area = mysqli_query($koneksi, "SELECT * FROM tb_area");
                while($area = mysqli_fetch_assoc($q_area)) {
                    $id_area = $area['id_area'];
                    $t_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk' AND id_area='$id_area'");
                    $terisi = mysqli_fetch_assoc($t_query)['total'] ?? 0;
                    $sisa_per_area = $area['kapasitas'] - $terisi;
                ?>
                <div class="area-card">
                    <h3 style="font-size: 16px; color: var(--text-main); margin: 0; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($area['nama_area']) ?></h3>
                    <div style="font-size: 9px; color: var(--text-sub); font-weight: 700; margin-top: 5px;">KAPASITAS: <?= $area['kapasitas'] ?></div>
                    
                    <span class="count" style="color: <?= ($sisa_per_area <= 5) ? 'var(--danger)' : 'var(--indigo-mature)' ?>;">
                        <?= $sisa_per_area ?>
                    </span>
                    
                    <div style="font-size: 10px; font-weight: 800; color: var(--text-sub);">SLOT TERSEDIA</div>
                    <a href="transaksi_masuk.php?area=<?= urlencode($area['nama_area']) ?>" class="btn-checkin">Mulai Check-In</a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

</body>
</html>