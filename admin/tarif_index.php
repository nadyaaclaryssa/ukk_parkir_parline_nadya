<?php
session_start();
if($_SESSION['role'] != "admin") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

// Ambil data tarif
$query = mysqli_query($koneksi, "SELECT * FROM tb_tarif");

// Olah ke dalam Array
$semua_tarif = [];
while($row = mysqli_fetch_assoc($query)){
    $semua_tarif[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tarif - Hogwarts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            display: flex; 
            background: #f8fafc; 
            color: #1e293b;
        }

        /* Sidebar Styling - Tema Hogwarts Marun */
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
            width: 60px;
            margin-bottom: 10px;
        }

        .sidebar-header h2 { 
            font-size: 18px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            color: #ffffff;
            margin: 0;
        }

        .sidebar a { 
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.8); 
            padding: 14px 25px; 
            text-decoration: none; 
            transition: all 0.3s;
            font-size: 15px;
        }

        .sidebar a:hover { 
            background: rgba(255,255,255,0.1); 
            color: white; 
            padding-left: 30px; 
        }

        .sidebar a.active {
            background: #782626; 
            color: white;
            border-radius: 0 50px 50px 0;
            margin-right: 20px;
            font-weight: 600;
        }

        /* Content Area */
        .content { 
            margin-left: 260px; 
            padding: 40px; 
            width: 100%; 
        }

        h2 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 30px; }

        /* Table Container Styling */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            max-width: 900px; /* Membatasi lebar agar tidak terlalu melar */
        }

        table { width: 100%; border-collapse: collapse; }
        
        th { 
            background: #8f3434; 
            color: #ffffff; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 12px; 
            letter-spacing: 0.05em; 
            padding: 16px 20px; 
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        td { padding: 16px 20px; color: #1e293b; font-size: 14px; border-bottom: 1px solid #f1f5f9; }

        tr:last-child td { border-bottom: none; }

        tr:hover { background: #fcfcfd; }

        /* Action Link */
        .btn-edit { 
            color: #8f3434; 
            font-weight: 600; 
            text-decoration: none;
            font-size: 13px;
        }
        .btn-edit:hover { 
            text-decoration: underline; 
            color: #8d2e2e;
        }

        /* Currency Styling */
        .price {
            font-weight: 600;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../hogwarts-removebg-preview.png" alt="Logo">
            <h2>Admin</h2>
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_index.php">Kelola User</a>
        <a href="tarif_index.php" class="active">Tarif Parkir</a>
        <a href="area_parkir.php">Area Parkir</a>
        <a href="../logout.php" style="margin-top: auto; color: #ffb1b1;">Logout</a>
    </div>

    <div class="content">
        <h2>Pengaturan Tarif Parkir</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Kendaraan</th>
                        <th>Tarif / Jam</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($semua_tarif as $t): ?>
                    <tr>
                        <td style="color: #94a3b8; width: 50px;"><?= $no++; ?></td>
                        <td style="font-weight: 500; text-transform: capitalize;">
                            <?php 
                                // Memberi ikon sederhana berdasarkan jenis kendaraan
                                if(strpos(strtolower($t['jenis_kendaraan']), 'motor') !== false) echo "🛵 ";
                                else if(strpos(strtolower($t['jenis_kendaraan']), 'mobil') !== false) echo "🚗 ";
                                else if(strpos(strtolower($t['jenis_kendaraan']), 'lainnya') !== false) echo "🚚 ";
                                echo $t['jenis_kendaraan']; 
                            ?>
                        </td>
                        <td class="price">Rp <?= number_format($t['tarif_per_jam'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                            <a href="tarif_edit.php?id=<?= $t['id_tarif']; ?>" class="btn-edit">Ubah Tarif</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>