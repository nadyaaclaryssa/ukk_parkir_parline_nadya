<?php
session_start();
include '../config/koneksi.php';

// Proteksi Admin
if($_SESSION['role'] != "admin") { header("location:../index.php"); exit; }

// Ambil data filter jika ada
$filter_tgl = $_GET['tanggal'] ?? '';
$search = $_GET['search'] ?? '';

// Query dasar
$query_str = "SELECT * FROM tb_log WHERE 1=1";

if($filter_tgl) {
    $query_str .= " AND DATE(waktu) = '$filter_tgl'";
}
if($search) {
    $query_str .= " AND (user_petugas LIKE '%$search%' OR aktivitas LIKE '%$search%')";
}

$query_str .= " ORDER BY waktu DESC";
$query = mysqli_query($koneksi, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas - Hogwarts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- CSS TERPUSAT DI ATAS --- */
        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            display: flex; 
            background: #f8fafc; 
            color: #1e293b;
        }

        /* Sidebar Tema Hogwarts */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: #8f3434; 
            color: white; 
            position: fixed; 
            display: flex; 
            flex-direction: column; 
            padding: 20px 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header { 
            text-align: center; 
            padding: 20px; 
            margin-bottom: 20px; 
        }

        .sidebar-header img { 
            width: 65px; 
            margin-bottom: 10px; 
        }

        .sidebar-header h2 { 
            font-size: 14px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            margin: 0; 
        }

        .sidebar a { 
            display: flex; 
            align-items: center; 
            color: rgba(255,255,255,0.8); 
            padding: 14px 25px; 
            text-decoration: none; 
            transition: 0.3s; 
            font-size: 15px;
        }

        .sidebar a:hover { 
            background: rgba(255,255,255,0.1); 
            color: white; 
        }

        .sidebar a.active { 
            background: #782626; 
            color: white; 
            border-radius: 0 50px 50px 0; 
            margin-right: 20px; 
            font-weight: 600; 
        }

        /* Content Area */
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: 100%; 
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 { 
            font-size: 24px; 
            font-weight: 700; 
            color: #0f172a; 
            margin: 0;
        }

        /* Filter Box Style */
        .filter-card {
            background: white; 
            padding: 25px; 
            border-radius: 16px; 
            display: flex; 
            gap: 20px; 
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            border: 1px solid #f1f5f9;
        }

        .input-group { 
            display: flex; 
            flex-direction: column; 
            gap: 8px; 
            flex: 1; 
        }

        .input-group label { 
            font-size: 12px; 
            font-weight: 700; 
            color: #64748b; 
            text-transform: uppercase;
        }

        .input-group input { 
            padding: 12px 15px; 
            border-radius: 10px; 
            border: 1px solid #e2e8f0; 
            font-family: inherit; 
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        .input-group input:focus {
            border-color: #8f3434;
            box-shadow: 0 0 0 3px rgba(143, 52, 52, 0.1);
        }

        /* Table Log Style */
        .log-container {
            background: white; 
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            border: 1px solid #f1f5f9;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        th { 
            text-align: left; 
            padding: 18px 25px; 
            font-size: 12px; 
            font-weight: 600;
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }

        td { 
            padding: 20px 25px; 
            font-size: 14px; 
            color: #334155;
            border-bottom: 1px solid #f8fafc; 
        }

        tr:last-child td { border-bottom: none; }

        .time-col { color: #94a3b8; width: 200px; }
        .user-col { font-weight: 700; color: #0f172a; width: 250px; }
        .activity-col { color: #475569; }

        tr:hover { background: #fcfcfd; }

        .btn-submit {
            display: none; /* Otomatis submit saat ganti tanggal */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../hogwarts.jpg" alt="Logo">
            <h2>ADMIN PANEL</h2>
        </div>
        
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="data_user.php">👥 Data User</a>
        <a href="log_aktivitas.php" class="active">📜 Log Aktivitas</a>
        
        <div style="margin-top: auto;">
            <a href="../auth/logout.php" style="color: #ffb1b1;">🚪 Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Log Aktivitas Sistem</h1>
        </div>

        <form method="GET">
            <div class="filter-card">
                <div class="input-group">
                    <label>Pilih Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $filter_tgl ?>" onchange="this.form.submit()">
                </div>
                <div class="input-group">
                    <label>Cari Aktivitas / User</label>
                    <input type="text" name="search" placeholder="Ketik kata kunci..." value="<?= $search ?>" onchange="this.form.submit()">
                </div>
            </div>
        </form>

                <div class="log-container">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User / Petugas</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($query) > 0):
                        while($row = mysqli_fetch_assoc($query)): 
                    ?>
                    <tr>
                        <td class="time-col"><?= date('d M Y, H:i', strtotime($row['waktu'])) ?></td>
                        <td class="user-col"><?= $row['user_petugas'] ?></td>
                        <td class="activity-col"><?= $row['aktivitas'] ?></td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 50px; color: #94a3b8;">
                            Tidak ditemukan rekaman aktivitas.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>