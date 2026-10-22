-- ====================================================================
-- SISTEM PENILAIAN MUNAQOSYAH & CETAK SYAHADAH RTQ UTSMANIYYAH
-- LAJNAH MUROQOBAH YANBU'A KABUPATEN LAMPUNG BARAT
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `utsmaniyyah_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `utsmaniyyah_db`;

-- 1. TABEL LEMBAGA PUSAT
DROP TABLE IF EXISTS `nilai`;
DROP TABLE IF EXISTS `santri`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `periode`;
DROP TABLE IF EXISTS `rtq`;
DROP TABLE IF EXISTS `lembaga`;

CREATE TABLE `lembaga` (
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
CREATE TABLE `rtq` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(20) NOT NULL UNIQUE,
    `nama` VARCHAR(150) NOT NULL,
    `alamat` TEXT NOT NULL,
    `kepala_rtq` VARCHAR(150) NOT NULL,
    `ketua_penguji` VARCHAR(150) NOT NULL,
    `wa` VARCHAR(30) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL PERIODE / TAHUN PELAJARAN
CREATE TABLE `periode` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tahun_pelajaran` VARCHAR(50) NOT NULL UNIQUE,
    `tanggal_h` VARCHAR(100) NOT NULL,
    `tanggal_m` VARCHAR(100) NOT NULL,
    `tanggal_syahadah` VARCHAR(100) NOT NULL,
    `titimangsa` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL PENGURUS / USERS (Terisolasi per RTQ)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'PENGURUS_RTQ',
    `rtq_id` INT NULL,
    FOREIGN KEY (`rtq_id`) REFERENCES `rtq`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL SANTRI
