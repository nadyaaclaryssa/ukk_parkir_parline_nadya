<?php
session_start();
// Proteksi Role
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// --- LOGIKA PROSES: Aktif/Nonaktifkan Area ---
if(isset($_GET['toggle_id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['toggle_id']);
    $current_status = mysqli_real_escape_string($koneksi, $_GET['current']);
    $new_status = ($current_status == 1) ? 0 : 1;
    
    mysqli_query($koneksi, "UPDATE tb_area SET status = '$new_status' WHERE id_area = '$id'");
    header("location:area_parkir.php");
    exit;
}

// Ambil data Area asli dari Database
$query_area = mysqli_query($koneksi, "SELECT * FROM tb_area");

// Data untuk sidebar storage
$query_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'");
$kendaraan_masuk = mysqli_fetch_assoc($query_masuk)['total'] ?? 0;

$query_total_cap = mysqli_query($koneksi, "SELECT SUM(kapasitas) as total_kap FROM tb_area");
$res_cap = mysqli_fetch_assoc($query_total_cap);
$total_kapasitas_all = $res_cap['total_kap'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Admin - Data Area</title>
    <link rel="icon" href="../parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #2563eb;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #475569; 
            --success: #10b981;
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

        /* --- SIDEBAR --- */
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
        .storage-box { margin-top: auto; padding: 25px; background: #f8fafc; border-radius: 30px; }
        .progress-bg { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
        .progress-fill { height: 100%; background: var(--primary); }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .user-avatar { width: 40px; height: 40px; background: var(--primary); border-radius: 12px; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .section-title { font-size: 12px; font-weight: 800; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 25px; }
        .area-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }

        /* --- CARD & SWITCH (BAGIAN YANG DIUBAH) --- */
        .area-card {
            background: white; padding: 30px; border-radius: 35px;
            border: 1px solid #f0f4f8; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.03);
            transition: 0.3s ease; position: relative;
        }
        .area-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        
        /* Neumorphic Switch ON/OFF */
        .switch { position: relative; width: 65px; height: 30px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #333; transition: .4s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content: ""; height: 22px; width: 22px;
            left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;
        }
        input:checked + .slider { background-color: #6684c5; } /* Warna Gold saat ON */
        input:checked + .slider:before { transform: translateX(35px); }
        .slider:after {
            content: "OFF"; position: absolute; right: 8px; top: 50%;
            transform: translateY(-50%); font-size: 9px; font-weight: 800; color: white;
        }
        input:checked + .slider:after { content: "ON"; left: 10px; right: auto; color: #333; }

        .area-off { opacity: 0.7; filter: grayscale(0.8); }
        .area-name { font-size: 20px; font-weight: 800; color: var(--text-main); margin: 0; }
        .area-loc { color: var(--text-sub); font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .stat-row { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .stat-label { font-size: 13px; color: var(--text-sub); font-weight: 600; }
        .stat-value { font-size: 14px; font-weight: 800; color: var(--text-main); }
        .progress-container { height: 10px; background: #f1f5f9; border-radius: 20px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: var(--primary); transition: 0.5s; }
        .perc-label { text-align: right; font-size: 11px; color: var(--text-sub); font-weight: 700; }
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
                <a href="tarif_parkir.php"> Data Tarif</a>
                <a href="area_parkir.php" class="active"> Data Area</a>
            </div>
        
            <a href="../logout.php" style="margin-top: 25px; color: var(--text-sub); text-decoration: none; font-size: 14px; font-weight: 600;"> Logout</a>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Monitoring Area</h1>
                    <p style="color: var(--text-sub); font-size: 14px; margin: 5px 0 0 0;">Kapasitas parkir secara real-time</p>
                </div>
                <div style="display: flex; align-items: center; gap: 15px; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                    <div style="text-align: right;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Administrator</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?? 'Admin' ?></div>
                    </div>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
                </div>
            </div>

            <h2 class="section-title">Live Capacity</h2>
            <div class="area-grid">
                <?php while($a = mysqli_fetch_assoc($query_area)): 
                    $is_active = ($a['status'] == 1);
                    $persen = ($a['kapasitas'] > 0) ? ($a['terisi'] / $a['kapasitas']) * 100 : 0;
                    $sisa = $a['kapasitas'] - $a['terisi'];
                ?>
                <div class="area-card <?= !$is_active ? 'area-off' : '' ?>">
                    <div class="area-header">
                        <div>
                            <h3 class="area-name"><?= $a['nama_area'] ?></h3>
                            <div class="area-loc">📍 ID: <?= $a['id_area'] ?></div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" class="btn-konfirmasi" 
                                   data-href="?toggle_id=<?= $a['id_area'] ?>&current=<?= $a['status'] ?>" 
                                   <?= $is_active ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="stat-row">
                        <span class="stat-label">Total Kapasitas</span>
                        <span class="stat-value"><?= $a['kapasitas'] ?> Slot</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Tersedia</span>
                        <span class="stat-value" style="color: var(--success);"><?= $sisa ?> Slot</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $is_active ? $persen : 0 ?>%;"></div>
                    </div>
                    <div class="perc-label">Terisi: <?= round($persen) ?>%</div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.btn-konfirmasi').forEach(sw => {
        sw.addEventListener('click', function(e) {
            e.preventDefault(); // Stop sebentar buat konfirmasi
            const url = this.getAttribute('data-href');
            const targetStatus = !this.checked; // Cek logic target

            Swal.fire({
                title: 'Konfirmasi Perubahan',
                text: targetStatus ? "Nyalakan area ini?" : "Matikan area parkir ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
    </script>
</body>
</html>