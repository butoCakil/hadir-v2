<?php ob_start();

$namaBulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                   '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                   '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

// Generate daftar bulan dalam periode untuk dropdown
$bulanOptions = [];
$cur = strtotime(date('Y-m-01', strtotime($periode['tanggal_mulai'])));
$end = strtotime(date('Y-m-01', strtotime($periode['tanggal_selesai'])));
while ($cur <= $end) {
    $ym = date('Y-m', $cur);
    $bulanOptions[$ym] = $namaBulanIndo[date('m', $cur)] . ' ' . date('Y', $cur);
    $cur = strtotime('+1 month', $cur);
}

// Generate kolom tanggal
$tanggalKolom = [];
$cur = strtotime($mulai);
$endTs = strtotime($selesai);
while ($cur <= $endTs) {
    $tanggalKolom[] = date('Y-m-d', $cur);
    $cur = strtotime('+1 day', $cur);
}

// Build query string untuk export (preserve filters)
$exportParams = http_build_query(array_filter([
    'kelas'      => $filterKelas,
    'pembimbing' => $filterPembimbing,
    'dudika'     => $filterDudika,
    'nis'        => $filterNis,
    'bulan'      => $filterBulan,
]));

$extraCss = <<<CSS
<style>
.arsip-breadcrumb { display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; color:var(--text3); margin-bottom:1.25rem; flex-wrap:wrap; }
.arsip-breadcrumb a { color:var(--blue); text-decoration:none; }
.arsip-breadcrumb a:hover { text-decoration:underline; }
.arsip-nav { display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.arsip-nav a {
    display:inline-flex; align-items:center; gap:0.4rem;
    padding:0.45rem 1rem; border-radius:8px; font-size:0.82rem; font-weight:600;
    text-decoration:none; border:1px solid var(--border2);
    color:var(--text2); background:var(--bg3); transition:all 0.15s;
}
.arsip-nav a:hover { background:var(--bg2); color:var(--text); }
.arsip-nav a.active { background:var(--blue); color:#fff; border-color:var(--blue); }

/* Tabel rekap */
.rekap-table { width:100%; border-collapse:collapse; font-size:0.75rem; }
.rekap-table th, .rekap-table td { border:1px solid var(--border); padding:0.3rem 0.4rem; text-align:center; white-space:nowrap; }
.rekap-table thead th { background:var(--bg3); color:var(--text3); font-size:0.65rem; text-transform:uppercase; font-weight:700; }
.rekap-table .col-nama { text-align:left !important; min-width:140px; }
.rekap-table .col-fix { min-width:80px; }
.ket-M { background:var(--green-bg);  color:var(--green);  font-weight:700; }
.ket-I { background:var(--yellow-bg); color:var(--yellow); font-weight:700; }
.ket-S { background:var(--red-bg);    color:var(--red);    font-weight:700; }
.ket-L { background:var(--blue-bg);   color:var(--blue);   font-weight:700; }

/* Print styles */
@media print {
    .no-print { display:none !important; }
    body { background:white !important; color:black !important; }
    .card-app { box-shadow:none !important; border:1px solid #ccc !important; }
    .rekap-table th, .rekap-table td { border:1px solid #999 !important; color:black !important; }
    .rekap-table thead th { background:#eee !important; color:#333 !important; }
    .ket-M { background:#d1fae5 !important; color:#065f46 !important; }
    .ket-I { background:#fef9c3 !important; color:#713f12 !important; }
    .ket-S { background:#fee2e2 !important; color:#7f1d1d !important; }
    .ket-L { background:#dbeafe !important; color:#1e3a8a !important; }
    .arsip-breadcrumb, .arsip-nav { display:none !important; }
    .print-header { display:block !important; }
    @page { margin:1cm; size:landscape; }
}
.print-header { display:none; text-align:center; margin-bottom:1rem; }
.print-header h2 { margin:0; font-size:14pt; }
.print-header h4 { margin:0.25rem 0 0; font-size:10pt; color:#555; }
</style>
CSS;
?>

<!-- Breadcrumb -->
<div class="arsip-breadcrumb no-print">
    <a href="/arsip"><i class="fa-solid fa-box-archive"></i> Arsip</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <a href="/arsip/<?= $periode['id'] ?>"><?= htmlspecialchars($periode['nama_periode']) ?></a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <span>Rekap & Export</span>
</div>

<!-- Nav arsip -->
<div class="arsip-nav no-print">
    <a href="/arsip/<?= $periode['id'] ?>"><i class="fa-solid fa-chart-pie"></i> Ringkasan</a>
    <a href="/arsip/<?= $periode['id'] ?>/siswa"><i class="fa-solid fa-users"></i> Siswa</a>
    <a href="/arsip/<?= $periode['id'] ?>/rekap" class="active"><i class="fa-solid fa-print"></i> Rekap & Export</a>
</div>

<!-- Filter panel -->
<div class="card-app mb-3 no-print">
    <div class="card-header-app">
        <span class="card-title"><i class="fa-solid fa-filter me-1" style="color:var(--blue)"></i> Filter Rekap</span>
    </div>
    <div class="card-body-app">
        <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Bulan</label>
                <select name="bulan" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua Bulan (periode penuh)</option>
                    <?php foreach ($bulanOptions as $ym => $label): ?>
                    <option value="<?= $ym ?>" <?= $filterBulan === $ym ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Kelas</label>
                <select name="kelas" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($listKelas as $k): ?>
                    <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= $filterKelas === $k['kelas'] ? 'selected' : '' ?>><?= htmlspecialchars($k['kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Pembimbing</label>
                <select name="pembimbing" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua Pembimbing</option>
                    <?php foreach ($listPembimbing as $p): ?>
                    <option value="<?= htmlspecialchars($p['nama_pembimbing']) ?>" <?= $filterPembimbing === $p['nama_pembimbing'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_pembimbing']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">DUDIKA</label>
                <select name="dudika" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua DUDIKA</option>
                    <?php foreach ($listDudika as $d): ?>
                    <option value="<?= htmlspecialchars($d['nama_dudika']) ?>" <?= $filterDudika === $d['nama_dudika'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nama_dudika']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">NIS (individu)</label>
                <input type="text" name="nis" value="<?= htmlspecialchars($filterNis) ?>" placeholder="cth: 2801"
                    style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;width:110px;">
            </div>
            <button type="submit" class="btn-app btn-primary-app"><i class="fa-solid fa-filter"></i> Tampilkan</button>
            <?php if ($filterKelas || $filterPembimbing || $filterDudika || $filterNis || $filterBulan): ?>
            <a href="/arsip/<?= $periode['id'] ?>/rekap" class="btn-app btn-ghost" style="text-decoration:none;"><i class="fa-solid fa-xmark"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Action buttons -->
<div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;" class="no-print">
    <button onclick="window.print()" class="btn-app btn-primary-app">
        <i class="fa-solid fa-print"></i> Print / Save PDF
    </button>
    <a href="/arsip/<?= $periode['id'] ?>/rekap/export-excel<?= $exportParams ? '?' . $exportParams : '' ?>"
       class="btn-app" style="background:var(--green-bg);color:var(--green);border:1px solid rgba(34,197,94,0.2);text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </a>
    <span style="font-size:0.75rem;color:var(--text3);align-self:center;">
        <?= count($siswaList) ?> siswa |
        <?= $filterBulan ? $bulanOptions[$filterBulan] : 'Periode penuh' ?> |
        <?= date('d M Y', strtotime($mulai)) ?> — <?= date('d M Y', strtotime($selesai)) ?>
    </span>
</div>

<!-- Print header (tampil saat print saja) -->
<div class="print-header">
    <h2>REKAP PRESENSI PKL — SMK NEGERI BANSARI</h2>
    <h4>
        <?= htmlspecialchars($periode['nama_periode']) ?>
        <?= $filterBulan ? '| ' . $bulanOptions[$filterBulan] : '' ?>
        <?= $filterKelas ? '| Kelas ' . htmlspecialchars($filterKelas) : '' ?>
        <?= $filterPembimbing ? '| Pembimbing: ' . htmlspecialchars($filterPembimbing) : '' ?>
        <?= $filterDudika ? '| DUDIKA: ' . htmlspecialchars($filterDudika) : '' ?>
        <?= $filterNis ? '| NIS: ' . htmlspecialchars($filterNis) : '' ?>
    </h4>
    <h4 style="font-weight:normal;"><?= date('d M Y', strtotime($mulai)) ?> — <?= date('d M Y', strtotime($selesai)) ?></h4>
</div>

<!-- Tabel rekap -->
<div class="card-app">
    <?php if (empty($siswaList)): ?>
    <div style="padding:2rem;text-align:center;color:var(--text3);font-size:0.85rem;" class="no-print">
        <i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:0.75rem;display:block;"></i>
        Tidak ada data dengan filter yang dipilih.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="rekap-table">
            <thead>
                <tr>
                    <th rowspan="2" style="min-width:30px;">No</th>
                    <th rowspan="2" style="min-width:70px;">NIS</th>
                    <th rowspan="2" class="col-nama">Nama Siswa</th>
                    <th rowspan="2" style="min-width:80px;">Kelas</th>
                    <th rowspan="2" class="col-nama">Pembimbing</th>
                    <th rowspan="2" class="col-nama">DUDIKA</th>
                    <?php if (count($tanggalKolom) <= 31): ?>
                    <th colspan="<?= count($tanggalKolom) ?>">Tanggal</th>
                    <?php endif; ?>
                    <th colspan="4">Ket</th>
                </tr>
                <tr>
                    <?php if (count($tanggalKolom) <= 31):
                        foreach ($tanggalKolom as $tgl): ?>
                    <th style="font-size:0.6rem;width:22px;"><?= date('d', strtotime($tgl)) ?></th>
                    <?php endforeach; endif; ?>
                    <th style="color:var(--green);">M</th>
                    <th style="color:var(--yellow);">I</th>
                    <th style="color:var(--red);">S</th>
                    <th style="color:var(--blue);">L</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $i => $s):
                    $M = $I = $S = $L = 0;
                    $nisPres = $rekapData[$s['nis']] ?? [];
                ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td style="font-family:monospace;font-size:0.7rem;"><?= htmlspecialchars($s['nis']) ?></td>
                    <td class="col-nama" style="text-align:left;"><?= htmlspecialchars($s['nama']) ?></td>
                    <td><?= htmlspecialchars($s['kelas']) ?></td>
                    <td class="col-nama" style="text-align:left;font-size:0.7rem;"><?= htmlspecialchars($s['nama_pembimbing'] ?? '-') ?></td>
                    <td class="col-nama" style="text-align:left;font-size:0.7rem;"><?= htmlspecialchars($s['nama_dudika'] ?? '-') ?></td>
                    <?php if (count($tanggalKolom) <= 31):
                        foreach ($tanggalKolom as $tgl):
                            $ket = $nisPres[$tgl] ?? '';
                            if ($ket === 'M') $M++;
                            elseif ($ket === 'I') $I++;
                            elseif ($ket === 'S') $S++;
                            elseif ($ket === 'L') $L++;
                    ?>
                    <td class="<?= $ket ? 'ket-'.$ket : '' ?>"><?= $ket ?></td>
                    <?php endforeach;
                    else:
                        // Kalau lebih dari 31 hari (periode penuh), hitung saja dari data
                        foreach ($nisPres as $ket) {
                            if ($ket === 'M') $M++;
                            elseif ($ket === 'I') $I++;
                            elseif ($ket === 'S') $S++;
                            elseif ($ket === 'L') $L++;
                        }
                    endif; ?>
                    <td style="color:var(--green);font-weight:700;"><?= $M ?></td>
                    <td style="color:var(--yellow);font-weight:700;"><?= $I ?></td>
                    <td style="color:var(--red);font-weight:700;"><?= $S ?></td>
                    <td style="color:var(--blue);font-weight:700;"><?= $L ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($tanggalKolom) > 31): ?>
    <div style="padding:0.75rem 1rem;font-size:0.75rem;color:var(--yellow);background:var(--yellow-bg);border-top:1px solid rgba(245,158,11,0.2);" class="no-print">
        <i class="fa-solid fa-info-circle"></i>
        Rentang tanggal melebihi 31 hari — kolom tanggal tidak ditampilkan. Gunakan filter <strong>Bulan</strong> untuk melihat detail per hari, atau gunakan <strong>Export Excel</strong> untuk data lengkap.
    </div>
    <?php endif; ?>

    <!-- Legend -->
    <div style="padding:0.75rem 1rem;border-top:1px solid var(--border);display:flex;gap:1rem;flex-wrap:wrap;font-size:0.72rem;" class="no-print">
        <span><strong>M</strong> = Masuk</span>
        <span><strong>I</strong> = Izin</span>
        <span><strong>S</strong> = Sakit</span>
        <span><strong>L</strong> = Libur</span>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$activePage = 'arsip';
require BASE_PATH . '/app/Views/layouts/app.php';