CREATE TABLE `santri` (
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

-- 6. TABEL NILAI 9 MATA UJIAN (Skala 0 - 100)
CREATE TABLE `nilai` (
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

-- ====================================================================
-- SEED DATA AWAL LENGKAP
-- ====================================================================

-- 1. Lembaga Pusat
INSERT INTO `lembaga` (`id`, `nama`, `metode`, `wilayah`, `tingkat`, `pimpinan_lajnah`, `ketua_panitia`, `kkm`) VALUES
(1, 'LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR\'AN', 'THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR\'AN YANBU\'A', 'KABUPATEN LAMPUNG BARAT', 'RTQ TINGKAT DASAR', 'SYA\'BUL MUBAROK', 'A. Muslih Abdurrahman', 60.00);

-- 2. Daftar 3 Unit RTQ
INSERT INTO `rtq` (`id`, `kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES
(1, 'RTQ-U1', 'RTQ Utsmaniyyah I', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. Ahmad Syarif', 'IKHWANUL KIROM', '082184427288'),
(2, 'RTQ-U2', 'RTQ Utsmaniyyah II', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. M. Ridwan', 'IKHWANUL KIROM', '082184427288'),
(3, 'RTQ-U3', 'RTQ Utsmaniyyah III', 'Jl. Tirtomoyo, No.083, Margorahayu, Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'ABU HANIFAN', 'IKHWANUL KIROM', '082184427288');

-- 3. Periode Ujian
INSERT INTO `periode` (`id`, `tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`, `is_active`) VALUES
(1, '2026 M / 1448 H', '21 Rabi\'ul Akhir 1448 H', '04 Oktober 2026 M', '25 Oktober 2026', 'Tambak Jaya, 25 Oktober 2026', 1);

-- 4. Pengurus & Akun Terisolasi
INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `rtq_id`) VALUES
(1, 'Maul', 'Basenglah123.', 'Maul (Developer)', 'DEVELOPER', NULL),
(2, 'Utsmaniyyah1', 'Utsmaniyyah1', 'Admin RTQ Utsmaniyyah I', 'PENGURUS_RTQ', 1),
(3, 'Utsmaniyyah2', 'Utsmaniyyah2', 'Admin RTQ Utsmaniyyah II', 'PENGURUS_RTQ', 2),
(4, 'Utsmaniyyah3', 'Utsmaniyyah3', 'Admin RTQ Utsmaniyyah III', 'PENGURUS_RTQ', 3);

-- 5. Data 11 Santri RTQ Utsmaniyyah I (RTQ 1)
INSERT INTO `santri` (`id`, `rtq_id`, `periode_id`, `no_urut`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`, `no_induk`, `no_ujian`, `no_syahadah`) VALUES
(1, 1, 1, 1, 'MUHAMMAD ISHAQ AL FARIZKY', 'L', 'Sumber Sari, 11 Juni 2013', 'Sugeng Nur Cahyo', 'Sumarni', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0001', '0001', 'LMY-LB/RTQ-U1/001/09/10/2026'),
(2, 1, 1, 2, 'Ibnul Hasan Mubarak', 'L', 'Liwa, 07 Desember 2015', 'Sya\'bul Mubarok', 'Siti Mukaromah', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0002', '0002', 'LMY-LB/RTQ-U1/002/09/10/2026'),
(3, 1, 1, 3, 'Tenda Afita Mubarokah', 'P', 'Kebas Kanan, 06 Juni 2013', 'Romli', 'Khofifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0003', '0003', 'LMY-LB/RTQ-U1/003/09/10/2026'),
(4, 1, 1, 4, 'Sheena Hayumi Arrahma', 'P', 'Lampung Barat, 09 Oktober 2015', 'Muhammad Hadi', 'Suheni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0004', '0004', 'LMY-LB/RTQ-U1/004/09/10/2026'),
(5, 1, 1, 5, 'Nizmatul Ilma Khoerunnisa', 'P', 'Tambak Jaya, 07 Juni 2015', 'Andriyanto', 'Siti Robiah', 'Pemangku Margo Mulyo, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0005', '0005', 'LMY-LB/RTQ-U1/005/09/10/2026'),
(6, 1, 1, 6, 'Rizka Dewi Setyawati', 'P', 'Lampung Barat, 03 Agustus 2012', 'Taufik', 'Apriani', 'Pemangku Margo Rukun, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0006', '0006', 'LMY-LB/RTQ-U1/006/09/10/2026'),
(7, 1, 1, 7, 'Lailatul Rohmah', 'P', 'Kebas Kanan, 13 April 2010', 'Sagiman', 'Siti Kholimah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0007', '0007', 'LMY-LB/RTQ-U1/007/09/10/2026'),
(8, 1, 1, 8, 'Zulva Assifa Zahira', 'P', 'Sekincau, 19 April 2011', 'Wahyu Purwanto', 'Siti Khaifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0008', '0008', 'LMY-LB/RTQ-U1/008/09/10/2026'),
(9, 1, 1, 9, 'Khanifah Arum Manissa', 'P', 'Penumangan Baru, 07 Agustus 2013', 'Sugeng', 'Suheni', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0009', '0009', 'LMY-LB/RTQ-U1/009/09/10/2026'),
(10, 1, 1, 10, 'Meliyana Okta Sari', 'P', 'Metro, 13 Oktober 2013', 'Suprapto', 'Ita Novianti', 'Desa Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0010', '0010', 'LMY-LB/RTQ-U1/010/09/10/2026'),
(11, 1, 1, 11, 'Gera Adha Fradeta', 'L', 'Lampung Barat, 07 Oktober 2015', 'Sukanto', 'Dewi Tiarni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0011', '0011', 'LMY-LB/RTQ-U1/011/09/10/2026');

-- 6. Nilai 9 Mata Ujian Santri RTQ 1 (Skala Puluhan 0-100)
INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`) VALUES
(1, 80.00, 70.00, 65.00, 60.00, 100.00, 80.00, 80.00, 75.00, 70.00, 680.00, 75.56, 1),
(2, 90.00, 60.00, 70.00, 65.00, 100.00, 80.00, 75.00, 90.00, 90.00, 720.00, 80.00, 1),
(3, 100.00, 70.00, 65.00, 57.00, 90.00, 100.00, 80.00, 60.00, 70.00, 692.00, 76.89, 1),
(4, 95.00, 70.00, 60.00, 60.00, 100.00, 80.00, 70.00, 70.00, 55.00, 660.00, 73.33, 1),
(5, 85.00, 70.00, 60.00, 85.00, 100.00, 100.00, 70.00, 85.00, 80.00, 735.00, 81.67, 1),
(6, 95.00, 70.00, 60.00, 60.00, 100.00, 90.00, 75.00, 70.00, 80.00, 700.00, 77.78, 1),
(7, 90.00, 90.00, 60.00, 80.00, 100.00, 95.00, 80.00, 70.00, 90.00, 755.00, 83.89, 1),
(8, 70.00, 60.00, 70.00, 60.00, 85.00, 70.00, 80.00, 75.00, 70.00, 640.00, 71.11, 1),
(9, 90.00, 60.00, 55.00, 55.00, 60.00, 55.00, 65.00, 55.00, 60.00, 555.00, 61.67, 1),
(10, 95.00, 70.00, 65.00, 70.00, 95.00, 100.00, 80.00, 86.00, 70.00, 731.00, 81.22, 1),
(11, 85.00, 60.00, 65.00, 65.00, 100.00, 95.00, 75.00, 65.00, 57.00, 667.00, 74.11, 1);
