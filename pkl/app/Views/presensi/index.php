<?php
ob_start();

// CSS tambahan khusus presensi
$extraCss = <<<CSS
<style>
/* ── Mode tab ── */
.mode-tab { display:inline-flex; background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:3px; gap:2px; }
.mode-tab a { padding:0.3rem 1rem; border-radius:6px; font-size:0.8rem; color:var(--text2); text-decoration:none; transition:all 0.15s; }
.mode-tab a.active { background:var(--blue); color:white; font-weight:600; }

/* ── Week nav ── */
.week-nav { display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap; }
.week-nav a { background:var(--bg3); border:1px solid var(--border2); color:var(--text2); border-radius:6px; padding:0.28rem 0.7rem; text-decoration:none; font-size:0.78rem; transition:all 0.15s; }
.week-nav a:hover { border-color:var(--blue); color:var(--blue); }
.week-label { color:var(--text); font-size:0.875rem; font-weight:600; }

/* ── Stat bar ── */
.stat-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem; margin-bottom:1rem; }
.stat-item { background:var(--bg2); border:1px solid var(--border); border-radius:10px; padding:0.875rem 1rem; }
.stat-item .num { font-size:1.6rem; font-weight:700; line-height:1; }
.stat-item .lbl { color:var(--text3); font-size:0.72rem; margin-top:0.2rem; }
.stat-item.masuk { border-left:3px solid var(--green); }
.stat-item.izin  { border-left:3px solid var(--yellow); }
.stat-item.sakit { border-left:3px solid var(--orange); }
.stat-item.libur { border-left:3px solid var(--border2); }

/* ── Rekap kelas chips ── */
.rekap-kelas-wrap { display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:1rem; }
.rekap-kelas-chip { background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:0.5rem 0.875rem; font-size:0.78rem; }
.rekap-kelas-chip .kls { font-weight:700; color:var(--text); margin-bottom:3px; }

/* ── Tabel harian ── */
.tabel-wrap { background:var(--bg2); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.tabel-header { padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; }
.tabel-header-title { font-weight:600; font-size:0.9rem; }
table.presensi-table { width:100%; border-collapse:collapse; }
table.presensi-table thead tr { background:var(--bg3); }
table.presensi-table thead th { color:var(--text3); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.06em; padding:0.65rem 1rem; border-bottom:1px solid var(--border); white-space:nowrap; font-weight:600; }
table.presensi-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.1s; }
table.presensi-table tbody tr:last-child { border-bottom:none; }
table.presensi-table tbody tr:hover { background:var(--blue-bg); }
table.presensi-table tbody td { padding:0.7rem 1rem; font-size:0.84rem; vertical-align:middle; }
table.presensi-table tbody tr.row-masuk { border-left:3px solid var(--green); }
table.presensi-table tbody tr.row-izin  { border-left:3px solid var(--yellow); background:rgba(245,158,11,0.03); }
table.presensi-table tbody tr.row-sakit { border-left:3px solid var(--orange); background:rgba(249,115,22,0.04); }
table.presensi-table tbody tr.row-libur { border-left:3px solid var(--border2); background:var(--bg3); opacity:0.75; }
table.presensi-table tbody tr.row-kosong { border-left:3px solid var(--border); background:var(--bg3); opacity:0.6; }
.avatar { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.72rem; font-weight:700; flex-shrink:0; }
.avatar.masuk { background:var(--green-bg); color:var(--green); }
.avatar.izin  { background:var(--yellow-bg); color:var(--yellow); }
.avatar.sakit { background:var(--orange-bg); color:var(--orange); }
.avatar.libur { background:var(--bg3); color:var(--text2); }
.avatar.kosong{ background:var(--bg3); color:var(--text3); }
.time-cell { display:flex; flex-direction:column; align-items:center; }
.time-dot { width:8px; height:8px; border-radius:50%; margin-bottom:3px; }
.time-dot.masuk { background:var(--green); box-shadow:0 0 6px rgba(34,197,94,0.5); }
.time-dot.izin  { background:var(--yellow); box-shadow:0 0 6px rgba(245,158,11,0.5); }
.time-dot.sakit { background:var(--orange); box-shadow:0 0 6px rgba(249,115,22,0.5); }
.time-dot.libur { background:var(--border2); }
.time-dot.kosong{ background:var(--border); }
.time-val { font-size:0.78rem; font-weight:600; }
.ket-badge { display:inline-flex; align-items:center; gap:4px; padding:0.2rem 0.55rem; border-radius:20px; font-size:0.72rem; font-weight:600; }
.ket-badge.masuk { background:var(--green-bg); color:var(--green); border:1px solid rgba(34,197,94,0.25); }
.ket-badge.izin  { background:var(--yellow-bg); color:var(--yellow); border:1px solid rgba(245,158,11,0.25); }
.ket-badge.sakit { background:var(--orange-bg); color:var(--orange); border:1px solid rgba(249,115,22,0.25); }
.ket-badge.libur { background:var(--bg3); color:var(--text2); border:1px solid var(--border); }
.ket-badge.kosong{ background:var(--bg3); color:var(--text3); border:1px solid var(--border); }

