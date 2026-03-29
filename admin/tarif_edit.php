<?php
session_start();
if($_SESSION['role'] != "admin") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

$id = $_GET['id'];
$data = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE id_tarif='$id'");
$t = mysqli_fetch_array($data);

if(isset($_POST['update'])){
    $tarif = $_POST['tarif'];
    mysqli_query($koneksi, "UPDATE tb_tarif SET tarif_per_jam='$tarif' WHERE id_tarif='$id'");
    echo "<script>alert('Tarif Berhasil Diupdate!'); window.location='tarif_index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tarif - Hogwarts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; display: flex; background: #f8fafc; color: #1e293b; }
        
        /* Sidebar Hogwarts */
        .sidebar { width: 260px; height: 100vh; background: #8f3434; color: white; position: fixed; padding: 20px 0; }
        .sidebar-header { text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-header img { width: 60px; border-radius: 50%; margin-bottom: 10px; }
        .sidebar a { display: block; color: rgba(255,255,255,0.8); padding: 14px 25px; text-decoration: none; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); }
        .sidebar a.active { background: #782626; border-left: 4px solid white; font-weight: 600; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        /* Edit Card Style */
        .content-card { background: white; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; max-width: 500px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .form-group label { font-size: 13px; font-weight: 700; color: #64748b; }
        .form-group input { padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; outline: none; font-size: 14px; }
        .form-group input:focus { border-color: #2563eb; background: white; }

        .btn-update { background: #2563eb; color: white; padding: 12px 20px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-update:hover { background: #1d4ed8; }
        .btn-batal { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
        .btn-batal:hover { color: #1e293b; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../hogwarts2.jpg" alt="Logo">
            <h2>Hogwarts Admin</h2>
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_user.php">Data User</a>
        <a href="tarif_parkir.php" class="active">Data Tarif</a>
        <a href="area_parkir.php">Data Area</a>
        <a href="../auth/logout.php" style="margin-top:auto; color:#ffb1b1;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="margin: 0 0 30px 0; font-size: 28px;">Update Tarif</h1>
        
        <div class="content-card">
            <h3 style="margin-top:0; color:#1e293b;">Edit Tarif: <?= ucfirst($t['jenis_kendaraan']); ?></h3>
            <form method="POST">
                <div class="form-group">
                    <label>Tarif per Jam (Rp)</label>
                    <input type="number" name="tarif" value="<?= $t['tarif_per_jam']; ?>" required>
                </div>
                
                <button type="submit" name="update" class="btn-update">Simpan Perubahan</button>
                <a href="tarif_parkir.php" class="btn-batal">Kembali ke Daftar Tarif</a>
            </form>
        </div>
    </div>
</body>
</html>