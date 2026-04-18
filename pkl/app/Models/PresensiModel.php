<?php

namespace App\Models;

use App\Core\Database;

class PresensiModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==========================================
    // PERIODE AKTIF
    // ==========================================

    public function getPeriodeAktif(): array|false
    {
        return $this->db->queryOne(
            "SELECT * FROM periode_pkl WHERE aktif = 1 ORDER BY id DESC LIMIT 1"
        );
    }

    private function getPeriodeId(): int
    {
        $p = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        return $p ? (int)$p['id'] : 0;
    }

    // ==========================================
    // HARIAN — base datasiswa periode aktif
    // ==========================================

    public function getHarian(string $tanggal, string $kelas = '', string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();

        $sql = "
            SELECT
                ds.nis,
                ds.nama AS namasiswa,
                ds.kelas,
                pen.nama_dudika,
                pen.nama_pembimbing,
                p.id,
                p.ket,
                p.catatan,
                p.link,
                p.statuslink,
                p.kode,
                p.timestamp
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            LEFT JOIN presensi p
                ON ds.nis = p.nis
                AND DATE(p.timestamp) = ?
                AND p.periode_id = ?
            WHERE ds.periode_id = ?
        ";
        $params = [$tanggal, $periodeId, $periodeId];

        if (!empty($kelas))      { $sql .= " AND ds.kelas = ?";            $params[] = $kelas; }
        if (!empty($pembimbing)) { $sql .= " AND pen.nama_pembimbing = ?"; $params[] = $pembimbing; }

        $sql .= " ORDER BY CASE WHEN p.ket IS NULL THEN 1 ELSE 0 END ASC, p.timestamp ASC, ds.nama ASC";
        return $this->db->query($sql, $params);
    }

    public function getRingkasanHarian(string $tanggal, string $kelas = '', string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();

        $sql = "
            SELECT p.ket, COUNT(*) as total
            FROM presensi p
            LEFT JOIN penempatan pen ON p.nis = pen.nis_siswa
            WHERE DATE(p.timestamp) = ? AND p.periode_id = ?
        ";
        $params = [$tanggal, $periodeId];

        if (!empty($kelas))      { $sql .= " AND p.kelas = ?";            $params[] = $kelas; }
        if (!empty($pembimbing)) { $sql .= " AND pen.nama_pembimbing = ?"; $params[] = $pembimbing; }
        $sql .= " GROUP BY p.ket";

        $rows   = $this->db->query($sql, $params);
        $result = ['masuk' => 0, 'izin' => 0, 'sakit' => 0, 'libur' => 0];
        foreach ($rows as $row) {
            $key = strtolower($row['ket']);
            if (isset($result[$key])) $result[$key] = (int)$row['total'];
        }
        return $result;
    }

    public function getRekapPerKelas(string $tanggal): array
    {
        $periodeId = $this->getPeriodeId();

        return $this->db->query("
            SELECT
                p.kelas,
                COUNT(*) as total,
                SUM(CASE WHEN LOWER(p.ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(p.ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(p.ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(p.ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM presensi p
            WHERE DATE(p.timestamp) = ? AND p.periode_id = ?
            GROUP BY p.kelas ORDER BY p.kelas ASC
        ", [$tanggal, $periodeId]);
    }

    // ==========================================
    // MINGGUAN
    // ==========================================

    public function getMingguan(string $senin, string $minggu, string $kelas = '', string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();

        $sql = "
            SELECT
                ds.nis,
                ds.nama AS namasiswa,
                ds.kelas,
                pen.nama_dudika,
                pen.nama_pembimbing,
                DATE(p.timestamp) as tanggal,
                p.ket
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            LEFT JOIN presensi p
                ON ds.nis = p.nis
                AND DATE(p.timestamp) BETWEEN ? AND ?
                AND p.periode_id = ?
            WHERE ds.periode_id = ?
        ";
        $params = [$senin, $minggu, $periodeId, $periodeId];

        if (!empty($kelas))      { $sql .= " AND ds.kelas = ?";            $params[] = $kelas; }
        if (!empty($pembimbing)) { $sql .= " AND pen.nama_pembimbing = ?"; $params[] = $pembimbing; }
        $sql .= " ORDER BY ds.nama ASC, p.timestamp ASC";

        $rows    = $this->db->query($sql, $params);
        $bySiswa = [];
        foreach ($rows as $row) {
            $nis = $row['nis'];
            if (!isset($bySiswa[$nis])) {
                $bySiswa[$nis] = [
                    'nis'             => $nis,
                    'namasiswa'       => $row['namasiswa'],
                    'kelas'           => $row['kelas'],
                    'nama_dudika'     => $row['nama_dudika'],
                    'nama_pembimbing' => $row['nama_pembimbing'],
                    'presensi'        => [],
                    'rekap_minggu'    => ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0],
                ];
            }
            if (!empty($row['tanggal']) && !empty($row['ket'])) {
                $bySiswa[$nis]['presensi'][$row['tanggal']] = $row['ket'];
                $key = strtolower($row['ket']);
                if (isset($bySiswa[$nis]['rekap_minggu'][$key])) {
                    $bySiswa[$nis]['rekap_minggu'][$key]++;
                }
            }
        }
        return array_values($bySiswa);
    }

    // ==========================================
    // BULK REKAP — filter periode aktif
    // ==========================================

    public function getRekapTotalBulk(array $nisList): array
    {
        if (empty($nisList)) return [];
        $periodeId    = $this->getPeriodeId();
        $placeholders = implode(',', array_fill(0, count($nisList), '?'));
        $params       = array_merge([$periodeId], $nisList);

        $rows = $this->db->query("
            SELECT nis,
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM presensi WHERE periode_id = ? AND nis IN ($placeholders) GROUP BY nis
        ", $params);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['nis']] = [
                'masuk' => (int)$row['masuk'], 'izin' => (int)$row['izin'],
                'sakit' => (int)$row['sakit'], 'libur' => (int)$row['libur'],
            ];
        }
        return $result;
    }

    public function getRekapBulanBulk(array $nisList): array
    {
        if (empty($nisList)) return [];
        $periodeId    = $this->getPeriodeId();
        $bulan        = date('Y-m');
        $placeholders = implode(',', array_fill(0, count($nisList), '?'));
        $params       = array_merge([$bulan . '%', $periodeId], $nisList);

        $rows = $this->db->query("
            SELECT nis,
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM presensi WHERE timestamp LIKE ? AND periode_id = ? AND nis IN ($placeholders) GROUP BY nis
        ", $params);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['nis']] = [
                'masuk' => (int)$row['masuk'], 'izin' => (int)$row['izin'],
                'sakit' => (int)$row['sakit'], 'libur' => (int)$row['libur'],
            ];
        }
        return $result;
    }

    // ==========================================
    // KALENDER — presensi siswa dalam rentang tanggal
    // (tidak filter periode — bisa dipakai untuk lihat periode lama)
    // ==========================================

    public function getPresensiKalender(string $nis, string $tanggalMulai, string $tanggalSelesai): array
    {
        $rows = $this->db->query("
            SELECT DATE(timestamp) as tanggal, ket, catatan, link, statuslink, kode, timestamp
            FROM presensi
            WHERE nis = ? AND DATE(timestamp) BETWEEN ? AND ?
            ORDER BY timestamp ASC
        ", [$nis, $tanggalMulai, $tanggalSelesai]);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['tanggal']] = $row;
        }
        return $result;
    }

    // ==========================================
    // RINGKASAN RENTANG
    // ==========================================

    public function getRingkasanRentang(string $dari, string $sampai, string $kelas = '', string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();

        $sql = "
            SELECT p.ket, COUNT(*) as total
            FROM presensi p
            LEFT JOIN penempatan pen ON p.nis = pen.nis_siswa
            WHERE DATE(p.timestamp) BETWEEN ? AND ? AND p.periode_id = ?
        ";
        $params = [$dari, $sampai, $periodeId];

        if (!empty($kelas))      { $sql .= " AND p.kelas = ?";            $params[] = $kelas; }
        if (!empty($pembimbing)) { $sql .= " AND pen.nama_pembimbing = ?"; $params[] = $pembimbing; }
        $sql .= " GROUP BY p.ket";

        $rows   = $this->db->query($sql, $params);
        $result = ['masuk' => 0, 'izin' => 0, 'sakit' => 0, 'libur' => 0];
        foreach ($rows as $row) {
            $key = strtolower($row['ket']);
            if (isset($result[$key])) $result[$key] = (int)$row['total'];
        }
        return $result;
    }

    // ==========================================
    // HELPER
    // ==========================================

    public function getListKelas(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT DISTINCT kelas FROM datasiswa
            WHERE periode_id = ? AND kelas IS NOT NULL AND kelas != ''
            ORDER BY kelas ASC
        ", [$periodeId]);
    }
}