/* ── Kartu mingguan ── */
.siswa-card { background:var(--bg2); border:1px solid var(--border); border-radius:12px; margin-bottom:0.625rem; display:grid; grid-template-columns:2fr 2.5fr 3fr auto; overflow:hidden; transition:border-color 0.15s; }
.siswa-card:hover { border-color:var(--border2); }
.card-info { padding:0.875rem 1rem; border-right:1px solid var(--border); display:flex; flex-direction:column; justify-content:center; gap:0.2rem; min-width:0; }
.card-info .siswa-nama  { font-weight:700; font-size:0.875rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-info .siswa-kelas { font-size:0.72rem; color:var(--blue); font-weight:600; margin-bottom:0.2rem; }
.card-info .siswa-meta  { font-size:0.7rem; color:var(--text2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-info .siswa-nis   { font-size:0.68rem; color:var(--text3); font-family:monospace; }
.card-rekap { padding:0.75rem 1rem; border-right:1px solid var(--border); display:flex; flex-direction:column; justify-content:center; gap:0.35rem; }
.rekap-row  { display:flex; gap:0.5rem; align-items:center; }
.rekap-label{ font-size:0.62rem; color:var(--text2); width:60px; flex-shrink:0; }
.rekap-nums { display:flex; gap:0.4rem; }
.rn { padding:0.1rem 0.35rem; border-radius:3px; font-size:0.7rem; font-weight:600; }
.rn.m { background:var(--green-bg);  color:var(--green); }
.rn.i { background:var(--yellow-bg); color:var(--yellow); }
.rn.s { background:var(--orange-bg); color:var(--orange); }
.rn.l { background:var(--bg3); color:var(--text2); }
.card-hari  { padding:0.75rem 1rem; border-right:1px solid var(--border); display:flex; flex-direction:column; justify-content:center; }
.hari-grid  { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.hari-col   { display:flex; flex-direction:column; align-items:center; gap:2px; }
.hari-nama  { font-size:0.6rem; color:var(--text3); text-transform:uppercase; font-weight:600; }
.hari-tgl   { font-size:0.62rem; color:var(--text2); }
.hari-box   { width:100%; min-width:28px; height:26px; border-radius:5px; display:flex; align-items:center; justify-content:center; font-size:0.62rem; font-weight:700; }
.hari-box.masuk  { background:var(--green-bg);  color:var(--green); }
.hari-box.izin   { background:var(--yellow-bg); color:var(--yellow); }
.hari-box.sakit  { background:var(--orange-bg); color:var(--orange); }
.hari-box.libur  { background:var(--bg3); color:var(--text2); }
.hari-box.kosong { background:var(--bg3); color:var(--text3); font-size:0.55rem; }
.card-action { display:flex; align-items:center; justify-content:center; padding:0.75rem; }
.btn-detail-siswa { background:transparent; border:1px solid var(--border2); color:var(--text2); border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:0.85rem; transition:all 0.15s; }
.btn-detail-siswa:hover { border-color:var(--blue); color:var(--blue); background:var(--blue-bg); }

@media (max-width:768px) {
    .siswa-card { grid-template-columns:1fr; }
    .card-info,.card-rekap,.card-hari { border-right:none; border-bottom:1px solid var(--border); }
    .card-action { justify-content:flex-start; padding:0.75rem 1rem; }
    .stat-bar { grid-template-columns:repeat(2,1fr); }
}
</style>
CSS;
?>

<!-- Mode Tab + Week Nav -->
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
    <div class="mode-tab">
        <a href="/presensi?mode=harian&tanggal=<?= $tanggal ?>&kelas=<?= urlencode($kelas) ?>&pembimbing=<?= urlencode($pembimbing) ?>"
           class="<?= $mode==='harian'?'active':'' ?>">
            <i class="fa-solid fa-calendar-day me-1"></i>Harian
        </a>
        <a href="/presensi?mode=mingguan&week=<?= $senin ?>&kelas=<?= urlencode($kelas) ?>&pembimbing=<?= urlencode($pembimbing) ?>"
           class="<?= $mode==='mingguan'?'active':'' ?>">
            <i class="fa-solid fa-calendar-week me-1"></i>Mingguan
        </a>
    </div>
    <?php if ($mode==='mingguan'): ?>
    <div class="week-nav">
        <a href="/presensi?mode=mingguan&week=<?= $prevWeek ?>&kelas=<?= urlencode($kelas) ?>&pembimbing=<?= urlencode($pembimbing) ?>">
            <i class="fa-solid fa-chevron-left"></i> Lalu
        </a>
        <span class="week-label"><?= date('d M', strtotime($senin)) ?> — <?= date('d M Y', strtotime($minggu)) ?></span>
        <?php if ($nextWeek <= $today): ?>
        <a href="/presensi?mode=mingguan&week=<?= $nextWeek ?>&kelas=<?= urlencode($kelas) ?>&pembimbing=<?= urlencode($pembimbing) ?>">
            Depan <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php endif; ?>
        <input type="date" id="weekPicker" class="form-control" style="width:auto;padding:0.28rem 0.5rem;"
               value="<?= $senin ?>" max="<?= $today ?>">
    </div>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card-app mb-3">
    <div class="card-body-app" style="padding:0.875rem 1.25rem;">
        <form method="GET" action="/presensi" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
            <?php if ($mode==='harian'): ?>
                <input type="hidden" name="week" value="<?= $senin ?>">
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>" max="<?= $today ?>">
                </div>
            <?php else: ?>
                <input type="hidden" name="week" id="weekHidden" value="<?= $senin ?>">
            <?php endif; ?>
            <div>
                <label class="form-label">Pembimbing</label>
                <select name="pembimbing" class="form-select" style="min-width:180px;">
                    <option value="">Semua Pembimbing</option>
                    <?php foreach ($listPembimbing as $p): ?>
                        <option value="<?= htmlspecialchars($p['nama_pembimbing']) ?>"
                            <?= $pembimbing===$p['nama_pembimbing']?'selected':'' ?>>
                            <?= htmlspecialchars($p['nama_pembimbing']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Kelas</label>
                <select name="kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= htmlspecialchars($k['kelas']) ?>"
                            <?= $kelas===$k['kelas']?'selected':'' ?>>
                            <?= htmlspecialchars($k['kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn-app btn-primary-app">
                    <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
                </button>
                <a href="/presensi?mode=<?= $mode ?>" class="btn-app btn-ghost">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Stat Bar -->
<?php $r = $mode==='harian' ? $ringkasanHarian : $ringkasan; ?>
<div class="stat-bar">
    <div class="stat-item masuk">
        <div class="num" style="color:var(--green)"><?= number_format($r['masuk']) ?></div>
        <div class="lbl"><i class="fa-solid fa-circle-check me-1"></i>Masuk</div>
    </div>
    <div class="stat-item izin">
        <div class="num" style="color:var(--yellow)"><?= number_format($r['izin']) ?></div>
        <div class="lbl"><i class="fa-solid fa-clock me-1"></i>Izin</div>
    </div>
    <div class="stat-item sakit">
        <div class="num" style="color:var(--orange)"><?= number_format($r['sakit']) ?></div>
        <div class="lbl"><i class="fa-solid fa-heart-pulse me-1"></i>Sakit</div>
    </div>
    <div class="stat-item libur">
        <div class="num" style="color:var(--text2)"><?= number_format($r['libur']) ?></div>
        <div class="lbl"><i class="fa-solid fa-umbrella-beach me-1"></i>Libur</div>
    </div>
</div>

<?php if ($mode==='harian'): ?>

<!-- Rekap Kelas -->
<?php if (!empty($rekapKelas)): ?>
<div class="rekap-kelas-wrap">
    <?php foreach ($rekapKelas as $rk): ?>
    <div class="rekap-kelas-chip">
        <div class="kls"><?= htmlspecialchars($rk['kelas']) ?></div>
        <div style="display:flex;gap:6px;font-size:0.72rem;">
            <span style="color:var(--green);">✓<?= $rk['masuk'] ?></span>
            <?php if ($rk['izin']>0): ?><span style="color:var(--yellow);">~<?= $rk['izin'] ?></span><?php endif; ?>
            <?php if ($rk['sakit']>0): ?><span style="color:var(--orange);">+<?= $rk['sakit'] ?></span><?php endif; ?>
            <?php if ($rk['libur']>0): ?><span style="color:var(--text2);">-<?= $rk['libur'] ?></span><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabel Harian -->
<div class="tabel-wrap">
    <div class="tabel-header">
        <div>
            <div class="tabel-header-title">
                <i class="fa-solid fa-clipboard-list me-2" style="color:var(--blue)"></i>
                Presensi <?= date('l, d F Y', strtotime($tanggal)) ?>
            </div>
            <?php if ($pembimbing): ?>
            <div style="font-size:0.75rem;color:var(--text2);margin-top:2px;">
                Pembimbing: <strong style="color:var(--text)"><?= htmlspecialchars($pembimbing) ?></strong>
            </div>
            <?php endif; ?>
        </div>
        <span style="font-size:0.78rem;color:var(--text2);"><?= count($presensiHarian) ?> siswa</span>
    </div>
    <div style="overflow-x:auto;">
        <?php if (empty($presensiHarian)): ?>
            <div style="text-align:center;padding:3rem;color:var(--text3);">
                <i class="fa-solid fa-inbox fa-2x" style="opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
                Tidak ada data.
            </div>
        <?php else: ?>
        <table id="tabelHarian" class="presensi-table">
            <thead>
                <tr>
                    <th style="width:44px;">No</th>
                    <th style="width:56px;">Waktu</th>
                    <th>Siswa</th>
                    <th style="width:80px;">Kelas</th>
                    <th style="width:100px;">Status</th>
                    <th>Catatan</th>
                    <th>Pembimbing</th>
                    <th>DUDIKA</th>
                    <th style="width:44px;">Foto</th>
                    <th style="width:36px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presensiHarian as $i => $p):
                    $ket     = !empty($p['ket']) ? strtolower($p['ket']) : 'kosong';
                    $ts      = !empty($p['timestamp']) ? strtotime($p['timestamp']) : null;
                    $words   = array_filter(explode(' ', $p['namasiswa']), fn($w) => strlen($w) > 0);
                    $inisial = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(array_values($words),0,2))));
                    $icon    = match($ket) {'masuk'=>'circle-check','izin'=>'clock','sakit'=>'heart-pulse','libur'=>'umbrella-beach',default=>'circle-minus'};
                ?>
                <tr class="row-<?= $ket ?>">
                    <td style="color:var(--text3);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td>
                        <div class="time-cell">
                            <div class="time-dot <?= $ket ?>"></div>
                            <div class="time-val"><?= $ts ? date('H:i',$ts) : '—' ?></div>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div class="avatar <?= $ket ?>"><?= $inisial ?></div>
                            <div>
                                <div style="font-weight:600;font-size:0.84rem;"><?= htmlspecialchars($p['namasiswa']) ?></div>
                                <div style="font-size:0.7rem;color:var(--text3);font-family:monospace;"><?= htmlspecialchars($p['nis']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.75rem;font-weight:600;"><?= htmlspecialchars($p['kelas']) ?></span></td>
                    <td>
                        <span class="ket-badge <?= $ket ?>">
                            <i class="fa-solid fa-<?= $icon ?>"></i>
                            <?= $ket==='kosong' ? 'Belum' : htmlspecialchars($p['ket']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text2);"><?= htmlspecialchars($p['catatan']??'—') ?></td>
                    <td style="font-size:0.8rem;color:var(--text2);"><?= htmlspecialchars($p['nama_pembimbing']??'—') ?></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($p['nama_dudika']??'—') ?></td>
                    <td>
                        <?php if (!empty($p['link']) && $p['statuslink']==='OK'): ?>
                            <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" style="color:var(--blue);font-size:0.9rem;"><i class="fa-solid fa-image"></i></a>
                        <?php else: ?><span style="color:var(--text3);">—</span><?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if ($ket === 'kosong'): ?>
                        <button onclick="bukaModalInput('<?= htmlspecialchars($p['nis'],ENT_QUOTES) ?>','<?= htmlspecialchars($p['namasiswa'],ENT_QUOTES) ?>','<?= $tanggal ?>')"
                                style="background:var(--green-bg);color:var(--green);border:1px solid rgba(34,197,94,0.2);border-radius:6px;width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.78rem;margin-right:0.25rem;"
                                title="Input presensi">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <?php else: ?>
                        <button onclick="bukaModalEdit(<?= $p['id'] ?>,'<?= htmlspecialchars($p['ket'],ENT_QUOTES) ?>','<?= htmlspecialchars($p['catatan']??'',ENT_QUOTES) ?>','<?= htmlspecialchars($p['namasiswa'],ENT_QUOTES) ?>')"
                                style="background:var(--blue-bg);color:var(--blue);border:1px solid rgba(59,130,246,0.2);border-radius:6px;width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.78rem;margin-right:0.25rem;"
                                title="Edit presensi">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <?php endif; ?>
                        <a href="/siswa/<?= urlencode($p['nis']) ?>" class="btn-detail-siswa" title="Detail">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<!-- MINGGUAN -->
<?php $namaHari=['Sen','Sel','Rab','Kam','Jum','Sab','Min']; ?>
<?php if ($pembimbing||$kelas): ?>
<div style="font-size:0.78rem;color:var(--text2);margin-bottom:0.75rem;">
    <?= count($presensiMingguan) ?> siswa
    <?php if ($pembimbing): ?> · Pembimbing: <strong style="color:var(--text)"><?= htmlspecialchars($pembimbing) ?></strong><?php endif; ?>
    <?php if ($kelas): ?> · Kelas: <strong style="color:var(--text)"><?= htmlspecialchars($kelas) ?></strong><?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($presensiMingguan)): ?>
    <div style="text-align:center;padding:3rem;color:var(--text3);">
        <i class="fa-solid fa-inbox fa-2x" style="opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
        Tidak ada data.
    </div>
<?php else: ?>
    <?php foreach ($presensiMingguan as $s):
        $total = $s['rekap_total']  ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
        $bulan = $s['rekap_bulan']  ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
        $mgg   = $s['rekap_minggu'] ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
    ?>
    <div class="siswa-card">
        <div class="card-info">
            <div class="siswa-kelas"><?= htmlspecialchars($s['kelas']) ?></div>
            <div class="siswa-nama"><?= htmlspecialchars($s['namasiswa']) ?></div>
            <div class="siswa-nis"><?= htmlspecialchars($s['nis']) ?></div>
            <?php if ($s['nama_dudika']): ?>
            <div class="siswa-meta"><i class="fa-solid fa-building" style="font-size:0.6rem;color:var(--text3);margin-right:3px;"></i><?= htmlspecialchars($s['nama_dudika']) ?></div>
            <?php endif; ?>
            <?php if ($s['nama_pembimbing']): ?>
            <div class="siswa-meta"><i class="fa-solid fa-user-tie" style="font-size:0.6rem;color:var(--text3);margin-right:3px;"></i><?= htmlspecialchars($s['nama_pembimbing']) ?></div>
            <?php endif; ?>
        </div>
        <div class="card-rekap">
            <?php foreach ([['Total',$total],[$bulanLabel,$bulan],['Minggu ini',$mgg]] as [$lbl,$dat]): ?>
            <div class="rekap-row">
                <div class="rekap-label"><?= $lbl ?></div>
                <div class="rekap-nums">
                    <span class="rn m"><?= $dat['masuk'] ?>M</span>
                    <span class="rn i"><?= $dat['izin'] ?>I</span>
                    <span class="rn s"><?= $dat['sakit'] ?>S</span>
                    <span class="rn l"><?= $dat['libur'] ?>L</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="card-hari">
            <div class="hari-grid">
                <?php foreach ($hariMinggu as $idx => $tgl):
                    $ket    = $s['presensi'][$tgl] ?? null;
                    $ketLow = $ket ? strtolower($ket) : 'kosong';
                    $short  = $ket ? strtoupper(substr($ket,0,1)) : '—';
                    $isToday= $tgl===date('Y-m-d');
                ?>
                <div class="hari-col">
                    <div class="hari-nama" style="<?= $isToday?'color:var(--blue)':'' ?>"><?= $namaHari[$idx] ?></div>
                    <div class="hari-tgl"  style="<?= $isToday?'color:var(--blue)':'' ?>"><?= date('d/m',strtotime($tgl)) ?></div>
                    <div class="hari-box <?= $ketLow ?>"><?= $short ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-action">
            <a href="/siswa/<?= urlencode($s['nis']) ?>" class="btn-detail-siswa" title="Detail">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

<!-- Modal Presensi Admin -->
<div class="modal-overlay" id="modalPresensi" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:1.5rem;width:100%;max-width:400px;">
        <h3 id="modalPresensiTitle" style="margin:0 0 1.25rem;font-size:0.95rem;font-weight:700;"></h3>
        <input type="hidden" id="modalPresensiId">
        <input type="hidden" id="modalPresensiNis">
        <input type="hidden" id="modalPresensiTanggal">
        <div id="wrapTanggalRange" style="margin-bottom:0.85rem;display:none;">
            <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.3rem;">Rentang Tanggal</label>
            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                <input type="date" id="modalTanggalDari"
                       style="padding:0.4rem 0.6rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                <span style="font-size:0.78rem;color:var(--text3);">s/d</span>
                <input type="date" id="modalTanggalSampai"
                       style="padding:0.4rem 0.6rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
            </div>
            <div style="font-size:0.7rem;color:var(--text3);margin-top:0.3rem;">Untuk 1 hari, isi tanggal yang sama di keduanya.</div>
        </div>

        <div style="margin-bottom:0.85rem;">
            <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.3rem;">Keterangan</label>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <?php foreach (['Masuk','Izin','Sakit','Libur'] as $k):
                    $colors = ['Masuk'=>'--green','Izin'=>'--yellow','Sakit'=>'--orange','Libur'=>'--text2'];
                    $c = $colors[$k];
                ?>
                <label style="cursor:pointer;">
                    <input type="radio" name="modalKet" value="<?= $k ?>" style="display:none;" class="ket-radio">
                    <span class="ket-radio-btn" data-ket="<?= $k ?>"
                          style="display:inline-block;padding:0.35rem 0.85rem;border-radius:20px;font-size:0.78rem;font-weight:600;border:2px solid var(--border);color:var(--text2);cursor:pointer;transition:all 0.15s;">
                        <?= $k ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.3rem;">Catatan</label>
            <input type="text" id="modalCatatan" placeholder="Opsional..."
                   style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
        </div>

        <div class="result-box" id="modalPresensiResult" style="margin-bottom:0.75rem;"></div>

        <div style="display:flex;gap:0.75rem;align-items:center;">
            <button class="btn-app btn-primary-app" onclick="simpanPresensiAdmin()">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
            <button class="btn-app" id="btnHapusPresensi" style="display:none;background:var(--red-bg);color:var(--red);border:1px solid rgba(239,68,68,0.2);" onclick="hapusPresensiAdmin()">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>
            <button class="btn-app btn-ghost" onclick="tutupModalPresensi()">Batal</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraJs = <<<'JS'
<script>
$(function () {
   if ($('#tabelHarian').length) {
        var dtHarian = $('#tabelHarian').DataTable({
            pageLength: 50,
            order: [[6, 'asc'], [7, 'asc'], [3, 'asc'], [2, 'asc']],
            columnDefs: [{ orderable: false, targets: [0, 8, 9] }]
        });
        dtHarian.on('draw', function() {
            dtHarian.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    }
    const picker = document.getElementById('weekPicker');
    if (picker) {
        picker.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('week', this.value);
            url.searchParams.set('mode', 'mingguan');
            window.location.href = url.toString();
        });
    }
});

// ── Presensi Admin ────────────────────────────────────────
const ketColors = {
    'Masuk': 'var(--green)', 'Izin': 'var(--yellow)',
    'Sakit': 'var(--orange)', 'Libur': 'var(--text2)'
};

document.querySelectorAll('.ket-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.ket-radio-btn').forEach(btn => {
            btn.style.borderColor = 'var(--border)';
            btn.style.color       = 'var(--text2)';
            btn.style.background  = 'transparent';
        });
        const btn = this.nextElementSibling;
        const color = ketColors[this.value] || 'var(--blue)';
        btn.style.borderColor = color;
        btn.style.color       = color;
        btn.style.background  = color.replace(')', '-bg)').replace('var(', 'var(');
    });
});

