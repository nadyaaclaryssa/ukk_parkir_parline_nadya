<?php
session_start();
// Proteksi: Jika bukan admin, tendang balik ke login
if($_SESSION['role'] != "admin") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

// Ambil data dari database
$query = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user DESC");

// Masukkan ke dalam Array (Kriteria Penilaian UKK)
$users = [];
while($row = mysqli_fetch_assoc($query)){
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Hogwarts Admin</title>
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

        /* Sidebar Styling - Tema Hogwarts */
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

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0; }

        /* Button Styling */
        .btn { 
            padding: 10px 20px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 600; 
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }

        .btn-tambah { background: #8f3434; color: white; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4); }
        .btn-tambah:hover { background: rgb(92, 24, 24); transform: translateY(-2px); }

        /* Table Styling Modern */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
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

        /* Badge Status */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .badge-non { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .btn-edit { color: #6366f1; margin-right: 15px; }
        .btn-edit:hover { text-decoration: underline; }
        .btn-hapus { color: #ef4444; }
        .btn-hapus:hover { text-decoration: underline; }

    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../hogwarts-removebg-preview.png" alt="Logo">
            <h2>Admin</h2>
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_index.php" class="active">Kelola User</a>
        <a href="tarif_index.php">Tarif Parkir</a>
        <a href="area_index.php">Area Parkir</a>
        <div style="margin-top: auto;">
            <a href="../auth/logout.php" style="color: #ffb1b1;">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="header-flex">
            <h2>Daftar Pengguna</h2>
            <a href="user_tambah.php" class="btn btn-tambah">+ Tambah User</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach($users as $u): 
                    ?>
                    <tr>
                        <td><span style="color: #94a3b8; font-weight: 600;"><?= $no++; ?></span></td>
                        <td style="font-weight: 500;"><?= $u['nama_lengkap']; ?></td>
                        <td style="color: #64748b;"><?= $u['username']; ?></td>
                        <td>
                            <span style="font-size: 13px;"><?= ucfirst($u['role']); ?></span>
                        </td>
                        <td>
                            <?php if($u['status_aktif'] == 1): ?>
                                <span class="badge badge-aktif">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-non">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="user_edit.php?id=<?= $u['id_user']; ?>" class="btn-edit">Edit</a>
                            <a href="user_hapus.php?id=<?= $u['id_user']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus si <?= $u['nama_lengkap']; ?>?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>