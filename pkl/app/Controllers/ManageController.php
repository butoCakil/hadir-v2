<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

class ManageController
{
    private Database $db;

    public function __construct()
    {
        Auth::required();
        $this->db = Database::getInstance();
    }

    // ==========================================
    // GET /manage
    // ==========================================
    public function index(): void
    {
        $pembimbing = $this->db->query("SELECT * FROM datapembimbing ORDER BY nama ASC");
        $walikelas  = $this->db->query("SELECT * FROM datawalikelas ORDER BY kelas ASC");
        $dudika     = $this->db->query(
            "SELECT DISTINCT nama_dudika, alamat_dudika, nomor_telepon_dudika, nama_pembimbing
             FROM penempatan ORDER BY nama_dudika ASC"
        );

        $totalSiswa      = (int)($this->db->queryOne("SELECT COUNT(*) as n FROM datasiswa")['n'] ?? 0);
        $totalPembimbing = count($pembimbing);
        $totalWalikelas  = count($walikelas);
        $totalDudika     = count($dudika);

        Response::view('manage/index', [
            'title'           => 'Manage Data',
            'user'            => Auth::user(),
            'pembimbing'      => $pembimbing,
            'walikelas'       => $walikelas,
            'dudika'          => $dudika,
            'totalSiswa'      => $totalSiswa,
            'totalPembimbing' => $totalPembimbing,
            'totalWalikelas'  => $totalWalikelas,
            'totalDudika'     => $totalDudika,
        ]);
    }

