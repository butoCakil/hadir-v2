<?php

namespace App\Models;

use App\Core\Database;

class PenempatanModel
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

    public function getAll(string $dudika = '', string $pembimbing = ''): array
    {
        $periodeId = $this->getPeriodeId();
        $sql = "
            SELECT
                p.id, p.nis_siswa, p.nama_siswa, p.kelas,
                p.nama_dudika, p.alamat_dudika, p.nomor_telepon_dudika, p.nama_pembimbing,
                ds.nohp AS nohp_siswa,
                pb.nohp AS nohp_pembimbing
            FROM penempatan p
            LEFT JOIN datasiswa ds ON p.nis_siswa = ds.nis AND ds.periode_id = ?
            LEFT JOIN datapembimbing pb ON p.nama_pembimbing = pb.nama
            WHERE ds.nis IS NOT NULL
        ";
        $params = [$periodeId];

        if (!empty($dudika))     { $sql .= " AND p.nama_dudika = ?";     $params[] = $dudika; }
        if (!empty($pembimbing)) { $sql .= " AND p.nama_pembimbing = ?"; $params[] = $pembimbing; }

        $sql .= " ORDER BY p.nama_dudika ASC, p.nama_siswa ASC";
        return $this->db->query($sql, $params);
    }

    public function getRekapPerDudika(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT
                p.nama_dudika,
                p.nama_pembimbing,
                pb.nohp AS nohp_pembimbing,
                COUNT(*) AS total,
                SUM(CASE WHEN ds.nohp IS NOT NULL AND ds.nohp != '' THEN 1 ELSE 0 END) AS sudah_wa,
                SUM(CASE WHEN ds.nohp IS NULL OR ds.nohp = '' THEN 1 ELSE 0 END) AS belum_wa
            FROM penempatan p
            INNER JOIN datasiswa ds ON p.nis_siswa = ds.nis AND ds.periode_id = ?
            LEFT JOIN datapembimbing pb ON p.nama_pembimbing = pb.nama
            GROUP BY p.nama_dudika, p.nama_pembimbing, pb.nohp
            ORDER BY p.nama_dudika ASC
        ", [$periodeId]);
    }

    public function getListDudika(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT DISTINCT p.nama_dudika
            FROM penempatan p
            INNER JOIN datasiswa ds ON p.nis_siswa = ds.nis AND ds.periode_id = ?
            WHERE p.nama_dudika IS NOT NULL AND p.nama_dudika != ''
            ORDER BY p.nama_dudika ASC
        ", [$periodeId]);
    }

    public function getListPembimbing(): array
    {
        $periodeId = $this->getPeriodeId();
        return $this->db->query("
            SELECT DISTINCT p.nama_pembimbing
            FROM penempatan p
            INNER JOIN datasiswa ds ON p.nis_siswa = ds.nis AND ds.periode_id = ?
            WHERE p.nama_pembimbing IS NOT NULL AND p.nama_pembimbing != ''
            ORDER BY p.nama_pembimbing ASC
        ", [$periodeId]);
    }

    public function getAllDudi(): array
    {
        return $this->db->query("
            SELECT d.*, COUNT(p.id) AS jumlah_siswa
            FROM datadudi d
            LEFT JOIN penempatan p ON d.nama = p.nama_dudika
            GROUP BY d.id ORDER BY d.nama ASC
        ");
    }

    public function getAllWalikelas(): array
    {
        return $this->db->query("SELECT * FROM datawalikelas ORDER BY kelas ASC");
    }
}