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
.badge-ket { border-radius:4px; padding:0.15rem 0.5rem; font-size:0.72rem; font-weight:600; }
</style>
CSS;
?>

<!-- Breadcrumb -->
<div class="arsip-breadcrumb">
    <a href="/arsip"><i class="fa-solid fa-box-archive"></i> Arsip</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <a href="/arsip/<?= $periode['id'] ?>"><?= htmlspecialchars($periode['nama_periode']) ?></a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <span>Siswa</span>
</div>

<!-- Nav arsip -->
<div class="arsip-nav">
    <a href="/arsip/<?= $periode['id'] ?>"><i class="fa-solid fa-chart-pie"></i> Ringkasan</a>
    <a href="/arsip/<?= $periode['id'] ?>/siswa" class="active"><i class="fa-solid fa-users"></i> Siswa</a>
    <a href="/arsip/<?= $periode['id'] ?>/rekap"><i class="fa-solid fa-print"></i> Rekap & Export</a>
</div>

<!-- Filter -->
<div class="card-app mb-3">
    <div class="card-body-app" style="padding:0.875rem 1rem;">
        <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Kelas</label>
                <select name="kelas" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($listKelas as $k): ?>
                    <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= $filterKelas === $k['kelas'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['kelas']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Pembimbing</label>
                <select name="pembimbing" style="padding:0.4rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.82rem;">
                    <option value="">Semua Pembimbing</option>
                    <?php foreach ($listPembimbing as $p): ?>
                    <option value="<?= htmlspecialchars($p['nama_pembimbing']) ?>" <?= $filterPembimbing === $p['nama_pembimbing'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_pembimbing']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-app btn-primary-app" style="height:fit-content;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if ($filterKelas || $filterPembimbing): ?>
            <a href="/arsip/<?= $periode['id'] ?>/siswa" class="btn-app btn-ghost" style="height:fit-content;text-decoration:none;">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel siswa -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title"><i class="fa-solid fa-users me-1" style="color:var(--blue)"></i> Daftar Siswa</span>
        <span style="font-size:0.75rem;color:var(--text3);"><?= count($siswaList) ?> siswa ditemukan</span>
    </div>
    <div style="overflow-x:auto;">
        <table id="tabelSiswa" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--bg3);">
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">#</th>
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">NIS</th>
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Nama</th>
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Kelas</th>
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">DUDIKA</th>
                    <th style="padding:0.6rem 0.75rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Pembimbing</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--green);font-weight:700;">M</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--yellow);font-weight:700;">I</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--red);font-weight:700;">S</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--blue);font-weight:700;">L</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $i => $s): ?>
                <tr style="border-top:1px solid var(--border);<?= $i%2===1?'background:var(--bg3);':'' ?>">
                    <td style="padding:0.5rem 0.75rem;color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                    <td style="padding:0.5rem 0.75rem;font-family:monospace;font-size:0.78rem;color:var(--text3);"><?= htmlspecialchars($s['nis']) ?></td>
                    <td style="padding:0.5rem 0.75rem;font-size:0.84rem;font-weight:600;"><?= htmlspecialchars($s['nama']) ?></td>
                    <td style="padding:0.5rem 0.75rem;font-size:0.78rem;"><?= htmlspecialchars($s['kelas']) ?></td>
                    <td style="padding:0.5rem 0.75rem;font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($s['nama_dudika'] ?? '-') ?></td>
                    <td style="padding:0.5rem 0.75rem;font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($s['nama_pembimbing'] ?? '-') ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;font-size:0.82rem;color:var(--green);font-weight:600;"><?= (int)$s['masuk'] ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;font-size:0.82rem;color:var(--yellow);"><?= (int)$s['izin'] ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;font-size:0.82rem;color:var(--red);"><?= (int)$s['sakit'] ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;font-size:0.82rem;color:var(--blue);"><?= (int)$s['libur'] ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;">
                        <a href="/arsip/<?= $periode['id'] ?>/siswa/<?= htmlspecialchars($s['nis']) ?>"
                           style="font-size:0.72rem;padding:0.25rem 0.6rem;background:var(--blue-bg);color:var(--blue);border-radius:5px;text-decoration:none;display:inline-flex;align-items:center;gap:0.25rem;">
                            <i class="fa-solid fa-eye"></i> Detail
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

$extraJs = <<<'JS'
<script>
$(document).ready(function() {
    const dt = $('#tabelSiswa').DataTable({
        pageLength: 25,
        order: [[3,'asc'],[2,'asc']],
        columnDefs: [{ orderable: false, targets: [0, 10] }],
        language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: '_START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } }
    });
    dt.on('draw', function() {
        dt.column(0, { search:'applied', order:'applied' }).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    });
});
</script>
JS;

$activePage = 'arsip';
require BASE_PATH . '/app/Views/layouts/app.php';
