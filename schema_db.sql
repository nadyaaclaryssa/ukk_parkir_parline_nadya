-- phpMyAdmin SQL Dump

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: ukk_parkir

-- =========================
-- TABEL tb_area
-- =========================
CREATE TABLE `tb_area` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) DEFAULT NULL,
  `kapasitas` int(5) DEFAULT NULL,
  `terisi` int(5) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_area` VALUES
(1, 'Lantai 1', 50, 2, 1),
(2, 'Blok A - Depan', 50, 7, 1),
(3, 'Blok B - Samping', 60, 2, 0);

-- =========================
-- TABEL tb_kendaraan
-- =========================
CREATE TABLE `tb_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) DEFAULT NULL,
  `jenis_kendaraan` varchar(20) DEFAULT NULL,
  `pemilik` varchar(100) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_kendaraan` VALUES
(1, 'B 4411 svq', 'motor', NULL, 2),
(2, 'B 4411 svq', 'motor', NULL, 2),
(3, 'B 4411 svq', 'mobil', NULL, 2),
(9, 'D 1234 PKL', 'motor', NULL, 2),
(10, 'D 6379 ACF', 'motor', NULL, 2);

-- =========================
-- TABEL tb_log_aktivitas
-- =========================
CREATE TABLE `tb_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `waktu` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- TABEL tb_tarif
-- =========================
CREATE TABLE `tb_tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` varchar(50) NOT NULL,
  `tarif_per_jam` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_tarif` VALUES
(1, 'MOTOR', 2000),
(2, 'MOBIL', 5000),
(3, 'LAINNYA', 7000);

-- =========================
-- TABEL tb_transaksi
-- =========================
CREATE TABLE `tb_transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_tarif` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `status` enum('masuk','keluar') NOT NULL,
  `biaya_total` int(11) DEFAULT 0,
  `petugas` varchar(50) DEFAULT NULL,
  `jenis_kendaraan` varchar(20) DEFAULT NULL,
  `waktu_masuk` datetime DEFAULT current_timestamp(),
  `waktu_keluar` datetime DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `area` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- TABEL tb_user
-- =========================
CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` enum('admin','petugas','owner') DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 🔐 password = 123 (MD5)
INSERT INTO `tb_user` VALUES
(1, 'bos parline', 'admin', '202cb962ac59075b964b07152d234b70', 'admin', 1),
(2, 'Petugas 1', 'petugas', '202cb962ac59075b964b07152d234b70', 'petugas', 1),
(3, 'bu ceo', 'owner', '202cb962ac59075b964b07152d234b70', 'owner', 1);

-- =========================
-- INDEX & RELATION
-- =========================
ALTER TABLE `tb_area`
  ADD PRIMARY KEY (`id_area`);

ALTER TABLE `tb_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `id_user` (`id_user`);

ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_user` (`id_user`);

ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`);

ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_tarif` (`id_tarif`),
  ADD KEY `fk_transaksi_user` (`id_user`);

ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

-- AUTO INCREMENT
ALTER TABLE `tb_area` MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_kendaraan` MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_log_aktivitas` MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_tarif` MODIFY `id_tarif` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_transaksi` MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_user` MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

-- FOREIGN KEY
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `tb_kendaraan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`),
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

COMMIT;