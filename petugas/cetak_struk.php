<?php
include '../config/koneksi.php';

// 1. Validasi ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

// 2. Query disederhanakan (Mengambil kolom 'petugas' langsung dari tb_transaksi)
$query = "SELECT t.*, tr.jenis_kendaraan 
          FROM tb_transaksi t
          LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
          WHERE t.id_transaksi = '$id'";

$data = mysqli_query($koneksi, $query);
$r = mysqli_fetch_assoc($data);

// 3. Cek apakah data ditemukan
if (!$r) {
    die("<script>alert('Data transaksi tidak ditemukan!'); window.location='transaksi_masuk.php';</script>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Karcis - <?= $r['plat_nomor'] ?></title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            background-color: #e2e8f0; /* Warna background layar gelap agar struk menonjol */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            margin: 0;
        }

        .ticket-container {
            width: 300px; /* Dipersempit agar benar-benar menyerupai kertas thermal */
            background: #fff;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 2px;
            border: 1px solid #d1d5db;
        }

        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 15px; }
        
        .plat { 
            font-size: 26px; font-weight: 900; text-align: center; 
            border: 2px solid #000; margin: 15px 0; padding: 10px; 
            text-transform: uppercase; color: #000;
            letter-spacing: 2px;
        }
        
        table { width: 100%; font-size: 13px; border-collapse: collapse; margin: 15px 0; }
        table td { padding: 4px 0; vertical-align: top; color: #000; }
        
        .footer { text-align: center; border-top: 1px dashed #000; padding-top: 15px; font-size: 11px; }
        
        .no-print { margin-top: 25px; display: flex; gap: 10px; }
        .btn { 
            padding: 10px 20px; cursor: pointer; border: none; border-radius: 8px; 
            font-weight: 700; font-size: 14px; transition: 0.3s; text-decoration: none;
            font-family: sans-serif;
        }
        .btn-print { background: #2563eb; color: white; }
        .btn-back { background: #64748b; color: white; }

        /* Pengaturan saat diprint */
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .ticket-container { 
                box-shadow: none; 
                width: 100%; 
                padding: 0; 
                border: none;
            }
            @page { size: auto; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="ticket-container">
        <div class="header">
            <h2 style="margin:0; letter-spacing: 2px;">PARLINE</h2>
            <small>PARKING SYSTEM</small>
        </div>

        <div class="plat"><?= htmlspecialchars($r['plat_nomor']) ?></div>

        <table>
            <tr>
                <td width="40%">Jenis</td>
                <td>: <?= strtoupper($r['jenis_kendaraan'] ?? 'Kendaraan') ?></td>
            </tr>
            <tr>
                <td>Area</td>
                <td>: <?= strtoupper($r['area'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <?= date('d/m/Y H:i', strtotime($r['waktu_masuk'])) ?> WIB</td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>: <?= strtoupper($r['petugas'] ?? 'SYSTEM') ?></td>
            </tr>
        </table>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">SIMPAN KARCIS INI</p>
            <b style="color: #000;">DENDA HILANG: RP 20.000</b>
            <p style="margin-top: 15px;">--- TERIMAKASIH ---</p>
        </div>
    </div>

    <div class="no-print">
        <a href="dashboard.php" class="btn btn-back">KEMBALI</a>
        <button onclick="window.print()" class="btn btn-print">CETAK STRUK</button>
    </div>

</body>
</html>