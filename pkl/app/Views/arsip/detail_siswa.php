<?php ob_start();

$extraCss = <<<CSS
<style>
.arsip-breadcrumb { display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; color:var(--text3); margin-bottom:1.25rem; flex-wrap:wrap; }
.arsip-breadcrumb a { color:var(--blue); text-decoration:none; }
.arsip-breadcrumb a:hover { text-decoration:underline; }

/* Kalender */
.kal-wrap { margin-bottom:1.5rem; }
.kal-month-title { font-size:0.82rem; font-weight:700; color:var(--text2); margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em; }
.kal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.kal-header { font-size:0.65rem; text-align:center; color:var(--text3); font-weight:600; padding:0.2rem 0; }
.kal-day {
    aspect-ratio:1; display:flex; align-items:center; justify-content:center;
    border-radius:6px; font-size:0.72rem; font-weight:600; cursor:default;
    position:relative;
}
.kal-day.empty { background:transparent; }
.kal-day.masuk  { background:var(--green-bg);  color:var(--green); }
.kal-day.izin   { background:var(--yellow-bg); color:var(--yellow); }
.kal-day.sakit  { background:var(--red-bg);    color:var(--red); }
.kal-day.libur  { background:var(--blue-bg);   color:var(--blue); }
.kal-day.nodata { background:var(--bg3); color:var(--text3); }
.kal-day.today  { outline:2px solid var(--blue); outline-offset:1px; }

/* Rekap bar */
.rekap-stat { display:flex; align-items:center; gap:0.75rem; padding:0.45rem 0; border-bottom:1px solid var(--border); }
.rekap-stat:last-child { border-bottom:none; }
.rekap-stat-label { font-size:0.8rem; font-weight:600; width:60px; }
.rekap-stat-val { font-size:0.8rem; font-weight:700; width:36px; text-align:right; }
.rekap-stat-bar { flex:1; height:6px; border-radius:3px; background:var(--border); overflow:hidden; }
.rekap-stat-fill { height:100%; border-radius:3px; }

/* View toggle */
.view-toggle { display:flex; gap:0.25rem; background:var(--bg3); border:1px solid var(--border); border-radius:7px; padding:3px; }
.view-toggle a { padding:0.3rem 0.75rem; border-radius:5px; font-size:0.75rem; font-weight:600; text-decoration:none; color:var(--text2); }
.view-toggle a.active { background:var(--bg2); color:var(--text); box-shadow:0 1px 3px rgba(0,0,0,0.15); }

/* Read-only badge */
.readonly-badge { display:inline-flex; align-items:center; gap:0.35rem; background:var(--yellow-bg); color:var(--yellow); border-radius:6px; padding:0.25rem 0.65rem; font-size:0.72rem; font-weight:600; }
</style>
CSS;

$namaBulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                   '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                   '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
$hariPendek = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
$today = date('Y-m-d');
?>

<!-- Breadcrumb -->
<div class="arsip-breadcrumb">
    <a href="/arsip"><i class="fa-solid fa-box-archive"></i> Arsip</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <a href="/arsip/<?= $periode['id'] ?>"><?= htmlspecialchars($periode['nama_periode']) ?></a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <a href="/arsip/<?= $periode['id'] ?>/siswa">Siswa</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <span><?= htmlspecialchars($siswa['nama']) ?></span>
</div>

