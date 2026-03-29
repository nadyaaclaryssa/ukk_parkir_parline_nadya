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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --gringotts-gold: #c6a059;
            --text-main: #1e293b; 
            --text-menu-inactive: #334155; 
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

        .btn-report {
            background: var(--primary); color: white; border: none;
            padding: 12px 22px; border-radius: 15px; font-weight: 700;
            font-size: 13px; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
        }
        .btn-report:hover { 
            transform: translateY(-2px); background: #1d4ed8; 
            box-shadow: 0 12px 24px -5px rgba(37, 99, 235, 0.4); 
        }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 35px; }
        .stat-card {
            background: white; padding: 30px; border-radius: 35px;
            border: 1px solid #f1f5f9; transition: 0.3s;
        }
        .stat-card.highlight { 
            background: var(--primary); color: white; border: none;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);
        }
        .stat-card label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-menu-inactive); }
        .stat-card.highlight label { color: white; opacity: 0.9; }
        .stat-card h2 { font-size: 28px; margin: 15px 0 8px; font-weight: 800; color: var(--text-main); }
        .stat-card.highlight h2 { color: white; }
        .stat-card p { font-size: 12px; color: var(--text-menu-inactive); font-weight: 500; margin: 0; }
        .stat-card.highlight p { color: white; opacity: 0.8; }

        .chart-card {
            background: white; padding: 35px; border-radius: 40px;
            border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        .logout-link { margin-top: auto; color: var(--text-menu-inactive); text-decoration: none; font-size: 14px; font-weight: 700; padding-left: 20px; transition: 0.3s; }
        .logout-link:hover { color: #ef4444; }

        .print-only-kop { display: none; }

        @media print {
            .sidebar, .btn-report, .logout-link, .chart-card { display: none !important; }
            body { background: white !important; padding: 0 !important; margin: 10mm !important; }
            .app-container { box-shadow: none !important; border: none !important; width: 100% !important; height: auto !important; display: block !important; }
            .main-content { padding: 0 !important; background: white !important; }
            .print-only-kop { 
                display: block !important; text-align: center; margin-bottom: 40px;
                padding-bottom: 20px; border-bottom: 3px double var(--gringotts-gold);
            }
            .kop-logo { width: 70px; margin-bottom: 10px; }
            .kop-title { font-size: 32px; font-weight: 800; color: var(--text-main); text-transform: uppercase; margin: 0; }
            .header-top { display: none !important; }
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

            <a href="../logout.php" class="logout-link"> Logout</a>
        </div>

        <div class="main-content">
            <div class="print-only-kop">
                <img src="../../hogwarts-removebg-preview.png" class="kop-logo" alt="Logo">
                <h1 class="kop-title">Parking Online</h1>
                <p class="kop-subtitle">Parline - Gringotts System</p>
                <p class="kop-subtitle" style="font-size: 10px;">Periode Laporan: <?= date('d F Y'); ?></p>
            </div>

            <div class="header-top">
                <div>
                    <h1 class="section-title">Dashboard Pemilik</h1>
                    <p class="section-subtitle">Pantau data keuangan Parline hari ini secara real-time.</p>
                </div>
                <button class="btn-report" onclick="window.print()">
                    🖨️ Cetak Laporan Harian
                </button>
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
                    <p>Total pendapatan brankas</p>
                </div>
                
                <div class="stat-card">
                    <label>Kendaraan Keluar</label>
                    <h2><?= (int)$unit_keluar ?> <span style="font-size: 16px; color: var(--text-menu-inactive);">Unit</span></h2>
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
        const hariIni = new Date().getDay(); // 0 (Minggu) sampai 6 (Sabtu)
        const dataGrafik = [0, 0, 0, 0, 0, 0, 0];
        
        // Memasukkan data PHP ke array grafik (Indeks JS: 0=Minggu, 1=Senin...)
        // Kita sesuaikan dengan urutan label ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
        // Jika hari ini Kamis (indeks 4 di label), maka:
        const labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        const dayIndex = (hariIni === 0) ? 6 : hariIni - 1;
        dataGrafik[dayIndex] = <?= (int)$pendapatan_hari ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: [1200000, 1900000, 1500000, dataGrafik[3], 0, 0, 0], // Contoh statis + data asli
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
                        grid: { color: '#f1f5f9', drawBorder: false }, 
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }, color: '#334155' } 
                    },
                    x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }, color: '#334155' } }
                }
            }
        });
    </script>
</body>
</html>