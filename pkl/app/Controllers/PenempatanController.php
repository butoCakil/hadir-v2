<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\PenempatanModel;

class PenempatanController
{
    private PenempatanModel $model;

    public function __construct()
    {
        $this->model = new PenempatanModel();
    }

    // ==========================================
    // GET /penempatan
    // Rekap per DUDIKA
    // ==========================================

    public function index(): void
    {
        Auth::required();

        $filterDudika     = Request::get('dudika', '');
        $filterPembimbing = Request::get('pembimbing', '');

        $rekapDudika   = $this->model->getRekapPerDudika();
        $listDudika    = $this->model->getListDudika();
        $listPembimbing = $this->model->getListPembimbing();

        // Ringkasan global
        $totalSiswa  = array_sum(array_column($rekapDudika, 'total'));
        $totalSudah  = array_sum(array_column($rekapDudika, 'sudah_wa'));
        $totalBelum  = array_sum(array_column($rekapDudika, 'belum_wa'));
        $totalDudika = count($rekapDudika);

        Response::view('penempatan/index', [
            'title'            => 'Data Penempatan',
            'user'             => Auth::user(),
            'rekapDudika'      => $rekapDudika,
            'listDudika'       => $listDudika,
            'listPembimbing'   => $listPembimbing,
            'filterDudika'     => $filterDudika,
            'filterPembimbing' => $filterPembimbing,
            'totalSiswa'       => $totalSiswa,
            'totalSudah'       => $totalSudah,
            'totalBelum'       => $totalBelum,
            'totalDudika'      => $totalDudika,
        ]);
    }

    // ==========================================
    // GET /penempatan/detail/{dudika}
    // Detail siswa per DUDIKA
    // ==========================================

    public function detail(array $params): void
    {
        Auth::required();

        $dudika = urldecode($params['dudika'] ?? '');

        if (empty($dudika)) {
            Response::abort(404);
        }

        $siswa = $this->model->getAll($dudika);

        Response::view('penempatan/detail', [
            'title'  => 'Penempatan — ' . $dudika,
            'user'   => Auth::user(),
            'dudika' => $dudika,
            'siswa'  => $siswa,
        ]);
    }
}