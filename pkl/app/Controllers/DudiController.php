<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\Auth;

class DudiController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==========================================
    // GET /dudi
    // ==========================================
    public function index(): void
    {
        Auth::start();
        $isLoggedIn = Auth::check();

        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;

        $search     = trim($_GET['search']      ?? '');
        $pembimbing = trim($_GET['pembimbing']  ?? '');

        // Query DUDI — join ke penempatan untuk hitung siswa
        $sql = "
            SELECT
                dd.id, dd.nama, dd.kode, dd.alamat, dd.link_map,
                dd.nama_owner, dd.nama_pembimbing, dd.keterangan,
                CASE WHEN dd.nomor_telepon IS NOT NULL AND dd.nomor_telepon != '' THEN 1 ELSE 0 END as ada_wa,
                (
                    SELECT COUNT(DISTINCT ds2.nis)
                    FROM penempatan pen2
                    INNER JOIN datasiswa ds2 ON ds2.nis = pen2.nis_siswa AND ds2.periode_id = ?
                    WHERE pen2.nama_dudika COLLATE utf8mb4_general_ci = dd.nama
                ) as jumlah_siswa
            FROM datadudi dd
            GROUP BY dd.id, dd.nama, dd.kode, dd.alamat, dd.link_map,
                     dd.nama_owner, dd.nama_pembimbing, dd.keterangan, dd.nomor_telepon
        ";
        $params = [$periodeId];

        if ($search) {
            $sql .= " HAVING dd.nama LIKE ? OR dd.alamat LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($pembimbing) {
            $sql .= ($search ? " AND" : " HAVING") . " dd.nama_pembimbing = ?";
            $params[] = $pembimbing;
        }

        // Urutkan: ada siswa dulu, tidak ada siswa di bawah
        $sql .= " ORDER BY jumlah_siswa DESC, dd.nama ASC";

        $dudiList = $this->db->query($sql, $params);

        $listPembimbing = $this->db->query(
            "SELECT DISTINCT nama_pembimbing FROM datadudi
             WHERE nama_pembimbing IS NOT NULL AND nama_pembimbing != ''
             ORDER BY nama_pembimbing ASC"
        );

        $totalDudi   = count($dudiList);
        $totalSiswa  = array_sum(array_column($dudiList, 'jumlah_siswa'));

        Response::view('dudi/index', [
            'title'          => 'Data DUDI PKL',
            'isLoggedIn'     => $isLoggedIn,
            'dudiList'       => $dudiList,
            'listPembimbing' => $listPembimbing,
            'search'         => $search,
            'filterPembimbing' => $pembimbing,
            'totalDudi'      => $totalDudi,
            'totalSiswa'     => $totalSiswa,
        ]);
    }

    // ==========================================
    // GET /dudi/{kode}
    // ==========================================
    public function detail(array $params): void
    {
        Auth::start();
        $isLoggedIn = Auth::check();

        $kode = $params['kode'] ?? '';

        $dudi = $this->db->queryOne(
            "SELECT id, nama, kode, alamat, link_map, nama_owner, nama_pembimbing, keterangan,
                    CASE WHEN nomor_telepon IS NOT NULL AND nomor_telepon != '' THEN 1 ELSE 0 END as ada_wa
             FROM datadudi WHERE kode = ? LIMIT 1",
            [$kode]
        );

        if (!$dudi) Response::abort(404);

        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;

        // Siswa di DUDI ini periode aktif
        $siswaList = $this->db->query("
            SELECT ds.id, ds.nis, ds.nama, ds.kelas,
                CASE WHEN ds.nohp IS NOT NULL AND ds.nohp != '' THEN 1 ELSE 0 END as ada_wa
            FROM datasiswa ds
            INNER JOIN penempatan pen ON pen.nis_siswa = ds.nis AND pen.nama_dudika COLLATE utf8mb4_general_ci = ?
            WHERE ds.periode_id = ?
            ORDER BY ds.kelas ASC, ds.nama ASC
        ", [$dudi['nama'], $periodeId]);

        Response::view('dudi/detail', [
            'title'      => 'DUDI — ' . $dudi['nama'],
            'isLoggedIn' => $isLoggedIn,
            'dudi'       => $dudi,
            'siswaList'  => $siswaList,
        ]);
    }

    // ==========================================
    // POST /dudi/wa — proxy redirect WA (Opsi B)
    // ==========================================
    public function redirectWa(): void
    {
        $type = $_POST['type'] ?? ''; // 'dudi' atau 'siswa'
        $id   = (int)($_POST['id'] ?? 0);

        if (!$id || !in_array($type, ['dudi','siswa'])) {
            http_response_code(400); exit;
        }

        $nohp = null;

        if ($type === 'dudi') {
            $row  = $this->db->queryOne("SELECT nomor_telepon FROM datadudi WHERE id = ?", [$id]);
            $nohp = $row['nomor_telepon'] ?? null;
        } elseif ($type === 'siswa') {
            $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
            $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;
            $row  = $this->db->queryOne(
                "SELECT nohp FROM datasiswa WHERE id = ? AND periode_id = ?", [$id, $periodeId]
            );
            $nohp = $row['nohp'] ?? null;
        }

        if (!$nohp) {
            http_response_code(404); exit;
        }

        // Normalisasi ke format 62
        $nohp = preg_replace('/\D/', '', $nohp);
        if (str_starts_with($nohp, '0')) {
            $nohp = '62' . substr($nohp, 1);
        }

        header('Location: https://wa.me/' . $nohp);
        exit;
    }
}