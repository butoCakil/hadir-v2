<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;

class CekController
{
    public function index(): void
    {
        $db = Database::getInstance();

        \App\Core\Auth::start();
        $isLoggedIn = \App\Core\Auth::check();

        $pembimbing = trim($_GET['pembimbing'] ?? '');
        $kelas      = trim($_GET['kelas']      ?? '');

        // ── Filter lists ──
        $listPembimbing = $db->query(
            "SELECT DISTINCT nama_pembimbing FROM penempatan
             WHERE nama_pembimbing IS NOT NULL AND nama_pembimbing != ''
             ORDER BY nama_pembimbing ASC"
        );
        $listKelas = $db->query(
            "SELECT DISTINCT kelas FROM datasiswa
             WHERE kelas IS NOT NULL AND kelas != ''
             ORDER BY kelas ASC"
        );

        // ── Query siswa + presensi 7 hari ──
        $today   = date('Y-m-d');
        $tgl7    = date('Y-m-d', strtotime('-7 days'));

        $where  = [];
        $params = [];

        if ($pembimbing) {
            $where[]  = "p.nama_pembimbing = ?";
            $params[] = $pembimbing;
        }
        if ($kelas) {
            $where[]  = "d.kelas = ?";
            $params[] = $kelas;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $periodeAktif = $db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;
        
        // Tambah filter periode ke $where
        $where[]  = "d.periode_id = ?";
        $params[] = $periodeId;
        $whereSql = 'WHERE ' . implode(' AND ', $where);
        
        $siswaList = $db->query(
            "SELECT d.nis, d.nama, d.kelas, d.nohp,
                    p.nama_pembimbing, p.nama_dudika
             FROM datasiswa d
             LEFT JOIN penempatan p ON d.nis = p.nis_siswa
             $whereSql
             ORDER BY p.nama_pembimbing ASC, p.nama_dudika ASC, d.kelas ASC, d.nama ASC",
            $params
        );

        // ── Presensi 8 hari terakhir (bulk) ──
        $nisList = array_column($siswaList, 'nis');
        $presensiMap = [];

        if (!empty($nisList)) {
            $in = implode(',', array_fill(0, count($nisList), '?'));
            $rows = $db->query(
                "SELECT nis, DATE(timestamp) as tgl, ket, link, statuslink
                 FROM presensi
                 WHERE nis IN ($in) AND DATE(timestamp) >= ? AND periode_id = ?
                 ORDER BY timestamp ASC",
                array_merge($nisList, [$tgl7, $periodeId])
            );
            foreach ($rows as $r) {
                $presensiMap[$r['nis']][$r['tgl']] = $r;
            }
        }

        // ── Rekap total per siswa (bulk) ──
        $rekapMap = [];
        if (!empty($nisList)) {
            $in = implode(',', array_fill(0, count($nisList), '?'));
            $rows = $db->query(
                "SELECT nis,
                    SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                    SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
                 FROM presensi WHERE nis IN ($in) AND periode_id = ? GROUP BY nis",
                array_merge($nisList, [$periodeId])
            );
            foreach ($rows as $r) {
                $rekapMap[$r['nis']] = $r;
            }
        }

        // ── Tanggal 8 hari (header kolom) ──
        $hariKolom = [];
        for ($i = 7; $i >= 0; $i--) {
            $hariKolom[] = date('Y-m-d', strtotime("-$i days"));
        }

        // ── Stat ringkas ──
        $totalSiswa  = count($siswaList);
        $sudahHariIni = 0;
        foreach ($siswaList as $s) {
            if (isset($presensiMap[$s['nis']][$today])) $sudahHariIni++;
        }

        Response::view('cek/index', [
            'title'          => 'Cek Presensi PKL',
            'siswaList'      => $siswaList,
            'presensiMap'    => $presensiMap,
            'rekapMap'       => $rekapMap,
            'hariKolom'      => $hariKolom,
            'today'          => $today,
            'listPembimbing' => $listPembimbing,
            'listKelas'      => $listKelas,
            'filterPembimbing' => $pembimbing,
            'filterKelas'    => $kelas,
            'totalSiswa'     => $totalSiswa,
            'sudahHariIni'   => $sudahHariIni,
            'isLoggedIn'     => $isLoggedIn,
        ]);
    }

    public function detail(array $params): void
    {
        $nis = $params['nis'] ?? '';
        $db  = Database::getInstance();

        \App\Core\Auth::start();
        $isLoggedIn = \App\Core\Auth::check();

        // ── Data siswa ──
        $siswa = $db->queryOne(
            "SELECT d.*, p.nama_dudika, p.alamat_dudika, p.nama_pembimbing,
                    pb.nohp as nohp_pembimbing
             FROM datasiswa d
             LEFT JOIN penempatan p ON d.nis = p.nis_siswa
             LEFT JOIN datapembimbing pb ON p.nama_pembimbing = pb.nama
             WHERE d.nis = ? LIMIT 1",
            [$nis]
        );

        if (!$siswa) {
            Response::abort(404);
            return;
        }

        // ── Periode PKL ──
        $periode = $db->queryOne(
            "SELECT * FROM periode_pkl WHERE aktif = 1 LIMIT 1"
        );
        $tanggalMulai = $periode['tanggal_mulai'] ?? date('Y-m-01', strtotime('-3 months'));
        $tanggalAkhir = $periode['tanggal_selesai'] ?? date('Y-m-d');
        $namaPeriode  = $periode['nama_periode'] ?? 'Periode PKL';

        // ── Rekap total ──
        $rekap = $db->queryOne(
            "SELECT
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
             FROM presensi WHERE nis = ?",
            [$nis]
        ) ?: ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];

        // ── Presensi untuk kalender ──
        $presensiRows = $db->query(
            "SELECT DATE(timestamp) as tgl, ket, catatan, link, statuslink
             FROM presensi WHERE nis = ? ORDER BY timestamp ASC",
            [$nis]
        );
        $presensiKalender = [];
        foreach ($presensiRows as $r) {
            $presensiKalender[$r['tgl']] = $r;
        }

        // ── Bulan-bulan dalam periode ──
        $bulanPeriode = [];
        $cur = strtotime(date('Y-m-01', strtotime($tanggalMulai)));
        $end = strtotime(date('Y-m-01', strtotime($tanggalAkhir)));
        while ($cur <= $end) {
            $bulanPeriode[] = date('Y-m', $cur);
            $cur = strtotime('+1 month', $cur);
        }

        // ── Tabel riwayat ──
        $presensiTabel = $db->query(
            "SELECT * FROM presensi WHERE nis = ? ORDER BY timestamp DESC",
            [$nis]
        );

        // ── Presensi hari ini ──
        $today   = date('Y-m-d');
        $hariIni = null;
        if ($today >= $tanggalMulai && $today <= $tanggalAkhir) {
            $hariIni = $db->queryOne(
                "SELECT ket, timestamp FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
                [$nis, $today]
            );
        }

        $viewMode = $_GET['view'] ?? 'kalender';

        Response::view('cek/detail', [
            'title'            => 'Detail Presensi — ' . $siswa['nama'],
            'siswa'            => $siswa,
            'rekap'            => $rekap,
            'presensiKalender' => $presensiKalender,
            'presensiTabel'    => $presensiTabel,
            'bulanPeriode'     => $bulanPeriode,
            'tanggalMulai'     => $tanggalMulai,
            'tanggalAkhir'     => $tanggalAkhir,
            'namaPeriode'      => $namaPeriode,
            'periode'          => $periode,
            'viewMode'         => $viewMode,
            'hariIni'          => $hariIni,
            'isLoggedIn'       => $isLoggedIn,
        ]);
    }
}