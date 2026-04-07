<?php
function namaHari(string $tgl): string {
    $map = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
    return $map[date('D', strtotime($tgl))] ?? '';
}
function namaBulanIndo(string $yearMonth): string {
    $map = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
            '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    [$y, $m] = explode('-', $yearMonth);
    return ($map[$m] ?? $m) . ' ' . $y;
}

ob_start();

$extraCss = <<<CSS
<style>
.info-card { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1rem; }
.siswa-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border); }
.siswa-nama-big { font-size:1.2rem; font-weight:700; }
.siswa-kelas-big { color:var(--blue); font-size:0.8rem; font-weight:600; margin-top:2px; }
.info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:0.875rem; }
.info-item .lbl { color:var(--text3); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.2rem; }
.info-item .val { font-size:0.875rem; font-weight:500; }

.rekap-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:0.625rem; margin-bottom:1rem; }
.rekap-item { background:var(--bg2); border:1px solid var(--border); border-radius:10px; padding:0.875rem 1rem; }
.rekap-item .num { font-size:1.75rem; font-weight:700; line-height:1; }
.rekap-item .lbl { color:var(--text3); font-size:0.7rem; margin-top:0.2rem; }
.rekap-item.m { border-left:3px solid var(--green); }
.rekap-item.i { border-left:3px solid var(--yellow); }
.rekap-item.s { border-left:3px solid var(--orange); }
.rekap-item.l { border-left:3px solid var(--border2); }

.view-toggle { display:inline-flex; background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:3px; gap:2px; margin-bottom:1rem; }
.view-toggle a { padding:0.3rem 1rem; border-radius:6px; font-size:0.78rem; color:var(--text2); text-decoration:none; display:flex; align-items:center; gap:0.35rem; transition:all 0.15s; }
.view-toggle a.active { background:var(--blue); color:white; font-weight:600; }

