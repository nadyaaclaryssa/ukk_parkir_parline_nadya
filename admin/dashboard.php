<?php
session_start();
// Proteksi Role
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") { 
    header("location:../index.php"); 
    exit; 
}
include '../config/koneksi.php';

// --- LOGIKA PENCARIAN ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
}

// Statistik dengan fallback value 0 agar tidak error saat data kosong
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi");
$total_pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

$q_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status='masuk'");
$kendaraan_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

$q_petugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_user WHERE role='petugas'");
$total_petugas = mysqli_fetch_assoc($q_petugas)['total'] ?? 0;

$sisa_slot = 1350 - $kendaraan_masuk; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Admin - Dashboard</title>
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

        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 35px;
            padding-top: 15px; 
        }

        .search-form { position: relative; width: 350px; }
        .search-input {
            background: #f1f5f9; padding: 12px 25px 12px 20px;
            border-radius: 20px; width: 100%;
            border: 2px solid transparent; color: var(--text-main);
            font-size: 14px; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary-light); background: white; }

        .user-avatar {
            width: 40px; height: 40px; background: var(--primary); 
            border-radius: 12px; color: white; display: flex; 
            align-items: center; justify-content: center; font-weight: 800;
        }

        .section-title { font-size: 14px; font-weight: 800; margin-bottom: 25px; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 50px; }
        .stat-card { background: white; padding: 30px; border-radius: 35px; border: 1px solid #f0f4f8; transition: 0.4s; }
        .stat-card.primary-card { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; box-shadow: 0 20px 30px -10px rgba(37, 99, 235, 0.2); border: none; }
        .stat-card h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 15px 0; opacity: 0.7; }
        .stat-card .val { font-size: 28px; font-weight: 800; }
        
        .table-card { background: white; padding: 30px; border-radius: 40px; border: 1px solid #f0f4f8; }
        table { width: 100%; border-collapse: collapse; }
        
        th { text-align: left; padding: 15px; color: var(--text-sub); font-size: 11px; font-weight: 800; text-transform: uppercase; border-bottom: 1px solid #f8fafc; }
        td { padding: 22px 15px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #fcfdfe; }

        .badge { padding: 7px 14px; border-radius: 12px; font-size: 10px; font-weight: 800; }
        .badge-motor { background: #e0f2fe; color: #0369a1; }
        .badge-mobil { background: #dcfce7; color: #166534; }

        .plat-code { font-family: 'Courier New', monospace; font-weight: 800; background: #f8fafc; padding: 5px 10px; border-radius: 8px; color: var(--text-main); border: 1px solid #f1f5f9; }
        .storage-box { margin-top: auto; padding: 25px; background: #f8fafc; border-radius: 30px; }
        .btn-delete { color: #cbd5e1; text-decoration: none; font-weight: bold; font-size: 18px; transition: 0.2s; cursor: pointer; }
        .btn-delete:hover { color: #ef4444; }
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
                <a href="kelola_user.php"> Data User</a>
                <a href="tarif_parkir.php"> Data Tarif</a>
                <a href="area_parkir.php"> Data Area</a>
            </div>
            
            <a href="../logout.php" style="margin-top: 25px; color: var(--text-sub); text-decoration: none; font-size: 14px; padding-left: 20px; font-weight: 600;"> Logout</a>
        </div>

        <div class="main-content">
            <div class="header-top">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Cari Plat atau Petugas..." value="<?= htmlspecialchars($search) ?>">
                </form>

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

            <h2 class="section-title">Quick Access</h2>
            
            <div class="stats-grid">
                <div class="stat-card primary-card">
                    <h3>Total Pendapatan</h3>
                    <div class="val">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
                    <p style="font-size: 11px; margin-top: 15px; opacity: 0.8;">Bulan ini: Real-time Data</p>
                </div>
                <div class="stat-card">
                    <h3>Kendaraan Masuk</h3>
                    <div class="val"><?= $kendaraan_masuk ?></div>
                    <p style="font-size: 11px; margin-top: 15px; color: var(--text-sub);">Status: Aktif</p>
                </div>
                <div class="stat-card">
                    <h3>Sisa Slot</h3>
                    <div class="val"><?= $sisa_slot ?></div>
                    <p style="font-size: 11px; margin-top: 15px; color: var(--text-sub);">Kapasitas: 1350</p>
                </div>
                <div class="stat-card">
                    <h3>Total Petugas</h3>
                    <div class="val"><?= $total_petugas ?></div>
                    <p style="font-size: 11px; margin-top: 15px; color: var(--text-sub);">Role: Petugas</p>
                </div>
            </div>

            <h2 class="section-title"><?= ($search != "") ? "Search Results for '$search'" : "Recent Activity Logs" ?></h2>
            
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Kendaraan</th>
                            <th>Plat Nomor</th>
                            <th>Waktu Masuk</th>
                            <th>Estimasi Biaya</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query yang lebih stabil
                        $sql = "SELECT * FROM tb_transaksi ";
                        if ($search != "") { 
                            $sql .= "WHERE plat_nomor LIKE '%$search%' OR petugas LIKE '%$search%' "; 
                        }
                        $sql .= "ORDER BY id_transaksi DESC LIMIT 10";
                        $q_log = mysqli_query($koneksi, $sql);
                        
                        if ($q_log && mysqli_num_rows($q_log) > 0) {
                            while($row = mysqli_fetch_assoc($q_log)) {
                                // Fallback jika data null
                                $petugas = $row['petugas'] ?? 'Sistem';
                                $jenis = strtoupper($row['jenis_kendaraan'] ?? 'MOTOR');
                                $plat = $row['plat_nomor'] ?? '-';
                                $waktu = ($row['waktu_masuk']) ? date('d M, H:i', strtotime($row['waktu_masuk'])) : '-';
                                $biaya = $row['biaya_total'] ?? 0;
                        ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--text-main);"><?= $petugas ?></td>
                            <td><span class="badge <?= ($jenis == 'MOTOR') ? 'badge-motor' : 'badge-mobil' ?>"><?= $jenis ?></span></td>
                            <td><span class="plat-code"><?= $plat ?></span></td>
                            <td style="color: var(--text-main);"><?= $waktu ?></td>
                            <td style="font-weight: 800; color: var(--text-main);">Rp <?= number_format($biaya, 0, ',', '.') ?></td>
                            <td style="text-align: center;">
                                <a href="javascript:void(0)" class="btn-delete" onclick="confirmDelete('<?= $row['id_transaksi'] ?>')">•••</a>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr><td colspan='6' style='text-align:center; padding: 40px; color: var(--text-sub);'>Belum ada riwayat transaksi.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data transaksi ini akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "proses_hapus.php?id=" + id;
            }
        })
    }
    </script>
</body>
</html>