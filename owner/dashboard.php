<?php
session_start();
// Proteksi session agar lebih aman
if(!isset($_SESSION['role']) || $_SESSION['role'] != "owner") { 
    header("location:../index.php"); 
    exit; 
}

include '../config/koneksi.php';

$hari_ini = date('Y-m-d');
$bulan_ini = date('Y-m');

// Ambil Pendapatan Hari Ini
$q_hari = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = '$hari_ini'");
$res_hari = mysqli_fetch_assoc($q_hari);
$pendapatan_hari = $res_hari['total'] ?? 0;

// Ambil Pendapatan Bulan Ini
$q_bulan = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE DATE_FORMAT(waktu_keluar, '%Y-%m') = '$bulan_ini'");
$res_bulan = mysqli_fetch_assoc($q_bulan);
$pendapatan_bulan = $res_bulan['total'] ?? 0;

// Ambil Unit Keluar
$q_unit = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = '$hari_ini'");
$res_unit = mysqli_fetch_assoc($q_unit);
$unit_keluar = $res_unit['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline - Dashboard Pemilik</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b; 
            --text-sub: #475569;
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
            text-decoration: none; color: var(--text-sub); 
            font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }

        /* --- STYLING PROFIL & LOGOUT (PERSIS ADMIN) --- */
        .user-nav-wrapper { display: flex; align-items: center; gap: 15px; }
        .profile-stack { text-align: right; border-left: 1px solid #f1f5f9; padding-left: 15px; }
        .user-avatar {
            width: 42px; height: 42px; background: var(--primary); 
            border-radius: 12px; color: white; display: flex; 
            align-items: center; justify-content: center; font-weight: 800;
        }
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
        /* ---------------------------------------------- */

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 35px; }
        .stat-card {
            background: white; padding: 30px; border-radius: 35px;
            border: 1px solid #f1f5f9; transition: 0.3s;
        }
        .stat-card.highlight { 
            background: var(--primary); color: white; border: none;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);
        }
        .stat-card label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-sub); }
        .stat-card.highlight label { color: white; opacity: 0.9; }
        .stat-card h2 { font-size: 28px; margin: 15px 0 8px; font-weight: 800; color: var(--text-main); }
        .stat-card.highlight h2 { color: white; }
        .stat-card p { font-size: 12px; color: var(--text-sub); font-weight: 500; margin: 0; }
        .stat-card.highlight p { color: white; opacity: 0.8; }

        .chart-card {
            background: white; padding: 35px; border-radius: 40px;
            border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
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
                <a href="dashboard.php" class="active"> Dashboard</a>
                <a href="detail_laporan.php"> Detail Laporan</a>
            </div>

            <a href="../auth/logout.php" class="logout-link"> Logout</a>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Dashboard Pemilik</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 14px;">Pantau data keuangan Parline secara real-time.</p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Owner</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?? 'Pemilik' ?></div>
                    </div>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'O', 0, 1)) ?></div>
                    
                    <a href="../logout.php" class="btn-logout-direct">
                        <img src="logout.png" alt="Logout" onerror="this.src='https://cdn-icons-png.flaticon.com/512/182/182448.png';">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <label>Pendapatan Hari Ini</label>
                    <h2 style="color: #16a34a;">Rp <?= number_format((int)$pendapatan_hari, 0, ',', '.') ?></h2>
                    <p>Berdasarkan traffic harian</p>
                </div>
                
                <div class="stat-card highlight">
                    <label>Pendapatan Bulan Ini</label>
                    <h2>Rp <?= number_format((int)$pendapatan_bulan, 0, ',', '.') ?></h2>
                    <p>Total akumulasi bulan berjalan</p>
                </div>
                
                <div class="stat-card">
                    <label>Kendaraan Keluar</label>
                    <h2><?= (int)$unit_keluar ?> <span style="font-size: 16px; color: var(--text-sub);">Unit</span></h2>
                    <p>Total traffic keluar hari ini</p>
                </div>
            </div>

            <div class="chart-card">
                <h3 style="margin: 0 0 30px 0; font-size: 18px; color: var(--text-main);">📈 Tren Pendapatan Mingguan</h3>
                <canvas id="revenueChart" height="90"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: [1200000, 1900000, 1500000, <?= (int)$pendapatan_hari ?>, 0, 0, 0],
                    backgroundColor: '#2563eb',
                    borderRadius: 15,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f1f5f9', border: { display: false } }, 
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }, color: '#334155' } 
                    },
                    x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }, color: '#334155' } }
                }
            }
        });
    </script>
</body>
</html>