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

    // ==========================================
    // PENEMPATAN
    // ==========================================

    /**
     * Ambil semua data penempatan + info siswa
     * Opsional filter by nama_dudika atau nama_pembimbing
     */
    public function getAll(string $dudika = '', string $pembimbing = ''): array
    {
        $sql = "
            SELECT
                p.id,
                p.nis_siswa,
                p.nama_siswa,
                p.kelas,
                p.nama_dudika,
                p.alamat_dudika,
                p.nomor_telepon_dudika,
                p.nama_pembimbing,
                ds.nohp AS nohp_siswa,
                pb.nohp AS nohp_pembimbing
            FROM penempatan p
            LEFT JOIN datasiswa ds ON p.nis_siswa = ds.nis
            LEFT JOIN datapembimbing pb ON p.nama_pembimbing = pb.nama
        ";

        $conditions = [];
        $params     = [];

        if (!empty($dudika)) {
            $conditions[] = "p.nama_dudika = ?";
            $params[]     = $dudika;
        }

        if (!empty($pembimbing)) {
            $conditions[] = "p.nama_pembimbing = ?";
            $params[]     = $pembimbing;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.nama_dudika ASC, p.nama_siswa ASC";

        return $this->db->query($sql, $params);
    }

    /**
     * Rekap per DUDIKA — total siswa, sudah/belum WA
     */
    public function getRekapPerDudika(): array
    {
        $rows = $this->db->query("
            SELECT
                p.nama_dudika,
                p.nama_pembimbing,
                pb.nohp AS nohp_pembimbing,
                COUNT(*) AS total,
                SUM(CASE WHEN ds.nohp IS NOT NULL AND ds.nohp != '' THEN 1 ELSE 0 END) AS sudah_wa,
                SUM(CASE WHEN ds.nohp IS NULL OR ds.nohp = '' THEN 1 ELSE 0 END) AS belum_wa
            FROM penempatan p
            LEFT JOIN datasiswa ds ON p.nis_siswa = ds.nis
            LEFT JOIN datapembimbing pb ON p.nama_pembimbing = pb.nama
            GROUP BY p.nama_dudika, p.nama_pembimbing, pb.nohp
            ORDER BY p.nama_dudika ASC
        ");

        return $rows;
    }

    /**
     * Ambil daftar DUDIKA unik (untuk filter dropdown)
     */
    public function getListDudika(): array
    {
        return $this->db->query("
            SELECT DISTINCT nama_dudika
            FROM penempatan
            WHERE nama_dudika IS NOT NULL AND nama_dudika != ''
            ORDER BY nama_dudika ASC
        ");
    }

    /**
     * Ambil daftar pembimbing unik (untuk filter dropdown)
     */
    public function getListPembimbing(): array
    {
        return $this->db->query("
            SELECT DISTINCT nama_pembimbing
            FROM penempatan
            WHERE nama_pembimbing IS NOT NULL AND nama_pembimbing != ''
            ORDER BY nama_pembimbing ASC
        ");
    }

    // ==========================================
    // DATADUDI
    // ==========================================

    public function getAllDudi(): array
    {
        return $this->db->query("
            SELECT d.*, COUNT(p.id) AS jumlah_siswa
            FROM datadudi d
            LEFT JOIN penempatan p ON d.nama = p.nama_dudika
            GROUP BY d.id
            ORDER BY d.nama ASC
        ");
    }

    // ==========================================
    // WALIKELAS
    // ==========================================

    public function getAllWalikelas(): array
    {
        return $this->db->query("
            SELECT * FROM datawalikelas ORDER BY kelas ASC
        ");
    }
}