<div class="row g-3">

    <!-- Kolom kiri: info siswa + rekap -->
    <div class="col-12 col-lg-4">

        <!-- Info siswa -->
        <div class="card-app mb-3">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-user me-1" style="color:var(--blue)"></i> Info Siswa</span>
                <span class="readonly-badge"><i class="fa-solid fa-lock"></i> Read Only</span>
            </div>
            <div class="card-body-app">
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <?php
                    $infoRows = [
                        ['label'=>'NIS',         'value'=>$siswa['nis']],
                        ['label'=>'Nama',         'value'=>$siswa['nama']],
                        ['label'=>'Kelas',        'value'=>$siswa['kelas']],
                        ['label'=>'DUDIKA',       'value'=>$siswa['nama_dudika'] ?? '-'],
                        ['label'=>'Alamat',       'value'=>$siswa['alamat_dudika'] ?? '-'],
                        ['label'=>'Pembimbing',   'value'=>$siswa['nama_pembimbing'] ?? '-'],
                        ['label'=>'No. HP',       'value'=>$siswa['nohp'] ?? '-'],
                    ];
                    foreach ($infoRows as $row): ?>
                    <div style="display:flex;gap:0.5rem;font-size:0.82rem;">
                        <span style="color:var(--text3);min-width:90px;"><?= $row['label'] ?></span>
                        <span style="color:var(--text);font-weight:500;"><?= htmlspecialchars($row['value']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Rekap -->
        <div class="card-app mb-3">
            <div class="card-header-app">
                <span class="card-title">Rekap Kehadiran</span>
                <span style="font-size:0.72rem;color:var(--text3);"><?= htmlspecialchars($namaPeriode) ?></span>
            </div>
            <div class="card-body-app">
                <?php
                $totalRekap = (int)$rekap['total'];
                $rekapItems = [
                    ['label'=>'Masuk', 'key'=>'masuk', 'color'=>'var(--green)'],
                    ['label'=>'Izin',  'key'=>'izin',  'color'=>'var(--yellow)'],
                    ['label'=>'Sakit', 'key'=>'sakit', 'color'=>'var(--red)'],
                    ['label'=>'Libur', 'key'=>'libur', 'color'=>'var(--blue)'],
                ];
                foreach ($rekapItems as $ri):
                    $val = (int)($rekap[$ri['key']] ?? 0);
                    $pct = $totalRekap > 0 ? round($val / $totalRekap * 100) : 0;
                ?>
                <div class="rekap-stat">
                    <span class="rekap-stat-label" style="color:<?= $ri['color'] ?>"><?= $ri['label'] ?></span>
                    <div class="rekap-stat-bar">
                        <div class="rekap-stat-fill" style="width:<?= $pct ?>%;background:<?= $ri['color'] ?>;"></div>
                    </div>
                    <span class="rekap-stat-val"><?= $val ?></span>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:0.75rem;font-size:0.78rem;color:var(--text3);">
                    Total: <strong style="color:var(--text);"><?= $totalRekap ?></strong> hari tercatat
                </div>
            </div>
        </div>

        <!-- Kembali -->
        <a href="/arsip/<?= $periode['id'] ?>/siswa" class="btn-app btn-ghost" style="text-decoration:none;display:inline-flex;width:100%;justify-content:center;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Siswa
        </a>
    </div>

    <!-- Kolom kanan: kalender / tabel -->
    <div class="col-12 col-lg-8">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title">Riwayat Presensi</span>
                <div class="view-toggle">
                    <a href="?view=kalender" class="<?= $viewMode==='kalender'?'active':'' ?>"><i class="fa-solid fa-calendar"></i> Kalender</a>
                    <a href="?view=tabel"    class="<?= $viewMode==='tabel'?'active':'' ?>"><i class="fa-solid fa-table"></i> Tabel</a>
                </div>
            </div>
            <div class="card-body-app">

                <?php if ($viewMode === 'kalender'): ?>
                <!-- Kalender -->
                <?php foreach ($bulanPeriode as $ym):
                    [$tY, $tM] = explode('-', $ym);
                    $jumlahHari = cal_days_in_month(CAL_GREGORIAN, (int)$tM, (int)$tY);
                    $hariPertama = date('N', strtotime("$tY-$tM-01")); // 1=Mon..7=Sun
                ?>
                <div class="kal-wrap">
                    <div class="kal-month-title"><?= $namaBulanIndo[$tM] ?> <?= $tY ?></div>
                    <div class="kal-grid">
                        <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h): ?>
                        <div class="kal-header"><?= $h ?></div>
                        <?php endforeach; ?>
                        <?php for ($blank = 1; $blank < $hariPertama; $blank++): ?>
                        <div class="kal-day empty"></div>
                        <?php endfor; ?>
                        <?php for ($d = 1; $d <= $jumlahHari; $d++):
                            $tgl   = sprintf('%s-%s-%02d', $tY, $tM, $d);
                            $pres  = $presensiKalender[$tgl] ?? null;
                            $kls   = $pres ? strtolower($pres['ket']) : 'nodata';
                            $isToday = $tgl === $today;
                            $title = $pres ? ucfirst($pres['ket']) . ($pres['catatan'] ? ': ' . $pres['catatan'] : '') : 'Tidak ada presensi';
                        ?>
                        <div class="kal-day <?= $kls ?><?= $isToday?' today':'' ?>" title="<?= htmlspecialchars($title) ?>">
                            <?= $d ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Legend -->
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.5rem;font-size:0.72rem;">
                    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--green-bg);vertical-align:middle;margin-right:4px;"></span>Masuk</span>
                    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--yellow-bg);vertical-align:middle;margin-right:4px;"></span>Izin</span>
                    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--red-bg);vertical-align:middle;margin-right:4px;"></span>Sakit</span>
                    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--blue-bg);vertical-align:middle;margin-right:4px;"></span>Libur</span>
                    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--bg3);vertical-align:middle;margin-right:4px;"></span>Tidak ada</span>
                </div>

                <?php else: ?>
                <!-- Tabel -->
                <div style="overflow-x:auto;">
                    <table id="tabelPresensi" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg3);">
                                <th style="padding:0.55rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Tanggal</th>
                                <th style="padding:0.55rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Ket</th>
                                <th style="padding:0.55rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Catatan</th>
                                <th style="padding:0.55rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Kode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presensiTabel as $i => $pr):
                                $ketColor = ['masuk'=>'var(--green)','izin'=>'var(--yellow)','sakit'=>'var(--red)','libur'=>'var(--blue)'];
                                $ketBg    = ['masuk'=>'var(--green-bg)','izin'=>'var(--yellow-bg)','sakit'=>'var(--red-bg)','libur'=>'var(--blue-bg)'];
                                $kl = strtolower($pr['ket']);
                            ?>
                            <tr style="border-top:1px solid var(--border);<?= $i%2===1?'background:var(--bg3);':'' ?>">
                                <td style="padding:0.5rem 0.75rem;font-size:0.82rem;">
                                    <?php
                                    $tgl = strtotime($pr['timestamp']);
                                    $hariEn = date('D', $tgl);
                                    $hariId = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
                                    echo $hariId[$hariEn] . ', ' . date('d', $tgl) . ' ' . $namaBulanIndo[date('m', $tgl)] . ' ' . date('Y', $tgl);
                                    ?>
                                    <span style="color:var(--text3);font-size:0.72rem;margin-left:0.35rem;"><?= date('H:i', $tgl) ?></span>
                                </td>
                                <td style="padding:0.5rem 0.75rem;text-align:center;">
                                    <span style="background:<?= $ketBg[$kl] ?? 'var(--bg3)' ?>;color:<?= $ketColor[$kl] ?? 'var(--text)' ?>;border-radius:5px;padding:0.15rem 0.6rem;font-size:0.72rem;font-weight:700;">
                                        <?= htmlspecialchars($pr['ket']) ?>
                                    </span>
                                </td>
                                <td style="padding:0.5rem 0.75rem;font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($pr['catatan'] ?: '-') ?></td>
                                <td style="padding:0.5rem 0.75rem;text-align:center;font-family:monospace;font-size:0.72rem;color:var(--text3);"><?= htmlspecialchars($pr['kode'] ?: '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($presensiTabel)): ?>
                            <tr><td colspan="4" style="padding:2rem;text-align:center;color:var(--text3);font-size:0.82rem;">Tidak ada data presensi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
$(document).ready(function() {
    if ($('#tabelPresensi').length) {
        $('#tabelPresensi').DataTable({
            pageLength: 25,
            order: [[0,'desc']],
            language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: '_START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } }
        });
    }
});
</script>
JS;

$activePage = 'arsip';
require BASE_PATH . '/app/Views/layouts/app.php';
