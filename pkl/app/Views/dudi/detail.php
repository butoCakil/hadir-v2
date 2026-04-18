<?php ob_start();

$extraCss = <<<CSS
<style>
.info-row { display:flex; gap:0.5rem; font-size:0.83rem; padding:0.35rem 0; border-bottom:1px solid var(--border); }
.info-row:last-child { border-bottom:none; }
.info-label { color:var(--text3); min-width:110px; flex-shrink:0; }
.info-value { color:var(--text); font-weight:500; word-break:break-word; }
.siswa-table { width:100%; border-collapse:collapse; }
.siswa-table thead th {
    background:var(--bg3); color:var(--text3); font-size:0.68rem;
    text-transform:uppercase; letter-spacing:0.05em; padding:0.6rem 0.75rem;
    border-bottom:1px solid var(--border); font-weight:700;
}
.siswa-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.1s; }
.siswa-table tbody tr:last-child { border-bottom:none; }
.siswa-table tbody tr:hover { background:var(--bg3); }
.siswa-table tbody td { padding:0.5rem 0.75rem; font-size:0.83rem; vertical-align:middle; }
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
</style>
CSS;
?>

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:var(--text3);margin-bottom:1.25rem;flex-wrap:wrap;">
    <a href="/dudi" style="color:var(--blue);text-decoration:none;"><i class="fa-solid fa-building"></i> Data DUDI</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
    <span><?= htmlspecialchars($dudi['nama']) ?></span>
</div>

<div class="row g-3">

    <!-- Info DUDI -->
    <div class="col-12 col-lg-4">
        <div class="card-app mb-3">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-building me-1" style="color:var(--blue)"></i> Info DUDI</span>
            </div>
            <div class="card-body-app">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value"><?= htmlspecialchars($dudi['nama']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat</span>
                    <span class="info-value"><?= htmlspecialchars($dudi['alamat'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lokasi</span>
                    <span class="info-value">
                        <?php if (!empty($dudi['link_map'])): ?>
                        <a href="<?= htmlspecialchars($dudi['link_map']) ?>" target="_blank"
                           style="color:var(--blue);text-decoration:none;display:inline-flex;align-items:center;gap:0.35rem;">
                            <i class="fa-solid fa-location-dot"></i> Buka Maps
                        </a>
                        <?php else:
                            $q = urlencode(($dudi['nama'] ?? '') . ' ' . ($dudi['alamat'] ?? ''));
                        ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= $q ?>" target="_blank"
                           style="color:var(--text3);text-decoration:none;display:inline-flex;align-items:center;gap:0.35rem;">
                            <i class="fa-solid fa-location-dot"></i> Cari di Maps
                        </a>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pembimbing</span>
                    <span class="info-value"><?= htmlspecialchars($dudi['nama_pembimbing'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pimpinan</span>
                    <span class="info-value"><?= htmlspecialchars($dudi['nama_owner'] ?? '-') ?></span>
                </div>
                <?php if (!empty($dudi['keterangan'])): ?>
                <div class="info-row">
                    <span class="info-label">Keterangan</span>
                    <span class="info-value"><?= htmlspecialchars($dudi['keterangan']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol WA DUDI -->
        <?php if ($dudi['ada_wa']): ?>
        <div class="card-app mb-3">
            <div class="card-body-app">
                <p style="font-size:0.78rem;color:var(--text2);margin-bottom:0.75rem;">
                    Hubungi DUDI ini langsung via WhatsApp:
                </p>
                <form method="POST" action="/dudi/wa" target="_blank">
                    <input type="hidden" name="type" value="dudi">
                    <input type="hidden" name="id" value="<?= $dudi['id'] ?>">
                    <button type="submit" class="btn-app" style="width:100%;background:var(--green-bg);color:var(--green);border:1px solid rgba(34,197,94,0.2);display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.1rem;"></i> Hubungi via WhatsApp
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <a href="/dudi" class="btn-app btn-ghost" style="text-decoration:none;display:inline-flex;width:100%;justify-content:center;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar DUDI
        </a>
    </div>

    <!-- Daftar Siswa -->
    <div class="col-12 col-lg-8">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-users me-1" style="color:var(--green)"></i> Siswa PKL di DUDI Ini</span>
                <span style="font-size:0.75rem;color:var(--text3);"><?= count($siswaList) ?> siswa</span>
            </div>

            <?php if (empty($siswaList)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text3);font-size:0.85rem;">
                <i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>
                Tidak ada siswa PKL di DUDI ini pada periode aktif.
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="siswa-table" id="tabelSiswa">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th style="text-align:center;">WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswaList as $i => $s): ?>
                        <tr>
                            <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                            <td style="font-family:monospace;font-size:0.78rem;color:var(--text3);"><?= htmlspecialchars($s['nis']) ?></td>
                            <td style="font-weight:600;"><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= htmlspecialchars($s['kelas']) ?></td>
                            <td style="text-align:center;">
                                <?php if ($s['ada_wa']): ?>
                                <form method="POST" action="/dudi/wa" target="_blank" style="display:inline;">
                                    <input type="hidden" name="type" value="siswa">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
$(document).ready(function() {
    if ($('#tabelSiswa').length) {
        $('#tabelSiswa').DataTable({
            pageLength: 25,
            order: [[3, 'asc'], [2, 'asc']],
            columnDefs: [{ orderable: false, targets: [0, 4] }],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: '_START_–_END_ dari _TOTAL_',
                paginate: { previous: '‹', next: '›' }
            }
        });
    }
});
</script>
JS;

$activePage = 'dudi';
require BASE_PATH . '/app/Views/layouts/public.php';
