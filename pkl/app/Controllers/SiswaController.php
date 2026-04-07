<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\SiswaModel;
use App\Models\PresensiModel;

class SiswaController
{
    private SiswaModel $model;
    private PresensiModel $presensiModel;

    public function __construct()
    {
        $this->model         = new SiswaModel();
        $this->presensiModel = new PresensiModel();
    }

    public function index(): void
    {
        Auth::required();
        $pembimbing     = Request::get('pembimbing', '');
        $siswa          = $this->model->getAll($pembimbing);
        $listPembimbing = $this->model->getListPembimbing();
        $rekapKelas     = $this->model->getRekapKelas();

        Response::view('siswa/index', [
            'title'            => 'Data Siswa',
            'user'             => Auth::user(),
            'siswa'            => $siswa,
            'listPembimbing'   => $listPembimbing,
            'rekapKelas'       => $rekapKelas,
            'filterPembimbing' => $pembimbing,
        ]);
    }

    public function detail(array $params): void
    {
        Auth::required();

        $nis   = $params['nis'] ?? '';
        $siswa = $this->model->getByNis($nis);
        if (!$siswa) Response::abort(404);

        $viewMode = Request::get('view', 'kalender'); // kalender | tabel

        // Ambil periode aktif
        $periode      = $this->presensiModel->getPeriodeAktif();
        $tanggalMulai = $periode ? $periode['tanggal_mulai'] : date('Y-m-01');
        $tanggalAkhir = $periode ? $periode['tanggal_selesai'] : date('Y-m-t');
        $namaPeriode  = $periode ? $periode['nama_periode'] : 'Periode PKL';

        // Data presensi
        $presensiKalender = $this->presensiModel->getPresensiKalender($nis, $tanggalMulai, $tanggalAkhir);
        $presensiTabel    = $this->model->getPresensiByNis($nis);
        $rekap            = $this->model->getRekapSiswa($nis);

        // Generate array bulan dalam periode
        $bulanPeriode = [];
        $cur = strtotime(date('Y-m-01', strtotime($tanggalMulai)));
        $end = strtotime(date('Y-m-01', strtotime($tanggalAkhir)));
        while ($cur <= $end) {
            $bulanPeriode[] = date('Y-m', $cur);
            $cur = strtotime('+1 month', $cur);
        }

        Response::view('siswa/detail', [
            'title'            => 'Detail — ' . $siswa['nama'],
            'user'             => Auth::user(),
            'siswa'            => $siswa,
            'viewMode'         => $viewMode,
            'periode'          => $periode,
            'namaPeriode'      => $namaPeriode,
            'tanggalMulai'     => $tanggalMulai,
            'tanggalAkhir'     => $tanggalAkhir,
            'bulanPeriode'     => $bulanPeriode,
            'presensiKalender' => $presensiKalender,
            'presensiTabel'    => $presensiTabel,
            'rekap'            => $rekap,
        ]);
    }

    public function updateNohp(): void
    {
        Auth::required(); Auth::verifyCsrf();
        $nis  = Request::post('nis', '');
        $nohp = Request::post('nohp', '');
        if (empty($nis)) Response::error('NIS tidak boleh kosong.', 400);
        $this->model->updateNohp($nis, $nohp) > 0
            ? Response::success(null, 'Nomor HP berhasil diperbarui.')
            : Response::error('Gagal memperbarui.', 500);
    }

    public function updateDudika(): void
    {
        Auth::required(); Auth::verifyCsrf();
        $nis    = Request::post('nis', '');
        $dudika = Request::post('dudika', '');
        if (empty($nis)) Response::error('NIS tidak boleh kosong.', 400);
        $this->model->updateDudika($nis, $dudika);
        Response::success(null, 'DUDIKA berhasil diperbarui.');
    }

    public function updatePembimbing(): void
    {
        Auth::required(); Auth::verifyCsrf();
        $nis        = Request::post('nis', '');
        $pembimbing = Request::post('pembimbing', '');
        if (empty($nis)) Response::error('NIS tidak boleh kosong.', 400);
        $this->model->updatePembimbing($nis, $pembimbing);
        Response::success(null, 'Pembimbing berhasil diperbarui.');
    }
}