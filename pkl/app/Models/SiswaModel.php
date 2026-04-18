<?php

namespace App\Models;

use App\Core\Database;

class SiswaModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getPeriodeId(): int
    {
        $p = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        return $p ? (int)$p['id'] : 0;
    }

    public function getAll(string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();
        $sql = "
            SELECT ds.nis, ds.nama, ds.kelas, ds.jur, ds.lp, ds.nohp, ds.ket,
                pen.nama_pembimbing, pen.nama_dudika, pen.alamat_dudika
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            WHERE ds.periode_id = ?
        ";
        $params = [$periodeId];
        if (!empty($pembimbing)) { $sql .= " AND pen.nama_pembimbing = ?"; $params[] = $pembimbing; }
        $sql .= " ORDER BY pen.nama_pembimbing ASC, pen.nama_dudika ASC, ds.nis ASC";
        return $this->db->query($sql, $params);
    }

    public function getByNis(string $nis): array|false
    {
        return $this->db->queryOne("
            SELECT ds.*, pen.nama_pembimbing, pen.nama_dudika, pen.alamat_dudika, pen.nomor_telepon_dudika
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            WHERE ds.nis = ? LIMIT 1
        ", [$nis]);
    }

    public function getPresensiByNis(string $nis): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp
            FROM presensi WHERE nis = ? AND periode_id = ? ORDER BY timestamp DESC
        ", [$nis, $periodeId]);
    }

    public function getRekapSiswa(string $nis): array
    {
        $periodeId = $this->getPeriodeId();
        $row = $this->db->queryOne("
            SELECT
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur,
                COUNT(*) as total
            FROM presensi WHERE nis = ? AND periode_id = ?
        ", [$nis, $periodeId]);
        return $row ?: ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0,'total'=>0];
    }

    public function getListPembimbing(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT DISTINCT pen.nama_pembimbing
            FROM penempatan pen
            INNER JOIN datasiswa ds ON ds.nis = pen.nis_siswa
            WHERE pen.nama_pembimbing IS NOT NULL AND pen.nama_pembimbing != ''
              AND ds.periode_id = ?
            ORDER BY pen.nama_pembimbing ASC
        ", [$periodeId]);
    }

    public function getRekapKelas(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT kelas, COUNT(*) AS total_siswa,
                SUM(CASE WHEN nohp IS NOT NULL AND nohp != '' THEN 1 ELSE 0 END) AS sudah_daftar,
                SUM(CASE WHEN nohp IS NULL OR nohp = '' THEN 1 ELSE 0 END) AS belum_daftar
            FROM datasiswa WHERE periode_id = ? GROUP BY kelas ORDER BY kelas ASC
        ", [$periodeId]);
    }

    public function updateNohp(string $nis, string $nohp): int
    {
        return $this->db->execute("UPDATE datasiswa SET nohp = ? WHERE nis = ?", [$nohp, $nis]);
    }

    public function updateDudika(string $nis, string $dudika): int
    {
        $exists = $this->db->queryOne("SELECT id FROM penempatan WHERE nis_siswa = ?", [$nis]);
        if ($exists) return $this->db->execute("UPDATE penempatan SET nama_dudika = ? WHERE nis_siswa = ?", [$dudika, $nis]);
        return $this->db->execute("INSERT INTO penempatan (nis_siswa, nama_dudika) VALUES (?, ?)", [$nis, $dudika]);
    }

    public function updatePembimbing(string $nis, string $pembimbing): int
    {
        $val    = $pembimbing === '' ? null : $pembimbing;
        $exists = $this->db->queryOne("SELECT id FROM penempatan WHERE nis_siswa = ?", [$nis]);
        if ($exists) return $this->db->execute("UPDATE penempatan SET nama_pembimbing = ? WHERE nis_siswa = ?", [$val, $nis]);
        return $this->db->execute("INSERT INTO penempatan (nis_siswa, nama_pembimbing) VALUES (?, ?)", [$nis, $val]);
    }

    public function deleteByNis(string $nis): int
    {
        return $this->db->execute("DELETE FROM datasiswa WHERE nis = ?", [$nis]);
    }
}