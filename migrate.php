<?php
/**
 * Script Migrasi Database Otomatis (Live & Lokal)
 * Menyelaraskan 11 Data Santri Tulisan Tangan Resmi ke Database MySQL
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
    .box { max-width: 780px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
    h1 { color: #fbbf24; margin-top: 0; font-size: 1.6rem; border-bottom: 1px solid #334155; padding-bottom: 12px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; }
    .badge-success { background: #14532d; color: #86efac; }
    .log-item { background: #0f172a; border-left: 4px solid #10b981; padding: 10px 14px; margin: 8px 0; border-radius: 0 6px 6px 0; font-size: 0.92rem; }
    .btn { display: inline-block; background: #fbbf24; color: #78350f; font-weight: bold; text-decoration: none; padding: 10px 18px; border-radius: 8px; margin-top: 20px; }
    .btn:hover { background: #f59e0b; }
  </style>
</head>
<body>
<div class="box">
  <h1>⚡ Sinkronisasi Data 11 Santri Resmi ke Database</h1>
  <p>Menjalankan pembaruan otomatis data santri dari foto lembaran tulisan tangan...</p>

<?php
try {
    // 1. Dapatkan periode aktif 2026 M / 1448 H
    $stmtP = $pdo->query("SELECT id FROM `periode` WHERE `tahun_pelajaran` LIKE '%2026%' LIMIT 1");
    $pRow = $stmtP->fetch(PDO::FETCH_ASSOC);
    $pId = $pRow ? (int)$pRow['id'] : 1;

    // Pastikan periode 2026 ada dan aktif
    $pdo->exec("
        UPDATE `periode` 
        SET `tahun_pelajaran` = '2026 M / 1448 H',
            `tanggal_h` = '21 Rabi\'ul Akhir 1448 H',
            `tanggal_m` = '04 Oktober 2026 M',
            `tanggal_syahadah` = '25 Oktober 2026',
            `titimangsa` = 'Tambak Jaya, 25 Oktober 2026',
            `is_active` = 1
        WHERE `id` = $pId
    ");

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
        [11, 'Gera Adha Fradeta', 'L', 'Lampung Barat, 07 Oktober 2015', 'Sukanto', 'Dewi Tiarni', 'Desa Tambak Jaya, Sanyir, Margo Mulyo, Kec. Way Tenong, Kab. Lampung Barat', '0011', '0011', 'LMY-LB/RTQ-U1/011/09/10/2026', 85, 60, 65, 65, 100, 95, 75, 65, 57]
    ];

    $insSantri = $pdo->prepare("
        INSERT INTO `santri` (`id`, `rtq_id`, `periode_id`, `no_urut`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`, `no_induk`, `no_ujian`, `no_syahadah`)
        VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            `rtq_id` = 1,
            `periode_id` = VALUES(`periode_id`),
            `no_urut` = VALUES(`no_urut`),
            `nama` = VALUES(`nama`),
            `gender` = VALUES(`gender`),
            `ttl` = VALUES(`ttl`),
            `ayah` = VALUES(`ayah`),
            `ibu` = VALUES(`ibu`),
            `alamat` = VALUES(`alamat`),
            `no_induk` = VALUES(`no_induk`),
            `no_ujian` = VALUES(`no_ujian`),
            `no_syahadah` = VALUES(`no_syahadah`)
    ");

    $insNilai = $pdo->prepare("
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
        $insSantri->execute([$id, $pId, $id, $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9]]);

        $total = round($s[10] + $s[11] + $s[12] + $s[13] + $s[14] + $s[15] + $s[16] + $s[17] + $s[18], 2);
        $rata = round($total / 9, 2);
        $is_lulus = ($rata >= 60) ? 1 : 0;
        $insNilai->execute([$id, $s[10], $s[11], $s[12], $s[13], $s[14], $s[15], $s[16], $s[17], $s[18], $total, $rata, $is_lulus]);

        echo "<div class='log-item'>✅ <strong>#{$id} [No.{$id}] {$s[1]} ({$s[2]})</strong><br>TTL: {$s[3]} | Ortu: {$s[4]} & {$s[5]} | Total Nilai: {$total}</div>";
    }

    // Sinkronisasi Akun Pengurus RTQ (Termasuk RTQ Nurul Yasin Alhidayah / RTQ-NY)
    echo "<h2 style='color: #60a5fa; font-size: 1.2rem; margin-top: 24px; border-bottom: 1px solid #334155; padding-bottom: 8px;'>👥 Sinkronisasi Akun Pengurus Unit RTQ</h2>";
    $stmtAllRtq = $pdo->query("SELECT id, kode, nama FROM `rtq`");
    $allRtq = $stmtAllRtq->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allRtq as $rRow) {
        $stmtCheckU = $pdo->prepare("SELECT id, username FROM `users` WHERE `rtq_id` = ?");
        $stmtCheckU->execute([$rRow['id']]);
        $existingU = $stmtCheckU->fetch(PDO::FETCH_ASSOC);
        if (!$existingU) {
            $uname = strtolower(str_replace(['-', ' '], '', $rRow['kode']));
            if (stripos($rRow['nama'], 'Nurul Yasin') !== false || stripos($rRow['kode'], 'NY') !== false) {
                $uname = 'nurulyasin';
            }
            $stmtNewU = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES (?, '123', ?, 'PENGURUS_RTQ', ?)");
            $stmtNewU->execute([$uname, "Admin " . $rRow['nama'], $rRow['id']]);
            echo "<div class='log-item' style='border-left-color: #38bdf8;'>✨ Akun Baru Dibuat: <strong>{$uname}</strong> (Password: <code>123</code>) untuk <strong>{$rRow['nama']} ({$rRow['kode']})</strong></div>";
        } else {
            echo "<div class='log-item' style='border-left-color: #94a3b8;'>👤 Akun Pengurus: <strong>{$existingU['username']}</strong> terhubung ke <strong>{$rRow['nama']} ({$rRow['kode']})</strong></div>";
        }
    }

    echo "<p><span class='badge badge-success'>Selesai!</span> Seluruh 11 santri dan akun pengurus RTQ berhasil disinkronkan ke database MySQL.</p>";
    echo "<a href='dashboard.php' class='btn'>👉 Kembali ke Dashboard Aplikasi</a>";

} catch (Exception $e) {
    echo "<div style='color: #ef4444; background: #450a0a; padding: 14px; border-radius: 8px;'>";
    echo "<strong>Gagal memperbarui database:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>
</div>
</body>
</html>
