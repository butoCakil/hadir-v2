<?php
namespace App\Controllers;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\PresensiModel;
use App\Models\SiswaModel;

class PresensiController
{
    private PresensiModel $model;
    private SiswaModel $siswaModel;

    public function __construct()
    {
        $this->model      = new PresensiModel();
        $this->siswaModel = new SiswaModel();
    }

    public function index(): void
    {
        Auth::required();

        $today      = date('Y-m-d');
        $bulanLabel = date('F Y');
        $mode       = Request::get('mode', 'harian');
        $kelas      = Request::get('kelas', '');
        $pembimbing = Request::get('pembimbing', '');
        $tanggal    = Request::get('tanggal', $today);

        // Hitung Senin minggu aktif
        $weekRef  = Request::get('week', $today);
        $weekTs   = strtotime($weekRef);
        $dow      = (int) date('N', $weekTs);
        $senin    = date('Y-m-d', strtotime("-" . ($dow - 1) . " days", $weekTs));
        $minggu   = date('Y-m-d', strtotime("+6 days", strtotime($senin)));
        $prevWeek = date('Y-m-d', strtotime("-7 days", strtotime($senin)));
        $nextWeek = date('Y-m-d', strtotime("+7 days", strtotime($senin)));

        $hariMinggu = [];
        for ($i = 0; $i < 7; $i++) {
            $hariMinggu[] = date('Y-m-d', strtotime("+$i days", strtotime($senin)));
        }

        $listKelas      = $this->model->getListKelas();
        $listPembimbing = $this->siswaModel->getListPembimbing();

        if ($mode === 'mingguan') {
            $presensiMingguan = $this->model->getMingguan($senin, $minggu, $kelas, $pembimbing);
            $ringkasan        = $this->model->getRingkasanRentang($senin, $minggu, $kelas, $pembimbing);

            // Bulk rekap — 2 query untuk semua siswa
            $nisList      = array_column($presensiMingguan, 'nis');
            $rekapTotal   = $this->model->getRekapTotalBulk($nisList);
            $rekapBulan   = $this->model->getRekapBulanBulk($nisList);

            // Gabungkan ke data siswa
            foreach ($presensiMingguan as &$s) {
                $s['rekap_total'] = $rekapTotal[$s['nis']] ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
                $s['rekap_bulan'] = $rekapBulan[$s['nis']] ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
            }
            unset($s);

            $presensiHarian  = [];
            $ringkasanHarian = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
            $rekapKelas      = [];
        } else {
            $mode             = 'harian';
            $presensiHarian   = $this->model->getHarian($tanggal, $kelas, $pembimbing);
            $ringkasanHarian  = $this->model->getRingkasanHarian($tanggal, $kelas, $pembimbing);
            $rekapKelas       = $this->model->getRekapPerKelas($tanggal);
            $presensiMingguan = [];
            $ringkasan        = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
            $rekapTotal       = [];
            $rekapBulan       = [];
        }

        Response::view('presensi/index', [
            'title'            => 'Data Presensi',
            'user'             => Auth::user(),
            'mode'             => $mode,
            'today'            => $today,
            'bulanLabel'       => $bulanLabel,
            'tanggal'          => $tanggal,
            'presensiHarian'   => $presensiHarian,
            'ringkasanHarian'  => $ringkasanHarian,
            'rekapKelas'       => $rekapKelas,
            'senin'            => $senin,
            'minggu'           => $minggu,
            'prevWeek'         => $prevWeek,
            'nextWeek'         => $nextWeek,
            'hariMinggu'       => $hariMinggu,
            'presensiMingguan' => $presensiMingguan,
            'ringkasan'        => $ringkasan,
            'kelas'            => $kelas,
            'pembimbing'       => $pembimbing,
            'listKelas'        => $listKelas,
            'listPembimbing'   => $listPembimbing,
        ]);
    }
    
