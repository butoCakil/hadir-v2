<?php
ob_start();

// Helper hari
function hariPendek(string $tgl): string {
    $map = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
    return $map[date('D', strtotime($tgl))] ?? '';
}

$extraCss = <<<CSS
<style>
/* Grid presensi */
.grid-tbl { width:100%; border-collapse:collapse; font-size:0.78rem; }
.grid-tbl thead th { background:var(--bg3); color:var(--text3); font-size:0.65rem; text-transform:uppercase; letter-spacing:0.05em; padding:0.5rem 0.6rem; border-bottom:1px solid var(--border); white-space:nowrap; font-weight:700; }
.grid-tbl thead th.col-today { color:var(--blue); }
.grid-tbl tbody tr { border-bottom:1px solid var(--border); }
.grid-tbl tbody tr:last-child { border-bottom:none; }
.grid-tbl tbody tr:hover { background:var(--bg3); }
.grid-tbl tbody td { padding:0.55rem 0.6rem; vertical-align:middle; }
.grid-tbl tbody tr.row-hadir { border-left:3px solid var(--green); }
.grid-tbl tbody tr.row-belum { border-left:3px solid var(--border); opacity:0.8; }

/* Ket box */
.ket-box { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:5px; font-size:0.65rem; font-weight:700; }
.ket-box.masuk { background:var(--green-bg); color:var(--green); }
.ket-box.izin  { background:var(--blue-bg); color:var(--blue); }
.ket-box.sakit { background:var(--yellow-bg); color:var(--yellow); }
.ket-box.libur { background:var(--bg3); color:var(--text2); }
.ket-box.kosong{ background:var(--bg3); color:var(--text3); font-size:0.55rem; }
.ket-box.weekend{ background:transparent; color:var(--border2); }

/* Rekap badges */
.rekap-mini-badges { display:flex; gap:3px; }
.rb { padding:0.1rem 0.3rem; border-radius:3px; font-size:0.62rem; font-weight:700; }
.rb.m { background:var(--green-bg); color:var(--green); }
.rb.i { background:var(--blue-bg); color:var(--blue); }
.rb.s { background:var(--yellow-bg); color:var(--yellow); }
.rb.l { background:var(--bg3); color:var(--text2); }

/* Status badge hari ini */
.today-badge { display:inline-flex; align-items:center; gap:3px; padding:0.15rem 0.45rem; border-radius:20px; font-size:0.65rem; font-weight:700; }
.today-badge.hadir  { background:var(--green-bg); color:var(--green); }
.today-badge.belum  { background:var(--red-bg); color:var(--red); }
</style>
CSS;
?>

