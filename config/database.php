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
            `role` ENUM('DEVELOPER', 'PENGURUS_RTQ') NOT NULL,
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

    // Pastikan 3 RTQ terdaftar
    $pdo->exec("
        INSERT INTO `rtq` (`id`, `kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES
        (1, 'RTQ-U1', 'RTQ Utsmaniyyah I', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. Ahmad Syarif', 'IKHWANUL KIROM', '082184427288'),
        (2, 'RTQ-U2', 'RTQ Utsmaniyyah II', 'Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'Ust. M. Ridwan', 'IKHWANUL KIROM', '082184427288'),
        (3, 'RTQ-U3', 'RTQ Utsmaniyyah III', 'Jl. Tirtomoyo, No.083, Margorahayu, Desa Tambak Jaya Kec. Way Tenong Kab. Lampung Barat', 'ABU HANIFAN', 'IKHWANUL KIROM', '082184427288')
        ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

        INSERT INTO `periode` (`id`, `tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`, `is_active`) VALUES
        (1, '2025 M / 1447 H', '14 Rabi\'ul Awwal 1447 H', '07 September 2025 M', '21 September 2025', 'Tambak Jaya, 07 September 2025', 1),
        (2, '2026 M / 1448 H', 'Rabi\'ul Awwal 1448 H', 'September 2026 M', 'September 2026', 'Tambak Jaya, September 2026', 0)
        ON DUPLICATE KEY UPDATE `tahun_pelajaran`=VALUES(`tahun_pelajaran`);
    ");

    // Perbarui Akun Pengguna sesuai permintaan user:
    // 1. Developer "Maul" password "Basenglah123."
    // 2. Admin RTQ 1 "Utsmaniyyah1" password "Utsmaniyyah1"
    // 3. Admin RTQ 2 "Utsmaniyyah2" password "Utsmaniyyah2"
    // 4. Admin RTQ 3 "Utsmaniyyah3" password "Utsmaniyyah3"
    $usersSeed = [
        ['Maul', 'Basenglah123.', 'Maul (Developer)', 'DEVELOPER', null],
        ['Utsmaniyyah1', 'Utsmaniyyah1', 'Admin RTQ Utsmaniyyah I', 'PENGURUS_RTQ', 1],
        ['Utsmaniyyah2', 'Utsmaniyyah2', 'Admin RTQ Utsmaniyyah II', 'PENGURUS_RTQ', 2],
        ['Utsmaniyyah3', 'Utsmaniyyah3', 'Admin RTQ Utsmaniyyah III', 'PENGURUS_RTQ', 3]
    ];

    $stmtUser = $pdo->prepare("
        INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE `password`=VALUES(`password`), `nama`=VALUES(`nama`), `role`=VALUES(`role`), `rtq_id`=VALUES(`rtq_id`)
    ");

    foreach ($usersSeed as $u) {
        $stmtUser->execute([$u[0], $u[1], $u[2], $u[3], $u[4]]);
    }

} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
