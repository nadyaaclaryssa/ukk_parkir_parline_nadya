<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != "owner") { 
    header("location:../index.php"); 
    exit; 
}

include '../config/koneksi.php';

$hari_ini = date('Y-m-d');
$bulan_ini = date('Y-m');

// 1. Ambil Pendapatan Hari Ini
$q_hari = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = '$hari_ini'");
$res_hari = mysqli_fetch_assoc($q_hari);
$pendapatan_hari = $res_hari['total'] ?? 0;

// 2. Ambil Pendapatan Bulan Ini
$q_bulan = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE DATE_FORMAT(waktu_keluar, '%Y-%m') = '$bulan_ini'");
$res_bulan = mysqli_fetch_assoc($q_bulan);
$pendapatan_bulan = $res_bulan['total'] ?? 0;

// 3. Ambil Unit Keluar
$q_unit = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = '$hari_ini'");
$res_unit = mysqli_fetch_assoc($q_unit);
$unit_keluar = $res_unit['total'] ?? 0;

// 4. LOGIC DIAGRAM: 7 hari terakhir
$data_chart = [];
for ($i = 0; $i < 7; $i++) {
    $q_chart = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi 
                WHERE WEEKDAY(waktu_keluar) = $i 
                AND YEARWEEK(waktu_keluar, 1) = YEARWEEK(CURDATE(), 1)");
    $res_c = mysqli_fetch_assoc($q_chart);
    $data_chart[] = (int)($res_c['total'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline - Dashboard Pemilik</title>
    <link rel="icon" href="../assets/images/parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b; 
            --text-sub: #94a3b8;
            --indigo-mature: #3730a3; 
            --indigo-soft: #e0e7ff; 
            --border-light: #e2e8f0;
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

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; display: flex; flex-direction: column; }

        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px;
        }

        /* Gaya Kolom untuk Ringkasan Bisnis */
        .title-column {
            border-left: 6px solid var(--primary);
            padding-left: 20px;
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
            font-weight: 800; font-size: 16px;
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
        .btn-logout-direct img { width: 18px; filter: invert(18%) sepia(48%) saturate(3651%) hue-rotate(238deg) brightness(91%) contrast(100%); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: white; padding: 25px; border-radius: 30px;
            border: 1px solid var(--border-light); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
        }
        .stat-card label { font-size: 11px; font-weight: 700; color: var(--text-sub); text-transform: uppercase; }
        .stat-card h2 { font-size: 26px; margin: 10px 0 5px; font-weight: 800; color: var(--text-main); }
        
        /* Kontainer Utama dengan Border Lebih Tegas */
        .main-visual-row { display: grid; grid-template-columns: 1.8fr 1fr; gap: 25px; }
        
        .chart-container { 
            background: white; padding: 35px; border-radius: 40px; 
            border: 2px solid var(--border-light); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }
        
        .side-panel { display: flex; flex-direction: column; gap: 20px; }
        
        .panel-box { 
            background: white; padding: 30px; border-radius: 35px; 
            border: 2px solid var(--border-light); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .source-bar { height: 10px; background: #f1f5f9; border-radius: 10px; margin: 12px 0 25px; overflow: hidden; }
        .fill { height: 100%; background: var(--primary); border-radius: 10px; }
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="detail_laporan.php">Detail Laporan</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div class="title-column">
                    <h1 style="font-size: 32px; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1.2;">Ringkasan<br>Bisnis</h1>
                    <p style="color: var(--text-sub); margin: 8px 0 0 0; font-size: 14px; font-weight: 500;">Monitor pendapatan & performa unit</p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Owner</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'O', 0, 1)) ?>
                    </div>
                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="../assets/images/logout.png" alt="Exit">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <label>Pendapatan Hari Ini</label>
                    <h2 style="color: #16a34a;">Rp <?= number_format((int)$pendapatan_hari, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-card" style="background: var(--primary); border: none; color: white; box-shadow: 0 15px 30px rgba(37, 99, 235, 0.2);">
                    <label style="color: rgba(255,255,255,0.7);">Total Bulan Ini</label>
                    <h2 style="color: white;">Rp <?= number_format((int)$pendapatan_bulan, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-card">
                    <label>Unit Keluar Hari Ini</label>
                    <h2><?= (int)$unit_keluar ?> <span style="font-size: 14px; color: var(--text-sub);">Kendaraan</span></h2>
                </div>
            </div>

            <div class="main-visual-row">
                <div class="chart-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h4 style="margin: 0; color: var(--text-main); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800;">Tren Pendapatan Mingguan</h4>
                        <span style="font-size: 11px; color: var(--text-sub); font-weight: 700; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">7 Hari Terakhir</span>
                    </div>
                    <canvas id="revenueChart" height="150"></canvas>
                </div>

                <div class="side-panel">
                    <div class="panel-box">
                        <h4 style="margin: 0 0 20px; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-main); font-weight: 800;">Jenis Kendaraan</h4>
                        <div style="font-size: 12px; font-weight: 700; display: flex; justify-content: space-between; color: var(--text-main);">
                            <span>Motor</span><span>75%</span>
                        </div>
                        <div class="source-bar"><div class="fill" style="width: 75%;"></div></div>
                        
                        <div style="font-size: 12px; font-weight: 700; display: flex; justify-content: space-between; color: var(--text-main);">
                            <span>Mobil</span><span>25%</span>
                        </div>
                        <div class="source-bar"><div class="fill" style="width: 25%; background: #94a3b8;"></div></div>
                    </div>

                    <div class="panel-box" style="text-align: center; background: #ffffff; display: flex; flex-direction: column; justify-content: center; padding: 20px;">
                        <div style="font-weight: 800; color: var(--primary); text-transform: uppercase; font-size: 13px; letter-spacing: 1px;"><?= date('l') ?></div>
                        <div style="font-size: 48px; font-weight: 900; margin: 2px 0; color: var(--indigo-mature);"><?= date('d') ?></div>
                        <div style="font-size: 13px; color: var(--text-sub); font-weight: 700; text-transform: uppercase;"><?= date('F Y') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Income',
                    data: <?= json_encode($data_chart) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f1f5f9', borderDash: [5, 5] }, 
                        ticks: { font: { family: 'Plus Jakarta Sans', weight: 600, size: 10 }, color: '#94a3b8' } 
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { font: { family: 'Plus Jakarta Sans', weight: 600, size: 10 }, color: '#94a3b8' } 
                    }
                }
            }
        });
    </script>
</body>
</html>