function bukaModalInput(nis, nama, tanggal) {
    document.getElementById('modalPresensiTitle').textContent = '+ Input Presensi — ' + nama;
    document.getElementById('modalPresensiId').value      = '';
    document.getElementById('modalPresensiNis').value     = nis;
    document.getElementById('modalPresensiTanggal').value = tanggal;
    document.getElementById('modalTanggalDari').value     = tanggal;
    document.getElementById('modalTanggalSampai').value   = tanggal;
    document.getElementById('modalCatatan').value         = '';
    document.getElementById('modalPresensiResult').style.display  = 'none';
    document.getElementById('btnHapusPresensi').style.display     = 'none';
    document.getElementById('wrapTanggalRange').style.display     = 'block';
    document.querySelectorAll('.ket-radio').forEach(r => r.checked = false);
    document.querySelectorAll('.ket-radio-btn').forEach(b => {
        b.style.borderColor = 'var(--border)';
        b.style.color       = 'var(--text2)';
        b.style.background  = 'transparent';
    });
    document.getElementById('modalPresensi').style.display = 'flex';
}

function bukaModalEdit(id, ket, catatan, nama) {
    document.getElementById('modalPresensiTitle').textContent = '✏️ Edit Presensi — ' + nama;
    document.getElementById('modalPresensiId').value      = id;
    document.getElementById('modalPresensiNis').value     = '';
    document.getElementById('modalPresensiTanggal').value = '';
    document.getElementById('modalCatatan').value         = catatan;
    document.getElementById('modalPresensiResult').style.display = 'none';
    document.getElementById('btnHapusPresensi').style.display    = 'inline-flex';

    // Set radio
    document.querySelectorAll('.ket-radio').forEach(r => {
        r.checked = (r.value === ket);
        if (r.checked) r.dispatchEvent(new Event('change'));
    });

    document.getElementById('modalPresensi').style.display = 'flex';
    document.getElementById('wrapTanggalRange').style.display = 'none';
}