<!-- Stat ringkas -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalSiswa ?></div>
                <div class="stat-label">Total Siswa<?= $filterPembimbing||$filterKelas ? ' (filter)' : '' ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $sudahHariIni ?></div>
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-sub"><?= $totalSiswa > 0 ? round($sudahHariIni/$totalSiswa*100) : 0 ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--red-bg);color:var(--red);"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalSiswa - $sudahHariIni ?></div>
                <div class="stat-label">Belum Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--yellow-bg);color:var(--yellow);"><i class="fa-solid fa-calendar-days"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= date('d M') ?></div>
                <div class="stat-label"><?= date('l') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card-app mb-3">
    <div class="card-body-app" style="padding:0.875rem 1.25rem;">
        <form method="GET" action="/info" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
            <div>
                <label class="form-label">Pembimbing</label>
                <select name="pembimbing" class="form-select" style="min-width:200px;" onchange="this.form.submit()">
                    <option value="">-- Semua Pembimbing --</option>
                    <?php foreach ($listPembimbing as $p): ?>
                    <option value="<?= htmlspecialchars($p['nama_pembimbing']) ?>"
                        <?= $filterPembimbing===$p['nama_pembimbing']?'selected':'' ?>>
                        <?= htmlspecialchars($p['nama_pembimbing']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Kelas</label>
                <select name="kelas" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($listKelas as $k): ?>
                    <option value="<?= htmlspecialchars($k['kelas']) ?>"
                        <?= $filterKelas===$k['kelas']?'selected':'' ?>>
                        <?= htmlspecialchars($k['kelas']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filterPembimbing || $filterKelas): ?>
            <a href="/info" class="btn-app btn-ghost" style="align-self:flex-end;">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel Grid Presensi -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title">Rekap Presensi — 8 Hari Terakhir</span>
        <span style="font-size:0.75rem;color:var(--text3);"><?= $totalSiswa ?> siswa</span>
    </div>
    <div style="overflow-x:auto;">
        <table id="tabelCek" class="grid-tbl">
            <thead>
                <tr>
                    <th style="width:36px;">No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Pembimbing</th>
                    <th>DUDIKA</th>
                    <th>WA</th>
                    <th>Rekap</th>
                    <!-- Kolom hari -->
                    <?php foreach ($hariKolom as $tgl):
                        $isToday   = $tgl === $today;
                        $dow       = (int)date('N', strtotime($tgl));
                        $isWeekend = $dow >= 6;
                    ?>
                    <th class="<?= $isToday ? 'col-today' : '' ?>" style="text-align:center;min-width:38px;">
                        <div><?= hariPendek($tgl) ?></div>
                        <div style="font-size:0.6rem;opacity:0.7;"><?= date('d/m', strtotime($tgl)) ?></div>
                    </th>
                    <?php endforeach; ?>
                    <th style="width:60px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $i => $s):
                    $nis      = $s['nis'];
                    $prMap    = $presensiMap[$nis] ?? [];
                    $rekap    = $rekapMap[$nis]    ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
                    $hadir    = isset($prMap[$today]);
                    $nohp     = $s['nohp'] ?? '';
                    $wa62     = $nohp ? preg_replace('/^0/','62',preg_replace('/[^0-9]/','', $nohp)) : '';
                ?>
                <tr class="<?= $hadir ? 'row-hadir' : 'row-belum' ?>">
                    <td style="color:var(--text3);font-size:0.72rem;"></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($s['nama']) ?></div>
                        <div style="font-size:0.68rem;color:var(--text3);font-family:monospace;"><?= htmlspecialchars($nis) ?></div>
                    </td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.72rem;font-weight:600;"><?= htmlspecialchars($s['kelas']) ?></span></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($s['nama_pembimbing']??'-') ?></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($s['nama_dudika']??'-') ?></td>
                    <td>
                        <?php if ($wa62): ?>
                        <a href="https://wa.me/<?= $wa62 ?>" target="_blank"
                           style="color:var(--green);font-size:0.8rem;text-decoration:none;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                        <?php else: ?>
                        <span style="color:var(--text3);font-size:0.72rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="rekap-mini-badges">
                            <span class="rb m"><?= $rekap['masuk'] ?>M</span>
                            <span class="rb i"><?= $rekap['izin'] ?>I</span>
                            <span class="rb s"><?= $rekap['sakit'] ?>S</span>
                            <span class="rb l"><?= $rekap['libur'] ?>L</span>
                        </div>
                    </td>
                    <!-- Kolom hari -->
                    <?php foreach ($hariKolom as $tgl):
                        $dow       = (int)date('N', strtotime($tgl));
                        $isWeekend = $dow >= 6;
                        $isToday   = $tgl === $today;
                        $pr        = $prMap[$tgl] ?? null;
                        $ket       = $pr ? strtolower($pr['ket']) : null;
                        $short     = $ket ? strtoupper(substr($ket,0,1)) : ($isWeekend ? '—' : '');
                        $cls       = $ket ?: ($isWeekend ? 'weekend' : 'kosong');
                        $link      = $pr['link'] ?? null;
                        $hasLink   = $link && ($pr['statuslink'] ?? '') === 'OK';
                    ?>
                    <td style="text-align:center;<?= $isToday ? 'background:var(--blue-bg);' : '' ?>">
                        <?php if ($hasLink): ?>
                        <a href="<?= htmlspecialchars($link) ?>" target="_blank" title="Lihat foto">
                            <span class="ket-box <?= $cls ?>"><?= $short ?></span>
                        </a>
                        <?php else: ?>
                        <span class="ket-box <?= $cls ?>"><?= $short ?: '·' ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td>
                        <a href="/info/<?= urlencode($nis) ?>" class="btn-app btn-ghost btn-icon" title="Detail">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Legend -->
<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.75rem;font-size:0.75rem;color:var(--text3);">
    <span><span class="ket-box masuk" style="display:inline-flex;">M</span> Masuk</span>
    <span><span class="ket-box izin"  style="display:inline-flex;">I</span> Izin</span>
    <span><span class="ket-box sakit" style="display:inline-flex;">S</span> Sakit</span>
    <span><span class="ket-box libur" style="display:inline-flex;">L</span> Libur</span>
    <span><span class="ket-box kosong" style="display:inline-flex;">·</span> Belum</span>
    <span style="margin-left:auto;color:var(--text3);">Klik kotak yang ada foto untuk melihat foto presensi.</span>
</div>

<?php
$content    = ob_get_clean();
$activePage = '';
$extraJs    = <<<'JS'
<script>
var dtCek = $('#tabelCek').DataTable({
    pageLength: 50,
    order: [[2,'asc']],
    columnDefs: [{ orderable: false, targets: [0, -1] }]
});
dtCek.on('draw', function() {
    dtCek.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1;
    });
}).draw();
</script>
JS;

// Gunakan layout publik
require BASE_PATH . '/app/Views/layouts/public.php';