    // ==========================================
    // POST /presensi/input
    // ==========================================
    public function input(): void
    {
        Auth::required();
    
        $nis        = trim($_POST['nis']        ?? '');
        $tanggalDari= trim($_POST['tanggal_dari'] ?? '');
        $tanggalSampai = trim($_POST['tanggal_sampai'] ?? '');
        $ket        = trim($_POST['ket']        ?? '');
        $catatan    = trim($_POST['catatan']    ?? '');
        
        if (!$nis || !$tanggalDari || !$tanggalSampai || !$ket) {
            Response::error('Data tidak lengkap.', 400); return;
        }
        if ($tanggalSampai < $tanggalDari) {
            Response::error('Tanggal akhir tidak boleh sebelum tanggal awal.', 400); return;
        }
        
        $validKet = ['Masuk','Izin','Sakit','Libur'];
        if (!in_array($ket, $validKet)) {
            Response::error('Keterangan tidak valid.', 400); return;
        }
        
        $db = Database::getInstance();
        
        $siswa = $db->queryOne(
            "SELECT nis, nama, kelas FROM datasiswa WHERE nis = ? LIMIT 1", [$nis]
        );
        if (!$siswa) {
            Response::error('Siswa tidak ditemukan.', 404); return;
        }
        
        $periodeAktif = $db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : null;
        
        // Loop setiap tanggal dalam range
        $inserted = 0;
        $skipped  = [];
        $cur      = strtotime($tanggalDari);
        $end      = strtotime($tanggalSampai);
        
        while ($cur <= $end) {
            $tgl = date('Y-m-d', $cur);
        
            $sudah = $db->queryOne(
                "SELECT id FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
                [$nis, $tgl]
            );
        
            if (!$sudah) {
                $db->query(
                    "INSERT INTO presensi (periode_id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp)
                     VALUES (?, ?, ?, ?, ?, ?, '', '', 'ADMIN', ?)",
                    [$periodeId, $nis, $siswa['nama'], $siswa['kelas'], $ket, $catatan, $tgl . ' ' . date('H:i:s')]
                );
                $inserted++;
            } else {
                $skipped[] = date('d/m', $cur);
            }
        
            $cur = strtotime('+1 day', $cur);
        }
        
        $pesan = "$inserted presensi berhasil disimpan.";
        if (!empty($skipped)) {
            $pesan .= ' Dilewati (sudah ada): ' . implode(', ', $skipped) . '.';
        }
        
        Response::success(['inserted' => $inserted, 'skipped' => $skipped], $pesan);
    }
    
    // ==========================================
    // POST /presensi/edit
    // ==========================================
    public function edit(): void
    {
        Auth::required();
    
        $id      = (int)($_POST['id']      ?? 0);
        $ket     = trim($_POST['ket']      ?? '');
        $catatan = trim($_POST['catatan']  ?? '');
    
        if (!$id || !$ket) {
            Response::error('Data tidak lengkap.', 400); return;
        }
    
        $validKet = ['Masuk','Izin','Sakit','Libur'];
        if (!in_array($ket, $validKet)) {
            Response::error('Keterangan tidak valid.', 400); return;
        }
    
        $db = Database::getInstance();
    
        $presensi = $db->queryOne("SELECT id FROM presensi WHERE id = ?", [$id]);
        if (!$presensi) {
            Response::error('Data presensi tidak ditemukan.', 404); return;
        }
    
        $db->query(
            "UPDATE presensi SET ket = ?, catatan = ? WHERE id = ?",
            [$ket, $catatan, $id]
        );
    
        Response::success(['ket' => $ket, 'catatan' => $catatan], 'Presensi berhasil diperbarui.');
    }
    
    // ==========================================
    // POST /presensi/hapus
    // ==========================================
    public function hapus(): void
    {
        Auth::required();
    
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            Response::error('ID tidak valid.', 400); return;
        }
    
        $db = Database::getInstance();
    
        $presensi = $db->queryOne("SELECT id, link FROM presensi WHERE id = ?", [$id]);
        if (!$presensi) {
            Response::error('Data presensi tidak ditemukan.', 404); return;
        }
    
        $db->query("DELETE FROM presensi WHERE id = ?", [$id]);
    
        Response::success([], 'Presensi berhasil dihapus.');
    }
}