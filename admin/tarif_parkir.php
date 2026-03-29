<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// Variabel Notifikasi
$status_msg = "";

// 1. PROSES SIMPAN TARIF
if(isset($_POST['tambah'])){
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $harga = (int)$_POST['harga_per_jam'];
    
    if(!empty($jenis) && $harga > 0) {
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE jenis_kendaraan='$jenis'");
        if(mysqli_num_rows($cek) > 0) {
            $status_msg = "duplikat";
        } else {
            mysqli_query($koneksi, "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES ('$jenis', '$harga')");
            $status_msg = "sukses_tambah";
        }
    }
}

// 2. PROSES HAPUS
if(isset($_GET['hapus'])){
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $cek_relasi = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_tarif='$id'");
    
    if(mysqli_num_rows($cek_relasi) > 0) {
        $status_msg = "gagal_hapus";
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_tarif WHERE id_tarif='$id'");
        $status_msg = "sukses_hapus";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Admin - Data Tarif</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #2563eb; 
            --primary-light: #60a5fa;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #475569; 
            --danger: #ef4444;
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

        /* SIDEBAR (Bersih Tanpa Box Kapasitas) */
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
            display: block; padding: 14px 20px;
            text-decoration: none; color: var(--text-sub); 
            font-size: 14px; font-weight: 600; margin-bottom: 5px; 
            border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }
        .nav-menu a:hover:not(.active) { background: #f1f5f9; color: var(--text-main); }

        /* MAIN CONTENT */
        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; padding-top: 10px; }

        /* USER NAV & LOGOUT */
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
        .form-card { background: white; padding: 30px; border-radius: 35px; margin-bottom: 30px; border: 1px solid #f0f4f8; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); }
        .grid-form { display: grid; grid-template-columns: 1.5fr 1fr 0.8fr; gap: 20px; align-items: end; }
        .input-group { display: flex; flex-direction: column; gap: 10px; }
        .input-group label { font-size: 11px; font-weight: 800; color: var(--text-sub); text-transform: uppercase; }
        .input-group input, .input-group select { padding: 14px 18px; border-radius: 18px; border: 2px solid #f1f5f9; background: #f8fafc; font-size: 13px; outline: none; font-weight: 600; }
        .btn-simpan { background: var(--primary); color: white; border: none; padding: 15px; border-radius: 18px; font-weight: 800; cursor: pointer; transition: 0.3s; }

        .table-container { background: white; border-radius: 35px; border: 1px solid #f0f4f8; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 22px; color: var(--text-sub); font-size: 11px; font-weight: 800; text-transform: uppercase; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
        td { padding: 20px 22px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f8fafc; }
        .price-badge { background: #eff6ff; color: var(--primary); padding: 8px 15px; border-radius: 12px; font-weight: 800; }
        .btn-edit-ui { color: var(--primary); text-decoration: none; font-weight: 800; font-size: 12px; margin-right: 15px; }
        .btn-hapus-ui { color: var(--danger); text-decoration: none; font-weight: 800; font-size: 12px; cursor: pointer; }
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
                <a href="kelola_user.php">Data User</a>
                <a href="tarif_parkir.php" class="active">Data Tarif</a>
                <a href="area_parkir.php">Data Area</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Data Tarif Parkir</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 14px;">Atur biaya parkir per kategori</p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Administrator</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?? 'Admin' ?></div>
                    </div>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="logout.png" alt="Logout" onerror="this.src='https://cdn-icons-png.flaticon.com/512/182/182448.png';">
                        <span>KELUAR</span>
                    </a>
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
                        <label>Harga Per Jam (Rp)</label>
                        <input type="number" name="harga_per_jam" placeholder="7000" required>
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
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($koneksi, "SELECT * FROM tb_tarif ORDER BY FIELD(jenis_kendaraan, 'MOTOR', 'MOBIL', 'LAINNYA') ASC");
                        $no = 1; 
                        while($data = mysqli_fetch_assoc($q)){
                        ?>
                        <tr>
                            <td style="color: var(--text-sub); font-weight: 700;">#<?= $no++ ?></td>
                            <td style="font-weight: 700; color: var(--text-main);">
                                <?= strtoupper($data['jenis_kendaraan']) ?>
                            </td>
                            <td><span class="price-badge">Rp <?= number_format($data['tarif_per_jam'], 0, ',', '.'); ?></span></td>
                            <td style="text-align: right;">
                                <a href="tarif_edit.php?id=<?= $data['id_tarif']; ?>" class="btn-edit-ui">Edit</a>
                                <a onclick="konfirmasiHapus(<?= $data['id_tarif'] ?>)" class="btn-hapus-ui">Hapus</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Tarif?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?hapus=' + id;
                }
            })
        }

        <?php if($status_msg == "sukses_tambah"): ?>
            Swal.fire('Berhasil!', 'Tarif baru telah ditambahkan.', 'success');
        <?php elseif($status_msg == "duplikat"): ?>
            Swal.fire('Gagal!', 'Kategori kendaraan sudah ada!', 'error');
        <?php elseif($status_msg == "sukses_hapus"): ?>
            Swal.fire('Dihapus!', 'Data tarif berhasil dihapus.', 'success');
        <?php elseif($status_msg == "gagal_hapus"): ?>
            Swal.fire('Gagal!', 'Tarif sedang digunakan di transaksi!', 'error');
        <?php endif; ?>
    </script>
</body>
</html>