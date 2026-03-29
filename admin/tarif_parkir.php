<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// Ambil data kendaraan masuk untuk progress bar
$query_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'");
$kendaraan_masuk = mysqli_fetch_assoc($query_masuk)['total'] ?? 0;
$total_kapasitas_all = 1350;

// 1. PROSES SIMPAN TARIF (LOGIKA ANTI-DUPLIKAT)
if(isset($_POST['tambah'])){
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $harga = (int)$_POST['harga_per_jam']; // Memastikan input adalah angka
    
    if(!empty($jenis) && $harga > 0) {
        // Cek apakah jenis kendaraan sudah ada di database
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE jenis_kendaraan='$jenis'");
        if(mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Kategori $jenis sudah ada! Silakan hapus yang lama jika ingin mengganti harga.'); window.location='tarif_parkir.php';</script>";
        } else {
            mysqli_query($koneksi, "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES ('$jenis', '$harga')");
            header("location:tarif_parkir.php");
        }
    }
    exit;
}

// 2. PROSES HAPUS (LOGIKA ANTI-ERROR FOREIGN KEY)
if(isset($_GET['hapus'])){
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah data ini sedang dipakai di tabel transaksi
    $cek_relasi = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_tarif='$id'");
    
    if(mysqli_num_rows($cek_relasi) > 0) {
        echo "<script>alert('Gagal! Data tarif ini tidak bisa dihapus karena sudah ada data kendaraan di tabel transaksi yang menggunakannya.'); window.location='tarif_parkir.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_tarif WHERE id_tarif='$id'");
        header("location:tarif_parkir.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Admin - Data Tarif</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Warna diganti ke Biru Navy agar lebih profesional/ Ravenclaw vibe */
            --primary: #2563eb; 
            --primary-light: #3b82f6;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #475569; 
            --danger: #ef4444;
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
            text-decoration: none; color: var(--text-sub); font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        .storage-box { margin-top: auto; padding: 25px; background: #f8fafc; border-radius: 30px; }
        .storage-box p { margin: 0 0 12px 0; color: var(--text-sub); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        
        .progress-bg { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
        .progress-fill { height: 100%; background: var(--primary); }

        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 35px;
            padding-top: 15px; 
        }
        
        .form-card { 
            background: white; padding: 30px; border-radius: 35px; 
            margin-bottom: 30px; border: 1px solid #f0f4f8; 
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); 
        }
        .grid-form { display: grid; grid-template-columns: 1.5fr 1fr 0.8fr; gap: 20px; align-items: end; }
        
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
            box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.3); 
        }
        .btn-simpan:hover { transform: translateY(-3px); opacity: 0.9; }

        .table-container { background: white; border-radius: 35px; border: 1px solid #f0f4f8; overflow: hidden; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 22px; color: var(--text-sub); font-size: 11px; font-weight: 800; text-transform: uppercase; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
        td { padding: 20px 22px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f8fafc; }
        
        .price-badge { 
            background: #f1f5f9; color: var(--text-main); padding: 8px 15px; 
            border-radius: 12px; font-weight: 800; font-size: 13px; border: 1px solid #e2e8f0;
        }
        
        .btn-hapus { color: var(--danger); text-decoration: none; font-weight: 800; font-size: 12px; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        .btn-hapus:hover { background: #fff1f2; }

        .user-avatar {
            width: 40px; height: 40px; background: var(--primary); 
            border-radius: 12px; color: white; display: flex; 
            align-items: center; justify-content: center; font-weight: 800;
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
                <a href="kelola_user.php"> Data User</a>
                <a href="tarif_parkir.php" class="active"> Data Tarif</a>
                <a href="area_parkir.php"> Data Area</a>
            </div>
            
            <a href="../logout.php" style="margin-top: 25px; color: var(--text-sub); text-decoration: none; font-size: 14px; padding-left: 20px; font-weight: 600;"> Logout</a>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Data Tarif Parkir</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 14px;">Atur biaya parkir per kategori</p>
                </div>

                <div style="display: flex; align-items: center; gap: 15px; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                    <div style="text-align: right;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Administrator</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?? 'Admin' ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <form method="POST" class="grid-form">
                    <div class="input-group">
                        <label>Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="MOTOR">MOTOR</option>
                            <option value="MOBIL">MOBIL</option>
                            <option value="LAINNYA">LAINNYA</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Harga Per Jam</label>
                        <input type="number" name="harga_per_jam" placeholder="Contoh: 7000" required>
                    </div>
                    <button type="submit" name="tambah" class="btn-simpan">Tambah Tarif</button>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Kategori Kendaraan</th>
                            <th>Tarif / Jam</th>
                            <th style="text-align: right;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_str = "SELECT * FROM tb_tarif WHERE jenis_kendaraan != '' ORDER BY FIELD(jenis_kendaraan, 'MOTOR', 'MOBIL', 'LAINNYA') ASC";
                        $q = mysqli_query($koneksi, $query_str);
                        
                        $no = 1; 
                        while($data = mysqli_fetch_assoc($q)){
                        ?>
                        <tr>
                            <td style="color: var(--text-sub); font-weight: 700;">#<?= $no++ ?></td>
                            <td style="font-weight: 700; color: var(--text-main);"><?= strtoupper($data['jenis_kendaraan']) ?></td>
                            <td>
                                <span class="price-badge">
                                    <?php 
                                        $harga_tampil = $data['tarif_per_jam'] ?? 0;
                                        echo "Rp " . number_format($harga_tampil, 0, ',', '.');
                                    ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="?hapus=<?= $data['id_tarif'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus tarif ini?')">Hapus</a>
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