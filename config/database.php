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
            (1, '2026 M / 1448 H', '21 Rabi\'ul Akhir 1448 H', '04 Oktober 2026 M', '25 Oktober 2026', 'Tambak Jaya, 25 Oktober 2026', 1);
        ");
    }

    // Migrasi Otomatis: Pastikan seluruh santri terhubung ke periode 2026 M / 1448 H dan hapus periode lama (2025)
    try {
        $stmtCheck26 = $pdo->query("SELECT id FROM `periode` WHERE `tahun_pelajaran` LIKE '%2026%' LIMIT 1");
        $p26Row = $stmtCheck26->fetch(PDO::FETCH_ASSOC);

        if ($p26Row) {
            $p26Id = (int)$p26Row['id'];
            // Perbarui data tanggal periode 2026 sesuai tanggal resmi
            $stmtUp26 = $pdo->prepare("
                UPDATE `periode` 
                SET `tahun_pelajaran` = '2026 M / 1448 H',
                    `tanggal_h` = '21 Rabi\'ul Akhir 1448 H',
                    `tanggal_m` = '04 Oktober 2026 M',
                    `tanggal_syahadah` = '25 Oktober 2026',
                    `titimangsa` = 'Tambak Jaya, 25 Oktober 2026',
                    `is_active` = 1
                WHERE `id` = ?
            ");
            $stmtUp26->execute([$p26Id]);

            // Hubungkan seluruh data santri ke periode 2026 ini
            $pdo->exec("UPDATE `santri` SET `periode_id` = $p26Id");

            // Hapus periode lama (2025)
            $pdo->exec("DELETE FROM `periode` WHERE `id` != $p26Id AND `tahun_pelajaran` LIKE '%2025%'");
        } else {
            // Jika hanya ada 1 periode lama, langsung ubah menjadi 2026
            $pdo->exec("
                UPDATE `periode` 
                SET `tahun_pelajaran` = '2026 M / 1448 H',
                    `tanggal_h` = '21 Rabi\'ul Akhir 1448 H',
                    `tanggal_m` = '04 Oktober 2026 M',
                    `tanggal_syahadah` = '25 Oktober 2026',
                    `titimangsa` = 'Tambak Jaya, 25 Oktober 2026',
                    `is_active` = 1
                LIMIT 1
            ");
        }
    } catch (Exception $e) {
        // Abaikan error migrasi jika sudah tersinkronisasi
    }

    // Migrasi Otomatis Data 11 Santri Tulisan Tangan Resmi (Auto-sync untuk server live & lokal)
    try {
        $checkPlaceholder = $pdo->query("SELECT `nama`, `ayah` FROM `santri` WHERE `id` = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($checkPlaceholder && (empty($checkPlaceholder['ayah']) || strpos($checkPlaceholder['nama'], 'Alfa Fikri') !== false)) {
            $santriReal = [
                [1, 'MUHAMMAD ISHAQ AL FARIZKY', 'L', 'Sumber Sari, 11 Juni 2013', 'Sugeng Nur Cahyo', 'Sumarni', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0001', '0001', 'LMY-LB/RTQ-U1/001/09/10/2026', 80, 70, 65, 60, 100, 80, 80, 75, 70],
                [2, 'Ibnul Hasan Mubarak', 'L', 'Liwa, 07 Desember 2015', 'Sya\'bul Mubarok', 'Siti Mukaromah', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0002', '0002', 'LMY-LB/RTQ-U1/002/09/10/2026', 90, 60, 70, 65, 100, 80, 75, 90, 90],
                [3, 'Tenda Afita Mubarokah', 'P', 'Kebas Kanan, 06 Juni 2013', 'Romli', 'Khofifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0003', '0003', 'LMY-LB/RTQ-U1/003/09/10/2026', 100, 70, 65, 57, 90, 100, 80, 60, 70],
                [4, 'Sheena Hayumi Arrahma', 'P', 'Lampung Barat, 09 Oktober 2015', 'Muhammad Hadi', 'Suheni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0004', '0004', 'LMY-LB/RTQ-U1/004/09/10/2026', 95, 70, 60, 60, 100, 80, 70, 70, 55],
                [5, 'Nizmatul Ilma Khoerunnisa', 'P', 'Tambak Jaya, 07 Juni 2015', 'Andriyanto', 'Siti Robiah', 'Pemangku Margo Mulyo, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0005', '0005', 'LMY-LB/RTQ-U1/005/09/10/2026', 85, 70, 60, 85, 100, 100, 70, 85, 80],
                [6, 'Rizka Dewi Setyawati', 'P', 'Lampung Barat, 03 Agustus 2012', 'Taufik', 'Apriani', 'Pemangku Margo Rukun, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0006', '0006', 'LMY-LB/RTQ-U1/006/09/10/2026', 95, 70, 60, 60, 100, 90, 75, 70, 80],
                [7, 'Lailatul Rohmah', 'P', 'Kebas Kanan, 13 April 2010', 'Sagiman', 'Siti Kholimah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0007', '0007', 'LMY-LB/RTQ-U1/007/09/10/2026', 90, 90, 60, 80, 100, 95, 80, 70, 90],
                [8, 'Zulva Assifa Zahira', 'P', 'Sekincau, 19 April 2011', 'Wahyu Purwanto', 'Siti Khaifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0008', '0008', 'LMY-LB/RTQ-U1/008/09/10/2026', 70, 60, 70, 60, 85, 70, 80, 75, 70],
                [9, 'Khanifah Arum Manissa', 'P', 'Penumangan Baru, 07 Agustus 2013', 'Sugeng', 'Suheni', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0009', '0009', 'LMY-LB/RTQ-U1/009/09/10/2026', 90, 60, 55, 55, 60, 55, 65, 55, 60],
                [10, 'Meliyana Okta Sari', 'P', 'Metro, 13 Oktober 2013', 'Suprapto', 'Ita Novianti', 'Desa Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0010', '0010', 'LMY-LB/RTQ-U1/010/09/10/2026', 95, 70, 65, 70, 95, 100, 80, 86, 70],
                [11, 'Gera Adha Fradeta', 'L', 'Lampung Barat, 07 Oktober 2015', 'Sukanto', 'Dewi Tiarni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0011', '0011', 'LMY-LB/RTQ-U1/011/09/10/2026', 85, 60, 65, 65, 100, 95, 75, 65, 57],
                [12, 'Vanisha Azwatun Khairunisa', 'P', '-', 'Subandi', '-', 'Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0012', '0012', 'LMY-LB/RTQ-U1/012/09/10/2026', 60, 45, 59, 80, 45, 60, 48, 80, 59]
            ];

            $pId = 1;
            $stmtP = $pdo->query("SELECT id FROM `periode` WHERE `tahun_pelajaran` LIKE '%2026%' LIMIT 1");
            $pRow = $stmtP->fetch(PDO::FETCH_ASSOC);
            if ($pRow) {
                $pId = (int)$pRow['id'];
            }

            $upSantri = $pdo->prepare("
                INSERT INTO `santri` (`id`, `rtq_id`, `periode_id`, `no_urut`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`, `no_induk`, `no_ujian`, `no_syahadah`)
                VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    `periode_id` = VALUES(`periode_id`),
                    `no_urut` = VALUES(`no_urut`),
                    `nama` = VALUES(`nama`),
                    `gender` = VALUES(`gender`),
                    `ttl` = IF(VALUES(`ttl`) != '-' AND VALUES(`ttl`) != '', VALUES(`ttl`), `ttl`),
                    `ayah` = IF(VALUES(`ayah`) != '-' AND VALUES(`ayah`) != '', VALUES(`ayah`), `ayah`),
                    `ibu` = IF(VALUES(`ibu`) != '-' AND VALUES(`ibu`) != '', VALUES(`ibu`), `ibu`),
                    `alamat` = IF(VALUES(`alamat`) != '-' AND VALUES(`alamat`) != '', VALUES(`alamat`), `alamat`),
                    `no_induk` = VALUES(`no_induk`),
                    `no_ujian` = VALUES(`no_ujian`),
                    `no_syahadah` = VALUES(`no_syahadah`)
            ");

            $upNilai = $pdo->prepare("
                INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `ghorib` = VALUES(`ghorib`), `tajwid` = VALUES(`tajwid`), `tartil` = VALUES(`tartil`),
                    `surat` = VALUES(`surat`), `doa` = VALUES(`doa`), `tauhid` = VALUES(`tauhid`),
                    `wudlu` = VALUES(`wudlu`), `sholat` = VALUES(`sholat`), `kitabah` = VALUES(`kitabah`),
                    `total` = VALUES(`total`), `rata_rata` = VALUES(`rata_rata`), `is_lulus` = VALUES(`is_lulus`)
            ");

            foreach ($santriReal as $s) {
                $id = $s[0];
                $upSantri->execute([$pId, $id, $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $id]);

                $total = round($s[10] + $s[11] + $s[12] + $s[13] + $s[14] + $s[15] + $s[16] + $s[17] + $s[18], 2);
                $rata = round($total / 9, 2);
                $is_lulus = ($rata >= 60) ? 1 : 0;
                $upNilai->execute([$id, $s[10], $s[11], $s[12], $s[13], $s[14], $s[15], $s[16], $s[17], $s[18], $total, $rata, $is_lulus]);
            }
        }
    } catch (Exception $e) {
        // Abaikan jika migrasi telah selesai
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

    // Pastikan setiap unit RTQ (termasuk RTQ-NY / Nurul Yasin) memiliki akun login pengurus
    try {
        $stmtAllRtq = $pdo->query("SELECT id, kode, nama FROM `rtq`");
        if ($stmtAllRtq) {
            $allRtqList = $stmtAllRtq->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allRtqList as $rRow) {
                $stmtCheckU = $pdo->prepare("SELECT id FROM `users` WHERE `rtq_id` = ?");
                $stmtCheckU->execute([$rRow['id']]);
                if (!$stmtCheckU->fetch()) {
                    $uname = strtolower(str_replace(['-', ' '], '', $rRow['kode']));
                    if (stripos($rRow['nama'], 'Nurul Yasin') !== false || stripos($rRow['kode'], 'NY') !== false) {
                        $uname = 'nurulyasin';
                    }
                    // Cek username agar tidak bentrok
                    $stmtCheckUname = $pdo->prepare("SELECT id FROM `users` WHERE `username` = ?");
                    $stmtCheckUname->execute([$uname]);
                    if (!$stmtCheckUname->fetch()) {
                        $stmtNewU = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES (?, '123', ?, 'PENGURUS_RTQ', ?)");
                        $stmtNewU->execute([$uname, "Admin " . $rRow['nama'], $rRow['id']]);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Ignore if error
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
            [1, 'MUHAMMAD ISHAQ AL FARIZKY', 'L', 'Sumber Sari, 11 Juni 2013', 'Sugeng Nur Cahyo', 'Sumarni', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0001', '0001', 'LMY-LB/RTQ-U1/001/09/10/2026', 80, 70, 65, 60, 100, 80, 80, 75, 70],
            [2, 'Ibnul Hasan Mubarak', 'L', 'Liwa, 07 Desember 2015', 'Sya\'bul Mubarok', 'Siti Mukaromah', 'Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0002', '0002', 'LMY-LB/RTQ-U1/002/09/10/2026', 90, 60, 70, 65, 100, 80, 75, 90, 90],
            [3, 'Tenda Afita Mubarokah', 'P', 'Kebas Kanan, 06 Juni 2013', 'Romli', 'Khofifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0003', '0003', 'LMY-LB/RTQ-U1/003/09/10/2026', 100, 70, 65, 57, 90, 100, 80, 60, 70],
            [4, 'Sheena Hayumi Arrahma', 'P', 'Lampung Barat, 09 Oktober 2015', 'Muhammad Hadi', 'Suheni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0004', '0004', 'LMY-LB/RTQ-U1/004/09/10/2026', 95, 70, 60, 60, 100, 80, 70, 70, 55],
            [5, 'Nizmatul Ilma Khoerunnisa', 'P', 'Tambak Jaya, 07 Juni 2015', 'Andriyanto', 'Siti Robiah', 'Pemangku Margo Mulyo, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0005', '0005', 'LMY-LB/RTQ-U1/005/09/10/2026', 85, 70, 60, 85, 100, 100, 70, 85, 80],
            [6, 'Rizka Dewi Setyawati', 'P', 'Lampung Barat, 03 Agustus 2012', 'Taufik', 'Apriani', 'Pemangku Margo Rukun, Pekon Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0006', '0006', 'LMY-LB/RTQ-U1/006/09/10/2026', 95, 70, 60, 60, 100, 90, 75, 70, 80],
            [7, 'Lailatul Rohmah', 'P', 'Kebas Kanan, 13 April 2010', 'Sagiman', 'Siti Kholimah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0007', '0007', 'LMY-LB/RTQ-U1/007/09/10/2026', 90, 90, 60, 80, 100, 95, 80, 70, 90],
            [8, 'Zulva Assifa Zahira', 'P', 'Sekincau, 19 April 2011', 'Wahyu Purwanto', 'Siti Khaifah', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0008', '0008', 'LMY-LB/RTQ-U1/008/09/10/2026', 70, 60, 70, 60, 85, 70, 80, 75, 70],
            [9, 'Khanifah Arum Manissa', 'P', 'Penumangan Baru, 07 Agustus 2013', 'Sugeng', 'Suheni', 'Kebas Kanan, Kec. Sekincau, Kab. Lampung Barat', '0009', '0009', 'LMY-LB/RTQ-U1/009/09/10/2026', 90, 60, 55, 55, 60, 55, 65, 55, 60],
            [10, 'Meliyana Okta Sari', 'P', 'Metro, 13 Oktober 2013', 'Suprapto', 'Ita Novianti', 'Desa Tambak Jaya, Sanyir, Margo Rukun, Kec. Way Tenong, Kab. Lampung Barat', '0010', '0010', 'LMY-LB/RTQ-U1/010/09/10/2026', 95, 70, 65, 70, 95, 100, 80, 86, 70],
            [11, 'Gera Adha Fradeta', 'L', 'Lampung Barat, 07 Oktober 2015', 'Sukanto', 'Dewi Tiarni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0011', '0011', 'LMY-LB/RTQ-U1/011/09/10/2026', 85, 60, 65, 65, 100, 95, 75, 65, 57],
            [12, 'Vanisha Azwatun Khairunisa', 'P', '-', 'Subandi', '-', 'Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat', '0012', '0012', 'LMY-LB/RTQ-U1/012/09/10/2026', 60, 45, 59, 80, 45, 60, 48, 80, 59]
        ];

        $insSantri = $pdo->prepare("
            INSERT INTO `santri` (`id`, `rtq_id`, `periode_id`, `no_urut`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`, `no_induk`, `no_ujian`, `no_syahadah`)
            VALUES (?, 1, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insNilai = $pdo->prepare("
            INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($santriScores as $s) {
            $id = $s[0];
            $total = round($s[10] + $s[11] + $s[12] + $s[13] + $s[14] + $s[15] + $s[16] + $s[17] + $s[18], 2);
            $rata = round($total / 9, 2);
            $is_lulus = ($rata >= 60) ? 1 : 0;

            $insSantri->execute([$id, $id, $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9]]);
            $insNilai->execute([$id, $s[10], $s[11], $s[12], $s[13], $s[14], $s[15], $s[16], $s[17], $s[18], $total, $rata, $is_lulus]);
        }
    }

} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