    // ==========================================
    // POST /manage/update-pembimbing
    // ==========================================
    public function updatePembimbing(): void
    {
        $id    = (int)($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = trim($_POST['value'] ?? '');

        if (!$id || !in_array($field, ['nip','nama','nohp','ket'])) {
            Response::error('Field tidak valid', 400); return;
        }
        if ($field === 'nohp') $value = preg_replace('/\D/', '', $value);

        $this->db->query("UPDATE datapembimbing SET `$field` = ? WHERE id = ?", [$value, $id]);
        Response::success(['value' => $value], 'Berhasil diperbarui');
    }

    // ==========================================
    // POST /manage/update-walikelas
    // ==========================================
    public function updateWalikelas(): void
    {
        $id    = (int)($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = trim($_POST['value'] ?? '');

        if (!$id || !in_array($field, ['nip','nama','kelas','nohp'])) {
            Response::error('Field tidak valid', 400); return;
        }
        if ($field === 'nohp') $value = preg_replace('/\D/', '', $value);

        $this->db->query("UPDATE datawalikelas SET `$field` = ? WHERE id = ?", [$value, $id]);
        Response::success(['value' => $value], 'Berhasil diperbarui');
    }

    // ==========================================
    // POST /manage/update-dudika
    // ==========================================
    public function updateDudika(): void
    {
        $oldNama = trim($_POST['old_nama'] ?? '');
        $field   = $_POST['field'] ?? '';
        $value   = trim($_POST['value'] ?? '');

        if (!$oldNama || !in_array($field, ['nama_dudika','alamat_dudika','nomor_telepon_dudika','nama_pembimbing'])) {
            Response::error('Field tidak valid', 400); return;
        }

        $this->db->query("UPDATE penempatan SET `$field` = ? WHERE nama_dudika = ?", [$value, $oldNama]);
        Response::success(['value' => $value], 'Berhasil diperbarui');
    }

    // ==========================================
    // POST /manage/upload-penempatan
    // Format: Nama Pembimbing | Nama Dudika | Alamat | No Telp | Nama Siswa | NIS | Kelas
    // DUDIKA/Pembimbing di baris pertama per grup (merge pattern)
    // ==========================================
    public function uploadPenempatan(): void
    {
        $autoload = '/home/dvttaulx/public_html/dist/excel/vendor/autoload.php';
        if (!file_exists($autoload)) { Response::error('PhpSpreadsheet tidak ditemukan.', 500); return; }
        require_once $autoload;

        if (empty($_FILES['file']['tmp_name'])) { Response::error('File tidak ditemukan.', 400); return; }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx','xls'])) { Response::error('Hanya file .xlsx/.xls.', 400); return; }

        try {
            $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['file']['tmp_name'])
                ->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Exception $e) {
            Response::error('Gagal membaca file: ' . $e->getMessage(), 500); return;
        }

        // Deteksi header
        $headerRow = 0;
        $colMap    = [];
        $keywords  = ['nama pembimbing','nama dudika','nis','nis siswa','kelas','nama siswa'];

        foreach ($rows as $i => $row) {
            $rowLower = array_map(fn($v) => strtolower(trim((string)$v)), $row);
            $matches  = 0;
            foreach ($keywords as $kw) { if (in_array($kw, $rowLower)) $matches++; }
            if ($matches >= 3) {
                $headerRow = $i;
                foreach ($rowLower as $ci => $cell) {
                    if (in_array($cell, ['nama pembimbing','pembimbing']))                                   $colMap['pembimbing']  = $ci;
                    if (in_array($cell, ['nama dudika','dudika','nama dudi']))                               $colMap['dudika']      = $ci;
                    if (in_array($cell, ['alamat dudika','alamat dudi','alamat']))                           $colMap['alamat']      = $ci;
                    if (in_array($cell, ['no telepon dudika','no telepon','telp','no telp','nomor telepon'])) $colMap['telp']       = $ci;
                    if (in_array($cell, ['nama siswa','nama']))                                              $colMap['nama_siswa']  = $ci;
                    if (in_array($cell, ['nis siswa','nis']))                                               $colMap['nis']         = $ci;
                    if ($cell === 'kelas')                                                                   $colMap['kelas']       = $ci;
                }
                break;
            }
        }

        if (!isset($colMap['nis']) || !isset($colMap['dudika'])) {
            Response::error('Kolom wajib tidak ditemukan. Pastikan header: NIS Siswa, Nama Dudika, Nama Pembimbing, Kelas.', 400);
            return;
        }

        $lastPembimbing = $lastDudika = $lastAlamat = $lastTelp = '';
        $siswaStat      = ['inserted'=>0,'updated'=>0,'skipped'=>0];
        $penempatanStat = ['inserted'=>0,'updated'=>0,'skipped'=>0];
        $pembimbingStat = ['inserted'=>0,'updated'=>0,'skipped'=>0];

        for ($i = $headerRow + 1; $i < count($rows); $i++) {
            $row        = $rows[$i];
            $pembimbing = trim((string)($row[$colMap['pembimbing']] ?? ''));
            $dudika     = trim((string)($row[$colMap['dudika']]     ?? ''));
            $alamat     = isset($colMap['alamat'])     ? trim((string)($row[$colMap['alamat']]     ?? '')) : '';
            $telp       = isset($colMap['telp'])       ? trim((string)($row[$colMap['telp']]       ?? '')) : '';
            $namaSiswa  = isset($colMap['nama_siswa']) ? trim((string)($row[$colMap['nama_siswa']] ?? '')) : '';
            $nis        = trim((string)($row[$colMap['nis']]   ?? ''));
            $kelas      = trim((string)($row[$colMap['kelas']] ?? ''));

            if ($dudika)     $lastDudika     = $dudika;
            if ($pembimbing) $lastPembimbing = $pembimbing;
            if ($alamat)     $lastAlamat     = $alamat;
            if ($telp)       $lastTelp       = $telp;

            if (!$nis) { $siswaStat['skipped']++; continue; }

            // 1. Upsert datasiswa
            if ($namaSiswa && $kelas) {
                if ($this->db->queryOne("SELECT id FROM datasiswa WHERE nis = ?", [$nis])) {
                    $this->db->query("UPDATE datasiswa SET nama=?,kelas=? WHERE nis=?", [$namaSiswa,$kelas,$nis]);
                    $siswaStat['updated']++;
                } else {
                    $this->db->query("INSERT INTO datasiswa (nis,nama,kelas) VALUES (?,?,?)", [$nis,$namaSiswa,$kelas]);
                    $siswaStat['inserted']++;
                }
            } else {
                $siswaStat['skipped']++;
            }

            // 2. Upsert penempatan
            if ($lastDudika && $lastPembimbing) {
                if ($this->db->queryOne("SELECT id FROM penempatan WHERE nis_siswa = ?", [$nis])) {
                    $this->db->query(
                        "UPDATE penempatan SET nama_siswa=?,kelas=?,nama_dudika=?,alamat_dudika=?,nomor_telepon_dudika=?,nama_pembimbing=? WHERE nis_siswa=?",
                        [$namaSiswa,$kelas,$lastDudika,$lastAlamat,$lastTelp,$lastPembimbing,$nis]
                    );
                    $penempatanStat['updated']++;
                } else {
                    $this->db->query(
                        "INSERT INTO penempatan (nama_siswa,nis_siswa,kelas,nama_dudika,alamat_dudika,nomor_telepon_dudika,nama_pembimbing) VALUES (?,?,?,?,?,?,?)",
                        [$namaSiswa,$nis,$kelas,$lastDudika,$lastAlamat,$lastTelp,$lastPembimbing]
                    );
                    $penempatanStat['inserted']++;
                }
            } else {
                $penempatanStat['skipped']++;
            }

            // 3. Upsert datapembimbing (hanya jika baris baru pembimbing)
            if ($pembimbing) {
                if (!$this->db->queryOne("SELECT id FROM datapembimbing WHERE nama = ?", [$pembimbing])) {
                    $this->db->query("INSERT INTO datapembimbing (nip,nama,kode) VALUES ('',?,'')", [$pembimbing]);
                    $pembimbingStat['inserted']++;
                } else {
                    $pembimbingStat['updated']++;
                }
            }
        }

        Response::success([
            'siswa'      => $siswaStat,
            'penempatan' => $penempatanStat,
            'pembimbing' => $pembimbingStat,
        ], 'Upload selesai.');
    }

    // ==========================================
    // POST /manage/sinkron-pembimbing-preview
    // ==========================================
    public function sinkronPembimbingPreview(): void
    {
        $pembimbingList = $this->db->query("SELECT id, nip, nama, nohp FROM datapembimbing ORDER BY nama ASC");
        $namaMap        = array_column($pembimbingList, null, 'nama');

        $penempatan = $this->db->query(
            "SELECT DISTINCT nama_pembimbing FROM penempatan
             WHERE nama_pembimbing IS NOT NULL AND nama_pembimbing != ''
             ORDER BY nama_pembimbing ASC"
        );

        $tidakCocok = [];
        $sudahCocok = 0;
        foreach ($penempatan as $p) {
            if (isset($namaMap[$p['nama_pembimbing']])) {
                $sudahCocok++;
            } else {
                // Cari kandidat mirip dari datapembimbing
                $kandidat = [];
                foreach ($pembimbingList as $pb) {
                    similar_text(strtolower($p['nama_pembimbing']), strtolower($pb['nama']), $pct);
                    if ($pct >= 60) $kandidat[] = ['nama' => $pb['nama'], 'pct' => round($pct)];
                }
                usort($kandidat, fn($a,$b) => $b['pct'] <=> $a['pct']);
                $tidakCocok[] = [
                    'nama_penempatan' => $p['nama_pembimbing'],
                    'kandidat'        => array_slice($kandidat, 0, 3),
                ];
            }
        }

        Response::success([
            'sudah_cocok'          => $sudahCocok,
            'tidak_cocok'          => $tidakCocok,
            'jumlah_tidak_cocok'   => count($tidakCocok),
            'total_pembimbing_db'  => count($pembimbingList),
        ], count($tidakCocok) === 0
            ? 'Semua nama pembimbing di penempatan cocok dengan datapembimbing.'
            : count($tidakCocok) . ' nama pembimbing tidak cocok.'
        );
    }

    // ==========================================
    // POST /manage/sinkron-siswa-preview
    // ==========================================
    public function sinkronSiswaPreview(): void
    {
        $penempatan = $this->db->query("SELECT nis_siswa, nama_siswa, kelas FROM penempatan");
        $changes    = [];
        $noMatch    = [];

        foreach ($penempatan as $p) {
            $siswa = $this->db->queryOne("SELECT nis, nama, kelas FROM datasiswa WHERE nis = ?", [$p['nis_siswa']]);
            if (!$siswa) { $noMatch[] = ['nis'=>$p['nis_siswa'],'nama'=>$p['nama_siswa']]; continue; }

            $diff = [];
            if (trim($siswa['nama'])  !== trim($p['nama_siswa'])) $diff['nama']  = ['lama'=>$siswa['nama'],  'baru'=>$p['nama_siswa']];
            if (trim($siswa['kelas']) !== trim($p['kelas']))       $diff['kelas'] = ['lama'=>$siswa['kelas'], 'baru'=>$p['kelas']];
            if (!empty($diff)) $changes[] = ['nis'=>$p['nis_siswa'],'diff'=>$diff];
        }

        Response::success(['changes'=>$changes,'no_match'=>$noMatch,'total'=>count($changes)],
            count($changes) === 0
                ? 'Semua data siswa sudah sinkron.'
                : count($changes) . ' siswa akan diperbarui.'
        );
    }

    // ==========================================
    // POST /manage/sinkron-siswa-exec
    // ==========================================
    public function sinkronSiswaExec(): void
    {
        $penempatan = $this->db->query("SELECT nis_siswa, nama_siswa, kelas FROM penempatan");
        $updated    = 0;

        foreach ($penempatan as $p) {
            $siswa = $this->db->queryOne("SELECT nis, nama, kelas FROM datasiswa WHERE nis = ?", [$p['nis_siswa']]);
            if (!$siswa) continue;

            if (trim($siswa['nama']) !== trim($p['nama_siswa']) || trim($siswa['kelas']) !== trim($p['kelas'])) {
                $this->db->query("UPDATE datasiswa SET nama=?,kelas=? WHERE nis=?", [$p['nama_siswa'],$p['kelas'],$p['nis_siswa']]);
                $updated++;
            }
        }

        Response::success(['updated'=>$updated], "$updated data siswa berhasil diperbarui.");
    }

    // ==========================================
    // GET /manage/template-excel
    // ==========================================
    public function templateExcel(): void
    {
        $autoload = '/home/dvttaulx/public_html/dist/excel/vendor/autoload.php';
        if (!file_exists($autoload)) { http_response_code(500); echo 'PhpSpreadsheet tidak ditemukan.'; return; }
        require_once $autoload;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penempatan');

        $hFill  = ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['argb'=>'FF1E3A5F']];
        $hFont  = ['bold'=>true,'color'=>['argb'=>'FFE2E8F0'],'size'=>9];
        $border = ['borders'=>['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['argb'=>'FF334155']]]];
        $CENTER = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $cols   = ['A','B','C','D','E','F','G'];
        $headers= ['Nama Pembimbing','Nama Dudika','Alamat Dudika','No Telepon Dudika','Nama Siswa','NIS Siswa','Kelas'];

        // Baris 1: Judul
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1','TEMPLATE DATA PENEMPATAN PKL — SMK NEGERI BANSARI');
        $sheet->getStyle('A1')->applyFromArray(['font'=>['bold'=>true,'size'=>12,'color'=>['argb'=>'FF1E40AF']],'alignment'=>['horizontal'=>$CENTER]]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Baris 2: Petunjuk
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2','Isi mulai baris ke-4. Kolom A-D boleh kosong jika DUDIKA sama dengan baris di atasnya (merge otomatis terdeteksi).');
        $sheet->getStyle('A2')->applyFromArray(['font'=>['italic'=>true,'size'=>8,'color'=>['argb'=>'FF64748B']],'alignment'=>['horizontal'=>$CENTER]]);

        // Baris 3: Header
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i].'3', $h);
            $sheet->getStyle($cols[$i].'3')->applyFromArray(['fill'=>$hFill,'font'=>$hFont,'alignment'=>['horizontal'=>$CENTER]]);
        }
        $sheet->getStyle('A3:G3')->applyFromArray($border);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Baris 4-9: Contoh data (2 DUDIKA, masing-masing 3 siswa)
        $contoh = [
            ['Aprisia Khairunnisa, S.Pd', 'AD PRO AUDIO', 'Jl. Raya No. 1, Temanggung', '081234567890', 'Ahmad Fauzi',   '2801', 'XII AT 1'],
            ['',                          '',              '',                              '',              'Siti Rahayu',  '2802', 'XII AT 1'],
            ['',                          '',              '',                              '',              'Budi Santoso', '2803', 'XII AT 1'],
            ['Yeni Hanifah, S.Pd',        'Adli Group',   'Jl. Pemuda No. 5, Parakan',    '089876543210',  'Dewi Lestari', '2901', 'XII DKV 2'],
            ['',                          '',              '',                              '',              'Eko Prasetyo', '2902', 'XII DKV 2'],
            ['',                          '',              '',                              '',              'Fani Agustina','2903', 'XII DKV 2'],
        ];
        foreach ($contoh as $r => $row) {
            $rn = $r + 4;
            foreach ($cols as $ci => $col) $sheet->setCellValue($col.$rn, $row[$ci]);
            $fc = ($r < 3) ? 'FFF0F9FF' : 'FFF0FFF4'; // biru muda / hijau muda per grup
            $sheet->getStyle("A{$rn}:G{$rn}")->applyFromArray([
                'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['argb'=>$fc]],
                'font' => ['color'=>['argb'=>'FF334155'],'size'=>9],
            ]);
            $sheet->getStyle("A{$rn}:G{$rn}")->applyFromArray($border);
        }

        // Baris 10-25: kosong untuk isi
        for ($r = 10; $r <= 25; $r++) {
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                'fill'  => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['argb'=>'FFFFFFFF']],
                'font'  => ['size'=>9],
            ]);
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($border);
        }

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(24);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->freezePane('A4');

        // Sheet Panduan
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Panduan');
        $guide->setCellValue('A1','PANDUAN PENGISIAN TEMPLATE PENEMPATAN PKL');
        $guide->getStyle('A1')->applyFromArray(['font'=>['bold'=>true,'size'=>11]]);
        $panduanRows = [
            ['Kolom','Keterangan','Wajib?'],
            ['Nama Pembimbing','Nama guru pembimbing. Kosongkan jika DUDIKA sama dengan baris atas.','Ya (baris pertama grup)'],
            ['Nama Dudika','Nama tempat PKL/industri.','Ya (baris pertama grup)'],
            ['Alamat Dudika','Alamat lengkap DUDIKA.','Tidak'],
            ['No Telepon Dudika','Nomor telepon DUDIKA.','Tidak'],
            ['Nama Siswa','Nama lengkap siswa.','Ya'],
            ['NIS Siswa','Nomor Induk Siswa.','Ya'],
            ['Kelas','Contoh: XII AT 1, XII DKV 2.','Ya'],
        ];
        foreach ($panduanRows as $r => $row) {
            $guide->setCellValue('A'.($r+3),$row[0]);
            $guide->setCellValue('B'.($r+3),$row[1]);
            $guide->setCellValue('C'.($r+3),$row[2]);
            if ($r===0) $guide->getStyle('A3:C3')->applyFromArray(['font'=>['bold'=>true,'color'=>['argb'=>'FFE2E8F0']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['argb'=>'FF1E3A5F']]]);
        }
        $guide->getColumnDimension('A')->setWidth(22);
        $guide->getColumnDimension('B')->setWidth(55);
        $guide->getColumnDimension('C')->setWidth(22);
        $guide->setCellValue('A13','Catatan Penting:');
        $guide->getStyle('A13')->applyFromArray(['font'=>['bold'=>true]]);
        $catatan = [
            '• Upload akan memperbarui: datasiswa, penempatan, dan datapembimbing sekaligus.',
            '• Jika NIS sudah ada di database → data diperbarui (update).',
            '• Jika NIS belum ada → data ditambahkan (insert).',
            '• Kolom A-D boleh kosong jika DUDIKA & Pembimbing sama dengan baris di atasnya.',
            '• Baris tanpa NIS Siswa akan dilewati.',
            '• Header dideteksi otomatis — boleh ada baris judul di atas header.',
        ];
        foreach ($catatan as $n => $c) $guide->setCellValue('A'.($n+14), $c);

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Template_Penempatan_PKL_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');

        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        exit;
    }
}