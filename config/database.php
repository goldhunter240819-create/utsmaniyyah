<?php
/**
 * Database Connection & Migration
 * Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'utsmaniyyah_db';

try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    // Tabel-tabel
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
            `role` VARCHAR(50) NOT NULL DEFAULT 'PENGURUS_RTQ',
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

    // Pastikan kolom role di users adalah VARCHAR(50) agar mendukung role DEVELOPER tanpa batasan ENUM
    try {
        $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'PENGURUS_RTQ'");
    } catch (Exception $e) {
        // Table or column already modified
    }

    // 1. Seed RTQ jika masih kosong
    $rtqCount = (int)$pdo->query("SELECT COUNT(*) FROM `rtq`")->fetchColumn();
    if ($rtqCount === 0) {
        $pdo->exec("
            INSERT INTO `rtq` (`id`, `kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES
            (1, 'RTQ-U1', 'RTQ Utsmaniyyah I', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. Ahmad Syarif', 'IKHWANUL KIROM', '082184427288'),
            (2, 'RTQ-U2', 'RTQ Utsmaniyyah II', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. M. Ridwan', 'IKHWANUL KIROM', '082184427288'),
            (3, 'RTQ-U3', 'RTQ Utsmaniyyah III', 'Jl. Tirtomoyo, No.083, Margorahayu, Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'ABU HANIFAN', 'IKHWANUL KIROM', '082184427288');
        ");
    }

    // 2. Seed Periode jika masih kosong
    $periodeCount = (int)$pdo->query("SELECT COUNT(*) FROM `periode`")->fetchColumn();
    if ($periodeCount === 0) {
        $pdo->exec("
            INSERT INTO `periode` (`id`, `tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`, `is_active`) VALUES
            (1, '2025 M / 1447 H', '14 Rabi\'ul Awwal 1447 H', '07 September 2025 M', '21 September 2025', 'Tambak Jaya, 07 September 2025', 1),
            (2, '2026 M / 1448 H', 'Rabi\'ul Awwal 1448 H', 'September 2026 M', 'September 2026', 'Tambak Jaya, September 2026', 0);
        ");
    }

    // 3. Seed Akun Pengguna jika masih kosong
    $usersCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    if ($usersCount === 0) {
        $usersSeed = [
            ['Maul', 'Basenglah123.', 'Maul (Developer)', 'DEVELOPER', null],
            ['Utsmaniyyah1', 'Utsmaniyyah1', 'Admin RTQ Utsmaniyyah I', 'PENGURUS_RTQ', 1],
            ['Utsmaniyyah2', 'Utsmaniyyah2', 'Admin RTQ Utsmaniyyah II', 'PENGURUS_RTQ', 2],
            ['Utsmaniyyah3', 'Utsmaniyyah3', 'Admin RTQ Utsmaniyyah III', 'PENGURUS_RTQ', 3]
        ];

        $stmtUser = $pdo->prepare("
            INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($usersSeed as $u) {
            $stmtUser->execute([$u[0], $u[1], $u[2], $u[3], $u[4]]);
        }
    }

    // 4. Seed Lembaga jika masih kosong
    $lembagaCount = (int)$pdo->query("SELECT COUNT(*) FROM `lembaga`")->fetchColumn();
    if ($lembagaCount === 0) {
        $pdo->exec("
            INSERT INTO `lembaga` (`id`, `nama`, `metode`, `wilayah`, `tingkat`, `pimpinan_lajnah`, `ketua_panitia`, `kkm`) VALUES
            (1, 'LAJNAH MUROQOBAH LEMBAGA PENDIDIKAN AL-QUR\'AN', 'THORIQOH BACA TULIS DAN MENGHAFAL AL-QUR\'AN YANBU\'A', 'KABUPATEN LAMPUNG BARAT', 'RTQ TINGKAT DASAR', 'SYA\'BUL MUBAROK', 'A. Muslih Abdurrahman', 60.00);
        ");
    }

    // 6. Seed Data Santri & Nilai RTQ 1 Skala Puluhan (0-100) jika masih kosong
    $santriCount = (int)$pdo->query("SELECT COUNT(*) FROM `santri`")->fetchColumn();
    if ($santriCount === 0) {
        $santriScores = [
            [1, 'M. Ishaq Alfa Fikri', 'L', '0001', '0001', 'LMY-LB/RTQ-U1/001/09/10/2025', 80, 70, 65, 60, 100, 80, 80, 75, 70],
            [2, 'Ibnul Hasan Mubarok', 'L', '0002', '0002', 'LMY-LB/RTQ-U1/002/09/10/2025', 90, 60, 70, 65, 100, 80, 75, 90, 90],
            [3, 'Tienda Azka Mubarokah', 'P', '0003', '0003', 'LMY-LB/RTQ-U1/003/09/10/2025', 100, 70, 65, 57, 90, 100, 80, 60, 70],
            [4, 'Sheena Hayomi Arzalna', 'P', '0004', '0004', 'LMY-LB/RTQ-U1/004/09/10/2025', 95, 70, 60, 60, 100, 80, 70, 70, 55],
            [5, 'Hanifah Arum Manisa', 'P', '0005', '0005', 'LMY-LB/RTQ-U1/005/09/10/2025', 90, 60, 55, 55, 60, 55, 65, 55, 60],
            [6, 'Riska Dwi Setiawati', 'P', '0006', '0006', 'LMY-LB/RTQ-U1/006/09/10/2025', 95, 70, 60, 60, 100, 90, 75, 70, 80],
            [7, 'Ni\'matul Ilma Khoirunisa', 'P', '0007', '0007', 'LMY-LB/RTQ-U1/007/09/10/2025', 85, 70, 60, 85, 100, 100, 70, 85, 80],
            [8, 'Zulfa Asyifa Zahira', 'P', '0008', '0008', 'LMY-LB/RTQ-U1/008/09/10/2025', 70, 60, 70, 60, 85, 70, 80, 75, 70],
            [9, 'Lailatul Rohmah', 'P', '0009', '0009', 'LMY-LB/RTQ-U1/009/09/10/2025', 90, 90, 60, 80, 100, 95, 80, 70, 90],
            [10, 'Meliana Okta Sari', 'P', '0010', '0010', 'LMY-LB/RTQ-U1/010/09/10/2025', 95, 70, 65, 70, 95, 100, 80, 86, 70],
            [11, 'Gera Atha Praseta', 'L', '0011', '0011', 'LMY-LB/RTQ-U1/011/09/10/2025', 85, 60, 65, 65, 100, 95, 75, 65, 57]
        ];

        $insSantri = $pdo->prepare("
            INSERT INTO `santri` (`id`, `rtq_id`, `periode_id`, `no_urut`, `no_induk`, `no_ujian`, `no_syahadah`, `nama`, `gender`, `ttl`, `alamat`)
            VALUES (?, 1, 1, ?, ?, ?, ?, ?, ?, 'Tambak Jaya, 2013', 'Tambak Jaya, Way Tenong, Lampung Barat')
        ");

        $insNilai = $pdo->prepare("
            INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($santriScores as $s) {
            $id = $s[0];
            $total = round($s[6] + $s[7] + $s[8] + $s[9] + $s[10] + $s[11] + $s[12] + $s[13] + $s[14], 2);
            $rata = round($total / 9, 2);
            $is_lulus = ($rata >= 60) ? 1 : 0;

            $insSantri->execute([$id, $id, $s[3], $s[4], $s[5], $s[1], $s[2]]);
            $insNilai->execute([$id, $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12], $s[13], $s[14], $total, $rata, $is_lulus]);
        }
    }

} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