/* Kalender */
.kalender-wrap { display:flex; flex-direction:column; gap:1.5rem; }
.bulan-card { background:var(--bg2); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.bulan-header { padding:0.75rem 1.25rem; background:var(--bg3); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; }
.bulan-nama { font-weight:700; font-size:0.9rem; }
.bulan-rekap { display:flex; gap:0.5rem; flex-wrap:wrap; }
.bulan-rekap span { font-size:0.7rem; padding:0.1rem 0.45rem; border-radius:3px; font-weight:600; }
.kal-head-row { display:grid; grid-template-columns:repeat(7,1fr); border-bottom:1px solid var(--border); }
.kal-head { text-align:center; padding:0.45rem 0; font-size:0.65rem; font-weight:700; color:var(--text3); text-transform:uppercase; }
.kal-head.weekend { color:var(--orange); }
.kal-body { display:grid; grid-template-columns:repeat(7,1fr); }
.kal-cell { min-height:68px; padding:0.4rem; border-right:1px solid var(--border); border-bottom:1px solid var(--border); position:relative; }
.kal-cell:nth-child(7n) { border-right:none; }
.kal-cell:nth-last-child(-n+7) { border-bottom:none; }
.kal-cell.empty { background:var(--bg3); opacity:0.4; }
.kal-cell.today { background:var(--blue-bg); }
.kal-cell.today .kal-tgl { color:var(--blue); font-weight:700; background:var(--blue-bg); border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
.kal-cell.weekend-cell { background:var(--bg3); opacity:0.7; }
.kal-tgl { font-size:0.72rem; color:var(--text3); margin-bottom:0.3rem; width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
.kal-ket { display:block; text-align:center; border-radius:5px; font-size:0.68rem; font-weight:700; padding:0.2rem 0.1rem; }
.kal-ket.masuk { background:var(--green-bg);  color:var(--green); }
.kal-ket.izin  { background:var(--yellow-bg); color:var(--yellow); }
.kal-ket.sakit { background:var(--orange-bg); color:var(--orange); }
.kal-ket.libur { background:var(--bg3); color:var(--text2); }
.kal-foto { position:absolute; top:4px; right:4px; color:var(--blue); font-size:0.6rem; }

/* Tabel detail */
.tabel-wrap { background:var(--bg2); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.tabel-header { padding:0.875rem 1.25rem; border-bottom:1px solid var(--border); }
table.detail-table { width:100%; border-collapse:collapse; }
table.detail-table thead tr { background:var(--bg3); }
table.detail-table thead th { color:var(--text3); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.06em; padding:0.65rem 1rem; border-bottom:1px solid var(--border); white-space:nowrap; }
table.detail-table tbody tr { border-bottom:1px solid var(--border); }
table.detail-table tbody tr:last-child { border-bottom:none; }
table.detail-table tbody tr:hover { background:var(--bg3); }
table.detail-table tbody td { padding:0.65rem 1rem; font-size:0.83rem; vertical-align:middle; }
table.detail-table tbody tr.row-masuk { border-left:3px solid var(--green); }
table.detail-table tbody tr.row-izin  { border-left:3px solid var(--yellow); }
table.detail-table tbody tr.row-sakit { border-left:3px solid var(--orange); }
table.detail-table tbody tr.row-libur { border-left:3px solid var(--border2); }
.ket-badge { display:inline-flex; align-items:center; gap:4px; padding:0.2rem 0.55rem; border-radius:20px; font-size:0.72rem; font-weight:600; }
.ket-badge.masuk { background:var(--green-bg);  color:var(--green);  border:1px solid rgba(34,197,94,0.25); }
.ket-badge.izin  { background:var(--yellow-bg); color:var(--yellow); border:1px solid rgba(245,158,11,0.25); }
.ket-badge.sakit { background:var(--orange-bg); color:var(--orange); border:1px solid rgba(249,115,22,0.25); }
.ket-badge.libur { background:var(--bg3); color:var(--text2); border:1px solid var(--border); }

@media (max-width:768px) {
    .rekap-bar { grid-template-columns:repeat(2,1fr); }
    .info-grid  { grid-template-columns:repeat(2,1fr); }
}
</style>
CSS;
?>

<a href="/siswa" class="btn-app btn-ghost mb-3" style="display:inline-flex;">
    <i class="fa-solid fa-arrow-left"></i> Kembali
</a>

<!-- Info Siswa -->
<div class="info-card">
    <div class="siswa-header">
        <div>
            <div class="siswa-nama-big"><?= htmlspecialchars($siswa['nama']) ?></div>
            <div class="siswa-kelas-big"><?= htmlspecialchars($siswa['kelas']) ?> &nbsp;·&nbsp; NIS: <?= htmlspecialchars($siswa['nis']) ?></div>
        </div>
        <?php if ($periode): ?>
        <div style="font-size:0.72rem;color:var(--text2);text-align:right;">
            <div style="color:var(--blue);font-weight:600;margin-bottom:2px;"><?= htmlspecialchars($namaPeriode) ?></div>
            <div><?= date('d M Y', strtotime($tanggalMulai)) ?> — <?= date('d M Y', strtotime($tanggalAkhir)) ?></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="info-grid">
        <div class="info-item"><div class="lbl">Jurusan</div><div class="val"><?= htmlspecialchars($siswa['jur']??'-') ?></div></div>
        <div class="info-item"><div class="lbl">L/P</div><div class="val"><?= htmlspecialchars($siswa['lp']??'-') ?></div></div>
        <div class="info-item">
            <div class="lbl">No. WhatsApp</div>
            <div class="val">
                <?php if (!empty($siswa['nohp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/^0/','62',$siswa['nohp']) ?>" target="_blank" style="color:var(--green);text-decoration:none;">
                        <i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($siswa['nohp']) ?>
                    </a>
                <?php else: ?><span style="color:var(--orange);">Belum terdaftar</span><?php endif; ?>
            </div>
        </div>
        <div class="info-item"><div class="lbl">DUDIKA</div><div class="val"><?= htmlspecialchars($siswa['nama_dudika']??'-') ?></div></div>
        <div class="info-item"><div class="lbl">Alamat DUDIKA</div><div class="val" style="font-size:0.8rem;"><?= htmlspecialchars($siswa['alamat_dudika']??'-') ?></div></div>
        <div class="info-item"><div class="lbl">Pembimbing</div><div class="val"><?= htmlspecialchars($siswa['nama_pembimbing']??'-') ?></div></div>
    </div>
</div>

<!-- Rekap -->
<div class="rekap-bar">
    <div class="rekap-item m"><div class="num" style="color:var(--green)"><?= $rekap['masuk'] ?></div><div class="lbl"><i class="fa-solid fa-circle-check me-1"></i>Masuk</div></div>
    <div class="rekap-item i"><div class="num" style="color:var(--yellow)"><?= $rekap['izin'] ?></div><div class="lbl"><i class="fa-solid fa-clock me-1"></i>Izin</div></div>
    <div class="rekap-item s"><div class="num" style="color:var(--orange)"><?= $rekap['sakit'] ?></div><div class="lbl"><i class="fa-solid fa-heart-pulse me-1"></i>Sakit</div></div>
    <div class="rekap-item l"><div class="num" style="color:var(--text2)"><?= $rekap['libur'] ?></div><div class="lbl"><i class="fa-solid fa-umbrella-beach me-1"></i>Libur</div></div>
</div>

<!-- View Toggle -->
<div class="view-toggle">
    <a href="/siswa/<?= urlencode($siswa['nis']) ?>?view=kalender" class="<?= $viewMode==='kalender'?'active':'' ?>">
        <i class="fa-solid fa-calendar-days"></i> Kalender
    </a>
    <a href="/siswa/<?= urlencode($siswa['nis']) ?>?view=tabel" class="<?= $viewMode==='tabel'?'active':'' ?>">
        <i class="fa-solid fa-table-list"></i> Detail Tabel
    </a>
</div>

<?php if ($viewMode === 'kalender'): ?>
<!-- Kalender -->
<div class="kalender-wrap">
    <?php foreach ($bulanPeriode as $ym):
        [$y, $m]     = explode('-', $ym);
        $jumlahHari  = cal_days_in_month(CAL_GREGORIAN, (int)$m, (int)$y);
        $hariPertama = date('N', strtotime("$ym-01"));
        $today       = date('Y-m-d');
        $rMasuk = $rIzin = $rSakit = $rLibur = 0;
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $tgl = sprintf('%s-%02d', $ym, $d);
            if (isset($presensiKalender[$tgl])) {
                $k = strtolower($presensiKalender[$tgl]['ket']);
                if ($k==='masuk') $rMasuk++;
                elseif ($k==='izin') $rIzin++;
                elseif ($k==='sakit') $rSakit++;
                elseif ($k==='libur') $rLibur++;
            }
        }
    ?>
    <div class="bulan-card">
        <div class="bulan-header">
            <div class="bulan-nama"><?= namaBulanIndo($ym) ?></div>
            <div class="bulan-rekap">
                <?php if ($rMasuk>0): ?><span style="background:var(--green-bg);color:var(--green);"><?= $rMasuk ?>× Masuk</span><?php endif; ?>
                <?php if ($rIzin>0):  ?><span style="background:var(--yellow-bg);color:var(--yellow);"><?= $rIzin ?>× Izin</span><?php endif; ?>
                <?php if ($rSakit>0): ?><span style="background:var(--orange-bg);color:var(--orange);"><?= $rSakit ?>× Sakit</span><?php endif; ?>
                <?php if ($rLibur>0): ?><span style="background:var(--bg3);color:var(--text2);"><?= $rLibur ?>× Libur</span><?php endif; ?>
                <?php if ($rMasuk+$rIzin+$rSakit+$rLibur===0): ?><span style="color:var(--text3);font-size:0.7rem;">Tidak ada presensi</span><?php endif; ?>
            </div>
        </div>
        <div class="kal-head-row">
            <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $idx => $hl): ?>
            <div class="kal-head <?= $idx>=5?'weekend':'' ?>"><?= $hl ?></div>
            <?php endforeach; ?>
        </div>
        <div class="kal-body">
            <?php for ($i=1; $i<$hariPertama; $i++): ?><div class="kal-cell empty"></div><?php endfor; ?>
            <?php for ($d=1; $d<=$jumlahHari; $d++):
                $tgl  = sprintf('%s-%02d', $ym, $d);
                $dow  = (int)date('N', strtotime($tgl));
                $p    = $presensiKalender[$tgl] ?? null;
                $ket  = $p ? strtolower($p['ket']) : null;
                $isToday   = $tgl===$today;
                $isWeekend = in_array($dow,[6,7]);
                $cls = 'kal-cell'.($isToday?' today':'').($isWeekend&&!$isToday?' weekend-cell':'');
            ?>
            <div class="<?= $cls ?>">
                <div class="kal-tgl"><?= $d ?></div>
                <?php if ($ket):
                    $icon = match($ket){'masuk'=>'✓','izin'=>'~','sakit'=>'+','libur'=>'−',default=>'?'};
                ?>
                    <span class="kal-ket <?= $ket ?>"><?= $icon ?> <?= ucfirst($ket) ?></span>
                    <?php if (!empty($p['link']) && $p['statuslink']==='OK'): ?>
                        <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" class="kal-foto" title="Foto"><i class="fa-solid fa-image"></i></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
            <?php $hariTerakhir=(int)date('N',strtotime("$ym-$jumlahHari")); for($i=$hariTerakhir;$i<7;$i++): ?><div class="kal-cell empty"></div><?php endfor; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Tabel Detail -->
<div class="tabel-wrap">
    <div class="tabel-header">
        <span style="font-weight:600;font-size:0.9rem;">
            <i class="fa-solid fa-clipboard-list me-2" style="color:var(--blue)"></i>
            Riwayat Presensi
            <span style="color:var(--text3);font-weight:400;font-size:0.78rem;">(<?= count($presensiTabel) ?> data)</span>
        </span>
    </div>
    <div style="overflow-x:auto;">
        <?php if (empty($presensiTabel)): ?>
            <div style="text-align:center;padding:3rem;color:var(--text3);">
                <i class="fa-solid fa-inbox fa-2x" style="opacity:0.3;display:block;margin-bottom:0.75rem;"></i>Belum ada data presensi.
            </div>
        <?php else: ?>
        <table id="tabelDetail" class="detail-table">
            <thead><tr><th>No</th><th>Tanggal</th><th>Hari</th><th>Waktu</th><th>Status</th><th>Catatan Kegiatan</th><th>Foto</th><th>Kode</th></tr></thead>
            <tbody>
                <?php foreach ($presensiTabel as $i => $p):
                    $ket  = strtolower($p['ket']);
                    $ts   = strtotime($p['timestamp']);
                    $icon = match($ket){'masuk'=>'circle-check','izin'=>'clock','sakit'=>'heart-pulse','libur'=>'umbrella-beach',default=>'circle'};
                ?>
                <tr class="row-<?= $ket ?>">
                    <td style="color:var(--text3);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td style="white-space:nowrap;font-weight:600;"><?= date('d M Y',$ts) ?></td>
                    <td style="color:var(--text2);font-size:0.8rem;"><?= namaHari(date('Y-m-d',$ts)) ?></td>
                    <td style="color:var(--text2);font-size:0.8rem;"><?= date('H:i',$ts) ?></td>
                    <td><span class="ket-badge <?= $ket ?>"><i class="fa-solid fa-<?= $icon ?>"></i> <?= htmlspecialchars($p['ket']) ?></span></td>
                    <td style="font-size:0.82rem;color:var(--text2);max-width:250px;"><?= htmlspecialchars($p['catatan']??'-') ?></td>
                    <td>
                        <?php if (!empty($p['link']) && $p['statuslink']==='OK'): ?>
                            <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" style="color:var(--blue);font-size:0.85rem;"><i class="fa-solid fa-image me-1"></i>Lihat</a>
                        <?php else: ?><span style="color:var(--text3);">—</span><?php endif; ?>
                    </td>
                    <td><code style="color:var(--text3);font-size:0.72rem;"><?= htmlspecialchars($p['kode']) ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$extraJs = <<<'JS'
<script>
$(function () {
    if ($('#tabelDetail').length) {
        $('#tabelDetail').DataTable({
            pageLength: 25,
            order: [[1,'desc']],
            columnDefs:[{orderable:false,targets:[6,7]}]
        });
    }
});
</script>
JS;
$activePage = 'siswa';
require BASE_PATH . '/app/Views/layouts/app.php';