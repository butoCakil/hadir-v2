<?php
ob_start();
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-building"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalDudika) ?></div>
                <div class="stat-label">Total DUDIKA</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalSiswa) ?></div>
                <div class="stat-label">Total Siswa Ditempatkan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-brands fa-whatsapp"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalSudah) ?></div>
                <div class="stat-label">Sudah Daftar WA</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--red-bg);color:var(--red);"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalBelum) ?></div>
                <div class="stat-label">Belum Daftar WA</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Rekap Per DUDIKA -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title">Rekap Per DUDIKA</span>
    </div>
    <div class="p-3" style="overflow-x:auto;">
        <table id="tabelPenempatan" class="table table-sm" style="width:100%">
            <thead>
                <tr>
                    <th>No</th><th>Nama DUDIKA</th><th>Pembimbing</th>
                    <th>WA Pembimbing</th><th>Total Siswa</th>
                    <th>WA Terdaftar</th><th>Progress</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapDudika as $i => $d):
                    $persen       = $d['total'] > 0 ? round(($d['sudah_wa']/$d['total'])*100) : 0;
                    $nohp         = $d['nohp_pembimbing'] ?? '';
                    $wa62         = $nohp ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $nohp)) : '';
                    $barColor     = $persen >= 100 ? 'var(--green)' : ($persen >= 50 ? 'var(--yellow)' : 'var(--red)');
                ?>
                <tr>
                    <td style="color:var(--text3);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($d['nama_dudika']) ?></td>
                    <td style="font-size:0.82rem;"><?= htmlspecialchars($d['nama_pembimbing'] ?? '-') ?></td>
                    <td>
                        <?php if ($wa62): ?>
                            <a href="https://wa.me/<?= $wa62 ?>" target="_blank"
                               style="color:var(--green);text-decoration:none;font-size:0.8rem;">
                                <i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($nohp) ?>
                            </a>
                        <?php else: ?>
                            <span style="color:var(--text3);">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= $d['total'] ?></td>
                    <td>
                        <span class="badge-wa"><?= $d['sudah_wa'] ?></span>
                        <?php if ($d['belum_wa'] > 0): ?>
                            <span class="badge-no-wa ms-1"><?= $d['belum_wa'] ?> belum</span>
                        <?php endif; ?>
                    </td>
                    <td style="min-width:100px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-app flex-grow-1">
                                <div class="progress-bar-app" style="width:<?= $persen ?>%;background:<?= $barColor ?>"></div>
                            </div>
                            <span style="font-size:0.72rem;color:var(--text3);white-space:nowrap;"><?= $persen ?>%</span>
                        </div>
                    </td>
                    <td>
                        <a href="/penempatan/detail/<?= urlencode($d['nama_dudika']) ?>"
                           class="btn-app btn-ghost btn-icon" title="Detail">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraJs = <<<JS
<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [6, 7] }]
    });
});
</script>
JS;
$activePage = 'penempatan';
require BASE_PATH . '/app/Views/layouts/app.php';