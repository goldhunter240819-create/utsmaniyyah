<?php
/**
 * Script Migrasi Database Otomatis - Import Arsip RTQ Utsmaniyyah III
 * Menjamin Data Santri 2026 TIDAK TERTELAN/TERTIMPA
 */
require_once __DIR__ . '/config/database.php';
global $pdo;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sinkronisasi Database - RTQ Utsmaniyyah</title>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
    .box { max-width: 960px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
    h1 { color: #fbbf24; margin-top: 0; font-size: 1.6rem; border-bottom: 1px solid #334155; padding-bottom: 12px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; }
    .badge-success { background: #14532d; color: #86efac; }
    .log-item { background: #0f172a; border-left: 4px solid #10b981; padding: 10px 14px; margin: 8px 0; border-radius: 0 6px 6px 0; font-size: 0.92rem; }
    .btn { display: inline-block; background: #fbbf24; color: #78350f; font-weight: bold; text-decoration: none; padding: 10px 18px; border-radius: 8px; margin-top: 20px; }
    .btn:hover { background: #f59e0b; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.88rem; }
    th, td { border: 1px solid #334155; padding: 8px 10px; text-align: left; }
    th { background: #0f172a; color: #fbbf24; }
  </style>
</head>
<body>
<div class="box">
  <h1>📁 Import Arsip RTQ Utsmaniyyah III (Dari Excel)</h1>
  <p>Memasukkan data arsip RTQ Utsmaniyyah III tanpa mengubah maupun menimpa data santri yang sedang aktif saat ini...</p>

<?php
try {
    // 1. Dapatkan periode aktif 2026 M / 1448 H (JANGAN DIUBAH)
    $stmtP = $pdo->query("SELECT id FROM `periode` WHERE `tahun_pelajaran` LIKE '%2026%' LIMIT 1");
    $pRow = $stmtP->fetch(PDO::FETCH_ASSOC);
    $pId = $pRow ? (int)$pRow['id'] : 1;

    // 2. Dapatkan atau Buat Periode Arsip 2025 M / 1447 H
    $stmtP25 = $pdo->query("SELECT id FROM `periode` WHERE `tahun_pelajaran` LIKE '%2025%' LIMIT 1");
    $p25Row = $stmtP25->fetch(PDO::FETCH_ASSOC);
    if (!$p25Row) {
        $stmtInsP25 = $pdo->prepare("
            INSERT INTO `periode` (`tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`, `is_active`)
            VALUES ('2025 M / 1447 H', '17 Rabi\'ul Awwal 1447 H', '09 Oktober 2025 M', '09 Oktober 2025', 'Tambak Jaya, 09 Oktober 2025', 0)
        ");
        $stmtInsP25->execute();
        $p25Id = (int)$pdo->lastInsertId();
        echo "<div class='log-item'>📅 Periode Arsip Dibuat: <strong>2025 M / 1447 H</strong> (ID #{$p25Id})</div>";
    } else {
        $p25Id = (int)$p25Row['id'];
        echo "<div class='log-item'>📅 Periode Arsip Ditemukan: <strong>2025 M / 1447 H</strong> (ID #{$p25Id})</div>";
    }

    // 3. Dapatkan ID Unit RTQ Utsmaniyyah III (RTQ-U3)
    $stmtU3 = $pdo->query("SELECT id FROM `rtq` WHERE `kode` = 'RTQ-U3' OR `nama` LIKE '%Utsmaniyyah III%' LIMIT 1");
    $u3Id = (int)($stmtU3->fetchColumn() ?: 3);
    echo "<div class='log-item'>🏢 Unit RTQ: <strong>RTQ Utsmaniyyah III</strong> (ID #{$u3Id})</div>";

    // 4. Data 7 Santri Arsip RTQ Utsmaniyyah III Dari File Excel
    // Format: [no_urut, nama, gender, ttl, ayah, ibu, alamat, no_induk, no_ujian, no_syahadah, ghorib, tajwid, tartil, surat, doa, tauhid, wudlu, sholat, kitabah]
    $archiveSantri = [
        [
            1, 'Ahmad Alhabib Ramadhan', 'L', 'Sekincau, 12 Agustus 2012', 'Indra Saputra', 'Lilik Sulastri',
            'Margodadi, Tambak Jaya Kec.Way Tenong Kab. Lampung Barat',
            '0006', '0006', 'LMY-LB/RTQ-U3/001/09/10/2025',
            65, 70, 75, 80, 75, 100, 85, 80, 75
        ],
        [
            2, 'Aulia Zahra El Fikri', 'P', 'Tambak Jaya, 26 Juli 2007', 'M.Fikri', 'Eni Lestari',
            'Margorahayu, Tambak Jaya Kec. Way Tenong Kab. Lampung Barat',
            '0001', '0001', 'LMY-LB/RTQ-U3/002/09/10/2025',
            60, 85, 75, 75, 80, 100, 80, 80, 80
        ],
        [
            3, 'Nur Habibi', 'L', '-', '-', '-',
            'Desa Tambak Jaya, Kec. Way Tenong, Kab. Lampung Barat',
            '0003', '0003', 'LMY-LB/RTQ-U3/003/09/10/2025',
            60, 55, 62.5, 55, 55, 65, 65, 85, 90
        ],
        [
            4, 'M. Fatan Azzamy Isma\'il', 'L', 'Lampung Barat, 11 Juni 2014', 'Andi Rohman', 'Hanik Farida',
            'Margodadi, Tambak Jaya Kec.Way Tenong Kab. Lampung Barat',
            '0009', '0009', 'LMY-LB/RTQ-U3/004/09/10/2025',
            60, 60, 70, 85, 65, 80, 80, 80, 60
        ],
        [
            5, 'M. Mahi Mutawakkil', 'L', 'Padang Tambak, 22 Desember 2012', 'Kastahar', 'Nur Indah',
            'Margodadi, Tambak Jaya Kec.Way Tenong Kab. Lampung Barat',
            '0007', '0007', 'LMY-LB/RTQ-U3/005/09/10/2025',
            80, 70, 70, 80, 70, 80, 90, 80, 85
        ],
        [
            6, 'Renoval Faiz Al Ghifari', 'L', 'Tambak Jaya, 06 September 2013', 'Agus Hariyanto', 'Supiatun',
            'Margo Mulyo, Tambak Jaya Kec. Way Tenong Kab. Lampung Barat',
            '0008', '0008', 'LMY-LB/RTQ-U3/006/09/10/2025',
            70, 75, 65, 60, 65, 70, 65, 90, 65
        ],
        [
            7, 'Sindi Wulandari', 'P', 'Tambak Jaya, 26 Desember 2011', 'Jaenuri', 'Siti Alfiah',
            'Margorahayu, Tambak Jaya Kec. Way Tenong Kab. Lampung Barat',
            '0005', '0005', 'LMY-LB/RTQ-U3/007/09/10/2025',
            50, 60, 60, 60, 50, 80, 90, 80, 60
        ]
    ];

    echo "<h2 style='color: #fbbf24; font-size: 1.2rem; margin-top: 20px;'>📥 Proses Import Data Arsip:</h2>";

    $checkExistingSantri = $pdo->prepare("SELECT id FROM `santri` WHERE `periode_id` = ? AND `nama` = ?");
    $insSantri = $pdo->prepare("
        INSERT INTO `santri` (`rtq_id`, `periode_id`, `no_urut`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`, `no_induk`, `no_ujian`, `no_syahadah`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insNilai = $pdo->prepare("
        INSERT INTO `nilai` (`santri_id`, `ghorib`, `tajwid`, `tartil`, `surat`, `doa`, `tauhid`, `wudlu`, `sholat`, `kitabah`, `total`, `rata_rata`, `is_lulus`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $countAdded = 0;
    foreach ($archiveSantri as $s) {
        $no_urut = $s[0];
        $nama = $s[1];
        $gender = $s[2];
        $ttl = $s[3];
        $ayah = $s[4];
        $ibu = $s[5];
        $alamat = $s[6];
        $no_induk = $s[7];
        $no_ujian = $s[8];
        $no_syahadah = $s[9];

        $checkExistingSantri->execute([$p25Id, $nama]);
        $existingSantriId = $checkExistingSantri->fetchColumn();

        if (!$existingSantriId) {
            $insSantri->execute([$u3Id, $p25Id, $no_urut, $nama, $gender, $ttl, $ayah, $ibu, $alamat, $no_induk, $no_ujian, $no_syahadah]);
            $newSantriId = (int)$pdo->lastInsertId();

            $ghorib  = (float)$s[10];
            $tajwid  = (float)$s[11];
            $tartil  = (float)$s[12];
            $surat   = (float)$s[13];
            $doa     = (float)$s[14];
            $tauhid  = (float)$s[15];
            $wudlu   = (float)$s[16];
            $sholat  = (float)$s[17];
            $kitabah = (float)$s[18];

            $total = round($ghorib + $tajwid + $tartil + $surat + $doa + $tauhid + $wudlu + $sholat + $kitabah, 2);
            $rata = round($total / 9, 2);
            $is_lulus = ($rata >= 60) ? 1 : 0;

            $insNilai->execute([$newSantriId, $ghorib, $tajwid, $tartil, $surat, $doa, $tauhid, $wudlu, $sholat, $kitabah, $total, $rata, $is_lulus]);
            $countAdded++;
            echo "<div class='log-item'>✨ Santri Arsip Berhasil Ditambahkan: <strong>#{$newSantriId} {$nama}</strong> (Total Nilai: {$total} | Rata-rata: {$rata})</div>";
        } else {
            echo "<div class='log-item' style='border-left-color: #94a3b8;'>🛡️ Santri Arsip Sudah Ada: <strong>#{$existingSantriId} {$nama}</strong> (Tidak ditimpa)</div>";
        }
    }

    // 5. Tampilkan Seluruh Data Santri Per Periode
    echo "<h2 style='color: #34d399; font-size: 1.2rem; margin-top: 24px; border-bottom: 1px solid #334155; padding-bottom: 8px;'>📋 Status Database Seluruh Santri & Periode</h2>";
    $listAll = $pdo->query("
        SELECT s.id, s.nama, r.kode AS rtq_kode, p.tahun_pelajaran, s.no_induk, s.no_ujian, s.no_syahadah, n.total, n.rata_rata
        FROM `santri` s
        LEFT JOIN `rtq` r ON s.rtq_id = r.id
        LEFT JOIN `periode` p ON s.periode_id = p.id
        LEFT JOIN `nilai` n ON s.id = n.santri_id
        ORDER BY s.periode_id DESC, s.no_urut ASC, s.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<thead><tr><th>ID</th><th>Periode</th><th>Unit RTQ</th><th>Nama Santri</th><th>No. Induk</th><th>No. Ujian</th><th>Total</th><th>Rata</th></tr></thead><tbody>";
    foreach ($listAll as $row) {
        $badgeColor = (strpos($row['tahun_pelajaran'], '2026') !== false) ? '#059669' : '#d97706';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><span class='badge' style='background: {$badgeColor}; color: white;'>" . htmlspecialchars($row['tahun_pelajaran']) . "</span></td>";
        echo "<td><span class='badge' style='background: #0284c7; color: white;'>" . htmlspecialchars($row['rtq_kode']) . "</span></td>";
        echo "<td><strong>" . htmlspecialchars($row['nama']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['no_induk']) . "</td>";
        echo "<td>" . htmlspecialchars($row['no_ujian']) . "</td>";
        echo "<td>" . htmlspecialchars($row['total']) . "</td>";
        echo "<td>" . htmlspecialchars($row['rata_rata']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";

    echo "<p style='margin-top:20px;'><span class='badge badge-success'>Selesai & Aman!</span> Data arsip berhasil ditambahkan. Data santri aktif 2026 tidak terganggu sama sekali.</p>";
    echo "<a href='santri.php' class='btn'>👉 Buka Data Santri</a>";

} catch (Exception $e) {
    echo "<div style='color: #ef4444; background: #450a0a; padding: 14px; border-radius: 8px;'>";
    echo "<strong>Gagal memperbarui database:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>
</div>
</body>
</html>
