<?php ob_start(); ?>

<div class="row g-3">
    <div class="col-12">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-box-archive me-1" style="color:var(--blue)"></i> Arsip Periode PKL</span>
                <span style="font-size:0.78rem;color:var(--text3);">Data periode tersimpan — read only</span>
            </div>
            <div class="card-body-app" style="padding:0;">
                <?php if (empty($periodeList)): ?>
                <div style="padding:2rem;text-align:center;color:var(--text3);font-size:0.85rem;">
                    <i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:0.75rem;display:block;"></i>
                    Belum ada periode yang tersimpan.
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg3);">
                                <th style="padding:0.65rem 1rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">#</th>
                                <th style="padding:0.65rem 1rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Nama Periode</th>
                                <th style="padding:0.65rem 1rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Mulai</th>
                                <th style="padding:0.65rem 1rem;text-align:left;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Selesai</th>
                                <th style="padding:0.65rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Siswa</th>
                                <th style="padding:0.65rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Presensi</th>
                                <th style="padding:0.65rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Status</th>
                                <th style="padding:0.65rem 1rem;text-align:center;font-size:0.68rem;text-transform:uppercase;color:var(--text3);font-weight:700;letter-spacing:0.05em;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periodeList as $i => $p): ?>
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding:0.7rem 1rem;color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                                <td style="padding:0.7rem 1rem;font-weight:600;font-size:0.85rem;"><?= htmlspecialchars($p['nama_periode']) ?></td>
                                <td style="padding:0.7rem 1rem;font-size:0.82rem;"><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
                                <td style="padding:0.7rem 1rem;font-size:0.82rem;"><?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
                                <td style="padding:0.7rem 1rem;text-align:center;font-size:0.82rem;font-weight:600;"><?= number_format($p['total_siswa']) ?></td>
                                <td style="padding:0.7rem 1rem;text-align:center;font-size:0.82rem;"><?= number_format($p['total_presensi']) ?></td>
                                <td style="padding:0.7rem 1rem;text-align:center;">
                                    <?php if ($p['aktif']): ?>
                                    <span style="background:var(--green-bg);color:var(--green);border-radius:20px;padding:0.15rem 0.65rem;font-size:0.72rem;font-weight:700;">● AKTIF</span>
                                    <?php else: ?>
                                    <span style="background:var(--bg3);color:var(--text3);border-radius:20px;padding:0.15rem 0.65rem;font-size:0.72rem;">Arsip</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0.7rem 1rem;text-align:center;">
                                    <a href="/arsip/<?= $p['id'] ?>" class="btn-app btn-primary-app" style="font-size:0.72rem;padding:0.3rem 0.75rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </a>
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
</div>

<?php
$content = ob_get_clean();
$activePage = 'arsip';
require BASE_PATH . '/app/Views/layouts/app.php';
