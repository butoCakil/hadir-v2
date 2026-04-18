<?php ob_start();

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
.rekap-bar { height:8px; border-radius:4px; overflow:hidden; background:var(--border); display:flex; margin-top:0.35rem; }
.rekap-bar-fill { height:100%; border-radius:4px; }
</style>
CSS;
?>

<!-- Breadcrumb -->
<div class="arsip-breadcrumb">
    <a href="/arsip"><i class="fa-solid fa-box-archive"></i> Arsip</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <span><?= htmlspecialchars($periode['nama_periode']) ?></span>
</div>

<!-- Nav arsip -->
<div class="arsip-nav">
    <a href="/arsip/<?= $periode['id'] ?>" class="active"><i class="fa-solid fa-chart-pie"></i> Ringkasan</a>
    <a href="/arsip/<?= $periode['id'] ?>/siswa"><i class="fa-solid fa-users"></i> Siswa</a>
    <a href="/arsip/<?= $periode['id'] ?>/rekap"><i class="fa-solid fa-print"></i> Rekap & Export</a>
</div>

<!-- Info periode -->
<div style="background:var(--blue-bg);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:0.875rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
    <i class="fa-solid fa-circle-info" style="color:var(--blue);"></i>
    <div style="font-size:0.83rem;">
        <strong style="color:var(--blue);"><?= htmlspecialchars($periode['nama_periode']) ?></strong>
        <span style="color:var(--text2);margin-left:0.75rem;">
            <?= date('d M Y', strtotime($periode['tanggal_mulai'])) ?> — <?= date('d M Y', strtotime($periode['tanggal_selesai'])) ?>
        </span>
        <?php if ($periode['aktif']): ?>
        <span style="background:var(--green-bg);color:var(--green);border-radius:20px;padding:0.1rem 0.6rem;font-size:0.7rem;font-weight:700;margin-left:0.5rem;">● AKTIF</span>
        <?php else: ?>
        <span style="background:var(--bg3);color:var(--text3);border-radius:20px;padding:0.1rem 0.6rem;font-size:0.7rem;margin-left:0.5rem;">Arsip</span>
        <?php endif; ?>
    </div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalSiswa) ?></div><div class="stat-label">Total Siswa</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-building"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalDudika) ?></div><div class="stat-label">DUDIKA</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple);"><i class="fa-solid fa-person-chalkboard"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalPembimbing) ?></div><div class="stat-label">Pembimbing</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--yellow-bg);color:var(--yellow);"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalPresensi) ?></div><div class="stat-label">Total Presensi</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Rekap kehadiran -->
    <div class="col-12 col-lg-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Rekap Kehadiran</span>
                <span style="font-size:0.75rem;color:var(--text3);">Sepanjang periode</span>
            </div>
            <div class="card-body-app">
                <?php
                $total = array_sum($rekap);
                $items = [
                    ['label'=>'Masuk', 'key'=>'masuk', 'color'=>'var(--green)',  'bg'=>'var(--green-bg)'],
                    ['label'=>'Izin',  'key'=>'izin',  'color'=>'var(--yellow)', 'bg'=>'var(--yellow-bg)'],
                    ['label'=>'Sakit', 'key'=>'sakit', 'color'=>'var(--red)',    'bg'=>'var(--red-bg)'],
                    ['label'=>'Libur', 'key'=>'libur', 'color'=>'var(--blue)',   'bg'=>'var(--blue-bg)'],
                ];
                foreach ($items as $item):
                    $val = (int)($rekap[$item['key']] ?? 0);
                    $pct = $total > 0 ? round($val / $total * 100, 1) : 0;
                ?>
                <div style="margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                        <span style="font-size:0.82rem;font-weight:600;"><?= $item['label'] ?></span>
                        <span style="font-size:0.82rem;color:var(--text2);"><?= number_format($val) ?> <span style="color:var(--text3);font-size:0.72rem;">(<?= $pct ?>%)</span></span>
                    </div>
                    <div class="rekap-bar">
                        <div class="rekap-bar-fill" style="width:<?= $pct ?>%;background:<?= $item['color'] ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--border);font-size:0.78rem;color:var(--text3);">
                    Total: <strong style="color:var(--text);"><?= number_format($total) ?></strong> catatan presensi
                </div>
            </div>
        </div>
    </div>

    <!-- Chart per bulan -->
    <div class="col-12 col-lg-8">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Presensi per Bulan</span>
            </div>
            <div class="card-body-app">
                <div style="position:relative;height:200px;">
                    <canvas id="chartBulan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rekap per kelas -->
<div class="col-12">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title">Rekap per Kelas</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg3);">
                        <th style="padding:0.6rem 1rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Kelas</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Siswa</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;color:var(--green);">Masuk</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;color:var(--yellow);">Izin</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;color:var(--red);">Sakit</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;color:var(--blue);">Libur</th>
                        <th style="padding:0.6rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rekapKelas as $i => $k):
                        $tot = (int)$k['masuk'] + (int)$k['izin'] + (int)$k['sakit'] + (int)$k['libur'];
                    ?>
                    <tr style="border-top:1px solid var(--border);<?= $i%2===1?'background:var(--bg3);':'' ?>">
                        <td style="padding:0.55rem 1rem;font-weight:600;font-size:0.84rem;"><?= htmlspecialchars($k['kelas']) ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;"><?= $k['total_siswa'] ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;color:var(--green);font-weight:600;"><?= number_format($k['masuk']) ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;color:var(--yellow);"><?= number_format($k['izin']) ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;color:var(--red);"><?= number_format($k['sakit']) ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;color:var(--blue);"><?= number_format($k['libur']) ?></td>
                        <td style="padding:0.55rem 1rem;text-align:center;font-size:0.82rem;font-weight:600;"><?= number_format($tot) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$chartLabelsJson = json_encode($chartLabels);
$chartDataJson   = json_encode($chartData);

$extraJs = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels = {$chartLabelsJson};
    const data   = {$chartDataJson};
    const ctx    = document.getElementById('chartBulan').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Presensi',
                data,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 6,
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,0.1)' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>
JS;

$activePage = 'arsip';
require BASE_PATH . '/app/Views/layouts/app.php';
