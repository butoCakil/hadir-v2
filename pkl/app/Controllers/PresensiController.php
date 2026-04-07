<?php

namespace App\Controllers;

use App\Core\Auth;
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
}