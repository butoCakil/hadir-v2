<?php ob_start();

$extraCss = <<<CSS
<style>
.filter-bar { display:flex; gap:0.65rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.25rem; }
.filter-bar select, .filter-bar input {
    padding:0.4rem 0.65rem; border-radius:6px; border:1px solid var(--border2);
    background:var(--bg3); color:var(--text); font-size:0.82rem;
}
.filter-bar input { min-width:180px; }
.dudi-table { width:100%; border-collapse:collapse; }
.dudi-table thead th {
    background:var(--bg3); color:var(--text3); font-size:0.68rem;
    text-transform:uppercase; letter-spacing:0.05em; padding:0.6rem 0.75rem;
    border-bottom:1px solid var(--border); font-weight:700; white-space:nowrap;
}
.dudi-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.1s; }
.dudi-table tbody tr:last-child { border-bottom:none; }
.dudi-table tbody tr:hover { background:var(--bg3); }
.dudi-table tbody td { padding:0.5rem 0.75rem; font-size:0.83rem; vertical-align:middle; }
.dudi-table tbody tr.no-siswa { opacity:0.55; }
.btn-wa {
    display:inline-flex; align-items:center; justify-content:center;
    width:30px; height:30px; border-radius:50%;
    background:var(--green-bg); color:var(--green);
    border:none; cursor:pointer; font-size:0.85rem;
    transition:all 0.15s;
}
.btn-wa:hover { background:var(--green); color:#fff; }
.btn-wa-disabled {
    display:inline-flex; align-items:center; justify-content:center;
    width:30px; height:30px; border-radius:50%;
    background:var(--bg3); color:var(--border2);
    font-size:0.85rem; cursor:default;
}
.map-link { color:var(--blue); font-size:1rem; }
.map-search { color:var(--text3); font-size:1rem; }
</style>
CSS;
?>

<!-- Stat mini -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-building"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalDudi) ?></div><div class="stat-label">Total DUDI</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body"><div class="stat-value"><?= number_format($totalSiswa) ?></div><div class="stat-label">Siswa PKL</div></div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card-app mb-3">
    <div class="card-body-app" style="padding:0.875rem 1rem;">
        <form method="GET" class="filter-bar">
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Cari DUDI</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama atau alamat...">
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--text3);display:block;margin-bottom:0.25rem;">Pembimbing</label>
                <select name="pembimbing">
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
            <?php if ($search || $filterPembimbing): ?>
            <a href="/dudi" class="btn-app btn-ghost" style="text-decoration:none;height:fit-content;">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel DUDI -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title"><i class="fa-solid fa-building me-1" style="color:var(--blue)"></i> Daftar DUDI PKL</span>
        <span style="font-size:0.75rem;color:var(--text3);"><?= count($dudiList) ?> DUDI ditemukan</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dudi-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama DUDI</th>
                    <th>Alamat</th>
                    <th>Map</th>
                    <th>Pembimbing</th>
                    <th style="text-align:center;">Siswa</th>
                    <th style="text-align:center;">WA</th>
                    <th style="text-align:center;">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dudiList as $i => $d):
                    $noSiswa = (int)$d['jumlah_siswa'] === 0;
                ?>
                <tr class="<?= $noSiswa ? 'no-siswa' : '' ?>">
                    <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                    <td style="font-weight:600;max-width:180px;white-space:normal;word-break:break-word;">
                        <?= htmlspecialchars($d['nama']) ?>
                        <?php if ($noSiswa): ?>
                        <span style="font-size:0.65rem;background:var(--bg3);color:var(--text3);border-radius:4px;padding:0.1rem 0.4rem;margin-left:0.35rem;">Tidak aktif</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text2);max-width:200px;white-space:normal;word-break:break-word;">
                        <?= htmlspecialchars($d['alamat'] ?? '-') ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if (!empty($d['link_map'])): ?>
                        <a href="<?= htmlspecialchars($d['link_map']) ?>" target="_blank" class="map-link" title="Buka di Google Maps">
                            <i class="fa-solid fa-location-dot"></i>
                        </a>
                        <?php else:
                            $q = urlencode(($d['nama'] ?? '') . ' ' . ($d['alamat'] ?? ''));
                        ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= $q ?>" target="_blank" class="map-search" title="Cari di Google Maps">
                            <i class="fa-solid fa-location-dot"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;"><?= htmlspecialchars($d['nama_pembimbing'] ?? '-') ?></td>
                    <td style="text-align:center;font-weight:600;font-size:0.85rem;">
                        <?= (int)$d['jumlah_siswa'] ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($d['ada_wa']): ?>
                        <form method="POST" action="/dudi/wa" target="_blank" style="display:inline;">
                            <input type="hidden" name="type" value="dudi">
                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                            <button type="submit" class="btn-wa" title="Hubungi via WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="btn-wa-disabled" title="Nomor WA tidak tersedia">
                            <i class="fa-brands fa-whatsapp"></i>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if (!empty($d['kode'])): ?>
                        <a href="/dudi/<?= urlencode($d['kode']) ?>"
                           style="font-size:0.72rem;padding:0.25rem 0.6rem;background:var(--blue-bg);color:var(--blue);border-radius:5px;text-decoration:none;display:inline-flex;align-items:center;gap:0.25rem;">
                            <i class="fa-solid fa-eye"></i> Detail
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($dudiList)): ?>
                <tr>
                    <td colspan="8" style="padding:2rem;text-align:center;color:var(--text3);">
                        <i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>
                        Tidak ada DUDI ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
$(document).ready(function() {
    $('.dudi-table').DataTable({
        pageLength: 25,
        order: [[5, 'desc'], [1, 'asc']],
        columnDefs: [{ orderable: false, targets: [0, 3, 6, 7] }],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: '_START_–_END_ dari _TOTAL_',
            paginate: { previous: '‹', next: '›' }
        }
    });
});
</script>
JS;

$activePage = 'dudi';
require BASE_PATH . '/app/Views/layouts/public.php';
