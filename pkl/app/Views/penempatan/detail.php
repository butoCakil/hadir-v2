<?php
ob_start();
?>

<a href="/penempatan" class="btn-app btn-ghost mb-3" style="display:inline-flex;">
    <i class="fa-solid fa-arrow-left"></i> Kembali ke Penempatan
</a>

<!-- Info DUDIKA -->
<div class="card-app mb-3">
    <div class="card-header-app">
        <span style="font-weight:700;font-size:1rem;">
            <i class="fa-solid fa-building me-2" style="color:var(--blue)"></i>
            <?= htmlspecialchars($dudika) ?>
        </span>
    </div>
    <?php if (!empty($siswa)): ?>
    <div class="card-body-app">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">Pembimbing</div>
                <div style="font-weight:500;"><?= htmlspecialchars($siswa[0]['nama_pembimbing'] ?? '-') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">WA Pembimbing</div>
                <div>
                    <?php
                        $nohpPb = $siswa[0]['nohp_pembimbing'] ?? '';
                        $waPb   = $nohpPb ? preg_replace('/^0/','62',preg_replace('/[^0-9]/','', $nohpPb)) : '';
                    ?>
                    <?php if ($waPb): ?>
                        <a href="https://wa.me/<?= $waPb ?>" target="_blank" style="color:var(--green);text-decoration:none;font-size:0.85rem;">
                            <i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($nohpPb) ?>
                        </a>
                    <?php else: ?><span style="color:var(--text3);">-</span><?php endif; ?>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">Total Siswa</div>
                <div style="font-weight:600;"><?= count($siswa) ?> siswa</div>
            </div>
            <div class="col-6 col-md-3">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">Sudah WA</div>
                <div>
                    <?php $sudahWa = count(array_filter($siswa, fn($s) => !empty($s['nohp_siswa']))); ?>
                    <span class="badge-wa"><?= $sudahWa ?></span>
                    <?php if (count($siswa)-$sudahWa > 0): ?>
                        <span class="badge-no-wa ms-1"><?= count($siswa)-$sudahWa ?> belum</span>
                    <?php endif; ?>
                </div>
            </div>
        
            <!-- ++ Tambahan baru ++ -->
            <div class="col-12 col-md-6">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">Alamat DUDIKA</div>
                <div style="font-size:0.85rem;"><?= htmlspecialchars($siswa[0]['alamat_dudika'] ?? '-') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div style="color:var(--text3);font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.2rem;">No. Telepon DUDIKA</div>
                <div style="font-size:0.85rem;">
                    <?php $telp = $siswa[0]['nomor_telepon_dudika'] ?? ''; ?>
                    <?php if ($telp): ?>
                        <a href="tel:<?= htmlspecialchars($telp) ?>" style="color:var(--text);text-decoration:none;">
                            <i class="fa-solid fa-phone me-1" style="font-size:0.75rem;color:var(--text3);"></i><?= htmlspecialchars($telp) ?>
                        </a>
                    <?php else: ?>-<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tabel Siswa -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title">Daftar Siswa</span>
    </div>
    <div class="p-3" style="overflow-x:auto;">
        <table id="tabelSiswa" class="table table-sm" style="width:100%">
            <thead>
                <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>No. WhatsApp</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($siswa as $i => $s):
                    $nohp = $s['nohp_siswa'] ?? '';
                    $wa   = $nohp ? preg_replace('/^0/','62',preg_replace('/[^0-9]/','', $nohp)) : '';
                ?>
                <tr>
                    <td style="color:var(--text3);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td><code style="color:var(--text2);font-size:0.78rem;"><?= htmlspecialchars($s['nis_siswa']) ?></code></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.75rem;font-weight:600;"><?= htmlspecialchars($s['kelas']) ?></span></td>
                    <td>
                        <?php if ($wa): ?>
                            <a href="https://wa.me/<?= $wa ?>" target="_blank" style="color:var(--green);text-decoration:none;font-size:0.82rem;">
                                <i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($nohp) ?>
                            </a>
                        <?php else: ?>
                            <span class="badge-no-wa">Belum terdaftar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/siswa/<?= urlencode($s['nis_siswa']) ?>" class="btn-app btn-ghost btn-icon" title="Detail">
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
$extraJs = <<<'JS'
<script>
$(document).ready(function () {
    $('#tabelSiswa').DataTable({
        pageLength: 25,
        order: [[2,'asc']],
        columnDefs:[{orderable:false,targets:[5]}]
    });
});
</script>
JS;
$activePage = 'penempatan';
require BASE_PATH . '/app/Views/layouts/app.php';