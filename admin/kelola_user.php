<?php
session_start();
// Proteksi Role
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// Data slot untuk sidebar konsisten
$query_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'");
$kendaraan_masuk = mysqli_fetch_assoc($query_masuk)['total'] ?? 0;
$total_kapasitas_all = 1350;

$error_msg = "";

// Proses Simpan
if(isset($_POST['simpan'])){
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $user = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $pass = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']); 
    
    $cek = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$user'");
    
    if(mysqli_num_rows($cek) > 0) {
        $error_msg = "Username '$user' sudah terdaftar!"; 
    } else {
        $q = "INSERT INTO tb_user (nama_lengkap, username, password, role) VALUES ('$nama', '$user', '$pass', '$role')";
        if(mysqli_query($koneksi, $q)) {
            header("location:kelola_user.php");
            exit;
        } else {
            $error_msg = "Gagal simpan: " . mysqli_error($koneksi);
        }
    }
}

// Proses Hapus
if(isset($_GET['hapus'])){
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user='$id'");
    header("location:kelola_user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Admin - Kelola Pengguna</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #475569;
            --danger: #ef4444;
            --success: #10b981;
            --indigo-soft: #e0e7ff; /* Ditambahkan agar sama */
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

        /* SIDEBAR */
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
            text-decoration: none; color: var(--text-sub); font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        /* MAIN CONTENT */
        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        .header-top { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 35px; padding-top: 15px; 
        }

        /* USER NAV & LOGOUT CSS (IDENTIK) */
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

        /* FORM & TABLE */
        .form-card { 
            background: white; padding: 30px; border-radius: 35px; 
            margin-bottom: 30px; border: 1px solid #f0f4f8; 
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); 
        }
        .grid-form { display: grid; grid-template-columns: 1.2fr 1fr 1fr 0.8fr 0.6fr; gap: 15px; align-items: end; }
        
        .input-group { display: flex; flex-direction: column; gap: 10px; }
        .input-group label { font-size: 11px; font-weight: 800; color: var(--text-sub); text-transform: uppercase; padding-left: 5px; }
        .input-group input, .input-group select { 
            padding: 14px 18px; border-radius: 18px; border: 2px solid #f1f5f9; 
            background: #f8fafc; font-size: 13px; outline: none; transition: 0.3s; font-weight: 600; color: var(--text-main);
        }
        .input-group input:focus { border-color: var(--primary-light); background: white; }

        .btn-simpan { 
            background: var(--primary); color: white; border: none; 
            padding: 15px; border-radius: 18px; font-weight: 800; 
            cursor: pointer; transition: 0.3s; 
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); 
        }
        .btn-simpan:hover { transform: translateY(-3px); background: #1d4ed8; }

        .table-container { background: white; border-radius: 35px; border: 1px solid #f0f4f8; overflow: hidden; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 22px; color: var(--text-sub); font-size: 11px; font-weight: 800; text-transform: uppercase; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
        td { padding: 20px 22px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f8fafc; }
        
        .role-badge { padding: 6px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; }
        .role-ADMIN { background: #fee2e2; color: #b91c1c; }
        .role-PETUGAS { background: #dcfce7; color: #166534; }
        .role-OWNER { background: #fef9c3; color: #854d0e; }
        
        .btn-hapus { color: var(--danger); text-decoration: none; font-weight: 800; font-size: 12px; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        .btn-hapus:hover { background: #fff1f2; }
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
                <a href="kelola_user.php" class="active"> Data User</a>
                <a href="tarif_parkir.php"> Data Tarif</a>
                <a href="area_parkir.php"> Data Area</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Manajemen Pengguna</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 14px;">Kelola hak akses dan data petugas</p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Administrator</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?? 'Admin' ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?>
                    </div>
                    
                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="logout.png" alt="Logout" onerror="this.src='https://cdn-icons-png.flaticon.com/512/182/182448.png';">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <?php if($error_msg != ""): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 18px 25px; border-radius: 20px; margin-bottom: 25px; font-size: 13px; font-weight: 800; border: 1px solid #fecaca;">
                    ⚠️ <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" class="grid-form">
                    <div class="input-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" placeholder="Ex: Harry Potter" required>
                    </div>
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="user123" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="input-group">
                        <label>Role Access</label>
                        <select name="role" required>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" name="simpan" class="btn-simpan">Tambah User</button>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Akses</th>
                            <th style="text-align: right;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $q = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user DESC");
                        while($data = mysqli_fetch_assoc($q)){
                        ?>
                        <tr>
                            <td style="color: var(--text-sub); font-weight: 700;">#<?= $no++ ?></td>
                            <td style="font-weight: 700;"><?= $data['nama_lengkap'] ?></td>
                            <td style="color: #64748b; font-weight: 500;">@<?= $data['username'] ?></td>
                            <td><span class="role-badge role-<?= strtoupper($data['role']) ?>"><?= strtoupper($data['role']) ?></span></td>
                            <td style="text-align: right;">
                                <a href="?hapus=<?= $data['id_user'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
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