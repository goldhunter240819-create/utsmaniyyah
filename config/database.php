<?php
/**
 * Database Connection & Auto-Setup
 * Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'utsmaniyyah_db';

try {
    // 1. Hubungkan ke MySQL Server
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    // 3. Buat tabel-tabel utama jika belum ada
    $pdo->exec("
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

        CREATE TABLE IF NOT EXISTS `rtq` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kode` VARCHAR(20) NOT NULL UNIQUE,
            `nama` VARCHAR(150) NOT NULL,
            `alamat` TEXT NOT NULL,
            `kepala_rtq` VARCHAR(150) NOT NULL,
            `ketua_penguji` VARCHAR(150) NOT NULL,
            `wa` VARCHAR(30) DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `periode` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tahun_pelajaran` VARCHAR(50) NOT NULL UNIQUE,
            `tanggal_h` VARCHAR(100) NOT NULL,
            `tanggal_m` VARCHAR(100) NOT NULL,
            `tanggal_syahadah` VARCHAR(100) NOT NULL,
            `titimangsa` VARCHAR(100) NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `nama` VARCHAR(100) NOT NULL,
            `role` ENUM('SUPER_ADMIN', 'PENGURUS_RTQ') NOT NULL,
            `rtq_id` INT NULL,
            FOREIGN KEY (`rtq_id`) REFERENCES `rtq`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    ");

    // 4. Periksa apakah data awal perlu di-seed
    $stmt = $pdo->query("SELECT COUNT(*) FROM `lembaga`");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO `lembaga` (`nama`, `metode`, `wilayah`, `tingkat`, `pimpinan_lajnah`, `ketua_panitia`, `kkm`)
            VALUES (
                'LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR\'AN',
                'THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR\'AN YANBU\'A',
                'KABUPATEN LAMPUNG BARAT',
                'RTQ TINGKAT DASAR',
                'SYA\'BUL MUBAROK',
                'A. Muslih Abdurrahman',
                60.00
            );

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
        ");

        // Masukkan 11 Santri baru dari lembar nilai foto
        $santriBaru = [
            [1, '0001', '0001', 'LMY-LB/RTQ-U3/001/09/10/2025', 'M. Ishaq Alfa Fikri', 'L', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 8.0, 7.0, 6.5, 6.0, 10.0, 8.0, 8.0, 7.5, 7.0],
            [2, '0002', '0002', 'LMY-LB/RTQ-U3/002/09/10/2025', 'Ibnul Hasan Mubarok', 'L', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.0, 6.0, 7.0, 6.5, 10.0, 8.0, 7.5, 9.0, 9.0],
            [3, '0003', '0003', 'LMY-LB/RTQ-U3/003/09/10/2025', 'Tienda Azka Mubarokah', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 10.0, 7.0, 6.5, 5.7, 9.0, 10.0, 8.0, 6.0, 7.0],
            [4, '0004', '0004', 'LMY-LB/RTQ-U3/004/09/10/2025', 'Sheena Hayomi Arzalna', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.5, 7.0, 6.0, 6.0, 10.0, 8.0, 7.0, 7.0, 5.5],
            [5, '0005', '0005', 'LMY-LB/RTQ-U3/005/09/10/2025', 'Hanifah Arum Manisa', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.0, 6.0, 5.5, 5.5, 6.0, 5.5, 6.5, 5.5, 6.0],
            [6, '0006', '0006', 'LMY-LB/RTQ-U3/006/09/10/2025', 'Riska Dwi Setiawati', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.5, 7.0, 6.0, 6.0, 10.0, 9.0, 7.5, 7.0, 8.0],
            [7, '0007', '0007', 'LMY-LB/RTQ-U3/007/09/10/2025', 'Ni\'matul Ilma Khoirunisa', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 8.5, 7.0, 6.0, 8.5, 10.0, 10.0, 7.0, 8.5, 8.0],
            [8, '0008', '0008', 'LMY-LB/RTQ-U3/008/09/10/2025', 'Zulfa Asyifa Zahira', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 7.0, 6.0, 7.0, 6.0, 8.5, 7.0, 8.0, 7.5, 7.0],
            [9, '0009', '0009', 'LMY-LB/RTQ-U3/009/09/10/2025', 'Lailatul Rohmah', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.0, 9.0, 6.0, 8.0, 10.0, 9.5, 8.0, 7.0, 9.0],
            [10, '0010', '0010', 'LMY-LB/RTQ-U3/010/09/10/2025', 'Meliana Okta Sari', 'P', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 9.5, 7.0, 6.5, 7.0, 9.5, 10.0, 8.0, 8.6, 7.0],
            [11, '0011', '0011', 'LMY-LB/RTQ-U3/011/09/10/2025', 'Gera Atha Praseta', 'L', 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong', 8.5, 6.0, 6.5, 6.5, 10.0, 9.5, 7.5, 6.5, 5.7]
        ];

        $stmtSantri = $pdo->prepare("
            INSERT INTO `santri` (`rtq_id`, `periode_id`, `no_urut`, `no_induk`, `no_ujian`, `no_syahadah`, `nama`, `gender`, `ttl`, `alamat`)
            VALUES (3, 1, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtNilai = $pdo->prepare("
            INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($santriBaru as $row) {
            $stmtSantri->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]]);
            $santriId = $pdo->lastInsertId();

            $ghorib = $row[8];
            $tajwid = $row[9];
            $tartil = $row[10];
            $surat = $row[11];
            $doa = $row[12];
            $tauhid = $row[13];
            $wudlu = $row[14];
            $sholat = $row[15];
            $kitabah = $row[16];

            $total = $ghorib + $tajwid + $tartil + $surat + $doa + $tauhid + $wudlu + $sholat + $kitabah;
            $rata = round($total / 9, 2);
            $isLulus = ($total >= 60.00) ? 1 : 0;

            $stmtNilai->execute([$santriId, $ghorib, $tajwid, $tartil, $surat, $doa, $tauhid, $wudlu, $sholat, $kitabah, $total, $rata, $isLulus]);
        }
    }
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
