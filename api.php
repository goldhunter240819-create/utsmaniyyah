<?php
/**
 * REST API Backend for Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 * Strict Multi-RTQ Data Isolation & Role Management
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/config/database.php';

$action = $_GET['action'] ?? '';

try {
    // ---------------------------------------------------------
    // AUTH: LOGIN & LOGOUT
    // ---------------------------------------------------------
    if ($action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        $stmt = $pdo->prepare("
            SELECT u.*, r.nama AS rtq_nama, r.kode AS rtq_kode 
            FROM `users` u 
            LEFT JOIN `rtq` r ON u.rtq_id = r.id 
            WHERE u.username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            $userRole = ($user['username'] === 'Maul' || $user['role'] === 'DEVELOPER') ? 'DEVELOPER' : $user['role'];
            $_SESSION['current_user'] = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'nama' => $user['nama'],
                'role' => $userRole,
                'rtq_id' => $user['rtq_id'] ? (int)$user['rtq_id'] : null,
                'rtq_nama' => $user['rtq_nama'] ?? 'Semua RTQ (Developer)',
                'rtq_kode' => $user['rtq_kode'] ?? 'ALL'
            ];
            echo json_encode(['status' => 'success', 'user' => $_SESSION['current_user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah!']);
        }
        exit;
    }

    if ($action === 'logout') {
        unset($_SESSION['current_user']);
        session_destroy();
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Cek Session User Login
    if (!isset($_SESSION['current_user'])) {
        echo json_encode(['status' => 'unauthorized', 'message' => 'Silakan login terlebih dahulu']);
        exit;
    }

    $currentUser = $_SESSION['current_user'];
    $isDeveloper = (in_array($currentUser['role'] ?? '', ['DEVELOPER', 'SUPER_ADMIN']) || ($currentUser['username'] ?? '') === 'Maul');
    $userRtqId = $currentUser['rtq_id'];

    switch ($action) {
        // ---------------------------------------------------------
        // 1. GET INITIAL FULL DATA (STRICT ISOLATION)
        // ---------------------------------------------------------
        case 'get_init':
            // Data Lembaga
            $stmtLembaga = $pdo->query("SELECT * FROM `lembaga` LIMIT 1");
            $lembaga = $stmtLembaga->fetch() ?: [];

            // Daftar RTQ (Jika Pengurus RTQ, hanya boleh lihat RTQ miliknya)
            if ($isDeveloper) {
                $stmtRtq = $pdo->query("SELECT * FROM `rtq` ORDER BY `id` ASC");
                $daftarRtq = $stmtRtq->fetchAll();
            } else {
                $stmtRtq = $pdo->prepare("SELECT * FROM `rtq` WHERE `id` = ?");
                $stmtRtq->execute([$userRtqId]);
                $daftarRtq = $stmtRtq->fetchAll();
            }

            // Daftar Periode
            $stmtPeriode = $pdo->query("SELECT * FROM `periode` ORDER BY `id` DESC");
            $daftarPeriode = $stmtPeriode->fetchAll();

            // Daftar Users (Hanya developer yang boleh lihat daftar user)
            $daftarUsers = [];
            if ($isDeveloper) {
                $stmtUsers = $pdo->query("
                    SELECT u.id, u.username, u.nama, u.role, u.rtq_id, r.nama AS rtq_nama 
                    FROM `users` u 
                    LEFT JOIN `rtq` r ON u.rtq_id = r.id 
                    ORDER BY u.id ASC
                ");
                $daftarUsers = $stmtUsers->fetchAll();
            }

            // Filter RTQ & Periode
            // PENGURUS_RTQ DIPAKSA HANYA MELIHAT DATA RTQ-NYA SENDIRI!
            if ($isDeveloper) {
                $rtqFilter = $_GET['rtq_id'] ?? 'ALL';
            } else {
                $rtqFilter = $userRtqId;
            }
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
        // 2. SWITCH USER (Hanya Developer "Maul" yang bisa switch cepat)
        // ---------------------------------------------------------
        case 'switch_user':
            if (!$isDeveloper) {
                echo json_encode(['status' => 'error', 'message' => 'Hanya akun Developer yang dapat berganti profil langsung']);
                exit;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = (int)($input['user_id'] ?? 0);

            $stmt = $pdo->prepare("
                SELECT u.*, r.nama AS rtq_nama, r.kode AS rtq_kode 
                FROM `users` u 
                LEFT JOIN `rtq` r ON u.rtq_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['current_user'] = [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'nama' => $user['nama'],
                    'role' => $user['role'],
                    'rtq_id' => $user['rtq_id'] ? (int)$user['rtq_id'] : null,
                    'rtq_nama' => $user['rtq_nama'] ?? 'Semua RTQ (Developer)',
                    'rtq_kode' => $user['rtq_kode'] ?? 'ALL'
                ];
                echo json_encode(['status' => 'success', 'user' => $_SESSION['current_user']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan']);
            }
            break;

        // ---------------------------------------------------------
        // 3. SIMPAN / EDIT SANTRI (STRICT ISOLATION)
        // ---------------------------------------------------------
        case 'save_santri':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = !empty($data['id']) ? (int)$data['id'] : null;

            // Jika bukan developer, paksa rtq_id milik pengurus yang login
            if (!$isDeveloper) {
                $rtq_id = $userRtqId;
            } else {
                $rtq_id = (int)($data['rtq_id'] ?? 1);
            }

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
                // Pastikan jika bukan developer, santri yang diedit adalah milik RTQ-nya
                if (!$isDeveloper) {
                    $stmtCheck = $pdo->prepare("SELECT `rtq_id` FROM `santri` WHERE `id` = ?");
                    $stmtCheck->execute([$id]);
                    if ($stmtCheck->fetchColumn() != $userRtqId) {
                        echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses mengubah santri RTQ lain!']);
                        exit;
                    }
                }

                $stmt = $pdo->prepare("
                    UPDATE `santri` 
                    SET `rtq_id`=?, `periode_id`=?, `nama`=?, `gender`=?, `ttl`=?, `ayah`=?, `ibu`=?, `alamat`=?, `no_induk`=?, `no_ujian`=?, `no_syahadah`=?
                    WHERE `id`=?
                ");
                $stmt->execute([$rtq_id, $periode_id, $nama, $gender, $ttl, $ayah, $ibu, $alamat, $no_induk, $no_ujian, $no_syahadah, $id]);
            } else {
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `santri` WHERE `rtq_id` = ? AND `periode_id` = ?");
                $stmtCount->execute([$rtq_id, $periode_id]);
                $nextNo = $stmtCount->fetchColumn() + 1;

                $stmt = $pdo->prepare("
                    INSERT INTO `santri` (`rtq_id`, `periode_id`, `no_urut`, `no_induk`, `no_ujian`, `no_syahadah`, `nama`, `gender`, `ttl`, `ayah`, `ibu`, `alamat`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$rtq_id, $periode_id, $nextNo, $no_induk, $no_ujian, $no_syahadah, $nama, $gender, $ttl, $ayah, $ibu, $alamat]);
                $id = $pdo->lastInsertId();

                $stmtNilai = $pdo->prepare("INSERT INTO `nilai` (`santri_id`) VALUES (?)");
                $stmtNilai->execute([$id]);
            }

            echo json_encode(['status' => 'success', 'santri_id' => $id]);
            break;

        // ---------------------------------------------------------
        // 4. HAPUS SANTRI (STRICT ISOLATION)
        // ---------------------------------------------------------
        case 'delete_santri':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);

            if (!$isDeveloper) {
                $stmtCheck = $pdo->prepare("SELECT `rtq_id` FROM `santri` WHERE `id` = ?");
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetchColumn() != $userRtqId) {
                    echo json_encode(['status' => 'error', 'message' => 'Anda tidak berhak menghapus santri dari RTQ lain!']);
                    exit;
                }
            }

            $stmt = $pdo->prepare("DELETE FROM `santri` WHERE `id` = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        // ---------------------------------------------------------
        // 5. UPDATE NILAI (STRICT ISOLATION)
        // ---------------------------------------------------------
        case 'update_nilai':
            $data = json_decode(file_get_contents('php://input'), true);
            $santri_id = (int)($data['santri_id'] ?? 0);
            $subject = $data['subject'] ?? '';
            $value = (float)($data['value'] ?? 0);

            if (!$isDeveloper) {
                $stmtCheck = $pdo->prepare("SELECT `rtq_id` FROM `santri` WHERE `id` = ?");
                $stmtCheck->execute([$santri_id]);
                if ($stmtCheck->fetchColumn() != $userRtqId) {
                    echo json_encode(['status' => 'error', 'message' => 'Anda tidak berhak mengubah nilai santri dari RTQ lain!']);
                    exit;
                }
            }

            $allowed = ['ghorib', 'tajwid', 'tartil', 'surat', 'doa', 'tauhid', 'wudlu', 'sholat', 'kitabah'];
            if (!in_array($subject, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Mata ujian tidak valid']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE `nilai` SET `$subject` = ? WHERE `santri_id` = ?");
            $stmt->execute([$value, $santri_id]);

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
        // 6. UPDATE DATA LEMBAGA (Hanya Developer)
        // ---------------------------------------------------------
        case 'save_lembaga':
            if (!$isDeveloper) {
                echo json_encode(['status' => 'error', 'message' => 'Hanya Developer yang dapat mengubah data lembaga induk']);
                exit;
            }
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

            // Jika bukan developer, hanya boleh update RTQ miliknya sendiri
            if (!$isDeveloper && $id != $userRtqId) {
                echo json_encode(['status' => 'error', 'message' => 'Anda hanya berhak mengedit data RTQ Anda sendiri!']);
                exit;
            }

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
                if (!$isDeveloper) {
                    echo json_encode(['status' => 'error', 'message' => 'Hanya Developer yang dapat menambah unit RTQ baru']);
                    exit;
                }
                $stmt = $pdo->prepare("INSERT INTO `rtq` (`kode`, `nama`, `alamat`, `kepala_rtq`, `ketua_penguji`, `wa`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $alamat, $kepala, $penguji, $wa]);
                $id = $pdo->lastInsertId();

                $rtqUsername = trim($data['username'] ?? '');
                $rtqPassword = trim($data['password'] ?? '');
                if (!empty($rtqUsername) && !empty($rtqPassword)) {
                    $stmtCheckUser = $pdo->prepare("SELECT id FROM `users` WHERE `username` = ?");
                    $stmtCheckUser->execute([$rtqUsername]);
                    if (!$stmtCheckUser->fetch()) {
                        $stmtUser = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES (?, ?, ?, 'PENGURUS_RTQ', ?)");
                        $stmtUser->execute([$rtqUsername, $rtqPassword, "Admin " . $nama, $id]);
                    }
                }
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

            if (empty($tahun)) {
                echo json_encode(['status' => 'error', 'message' => 'Tahun Pelajaran wajib diisi.']);
                break;
            }

            if ($id) {
                // Cek apakah nama tahun pelajaran ini sudah dipakai baris periode lain
                $check = $pdo->prepare("SELECT id FROM `periode` WHERE `tahun_pelajaran`=? AND id != ?");
                $check->execute([$tahun, $id]);
                $dup = $check->fetch(PDO::FETCH_ASSOC);
                if ($dup) {
                    // Update periode yang sudah ada tersebut agar tidak terjadi duplicate key error
                    $stmt = $pdo->prepare("UPDATE `periode` SET `tanggal_h`=?, `tanggal_m`=?, `tanggal_syahadah`=?, `titimangsa`=? WHERE `id`=?");
                    $stmt->execute([$tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa, $dup['id']]);
                    $id = $dup['id'];
                } else {
                    $stmt = $pdo->prepare("UPDATE `periode` SET `tahun_pelajaran`=?, `tanggal_h`=?, `tanggal_m`=?, `tanggal_syahadah`=?, `titimangsa`=? WHERE `id`=?");
                    $stmt->execute([$tahun, $tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa, $id]);
                }
            } else {
                // Cek apakah tahun pelajaran sudah ada di database saat penambahan baru
                $check = $pdo->prepare("SELECT id FROM `periode` WHERE `tahun_pelajaran`=?");
                $check->execute([$tahun]);
                $existing = $check->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    // Update data periode yang sudah ada
                    $stmt = $pdo->prepare("UPDATE `periode` SET `tanggal_h`=?, `tanggal_m`=?, `tanggal_syahadah`=?, `titimangsa`=? WHERE `id`=?");
                    $stmt->execute([$tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa, $existing['id']]);
                    $id = $existing['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `periode` (`tahun_pelajaran`, `tanggal_h`, `tanggal_m`, `tanggal_syahadah`, `titimangsa`) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$tahun, $tanggal_h, $tanggal_m, $tanggal_syahadah, $titimangsa]);
                    $id = $pdo->lastInsertId();
                }
            }

            echo json_encode(['status' => 'success', 'periode_id' => $id]);
            break;

        // ---------------------------------------------------------
        // 9. HAPUS PERIODE
        // ---------------------------------------------------------
        case 'delete_periode':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $force = !empty($data['force']);

            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID periode tidak valid.']);
                break;
            }

            // Cek apakah ada santri yang terhubung dengan periode ini
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `santri` WHERE `periode_id` = ?");
            $stmtCheck->execute([$id]);
            $countSantri = (int)$stmtCheck->fetchColumn();

            if ($countSantri > 0 && !$force) {
                echo json_encode([
                    'status' => 'has_santri',
                    'count' => $countSantri,
                    'message' => "Tidak dapat menghapus periode ini karena masih memiliki $countSantri data santri."
                ]);
                break;
            }

            if ($countSantri > 0 && $force) {
                $delSantri = $pdo->prepare("DELETE FROM `santri` WHERE `periode_id` = ?");
                $delSantri->execute([$id]);
            }

            $stmt = $pdo->prepare("DELETE FROM `periode` WHERE `id` = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        // ---------------------------------------------------------
        // 10. HAPUS UNIT RTQ
        // ---------------------------------------------------------
        case 'delete_rtq':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $force = !empty($data['force']);

            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID RTQ tidak valid.']);
                break;
            }

            // Cek apakah ada santri yang terhubung dengan RTQ ini
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `santri` WHERE `rtq_id` = ?");
            $stmtCheck->execute([$id]);
            $countSantri = (int)$stmtCheck->fetchColumn();

            if ($countSantri > 0 && !$force) {
                echo json_encode([
                    'status' => 'has_santri',
                    'count' => $countSantri,
                    'message' => "Tidak dapat menghapus RTQ ini karena masih memiliki $countSantri data santri."
                ]);
                break;
            }

            if ($countSantri > 0 && $force) {
                $delSantri = $pdo->prepare("DELETE FROM `santri` WHERE `rtq_id` = ?");
                $delSantri->execute([$id]);
            }

            $stmt = $pdo->prepare("DELETE FROM `rtq` WHERE `id` = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        // ---------------------------------------------------------
        // 11. TAMBAH / UPDATE AKUN PENGGUNA (Hanya Developer)
        // ---------------------------------------------------------
        case 'save_user':
            if (!$isDeveloper) {
                echo json_encode(['status' => 'error', 'message' => 'Hanya Developer yang dapat mengelola akun pengguna']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = !empty($data['id']) ? (int)$data['id'] : null;
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $nama = trim($data['nama'] ?? '');
            $role = trim($data['role'] ?? 'PENGURUS_RTQ');
            $rtq_id = !empty($data['rtq_id']) ? (int)$data['rtq_id'] : null;

            if (empty($username) || empty($nama)) {
                echo json_encode(['status' => 'error', 'message' => 'Username dan Nama Pengurus wajib diisi']);
                exit;
            }

            if ($userId) {
                // Cek duplikasi username pada user lain
                $check = $pdo->prepare("SELECT id FROM `users` WHERE `username` = ? AND id != ?");
                $check->execute([$username, $userId]);
                if ($check->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh akun lain']);
                    exit;
                }

                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE `users` SET `username`=?, `password`=?, `nama`=?, `role`=?, `rtq_id`=? WHERE `id`=?");
                    $stmt->execute([$username, $password, $nama, $role, $rtq_id, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE `users` SET `username`=?, `nama`=?, `role`=?, `rtq_id`=? WHERE `id`=?");
                    $stmt->execute([$username, $nama, $role, $rtq_id, $userId]);
                }
            } else {
                if (empty($password)) {
                    echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi untuk akun baru']);
                    exit;
                }

                $check = $pdo->prepare("SELECT id FROM `users` WHERE `username` = ?");
                $check->execute([$username]);
                if ($check->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'Username sudah terdaftar']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama`, `role`, `rtq_id`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $password, $nama, $role, $rtq_id]);
                $userId = $pdo->lastInsertId();
            }

            echo json_encode(['status' => 'success', 'user_id' => $userId]);
            break;

        // ---------------------------------------------------------
        // 12. HAPUS AKUN PENGGUNA (Hanya Developer)
        // ---------------------------------------------------------
        case 'delete_user':
            if (!$isDeveloper) {
                echo json_encode(['status' => 'error', 'message' => 'Hanya Developer yang dapat menghapus akun']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = (int)($data['id'] ?? 0);

            $stmtU = $pdo->prepare("SELECT `username` FROM `users` WHERE `id` = ?");
            $stmtU->execute([$userId]);
            $uName = $stmtU->fetchColumn();

            if ($uName === 'Maul' || $userId === (int)($currentUser['id'] ?? 0)) {
                echo json_encode(['status' => 'error', 'message' => 'Akun utama Developer tidak dapat dihapus!']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM `users` WHERE `id` = ?");
            $stmt->execute([$userId]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
