<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

class ArsipController
{
    private Database $db;

    public function __construct()
    {
        Auth::required();
        $this->db = Database::getInstance();
    }

    // Helper — ambil periode by id, pastikan bukan periode aktif
    private function getPeriode(int $id): array
    {
        $periode = $this->db->queryOne(
            "SELECT * FROM periode_pkl WHERE id = ?", [$id]
        );
        if (!$periode) Response::abort(404);
        return $periode;
    }

    // ==========================================
    // GET /arsip
    // Daftar semua periode
    // ==========================================
    public function index(): void
    {
        $periodeList = $this->db->query("
            SELECT p.*,
                (SELECT COUNT(*) FROM datasiswa WHERE periode_id = p.id) as total_siswa,
                (SELECT COUNT(*) FROM presensi WHERE periode_id = p.id) as total_presensi
            FROM periode_pkl p
            ORDER BY p.tanggal_mulai DESC
        ");

        Response::view('arsip/index', [
            'title'       => 'Arsip Periode PKL',
            'user'        => Auth::user(),
            'periodeList' => $periodeList,
        ]);
    }

    // ==========================================
    // GET /arsip/{id}
    // Dashboard ringkasan periode
    // ==========================================
    public function dashboard(array $params): void
    {
        $id      = (int)($params['id'] ?? 0);
        $periode = $this->getPeriode($id);

        $mulai   = $periode['tanggal_mulai'];
        $selesai = $periode['tanggal_selesai'];

        // Stat cards
        $totalSiswa  = (int)($this->db->queryOne(
            "SELECT COUNT(*) as n FROM datasiswa WHERE periode_id = ?", [$id]
        )['n'] ?? 0);
        $totalDudika = (int)($this->db->queryOne(
            "SELECT COUNT(DISTINCT p.nama_dudika) as n FROM penempatan p
             INNER JOIN datasiswa ds ON ds.nis = p.nis_siswa AND ds.periode_id = ?", [$id]
        )['n'] ?? 0);
        $totalPembimbing = (int)($this->db->queryOne(
            "SELECT COUNT(DISTINCT p.nama_pembimbing) as n FROM penempatan p
             INNER JOIN datasiswa ds ON ds.nis = p.nis_siswa AND ds.periode_id = ?", [$id]
        )['n'] ?? 0);
        $totalPresensi = (int)($this->db->queryOne(
            "SELECT COUNT(*) as n FROM presensi WHERE periode_id = ?", [$id]
        )['n'] ?? 0);

        // Rekap kehadiran keseluruhan
        $rekapRow = $this->db->queryOne("
            SELECT
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM presensi WHERE periode_id = ?
        ", [$id]);
        $rekap = $rekapRow ?: ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];

        // Rekap per kelas
        $rekapKelas = $this->db->query("
            SELECT ds.kelas,
                COUNT(DISTINCT ds.nis) as total_siswa,
                SUM(CASE WHEN LOWER(p.ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(p.ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(p.ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(p.ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM datasiswa ds
            LEFT JOIN presensi p ON ds.nis = p.nis AND p.periode_id = ?
            WHERE ds.periode_id = ?
            GROUP BY ds.kelas ORDER BY ds.kelas ASC
        ", [$id, $id]);

        // Chart presensi per bulan
        $chartData   = [];
        $chartLabels = [];
        $cur = strtotime(date('Y-m-01', strtotime($mulai)));
        $end = strtotime(date('Y-m-01', strtotime($selesai)));
        $namaBulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei',
                      '06'=>'Jun','07'=>'Jul','08'=>'Ags','09'=>'Sep','10'=>'Okt',
                      '11'=>'Nov','12'=>'Des'];
        while ($cur <= $end) {
            $ym    = date('Y-m', $cur);
            $n     = (int)($this->db->queryOne(
                "SELECT COUNT(*) as n FROM presensi WHERE periode_id = ? AND DATE_FORMAT(timestamp, '%Y-%m') = ?",
                [$id, $ym]
            )['n'] ?? 0);
            $chartLabels[] = $namaBulan[date('m', $cur)] . ' ' . date('Y', $cur);
            $chartData[]   = $n;
            $cur = strtotime('+1 month', $cur);
        }

        Response::view('arsip/dashboard', [
            'title'          => 'Arsip — ' . $periode['nama_periode'],
            'user'           => Auth::user(),
            'periode'        => $periode,
            'totalSiswa'     => $totalSiswa,
            'totalDudika'    => $totalDudika,
            'totalPembimbing'=> $totalPembimbing,
            'totalPresensi'  => $totalPresensi,
            'rekap'          => $rekap,
            'rekapKelas'     => $rekapKelas,
            'chartLabels'    => $chartLabels,
            'chartData'      => $chartData,
        ]);
    }

    // ==========================================
    // GET /arsip/{id}/siswa
    // Daftar siswa + penempatan periode ini
    // ==========================================
    public function siswa(array $params): void
    {
        $id      = (int)($params['id'] ?? 0);
        $periode = $this->getPeriode($id);

        $kelas      = trim($_GET['kelas']      ?? '');
        $pembimbing = trim($_GET['pembimbing'] ?? '');

        $sql = "
            SELECT ds.nis, ds.nama, ds.kelas, ds.nohp,
                   pen.nama_dudika, pen.nama_pembimbing,
                   SUM(CASE WHEN LOWER(pr.ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                   SUM(CASE WHEN LOWER(pr.ket)='izin'  THEN 1 ELSE 0 END) as izin,
                   SUM(CASE WHEN LOWER(pr.ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                   SUM(CASE WHEN LOWER(pr.ket)='libur' THEN 1 ELSE 0 END) as libur
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            LEFT JOIN presensi pr ON ds.nis = pr.nis AND pr.periode_id = ?
            WHERE ds.periode_id = ?
        ";
        $sqlParams = [$id, $id];

        if ($kelas)      { $sql .= " AND ds.kelas = ?";            $sqlParams[] = $kelas; }
        if ($pembimbing) { $sql .= " AND pen.nama_pembimbing = ?"; $sqlParams[] = $pembimbing; }

        $sql .= " GROUP BY ds.nis, ds.nama, ds.kelas, ds.nohp, pen.nama_dudika, pen.nama_pembimbing";
        $sql .= " ORDER BY pen.nama_pembimbing ASC, ds.kelas ASC, ds.nama ASC";

        $siswaList = $this->db->query($sql, $sqlParams);

        $listKelas = $this->db->query(
            "SELECT DISTINCT kelas FROM datasiswa WHERE periode_id = ? ORDER BY kelas", [$id]
        );
        $listPembimbing = $this->db->query(
            "SELECT DISTINCT pen.nama_pembimbing FROM penempatan pen
             INNER JOIN datasiswa ds ON ds.nis = pen.nis_siswa AND ds.periode_id = ?
             WHERE pen.nama_pembimbing IS NOT NULL AND pen.nama_pembimbing != ''
             ORDER BY pen.nama_pembimbing", [$id]
        );

        Response::view('arsip/siswa', [
            'title'           => 'Siswa — ' . $periode['nama_periode'],
            'user'            => Auth::user(),
            'periode'         => $periode,
            'siswaList'       => $siswaList,
            'listKelas'       => $listKelas,
            'listPembimbing'  => $listPembimbing,
            'filterKelas'     => $kelas,
            'filterPembimbing'=> $pembimbing,
        ]);
    }

    // ==========================================
    // GET /arsip/{id}/siswa/{nis}
    // Detail presensi siswa (read-only)
    // ==========================================
    public function detailSiswa(array $params): void
    {
        $id      = (int)($params['id']  ?? 0);
        $nis     = $params['nis'] ?? '';
        $periode = $this->getPeriode($id);

        $siswa = $this->db->queryOne("
            SELECT ds.*, pen.nama_dudika, pen.alamat_dudika,
                   pen.nama_pembimbing, pen.nomor_telepon_dudika,
                   pb.nohp as nohp_pembimbing
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            LEFT JOIN datapembimbing pb ON pen.nama_pembimbing = pb.nama
            WHERE ds.nis = ? AND ds.periode_id = ? LIMIT 1
        ", [$nis, $id]);

        if (!$siswa) Response::abort(404);

        $rekap = $this->db->queryOne("
            SELECT
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur,
                COUNT(*) as total
            FROM presensi WHERE nis = ? AND periode_id = ?
        ", [$nis, $id]) ?: ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0,'total'=>0];

        // Presensi untuk kalender
        $presensiRows = $this->db->query("
            SELECT DATE(timestamp) as tgl, ket, catatan, link, statuslink, kode, timestamp
            FROM presensi WHERE nis = ? AND periode_id = ? ORDER BY timestamp ASC
        ", [$nis, $id]);
        $presensiKalender = [];
        foreach ($presensiRows as $r) {
            $presensiKalender[$r['tgl']] = $r;
        }

        // Tabel riwayat
        $presensiTabel = $this->db->query("
            SELECT * FROM presensi WHERE nis = ? AND periode_id = ? ORDER BY timestamp DESC
        ", [$nis, $id]);

        // Bulan dalam periode
        $bulanPeriode = [];
        $cur = strtotime(date('Y-m-01', strtotime($periode['tanggal_mulai'])));
        $end = strtotime(date('Y-m-01', strtotime($periode['tanggal_selesai'])));
        while ($cur <= $end) {
            $bulanPeriode[] = date('Y-m', $cur);
            $cur = strtotime('+1 month', $cur);
        }

        $viewMode = $_GET['view'] ?? 'kalender';

        Response::view('arsip/detail_siswa', [
            'title'            => 'Detail — ' . $siswa['nama'],
            'user'             => Auth::user(),
            'periode'          => $periode,
            'siswa'            => $siswa,
            'rekap'            => $rekap,
            'presensiKalender' => $presensiKalender,
            'presensiTabel'    => $presensiTabel,
            'bulanPeriode'     => $bulanPeriode,
            'tanggalMulai'     => $periode['tanggal_mulai'],
            'tanggalAkhir'     => $periode['tanggal_selesai'],
            'namaPeriode'      => $periode['nama_periode'],
            'viewMode'         => $viewMode,
        ]);
    }

    // ==========================================
    // GET /arsip/{id}/rekap
    // Halaman rekap print-friendly
    // ==========================================
    public function rekap(array $params): void
    {
        $id      = (int)($params['id'] ?? 0);
        $periode = $this->getPeriode($id);

        $filterKelas      = trim($_GET['kelas']      ?? '');
        $filterPembimbing = trim($_GET['pembimbing'] ?? '');
        $filterDudika     = trim($_GET['dudika']     ?? '');
        $filterNis        = trim($_GET['nis']        ?? '');
        $filterBulan      = trim($_GET['bulan']      ?? ''); // format Y-m

        // Tentukan rentang tanggal
        if ($filterBulan) {
            $mulai   = $filterBulan . '-01';
            $selesai = date('Y-m-t', strtotime($mulai));
        } else {
            $mulai   = $periode['tanggal_mulai'];
            $selesai = $periode['tanggal_selesai'];
        }

        // Query siswa
        $sql = "
            SELECT ds.nis, ds.nama, ds.kelas,
                   pen.nama_dudika, pen.nama_pembimbing
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            WHERE ds.periode_id = ?
        ";
        $sqlParams = [$id];
        if ($filterKelas)      { $sql .= " AND ds.kelas = ?";            $sqlParams[] = $filterKelas; }
        if ($filterPembimbing) { $sql .= " AND pen.nama_pembimbing = ?"; $sqlParams[] = $filterPembimbing; }
        if ($filterDudika)     { $sql .= " AND pen.nama_dudika = ?";     $sqlParams[] = $filterDudika; }
        if ($filterNis)        { $sql .= " AND ds.nis = ?";              $sqlParams[] = $filterNis; }
        $sql .= " ORDER BY pen.nama_pembimbing ASC, ds.kelas ASC, ds.nama ASC";

        $siswaList = $this->db->query($sql, $sqlParams);

        // Hitung jumlah hari dalam rentang
        $jumlahHari = (int)((strtotime($selesai) - strtotime($mulai)) / 86400) + 1;
        // Batasi max 31 hari untuk layout tabel
        $jumlahHari = min($jumlahHari, 31);

        // Presensi bulk
        $nisList = array_column($siswaList, 'nis');
        $rekapData = [];
        if (!empty($nisList)) {
            $in   = implode(',', array_fill(0, count($nisList), '?'));
            $rows = $this->db->query("
                SELECT nis, DATE(timestamp) as tgl, ket
                FROM presensi
                WHERE periode_id = ? AND nis IN ($in)
                  AND DATE(timestamp) BETWEEN ? AND ?
                ORDER BY timestamp ASC
            ", array_merge([$id], $nisList, [$mulai, $selesai]));

            foreach ($rows as $r) {
                $rekapData[$r['nis']][$r['tgl']] = strtoupper(substr($r['ket'], 0, 1));
            }
        }

        // Filter lists untuk form
        $listKelas = $this->db->query(
            "SELECT DISTINCT kelas FROM datasiswa WHERE periode_id = ? ORDER BY kelas", [$id]
        );
        $listPembimbing = $this->db->query(
            "SELECT DISTINCT pen.nama_pembimbing FROM penempatan pen
             INNER JOIN datasiswa ds ON ds.nis = pen.nis_siswa AND ds.periode_id = ?
             WHERE pen.nama_pembimbing IS NOT NULL ORDER BY pen.nama_pembimbing", [$id]
        );
        $listDudika = $this->db->query(
            "SELECT DISTINCT pen.nama_dudika FROM penempatan pen
             INNER JOIN datasiswa ds ON ds.nis = pen.nis_siswa AND ds.periode_id = ?
             WHERE pen.nama_dudika IS NOT NULL ORDER BY pen.nama_dudika", [$id]
        );

        Response::view('arsip/rekap', [
            'title'            => 'Rekap — ' . $periode['nama_periode'],
            'user'             => Auth::user(),
            'periode'          => $periode,
            'siswaList'        => $siswaList,
            'rekapData'        => $rekapData,
            'mulai'            => $mulai,
            'selesai'          => $selesai,
            'jumlahHari'       => $jumlahHari,
            'filterKelas'      => $filterKelas,
            'filterPembimbing' => $filterPembimbing,
            'filterDudika'     => $filterDudika,
            'filterNis'        => $filterNis,
            'filterBulan'      => $filterBulan,
            'listKelas'        => $listKelas,
            'listPembimbing'   => $listPembimbing,
            'listDudika'       => $listDudika,
        ]);
    }

    // ==========================================
    // GET /arsip/{id}/rekap/export-excel
    // ==========================================
    public function exportExcel(array $params): void
    {
        $id      = (int)($params['id'] ?? 0);
        $periode = $this->getPeriode($id);

        $autoload = '/home/dvttaulx/public_html/dist/excel/vendor/autoload.php';
        if (!file_exists($autoload)) { http_response_code(500); echo 'PhpSpreadsheet tidak ditemukan.'; return; }
        require_once $autoload;

        $filterKelas      = trim($_GET['kelas']      ?? '');
        $filterPembimbing = trim($_GET['pembimbing'] ?? '');
        $filterDudika     = trim($_GET['dudika']     ?? '');
        $filterNis        = trim($_GET['nis']        ?? '');
        $filterBulan      = trim($_GET['bulan']      ?? '');

        if ($filterBulan) {
            $mulai   = $filterBulan . '-01';
            $selesai = date('Y-m-t', strtotime($mulai));
        } else {
            $mulai   = $periode['tanggal_mulai'];
            $selesai = $periode['tanggal_selesai'];
        }

        // Query siswa
        $sql = "
            SELECT ds.nis, ds.nama, ds.kelas, pen.nama_dudika, pen.nama_pembimbing
            FROM datasiswa ds
            LEFT JOIN penempatan pen ON ds.nis = pen.nis_siswa
            WHERE ds.periode_id = ?
        ";
        $sqlParams = [$id];
        if ($filterKelas)      { $sql .= " AND ds.kelas = ?";            $sqlParams[] = $filterKelas; }
        if ($filterPembimbing) { $sql .= " AND pen.nama_pembimbing = ?"; $sqlParams[] = $filterPembimbing; }
        if ($filterDudika)     { $sql .= " AND pen.nama_dudika = ?";     $sqlParams[] = $filterDudika; }
        if ($filterNis)        { $sql .= " AND ds.nis = ?";              $sqlParams[] = $filterNis; }
        $sql .= " ORDER BY pen.nama_pembimbing ASC, ds.kelas ASC, ds.nama ASC";
        $siswaList = $this->db->query($sql, $sqlParams);

        // Presensi bulk
        $nisList   = array_column($siswaList, 'nis');
        $rekapData = [];
        if (!empty($nisList)) {
            $in   = implode(',', array_fill(0, count($nisList), '?'));
            $rows = $this->db->query("
                SELECT nis, DATE(timestamp) as tgl, ket
                FROM presensi
                WHERE periode_id = ? AND nis IN ($in)
                  AND DATE(timestamp) BETWEEN ? AND ?
                ORDER BY timestamp ASC
            ", array_merge([$id], $nisList, [$mulai, $selesai]));
            foreach ($rows as $r) {
                $rekapData[$r['nis']][$r['tgl']] = strtoupper(substr($r['ket'], 0, 1));
            }
        }

        // Generate tanggal kolom
        $tanggalKolom = [];
        $cur = strtotime($mulai);
        $end = strtotime($selesai);
        while ($cur <= $end) {
            $tanggalKolom[] = date('Y-m-d', $cur);
            $cur = strtotime('+1 day', $cur);
        }

        // Build spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        $namaBulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                          '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                          '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

        $judulPeriode = $filterBulan
            ? $namaBulanIndo[date('m', strtotime($mulai))] . ' ' . date('Y', strtotime($mulai))
            : $periode['nama_periode'];

        // Baris 1 — Judul
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + count($tanggalKolom) + 3);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->setCellValue('A1', 'REKAP PRESENSI PKL — SMK NEGERI BANSARI');
        $sheet->getStyle('A1')->applyFromArray(['font'=>['bold'=>true,'size'=>13],'alignment'=>['horizontal'=>'center']]);

        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->setCellValue('A2', $judulPeriode . ($filterKelas ? " | Kelas $filterKelas" : '') . ($filterPembimbing ? " | Pembimbing: $filterPembimbing" : '') . ($filterDudika ? " | DUDIKA: $filterDudika" : ''));
        $sheet->getStyle('A2')->applyFromArray(['font'=>['italic'=>true,'size'=>10],'alignment'=>['horizontal'=>'center']]);

        // Baris 3 — Header
        $hStyle = ['font'=>['bold'=>true,'color'=>['argb'=>'FFE2E8F0']],'fill'=>['fillType'=>'solid','startColor'=>['argb'=>'FF1E3A5F']],'alignment'=>['horizontal'=>'center']];
        $headers = ['No','NIS','Nama Siswa','Kelas','Pembimbing','DUDIKA'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '3', $h);
            $sheet->getStyle($col . '3')->applyFromArray($hStyle);
        }
        // Header tanggal
        foreach ($tanggalKolom as $ti => $tgl) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $ti);
            $sheet->setCellValue($col . '3', date('d', strtotime($tgl)));
            $sheet->getStyle($col . '3')->applyFromArray($hStyle);
        }
        // Header rekap M/I/S/L
        $rekapCols = ['M','I','S','L'];
        foreach ($rekapCols as $ri => $rc) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + count($tanggalKolom) + $ri);
            $sheet->setCellValue($col . '3', $rc);
            $sheet->getStyle($col . '3')->applyFromArray($hStyle);
        }

        // Data rows
        $colorMap = ['M'=>'FFD1FAE5','I'=>'FFFEF9C3','S'=>'FFFEE2E2','L'=>'FFE0E7FF'];
        $no = 1;
        foreach ($siswaList as $row => $s) {
            $r       = $row + 4;
            $masuk   = $izin = $sakit = $libur = 0;
            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, $s['nis']);
            $sheet->setCellValue('C' . $r, $s['nama']);
            $sheet->setCellValue('D' . $r, $s['kelas']);
            $sheet->setCellValue('E' . $r, $s['nama_pembimbing']);
            $sheet->setCellValue('F' . $r, $s['nama_dudika']);

            foreach ($tanggalKolom as $ti => $tgl) {
                $ket = $rekapData[$s['nis']][$tgl] ?? '';
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $ti);
                $sheet->setCellValue($col . $r, $ket);
                if ($ket && isset($colorMap[$ket])) {
                    $sheet->getStyle($col . $r)->applyFromArray(['fill'=>['fillType'=>'solid','startColor'=>['argb'=>$colorMap[$ket]]]]);
                }
                if ($ket === 'M') $masuk++;
                elseif ($ket === 'I') $izin++;
                elseif ($ket === 'S') $sakit++;
                elseif ($ket === 'L') $libur++;
            }

            $baseRekap = 7 + count($tanggalKolom);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseRekap)     . $r, $masuk);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseRekap + 1) . $r, $izin);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseRekap + 2) . $r, $sakit);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseRekap + 3) . $r, $libur);

            // Stripe rows
            $bg = ($row % 2 === 0) ? 'FFFFFFFF' : 'FFF8FAFC';
            $sheet->getStyle('A'.$r.':'.'F'.$r)->applyFromArray(['fill'=>['fillType'=>'solid','startColor'=>['argb'=>$bg]]]);
        }

        // Auto width kolom tetap
        foreach (['A'=>6,'B'=>12,'C'=>28,'D'=>12,'E'=>24,'F'=>24] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        // Lebar kolom tanggal
        for ($ti = 0; $ti < count($tanggalKolom); $ti++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $ti);
            $sheet->getColumnDimension($col)->setWidth(4);
        }

        $namaFile = 'Rekap_PKL_' . preg_replace('/[^a-zA-Z0-9]/', '_', $judulPeriode) . '_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $namaFile . '"');
        header('Cache-Control: max-age=0');
        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        exit;
    }
}