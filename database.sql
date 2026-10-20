-- ====================================================================
-- SISTEM PENILAIAN MUNAQOSYAH & CETAK SYAHADAH RTQ UTSMANIYYAH
-- LAJNAH MUROQOBAH YANBU'A KABUPATEN LAMPUNG BARAT
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `utsmaniyyah_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `utsmaniyyah_db`;

-- 1. TABEL LEMBAGA PUSAT
CREATE TABLE IF NOT EXISTS `lembaga` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(255) NOT NULL,
    `metode` VARCHAR(255) NOT NULL,
    `wilayah` VARCHAR(255) NOT NULL,
    `tingkat` VARCHAR(100) NOT NULL,
    `pimpinan_lajnah` VARCHAR(150) NOT NULL,
    `ketua_panitia` VARCHAR(150) NOT NULL,
    `kkm` DECIMAL(5,2) DEFAULT 60.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL UNIT RTQ
CREATE TABLE IF NOT EXISTS `rtq` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(20) NOT NULL UNIQUE,
    `nama` VARCHAR(150) NOT NULL,
    `alamat` TEXT NOT NULL,
    `kepala_rtq` VARCHAR(150) NOT NULL,
    `ketua_penguji` VARCHAR(150) NOT NULL,
    `wa` VARCHAR(30) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL PERIODE / TAHUN PELAJARAN
CREATE TABLE IF NOT EXISTS `periode` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tahun_pelajaran` VARCHAR(50) NOT NULL UNIQUE,
    `tanggal_h` VARCHAR(100) NOT NULL,
    `tanggal_m` VARCHAR(100) NOT NULL,
    `tanggal_syahadah` VARCHAR(100) NOT NULL,
    `titimangsa` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL PENGURUS / USERS
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `role` ENUM('SUPER_ADMIN', 'PENGURUS_RTQ') NOT NULL,
    `rtq_id` INT NULL,
    FOREIGN KEY (`rtq_id`) REFERENCES `rtq`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL SANTRI
CREATE TABLE IF NOT EXISTS `santri` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rtq_id` INT NOT NULL,
    `periode_id` INT NOT NULL,
    `no_urut` INT DEFAULT 1,
    `no_induk` VARCHAR(50) DEFAULT '',
    `no_ujian` VARCHAR(50) DEFAULT '',
    `no_syahadah` VARCHAR(100) DEFAULT '',
    `nama` VARCHAR(150) NOT NULL,
    `gender` ENUM('L', 'P') NOT NULL,
    `ttl` VARCHAR(150) DEFAULT '',
    `ayah` VARCHAR(150) DEFAULT '',
    `ibu` VARCHAR(150) DEFAULT '',
    `alamat` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`rtq_id`) REFERENCES `rtq`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`periode_id`) REFERENCES `periode`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABEL NILAI 9 MATA UJIAN
CREATE TABLE IF NOT EXISTS `nilai` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `santri_id` INT NOT NULL UNIQUE,
    `ghorib` DECIMAL(5,2) DEFAULT 0,
    `tajwid` DECIMAL(5,2) DEFAULT 0,
    `tartil` DECIMAL(5,2) DEFAULT 0,
    `surat` DECIMAL(5,2) DEFAULT 0,
    `doa` DECIMAL(5,2) DEFAULT 0,
    `tauhid` DECIMAL(5,2) DEFAULT 0,
    `wudlu` DECIMAL(5,2) DEFAULT 0,
    `sholat` DECIMAL(5,2) DEFAULT 0,
    `kitabah` DECIMAL(5,2) DEFAULT 0,
    `total` DECIMAL(6,2) DEFAULT 0,
    `rata_rata` DECIMAL(5,2) DEFAULT 0,
    `is_lulus` TINYINT(1) DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA AWAL
INSERT INTO `lembaga` (`nama`, `metode`, `wilayah`, `tingkat`, `pimpinan_lajnah`, `ketua_panitia`, `kkm`) VALUES
('LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR\'AN', 'THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR\'AN YANBU\'A', 'KABUPATEN LAMPUNG BARAT', 'RTQ TINGKAT DASAR', 'SYA\'BUL MUBAROK', 'A. Muslih Abdurrahman', 60.00);

INSERT INTO `rtq` (`kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES
('RTQ-U1', 'RTQ Utsmaniyyah I', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. Ahmad Syarif', 'IKHWANUL KIROM', '082184427288'),
('RTQ-U2', 'RTQ Utsmaniyyah II', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. M. Ridwan', 'IKHWANUL KIROM', '082184427288'),
('RTQ-U3', 'RTQ Utsmaniyyah III', 'Jl. Tirtomoyo, No.083, Margorahayu, Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'ABU HANIFAN', 'IKHWANUL KIROM', '082184427288');

INSERT INTO `periode` (`tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`, `is_active`) VALUES
('2025 M / 1447 H', '14 Rabi\'ul Awwal 1447 H', '07 September 2025 M', '21 September 2025', 'Tambak Jaya, 07 September 2025', 1),
('2026 M / 1448 H', 'Rabi\'ul Awwal 1448 H', 'September 2026 M', 'September 2026', 'Tambak Jaya, September 2026', 0);

INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES
('admin', 'admin123', 'Super Admin Lajnah Pusat', 'SUPER_ADMIN', NULL),
('rtq1', 'rtq123', 'Pengurus RTQ Utsmaniyyah I', 'PENGURUS_RTQ', 1),
('rtq2', 'rtq123', 'Pengurus RTQ Utsmaniyyah II', 'PENGURUS_RTQ', 2),
('rtq3', 'rtq123', 'Pengurus RTQ Utsmaniyyah III', 'PENGURUS_RTQ', 3);
