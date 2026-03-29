<?php
session_start();
// Proteksi role petugas
if($_SESSION['role'] != "petugas") { header("location:../index.php"); exit; }
include '../config/koneksi.php';

$area_terpilih = isset($_GET['area']) ? $_GET['area'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Parline Petugas - Transaksi Masuk</title>
    <link rel="icon" href="../assets/images/parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --grad-1: #d4e9f7; 
            --grad-2: #b2d7f5;
            --text-main: #1e293b;
            --text-sub: #94a3b8;
            --indigo-mature: #3730a3; 
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
            text-decoration: none; color: #475569; 
            font-size: 14px; font-weight: 600;
            margin-bottom: 5px; border-radius: 18px; transition: 0.3s;
        }
        .nav-menu a.active { background: var(--primary); color: white; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3); }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; background: #fcfdfe; padding: 40px 50px; overflow-y: auto; }

        /* HEADER HEADER (Sama dengan Dashboard) */
        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px;
        }

        .user-nav-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-stack {
            text-align: right;
            border-left: 1px solid #f1f5f9;
            padding-left: 15px;
        }

        .user-avatar { 
            width: 42px; height: 42px; 
            background: var(--primary); 
            border-radius: 12px; color: white; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 800; font-size: 16px;
        }

        /* Tombol Keluar di Kanan Atas */
        .btn-logout-direct {
            display: flex; align-items: center; gap: 10px;
            background: var(--indigo-soft); 
            color: var(--indigo-mature);
            text-decoration: none; padding: 10px 18px; border-radius: 15px;
            font-size: 12px; font-weight: 800; transition: 0.3s ease;
            border: 1px solid rgba(55, 48, 163, 0.1);
        }

        .btn-logout-direct:hover { background: var(--indigo-mature); color: white; }
        .btn-logout-direct img { 
            width: 18px; 
            filter: invert(18%) sepia(48%) saturate(3651%) hue-rotate(238deg) brightness(91%) contrast(100%); 
        }
        .btn-logout-direct:hover img { filter: brightness(0) invert(1); }

        /* --- FORM STYLING --- */
        .form-wrapper { max-width: 500px; margin: 0 auto; }
        .form-card {
            background: white; padding: 40px; border-radius: 40px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
        }

        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block; font-size: 11px; font-weight: 800; color: var(--text-sub);
            margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;
        }

        .form-group input, .form-group select {
            width: 100%; padding: 16px 20px; border-radius: 20px;
            border: 2px solid #f1f5f9; background: #f8fafc;
            font-size: 14px; color: var(--text-main); outline: none;
            transition: 0.3s; font-weight: 600;
        }

        .btn-submit {
            background: var(--primary); color: white; border: none;
            width: 100%; padding: 18px; border-radius: 20px;
            font-weight: 800; font-size: 14px; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
        }
        .btn-submit:hover { background: #1e40af; transform: translateY(-3px); }
    </style>
</head>
<body>

    <div class="app-container">
        <div class="sidebar">
            <div class="logo-section">
                <img src="../assets/images/parline.png" alt="Logo">
                <h2>Parline</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="transaksi_masuk.php" class="active">Transaksi Masuk</a>
                <a href="kendaraan_aktif.php">Kendaraan Aktif</a> 
                <a href="transaksi_keluar.php">Transaksi Keluar</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header-top">
                <div>
                    <h1 style="font-size: 28px; font-weight: 800; color: var(--text-main); margin: 0;">Input Transaksi</h1>
                    <p style="color: var(--text-sub); margin: 5px 0 0 0; font-size: 15px;"><?= date('l, d F Y') ?></p>
                </div>

                <div class="user-nav-wrapper">
                    <div class="profile-stack">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">Petugas</div>
                        <div style="font-size: 11px; color: var(--text-sub);"><?= $_SESSION['nama'] ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
                    </div>
                    <a href="../auth/logout.php" class="btn-logout-direct">
                        <img src="../assets/images/logout.png" alt="Exit">
                        <span>KELUAR</span>
                    </a>
                </div>
            </div>

            <div class="form-wrapper">
                <div class="form-card">
                    <form action="proses_masuk.php" method="POST">
                        <div class="form-group">
                            <label>Nomor Plat Kendaraan</label>
                            <input type="text" name="plat_nomor" placeholder="Contoh: D 9999 HP" required autofocus style="text-transform: uppercase;">
                        </div>

                        <div class="form-group">
                            <label>Jenis Kendaraan</label>
                            <select name="id_tarif" id="id_tarif" required onchange="updateJenis()">
                                <option value="">-- Pilih Jenis --</option>
                                <?php
                                $q_tarif = mysqli_query($koneksi, "SELECT * FROM tb_tarif");
                                while($t = mysqli_fetch_assoc($q_tarif)) {
                                    echo "<option value='".$t['id_tarif']."'>".$t['jenis_kendaraan']."</option>";
                                }
                                ?>
                            </select>
                            <input type="hidden" name="jenis_kendaraan" id="jenis_kendaraan_hidden">
                        </div>

                        <div class="form-group">
                            <label>Lokasi Area Parkir</label>
                            <select name="id_area" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <?php
                                $q_area = mysqli_query($koneksi, "SELECT * FROM tb_area");
                                while($a = mysqli_fetch_assoc($q_area)) {
                                    $selected = ($area_terpilih == $a['nama_area']) ? 'selected' : '';
                                    echo "<option value='".$a['id_area']."' $selected>".$a['nama_area']."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" name="simpan" class="btn-submit">SIMPAN & CETAK KARCIS</button>
                        <a href="dashboard.php" style="display:block; text-align:center; margin-top:25px; font-size:13px; color:var(--text-sub); text-decoration:none; font-weight:700;">← Kembali ke Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateJenis() {
        var select = document.getElementById("id_tarif");
        var hiddenInput = document.getElementById("jenis_kendaraan_hidden");
        if(select.selectedIndex > 0) {
            var selectedText = select.options[select.selectedIndex].text;
            hiddenInput.value = selectedText;
        }
    }
    </script>
</body>
</html>