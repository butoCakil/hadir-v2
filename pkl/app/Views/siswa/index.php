<?php
ob_start();
?>

<!-- Rekap Per Kelas -->
<?php if (!empty($rekapKelas)): ?>
<div class="row g-2 mb-3">
    <?php foreach ($rekapKelas as $r): ?>
    <div class="col-6 col-md-3 col-lg-2">
        <div class="rekap-mini">
            <div class="rm-title"><?= htmlspecialchars($r['kelas']) ?></div>
            <div class="rm-sub">Total: <?= $r['total_siswa'] ?> siswa</div>
            <div style="display:flex;gap:0.5rem;font-size:0.72rem;">
                <span style="color:var(--green);">✓ <?= $r['sudah_daftar'] ?> WA</span>
                <span style="color:var(--red);">✗ <?= $r['belum_daftar'] ?> belum</span>
            </div>
            <div class="progress-app mt-1">
                <div class="progress-bar-app" style="width:<?= $r['total_siswa']>0?round($r['sudah_daftar']/$r['total_siswa']*100):0 ?>%"></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabel Siswa -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title">Data Siswa PKL</span>
        <form method="GET" action="/siswa" class="d-flex gap-2 align-items-center flex-wrap">
            <select name="pembimbing" class="form-select form-select-sm" style="min-width:200px;" onchange="this.form.submit()">
                <option value="">-- Semua Pembimbing --</option>
                <?php foreach ($listPembimbing as $p): ?>
                    <option value="<?= htmlspecialchars($p['nama_pembimbing']) ?>"
                        <?= $filterPembimbing === $p['nama_pembimbing'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_pembimbing']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filterPembimbing): ?>
                <a href="/siswa" class="btn-app btn-ghost">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="p-3" style="overflow-x:auto;">
        <table id="tabelSiswa" class="table table-sm" style="width:100%">
            <thead>
                <tr>
                    <th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th>
                    <th>L/P</th><th>WA</th><th>Pembimbing</th><th>DUDIKA</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswa as $i => $s): ?>
                <tr>
                    <td style="color:var(--text3);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td><code style="color:var(--text2);font-size:0.78rem;"><?= htmlspecialchars($s['nis']) ?></code></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($s['nama']) ?></td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.75rem;font-weight:600;"><?= htmlspecialchars($s['kelas']) ?></span></td>
                    <td style="color:var(--text2);"><?= htmlspecialchars($s['lp'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($s['nohp'])): ?>
                            <span class="badge-wa"><i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($s['nohp']) ?></span>
                        <?php else: ?>
                            <span class="badge-no-wa">Belum</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.82rem;"><?= htmlspecialchars($s['nama_pembimbing'] ?? '-') ?></td>
                    <td style="font-size:0.82rem;color:var(--text2);"><?= htmlspecialchars($s['nama_dudika'] ?? '-') ?></td>
                    <td>
                        <a href="/siswa/<?= urlencode($s['nis']) ?>" class="btn-app btn-ghost btn-icon" title="Detail">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>ff
</div>

<?php
$content = ob_get_clean();
$extraJs = <<<JS
<script>
$(document).ready(function () {
    var dtSiswa = $('#tabelSiswa').DataTable({
        pageLength: 25,
        order: [[3, 'asc'], [2, 'asc']],
        columnDefs: [{ orderable: false, targets: [0, 8] }]
    });
    dtSiswa.on('draw', function() {
        dtSiswa.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
});
</script>
JS;
$activePage = 'siswa';
require BASE_PATH . '/app/Views/layouts/app.php';