function tutupModalPresensi() {
    document.getElementById('modalPresensi').style.display = 'none';
}

function simpanPresensiAdmin() {
    const id      = document.getElementById('modalPresensiId').value;
    const nis     = document.getElementById('modalPresensiNis').value;
    const tanggal = document.getElementById('modalPresensiTanggal').value;
    const catatan = document.getElementById('modalCatatan').value;
    const res     = document.getElementById('modalPresensiResult');
    const ketEl   = document.querySelector('.ket-radio:checked');

    if (!ketEl) {
        res.className = 'result-box error'; res.innerHTML = '❌ Pilih keterangan terlebih dahulu.'; res.style.display = 'block'; return;
    }

    const ket = ketEl.value;
    const isEdit = !!id;
    const url  = isEdit ? '/presensi/edit' : '/presensi/input';

    const fd = new FormData();
    fd.append('ket', ket);
    fd.append('catatan', catatan);
    if (isEdit) fd.append('id', id);
    else {
        fd.append('nis', nis);
        fd.append('tanggal_dari',   document.getElementById('modalTanggalDari').value);
        fd.append('tanggal_sampai', document.getElementById('modalTanggalSampai').value);
    }

    fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                res.className = 'result-box success';
                res.innerHTML = '✅ ' + data.message;
                res.style.display = 'block';
                setTimeout(() => { tutupModalPresensi(); location.reload(); }, 800);
            } else {
                res.className = 'result-box error';
                res.innerHTML = '❌ ' + data.message;
                res.style.display = 'block';
            }
        })
        .catch(() => {
            res.className = 'result-box error';
            res.innerHTML = '❌ Gagal menghubungi server.';
            res.style.display = 'block';
        });
}

function hapusPresensiAdmin() {
    const id  = document.getElementById('modalPresensiId').value;
    const res = document.getElementById('modalPresensiResult');

    if (!confirm('Hapus presensi ini?')) return;

    const fd = new FormData();
    fd.append('id', id);

    fetch('/presensi/hapus', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                tutupModalPresensi();
                location.reload();
            } else {
                res.className = 'result-box error';
                res.innerHTML = '❌ ' + data.message;
                res.style.display = 'block';
            }
        })
        .catch(() => {
            res.className = 'result-box error';
            res.innerHTML = '❌ Gagal menghubungi server.';
            res.style.display = 'block';
        });
}

// Tutup modal klik luar
document.getElementById('modalPresensi').addEventListener('click', function(e) {
    if (e.target === this) tutupModalPresensi();
});
</script>
JS;
$activePage = 'presensi';
require BASE_PATH . '/app/Views/layouts/app.php';