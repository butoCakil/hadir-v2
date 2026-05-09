<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\Auth;
use App\Api\Helpers\PeriodeHelper;

class PresensiWebController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==========================================
    // GET /presensi-web
    // ==========================================
    public function index(): void
    {
        // Cek status login untuk navbar
        Auth::start();
        $isLoggedIn = Auth::check();

        $gateway = \App\Api\Helpers\GatewayHelper::cek('web');

        Response::view('presensi_web/index', [
            'title'      => 'Presensi Web',
            'isLoggedIn' => $isLoggedIn,
            'gateway'    => $gateway,
        ]);
    }

    // ==========================================
    // POST /presensi-web/cek-nis
    // ==========================================
    public function cekNis(): void
    {
        $gateway = \App\Api\Helpers\GatewayHelper::cek('web');
        if (!$gateway['buka']) {
            Response::error('Presensi web sedang ditutup.', 403);
            return;
        }

        $nis = trim($_POST['nis'] ?? '');
        if (!ctype_digit($nis) || !$nis) {
            Response::error('NIS hanya boleh berisi angka.', 400);
            return;
        }

        $siswa = $this->db->queryOne(
            "SELECT d.nis, d.nama, d.kelas, d.jur,
                    p.nama_dudika, p.nama_pembimbing
             FROM datasiswa d
             LEFT JOIN penempatan p ON d.nis = p.nis_siswa
             WHERE d.nis = ?",
            [$nis]
        );

        if (!$siswa) {
            Response::error('NIS tidak ditemukan dalam database.', 404);
            return;
        }

        $today = date('Y-m-d');
        $sudah = $this->db->queryOne(
            "SELECT ket, timestamp FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
            [$nis, $today]
        );

        Response::success([
            'siswa' => $siswa,
            'sudah_presensi' => $sudah ? [
                'ket'       => $sudah['ket'],
                'timestamp' => $sudah['timestamp'],
                'waktu'     => date('H:i', strtotime($sudah['timestamp'])),
            ] : null,
        ]);
    }

    // ==========================================
    // POST /presensi-web/simpan
    // ==========================================
    public function simpan(): void
    {
        $nis      = trim($_POST['nis']     ?? '');
        $tanggal  = trim($_POST['tanggal'] ?? '');
        $ket      = trim($_POST['ket']     ?? '');
        $catatan  = trim($_POST['catatan'] ?? '');
        $mode     = trim($_POST['mode']    ?? 'hariini'); // hariini | lupa

        // Validasi dasar
        if (!$nis || !$tanggal || !$ket) {
            Response::error('Data tidak lengkap.', 400); return;
        }

        $validKet = ['Masuk','Izin','Sakit','Libur'];
        if (!in_array($ket, $validKet)) {
            Response::error('Keterangan tidak valid.', 400); return;
        }

        // Ambil data siswa
        $siswa = $this->db->queryOne(
            "SELECT nis, nama, kelas FROM datasiswa WHERE nis = ?", [$nis]
        );
        if (!$siswa) {
            Response::error('NIS tidak ditemukan.', 404); return;
        }

        $today     = date('Y-m-d');
        $tanggalYmd = date('Y-m-d', strtotime($tanggal));

        if ($tanggalYmd > $today) {
            Response::error('Tanggal tidak boleh melebihi hari ini.', 400); return;
        }

        // Cek periode
        $cekPeriode = PeriodeHelper::cekTanggalValid($tanggalYmd);
        if (!$cekPeriode['valid']) {
            Response::error($cekPeriode['pesan'], 400); return;
        }
        
        // Cek presensi sudah ada di tanggal itu
        $sudah = $this->db->queryOne(
            "SELECT id FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
            [$nis, $tanggalYmd]
        );
        if ($sudah) {
            Response::error('Sudah ada presensi pada tanggal ' . date('d/m/Y', strtotime($tanggalYmd)) . '.', 409);
            return;
        }

        // Mode lupa — validasi batas 2x/hari
        if ($mode === 'lupa' && $tanggalYmd !== $today) {
            $cekPeriode = PeriodeHelper::cekTanggalValid($tanggalYmd);
            if (!$cekPeriode['valid']) {
                Response::error($cekPeriode['pesan'], 400); return;
            }

            $lupaKey  = 'lupa_' . $nis . '_' . $today;
            $lupaFile = BASE_PATH . '/storage/lupa.json';
            $lupaData = file_exists($lupaFile)
                ? (json_decode(file_get_contents($lupaFile), true) ?: [])
                : [];

            $count = $lupaData[$nis][$today] ?? 0;
            if ($count >= 2) {
                Response::error('Batas maksimal lupa presensi (2x) untuk hari ini sudah tercapai.', 429); return;
            }
        }

        // Handle foto
        $fotoName  = '';
        $fotoPath  = '';
        if ($ket === 'Masuk') {
            if (empty($_FILES['foto']['tmp_name']) || $_FILES['foto']['error'] !== 0) {
                Response::error('Presensi Masuk wajib menyertakan foto.', 400); return;
            }

            $fotoSize = $_FILES['foto']['size'];
            if ($fotoSize > 200 * 1024) {
                Response::error('Ukuran foto terlalu besar (maksimal 200KB).', 400); return;
            }

            $ext      = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                Response::error('Format foto tidak didukung.', 400); return;
            }

            $tanggalFile = date('Ymd', strtotime($tanggalYmd));
            $fotoName    = $nis . '_' . $tanggalFile . '.' . $ext;
            $fotoDir     = '/home/dvttaulx/public_html/dev/assets/presensi/';

            if (!is_dir($fotoDir)) mkdir($fotoDir, 0755, true);

            $fotoPath = $fotoDir . $fotoName;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath)) {
                Response::error('Gagal menyimpan foto.', 500); return;
            }

            // Link publik
            $fotoLink   = '/assets/presensi/' . $fotoName;
            $statusLink = 'OK';
        } else {
            $fotoLink   = '';
            $statusLink = '';
        }

        // Timestamp
        $timestamp = $tanggalYmd === $today
            ? date('Y-m-d H:i:s')
            : $tanggalYmd . ' 08:00:00';

        // Ambil periode aktif
        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? $periodeAktif['id'] : null;
        
        // Simpan presensi
        $kode = '';
        $this->db->query(
            "INSERT INTO presensi (periode_id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$periodeId, $nis, $siswa['nama'], $siswa['kelas'], $ket, $catatan,
             $fotoLink ?? '', $statusLink ?? '', $kode, $timestamp]
        );

        // Update counter lupa
        if ($mode === 'lupa' && $tanggalYmd !== $today) {
            $lupaFile = BASE_PATH . '/storage/lupa.json';
            $lupaData = file_exists($lupaFile)
                ? (json_decode(file_get_contents($lupaFile), true) ?: [])
                : [];
            if (!isset($lupaData[$nis])) $lupaData[$nis] = [];
            $lupaData[$nis][$today] = ($lupaData[$nis][$today] ?? 0) + 1;
            file_put_contents($lupaFile, json_encode($lupaData, JSON_PRETTY_PRINT));
        }

        Response::success([
            'ket'      => $ket,
            'tanggal'  => date('d M Y', strtotime($tanggalYmd)),
            'waktu'    => date('H:i', strtotime($timestamp)),
            'foto_url' => $fotoLink ?? '',
            'nis'      => $nis,
        ], 'Presensi berhasil disimpan.');
    }

    // ==========================================
    // POST /presensi-web/batal
    // ==========================================
    public function batal(): void
    {
        $nis     = trim($_POST['nis']     ?? '');
        $tanggal = trim($_POST['tanggal'] ?? '');

        if (!$nis || !$tanggal) {
            Response::error('Data tidak lengkap.', 400); return;
        }

        $tanggalYmd = date('Y-m-d', strtotime($tanggal));

        // Cari presensi
        $presensi = $this->db->queryOne(
            "SELECT id, link FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
            [$nis, $tanggalYmd]
        );

        if (!$presensi) {
            Response::error('Data presensi tidak ditemukan.', 404); return;
        }

        // Hapus dari DB
        $this->db->query("DELETE FROM presensi WHERE id = ?", [$presensi['id']]);

        // Hapus file foto jika ada
        if (!empty($presensi['link'])) {
            $fotoPath = '/home/dvttaulx/public_html/dev' . $presensi['link'];
            if (file_exists($fotoPath)) @unlink($fotoPath);
        }

        // Update counter lupa (kurangi jika ada)
        $lupaFile = BASE_PATH . '/storage/lupa.json';
        if (file_exists($lupaFile)) {
            $lupaData = json_decode(file_get_contents($lupaFile), true) ?: [];
            $today    = date('Y-m-d');
            if (isset($lupaData[$nis][$today]) && $lupaData[$nis][$today] > 0) {
                $lupaData[$nis][$today]--;
                file_put_contents($lupaFile, json_encode($lupaData, JSON_PRETTY_PRINT));
            }
        }

        Response::success([], 'Presensi berhasil dibatalkan.');
    }
}
