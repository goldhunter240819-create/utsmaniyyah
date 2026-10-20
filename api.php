<?php
/**
 * REST API Backend for Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 * Powered by PHP & MySQL (PDO)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/database.php';

// Inisialisasi session user default jika belum login
if (!isset($_SESSION['current_user'])) {
    $_SESSION['current_user'] = [
        'id' => 1,
        'username' => 'admin',
        'nama' => 'Super Admin Lajnah Pusat',
        'role' => 'SUPER_ADMIN',
        'rtq_id' => null
    ];
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ---------------------------------------------------------
        // 1. GET INITIAL FULL DATA
        // ---------------------------------------------------------
        case 'get_init':
            $currentUser = $_SESSION['current_user'];

            // Data Lembaga
            $stmtLembaga = $pdo->query("SELECT * FROM `lembaga` LIMIT 1");
            $lembaga = $stmtLembaga->fetch() ?: [];

            // Daftar RTQ
            $stmtRtq = $pdo->query("SELECT * FROM `rtq` ORDER BY `id` ASC");
            $daftarRtq = $stmtRtq->fetchAll();

            // Daftar Periode
            $stmtPeriode = $pdo->query("SELECT * FROM `periode` ORDER BY `id` DESC");
            $daftarPeriode = $stmtPeriode->fetchAll();

            // Daftar Users / Pengurus
            $stmtUsers = $pdo->query("
                SELECT u.id, u.username, u.nama, u.role, u.rtq_id, r.nama AS rtq_nama 
                FROM `users` u 
                LEFT JOIN `rtq` r ON u.rtq_id = r.id 
                ORDER BY u.id ASC
            ");
            $daftarUsers = $stmtUsers->fetchAll();

            // Filter santri berdasarkan role
            $rtqFilter = $_GET['rtq_id'] ?? ($currentUser['role'] === 'PENGURUS_RTQ' ? $currentUser['rtq_id'] : 'ALL');
            $periodeFilter = $_GET['periode_id'] ?? 'ALL';

            $sql = "
                SELECT 
                    s.id, s.rtq_id, s.periode_id, s.no_urut, s.no_induk, s.no_ujian, s.no_syahadah, 
                    s.nama, s.gender, s.ttl, s.ayah, s.ibu, s.alamat,
                    r.nama AS rtq_nama, r.kode AS rtq_kode, r.kepala_rtq, r.ketua_penguji, r.alamat AS rtq_alamat, r.wa AS rtq_wa,
                    p.tahun_pelajaran, p.tanggal_h, p.tanggal_m, p.tanggal_syahadah, p.titimangsa,
                    n.ghorib, n.tajwid, n.tartil, n.surat, n.doa, n.tauhid, n.wudlu, n.sholat, n.kitabah,
                    n.total, n.rata_rata, n.is_lulus
                FROM `santri` s
                LEFT JOIN `rtq` r ON s.rtq_id = r.id
                LEFT JOIN `periode` p ON s.periode_id = p.id
                LEFT JOIN `nilai` n ON s.id = n.santri_id
                WHERE 1=1
            ";
            $params = [];

            if ($rtqFilter !== 'ALL' && !empty($rtqFilter)) {
                $sql .= " AND s.rtq_id = ?";
                $params[] = $rtqFilter;
            }
            if ($periodeFilter !== 'ALL' && !empty($periodeFilter)) {
                $sql .= " AND s.periode_id = ?";
                $params[] = $periodeFilter;
            }
            $sql .= " ORDER BY s.no_urut ASC, s.id ASC";

            $stmtSantri = $pdo->prepare($sql);
            $stmtSantri->execute($params);
            $santriList = $stmtSantri->fetchAll();

            echo json_encode([
                'status' => 'success',
                'currentUser' => $currentUser,
                'lembaga' => $lembaga,
                'daftarRtq' => $daftarRtq,
                'daftarPeriode' => $daftarPeriode,
                'daftarUsers' => $daftarUsers,
                'santri' => $santriList
            ]);
            break;

        // ---------------------------------------------------------
        // 2. SWITCH USER / ROLE LOGIN
        // ---------------------------------------------------------
        case 'switch_user':
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = (int)($input['user_id'] ?? 0);

            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['current_user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'nama' => $user['nama'],
                    'role' => $user['role'],
                    'rtq_id' => $user['rtq_id']
                ];
                echo json_encode(['status' => 'success', 'user' => $_SESSION['current_user']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan']);
            }
            break;

        // ---------------------------------------------------------
        // 3. SIMPAN / EDIT SANTRI
        // ---------------------------------------------------------
        case 'save_santri':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $rtq_id = (int)($data['rtq_id'] ?? 1);
            $periode_id = (int)($data['periode_id'] ?? 1);
            $nama = trim($data['nama'] ?? '');
            $gender = $data['gender'] ?? 'L';
            $ttl = trim($data['ttl'] ?? '');
            $ayah = trim($data['ayah'] ?? '');
            $ibu = trim($data['ibu'] ?? '');
            $alamat = trim($data['alamat'] ?? '');
            $no_induk = trim($data['no_induk'] ?? '');
            $no_ujian = trim($data['no_ujian'] ?? '');
            $no_syahadah = trim($data['no_syahadah'] ?? '');

            if (empty($nama)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama santri tidak boleh kosong']);
                exit;
            }

            if ($id) {
                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE `santri` 
                    SET `rtq_id`=?, `periode_id`=?, `nama`=?, `gender`=?, `ttl`=?, `ayah`=?, `ibu`=?, `alamat`=?, `no_induk`=?, `no_ujian`=?, `no_syahadah`=?
                    WHERE `id`=?
                ");
                $stmt->execute([$rtq_id, $periode_id, $nama, $gender, $ttl, $ayah, $ibu, $alamat, $no_induk, $no_ujian, $no_syahadah, $id]);
            } else {
                // INSERT
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `santri` WHERE `rtq_id` = ? AND `periode_id` = ?");
                $stmtCount->execute([$rtq_id, $periode_id]);
                $nextNo = $stmtCount->fetchColumn() + 1;

                $stmt = $pdo->prepare("
                    INSERT INTO `santri` (`rtq_id`, `periode_id`, `no_urut`, `no_induk`, `no_ujian`, `no_syahadah`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$rtq_id, $periode_id, $nextNo, $no_induk, $no_ujian, $no_syahadah, $nama, $gender, $ttl, $ayah, $ibu, $alamat]);
                $id = $pdo->lastInsertId();

                // Buat record nilai kosong
                $stmtNilai = $pdo->prepare("INSERT INTO `nilai` (`santri_id`) VALUES (?)");
                $stmtNilai->execute([$id]);
            }

            echo json_encode(['status' => 'success', 'santri_id' => $id]);
            break;

        // ---------------------------------------------------------
        // 4. HAPUS SANTRI
        // ---------------------------------------------------------
        case 'delete_santri':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM `santri` WHERE `id` = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        // ---------------------------------------------------------
        // 5. UPDATE NILAI
        // ---------------------------------------------------------
        case 'update_nilai':
            $data = json_decode(file_get_contents('php://input'), true);
            $santri_id = (int)($data['santri_id'] ?? 0);
            $subject = $data['subject'] ?? '';
            $value = (float)($data['value'] ?? 0);

            $allowed = ['ghorib', 'tajwid', 'tartil', 'surat', 'doa', 'tauhid', 'wudlu', 'sholat', 'kitabah'];
            if (!in_array($subject, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Mata ujian tidak valid']);
                exit;
            }

            // Update subjek nilai
            $stmt = $pdo->prepare("UPDATE `nilai` SET `$subject` = ? WHERE `santri_id` = ?");
            $stmt->execute([$value, $santri_id]);

            // Hitung ulang total dan rata-rata
            $stmtGet = $pdo->prepare("SELECT * FROM `nilai` WHERE `santri_id` = ?");
            $stmtGet->execute([$santri_id]);
            $n = $stmtGet->fetch();

            if ($n) {
                $total = $n['ghorib'] + $n['tajwid'] + $n['tartil'] + $n['surat'] + $n['doa'] + $n['tauhid'] + $n['wudlu'] + $n['sholat'] + $n['kitabah'];
                $rata = round($total / 9, 2);

                $kkm = (float)$pdo->query("SELECT `kkm` FROM `lembaga` LIMIT 1")->fetchColumn();
                $isLulus = ($total >= $kkm) ? 1 : 0;

                $stmtUpdate = $pdo->prepare("UPDATE `nilai` SET `total` = ?, `rata_rata` = ?, `is_lulus` = ? WHERE `santri_id` = ?");
                $stmtUpdate->execute([$total, $rata, $isLulus, $santri_id]);
            }

            echo json_encode(['status' => 'success', 'total' => $total, 'rata_rata' => $rata, 'is_lulus' => $isLulus]);
            break;

        // ---------------------------------------------------------
        // 6. UPDATE DATA LEMBAGA
        // ---------------------------------------------------------
        case 'save_lembaga':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("
                UPDATE `lembaga` 
                SET `nama`=?, `metode`=?, `wilayah`=?, `tingkat`=?, `pimpinan_lajnah`=?, `ketua_panitia`=?, `kkm`=?
                WHERE `id`=1
            ");
            $stmt->execute([
                $data['nama'],
                $data['metode'],
                $data['wilayah'],
                $data['tingkat'],
                $data['pimpinan_lajnah'],
                $data['ketua_panitia'],
                (float)$data['kkm']
            ]);
            echo json_encode(['status' => 'success']);
            break;

        // ---------------------------------------------------------
        // 7. TAMBAH / UPDATE RTQ
        // ---------------------------------------------------------
        case 'save_rtq':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $kode = trim($data['kode'] ?? '');
            $nama = trim($data['nama'] ?? '');
            $alamat = trim($data['alamat'] ?? '');
            $kepala = trim($data['kepala_rtq'] ?? '');
            $penguji = trim($data['ketua_penguji'] ?? '');
            $wa = trim($data['wa'] ?? '');

            if ($id) {
                $stmt = $pdo->prepare("UPDATE `rtq` SET `kode`=?, `nama`=?, `alamat`=?, `kepala_rtq`=?, `ketua_penguji`=?, `wa`=? WHERE `id`=?");
                $stmt->execute([$kode, $nama, $alamat, $kepala, $penguji, $wa, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO `rtq` (`kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $alamat, $kepala, $penguji, $wa]);
                $id = $pdo->lastInsertId();

                // Otomatis buatkan user pengurus untuk RTQ baru
                $username = strtolower(str_replace([' ', '-'], '', $kode));
                $stmtUser = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES (?, 'rtq123', ?, 'PENGURUS_RTQ', ?)");
                $stmtUser->execute([$username, "Pengurus $nama", $id]);
            }

            echo json_encode(['status' => 'success', 'rtq_id' => $id]);
            break;

        // ---------------------------------------------------------
        // 8. TAMBAH / UPDATE PERIODE
        // ---------------------------------------------------------
        case 'save_periode':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $tahun = trim($data['tahun_pelajaran'] ?? '');
            $tanggal_h = trim($data['tanggal_h'] ?? '');
            $tanggal_m = trim($data['tanggal_m'] ?? '');
            $tanggal_syahadah = trim($data['tanggal_syahadah'] ?? '');
            $titimangsa = trim($data['titimangsa'] ?? '');

            if ($id) {
                $stmt = $pdo->prepare("UPDATE `periode` SET `tahun_pelajaran`=?, `tanggal_h`=?, `tanggal_m`=?, `tanggal_syahadah`=?, `titimangsa`=? WHERE `id`=?");
                $stmt->execute([$tahun, $tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO `periode` (`tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$tahun, $tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa]);
                $id = $pdo->lastInsertId();
            }

            echo json_encode(['status' => 'success', 'periode_id' => $id]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
