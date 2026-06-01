<?php
ob_start();

$extraCss = <<<CSS
<style>
/* Hero */
.hero-section {
    background: linear-gradient(135deg, var(--bg2) 0%, var(--bg3) 100%);
    border-bottom: 1px solid var(--border);
    padding: 2.5rem 1.5rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.hero-section::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 50% 0%, var(--blue-bg) 0%, transparent 70%);
    pointer-events: none;
}
.hero-logo {
    width: 72px; height: 84px;
    overflow: hidden;
    margin: 0 auto 1rem;
    display: flex; align-items: center; justify-content: center;
}
.hero-logo img { width: 100%; height: 100%; object-fit: cover; }
.hero-title  { font-size: 1.75rem; font-weight: 800; color: var(--text); margin-bottom: 0.25rem; line-height: 1.2; }
.hero-sub    { font-size: 0.88rem; color: var(--text2); }
.hero-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--green-bg); color: var(--green);
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: 20px; font-size: 0.72rem; font-weight: 600;
    padding: 0.35rem 0.85rem; margin-top: 0.5rem;
    flex-wrap: wrap; justify-content: center;
    max-width: 90vw; text-align: center;
}
.hero-badge-row2 {
    width: 100%; text-align: center;
    font-size: 0.68rem; color: var(--text3);
    padding-top: 0.1rem;
}
@media (min-width: 541px) {
    .hero-badge-row2 { display: none; }
    .hero-badge-desktop-extra { display: inline; }
}
@media (max-width: 540px) {
    .hero-badge-desktop-extra { display: none; }
    .hero-badge-row2 { display: block; }
}
.btn-wa {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: #25d366; color: white;
    border-radius: 8px; padding: 0.65rem 1.5rem;
    font-size: 0.9rem; font-weight: 700;
    text-decoration: none; transition: opacity 0.15s;
    border: none; cursor: pointer;
}
.btn-wa:hover { opacity: 0.88; color: white; }
.btn-web {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background:var(--yellow); color: white;
    border: 1px solid var(--border); border-radius: 8px;
    padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 600;
    text-decoration: none; cursor: pointer; opacity: 0.65;
    transition: all 0.15s;
}

/* Section heading */
.sec-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem; }
.sec-head-title { font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--text2); display:flex; align-items:center; gap:0.5rem; }

/* Stat sub info */
.stat-sub-row { display:flex; gap:0.5rem; margin-top:0.3rem; flex-wrap:wrap; }
.stat-sub-badge { font-size:0.62rem; font-weight:600; padding:0.1rem 0.4rem; border-radius:3px; }

/* Recent table */
.recent-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.recent-table thead th { background:var(--bg3); color:var(--text3); font-size:0.65rem; text-transform:uppercase; letter-spacing:0.06em; padding:0.55rem 0.875rem; border-bottom:1px solid var(--border); font-weight:700; }
.recent-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.1s; }
.recent-table tbody tr:last-child { border-bottom:none; }
.recent-table tbody tr:hover { background:var(--bg3); }
.recent-table tbody td { padding:0.55rem 0.875rem; vertical-align:middle; }
.recent-table tbody tr.row-masuk { border-left:3px solid var(--green); }
.recent-table tbody tr.row-izin  { border-left:3px solid var(--blue); }
.recent-table tbody tr.row-sakit { border-left:3px solid var(--yellow); }
.recent-table tbody tr.row-libur { border-left:3px solid var(--border2); }
.ket-pill { display:inline-flex; align-items:center; gap:3px; padding:0.15rem 0.5rem; border-radius:20px; font-size:0.68rem; font-weight:700; }
.ket-pill.masuk { background:var(--green-bg); color:var(--green); }
.ket-pill.izin  { background:var(--blue-bg);  color:var(--blue); }
.ket-pill.sakit { background:var(--yellow-bg);color:var(--yellow); }
.ket-pill.libur { background:var(--bg3);      color:var(--text2); }
.time-dot { width:6px; height:6px; border-radius:50%; display:inline-block; margin-right:4px; }
.time-dot.masuk { background:var(--green); }
.time-dot.izin  { background:var(--blue); }
.time-dot.sakit { background:var(--yellow); }
.time-dot.libur { background:var(--border2); }

/* WA stat items */
.wa-stat-item { display:flex; flex-direction:column; align-items:center; gap:0.15rem; }
.wa-stat-num  { font-size:1.4rem; font-weight:800; color:var(--green); line-height:1; }
.wa-stat-lbl  { font-size:0.65rem; color:var(--text3); text-transform:uppercase; letter-spacing:0.04em; }

.hero-btns { display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap; margin-top:1.5rem; }
@media (max-width:540px) { .hero-btns { flex-direction:column; align-items:stretch; } }
@media (max-width:540px) { .hero-btns a { justify-content:center; } }
</style>
CSS;
?>

<!-- ═══ Hero ═══ -->
<div class="hero-section">
    <div class="hero-logo">
        <img src="/assets/img/SMKBOS-min.png" alt="SMKN Bansari"
             onerror="this.outerHTML='<i class=\'fa-solid fa-graduation-cap\' style=\'color:var(--blue);font-size:2rem;\'></i>'">
    </div>
    <div class="hero-title">Sistem Presensi PKL</div>
    <div class="hero-sub">SMK Negeri Bansari · <?= date('Y') ?></div>
    <div>
        <?php
        $hariIndo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                    'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        $bulanIndo = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
                    'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
                    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];

        $hariIni = $hariIndo[date('l')] . ', ' . date('d') . ' ' . $bulanIndo[date('F')] . ' ' . date('Y');

        function formatTglBadge(string $tgl, array $bulanIndo): string {
            return date('d', strtotime($tgl)) . ' ' . $bulanIndo[date('F', strtotime($tgl))] . ' ' . date('Y', strtotime($tgl));
        }
        ?>

        <?php if ($periodeAktif): ?>
        <div class="hero-badge">
            <i class="fa-solid fa-circle" style="font-size:0.45rem;color:var(--green);"></i>
            Aktif &nbsp;·&nbsp; <?= $hariIni ?>
            <span class="hero-badge-desktop-extra">
                &nbsp;·&nbsp; <?= formatTglBadge($periodeAktif['tanggal_mulai'], $bulanIndo) ?> — <?= formatTglBadge($periodeAktif['tanggal_selesai'], $bulanIndo) ?>
            </span>
            <span class="hero-badge-row2">
                <?= formatTglBadge($periodeAktif['tanggal_mulai'], $bulanIndo) ?> — <?= formatTglBadge($periodeAktif['tanggal_selesai'], $bulanIndo) ?>
            </span>
        </div>
        <?php elseif ($dalamToleransi): ?>
            <div class="hero-badge" style="background:var(--yellow-bg);border-color:rgba(245,158,11,0.3);color:var(--yellow);">
                <i class="fa-solid fa-circle" style="font-size:0.45rem;color:var(--yellow);"></i>
                Aktif
                &nbsp;·&nbsp; <span style="color:var(--text2);"><?= $hariIni ?></span>
                <span class="hero-badge-desktop-extra">
                    &nbsp;·&nbsp;
                    <span style="color:var(--text3);">
                        <?= formatTglBadge($periodeTolerasi['tanggal_mulai'], $bulanIndo) ?> — <?= formatTglBadge($periodeTolerasi['tanggal_selesai'], $bulanIndo) ?>
                    </span>
                    &nbsp;·&nbsp;
                    <span style="color:var(--yellow);">
                        <?= $sisaHariToleransi ?> hari lagi presensi ditutup
                    </span>
                </span>
                <span class="hero-badge-row2" style="color:var(--text3);">
                    <?= formatTglBadge($periodeTolerasi['tanggal_mulai'], $bulanIndo) ?> — <?= formatTglBadge($periodeTolerasi['tanggal_selesai'], $bulanIndo) ?>
                    &nbsp;·&nbsp; <span style="color:var(--yellow);"><?= $sisaHariToleransi ?> hari lagi ditutup</span>
                </span>
            </div>
        <?php else: ?>
        
        <div class="hero-badge" style="border-color:rgba(239,68,68,0.3);color:var(--red);">
            <i class="fa-solid fa-circle" style="font-size:0.45rem;color:var(--red);"></i>
            Tidak Aktif
            <?php if ($adaPeriodeLewat): ?>
                <span class="hero-badge-desktop-extra">&nbsp;·&nbsp; <span style="color:var(--text3);">Periode telah berakhir</span></span>
            <?php endif; ?>
            &nbsp;·&nbsp; <span style="color:var(--text2);"><?= $hariIni ?></span>
            <span class="hero-badge-desktop-extra">
                &nbsp;·&nbsp;
                <?php if ($periodeBerikutnya): ?>
                    <span style="color:var(--text3);">Periode selanjutnya: <?= formatTglBadge($periodeBerikutnya['tanggal_mulai'], $bulanIndo) ?></span>
                <?php else: ?>
                    <span style="color:var(--text3);">Tunggu informasi PKL dari sekolah/jurusan masing-masing</span>
                <?php endif; ?>
            </span>
            <span class="hero-badge-row2" style="color:var(--text3);">
                <?php if ($adaPeriodeLewat): ?>Periode telah berakhir · <?php endif; ?>
                <?php if ($periodeBerikutnya): ?>
                    Mulai: <?= formatTglBadge($periodeBerikutnya['tanggal_mulai'], $bulanIndo) ?>
                <?php else: ?>
                    Tunggu info PKL dari sekolah/jurusan
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
    <!-- Tombol utama -->
    <div class="hero-btns">
        <a href="https://wa.me/<?= htmlspecialchars($waBotNumber) ?>?text=info"
           target="_blank" class="btn-wa">
            <i class="fa-brands fa-whatsapp"></i> Presensi WA
        </a>
        <a href="/presensi-web" class="btn-web"
           style="cursor:pointer;opacity:1;"
           title="Segera hadir">
            <i class="fa-solid fa-globe"></i> Presensi Web
        </a>
        <a href="/panduan"
           style="display:inline-flex;align-items:center;gap:0.5rem;background:transparent;color:var(--text2);border:1px solid var(--border2);border-radius:8px;padding:0.65rem 1.25rem;font-size:0.9rem;font-weight:600;text-decoration:none;transition:all 0.15s;"
           onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)'"
           onmouseout="this.style.borderColor='var(--border2)';this.style.color='var(--text2)'">
            <i class="fa-solid fa-book-open"></i> Panduan
        </a>
    </div>
</div>

<div style="padding:1.5rem;">

<!-- ═══ Stat Cards ═══ -->
<div class="sec-head">
    <div class="sec-head-title"><i class="fa-solid fa-chart-bar" style="color:var(--blue)"></i> Ringkasan Hari Ini</div>
    <span style="font-size:0.75rem;color:var(--text3);"><?= date('d M Y') ?></span>
</div>
<div class="row g-3 mb-4">

    <!-- 1. Total Siswa -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalSiswa) ?></div>
                <div class="stat-label">Total Siswa PKL</div>
                <div class="stat-sub-row">
                    <span class="stat-sub-badge" style="background:var(--green-bg);color:var(--green);">
                        <?= $sudahWa ?>✓ WA
                    </span>
                    <span class="stat-sub-badge" style="background:var(--red-bg);color:var(--red);">
                        <?= $belumWa ?>✗
                    </span>
                </div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.2rem;">
                    <?= $totalSiswa > 0 ? round($sudahWa/$totalSiswa*100) : 0 ?>% terdaftar WA
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Total Hadir -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($hadirHariIni) ?></div>
                <div class="stat-label">Total Hadir</div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.2rem;">
                    <?= $totalSiswa > 0 ? round($hadirHariIni/$totalSiswa*100) : 0 ?>% dari <?= $totalSiswa ?> siswa
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Masuk -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-door-open"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statMasuk) ?></div>
                <div class="stat-label">Masuk Hari Ini</div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.2rem;">
                    <?= $hadirHariIni > 0 ? round($statMasuk/$hadirHariIni*100) : 0 ?>% dari hadir
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Sakit -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--yellow-bg);color:var(--yellow);"><i class="fa-solid fa-heart-pulse"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statSakit) ?></div>
                <div class="stat-label">Sakit Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- 5. Izin -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statIzin) ?></div>
                <div class="stat-label">Izin Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- 6. DUDIKA + Pembimbing -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--orange-bg);color:var(--orange);"><i class="fa-solid fa-building-user"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalDudika) ?></div>
                <div class="stat-label">DUDIKA</div>
                <div class="stat-sub-row">
                    <span class="stat-sub-badge" style="background:var(--orange-bg);color:var(--orange);">
                        <?= $totalPembimbing ?> pembimbing
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═══ Row: Chart Presensi + Status Pie ═══ -->
<div class="row g-3 mb-3">

    <!-- Chart presensi 7 hari + rata-rata -->
    <div class="col-12 col-lg-8">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Aktivitas Presensi 7 Hari Terakhir</span>
                <div style="display:flex;gap:1rem;font-size:0.72rem;color:var(--text3);">
                    <span><span style="display:inline-block;width:10px;height:3px;background:var(--blue);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Aktual</span>
                    <span><span style="display:inline-block;width:10px;height:3px;background:var(--yellow);border-radius:2px;vertical-align:middle;margin-right:4px;border-top:2px dashed var(--yellow);background:none;"></span>Rata-rata hari</span>
                </div>
            </div>
            <div class="card-body-app" style="padding-bottom:1rem;">
                <div style="position:relative;height:220px;"><canvas id="chartPresensi"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Status Pie -->
    <div class="col-12 col-lg-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Status Hari Ini</span>
                <span style="font-size:0.72rem;color:var(--text3);"><?= $hadirHariIni ?>/<?= $totalSiswa ?></span>
            </div>
            <div class="card-body-app">
                <div style="position:relative;height:200px;"><canvas id="chartPie"></canvas></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-top:1rem;">
                    <?php
                    $pieItems = [
                        ['label'=>'Masuk', 'val'=>$statMasuk,  'color'=>'var(--green)'],
                        ['label'=>'Izin',  'val'=>$statIzin,   'color'=>'var(--blue)'],
                        ['label'=>'Sakit', 'val'=>$statSakit,  'color'=>'var(--yellow)'],
                        ['label'=>'Libur', 'val'=>$statLibur,  'color'=>'var(--text2)'],
                        ['label'=>'Belum', 'val'=>$totalSiswa-$hadirHariIni, 'color'=>'var(--text3)'],
                    ];
                    foreach ($pieItems as $pi): ?>
                    <div style="display:flex;align-items:center;gap:0.4rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $pi['color'] ?>;flex-shrink:0;"></span>
                        <span style="font-size:0.72rem;color:var(--text2);"><?= $pi['label'] ?>:</span>
                        <span style="font-size:0.72rem;font-weight:700;color:<?= $pi['color'] ?>;"><?= $pi['val'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═══ Row: WA Bot Activity ═══ -->
<div class="row g-3 mb-4">
    <!-- Chart WA 7 hari + rata-rata -->
    <div class="col-12 col-lg-6">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">
                    <i class="fa-brands fa-whatsapp me-1" style="color:var(--green)"></i>
                    Aktivitas WA Bot 7 Hari
                </span>
                <div style="display:flex;gap:1rem;font-size:0.72rem;color:var(--text3);">
                    <span><span style="display:inline-block;width:10px;height:3px;background:var(--green);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Aktual</span>
                    <span><span style="display:inline-block;width:10px;height:0;border-top:2px dashed var(--orange);vertical-align:middle;margin-right:4px;"></span>Rata-rata hari</span>
                </div>
            </div>
            <!-- Info aktual hari ini -->
            <div style="display:flex;gap:1.5rem;padding:0.6rem 1.25rem;border-bottom:1px solid var(--border);flex-wrap:wrap;">
                <div class="wa-stat-item">
                    <span class="wa-stat-num"><?= $waBotHariIni ?></span>
                    <span class="wa-stat-lbl">Pesan hari ini</span>
                </div>
                <div class="wa-stat-item">
                    <span class="wa-stat-num"><?= $waPengirimUnik ?></span>
                    <span class="wa-stat-lbl">Pengirim unik</span>
                </div>
                <?php if ($waJamPuncak): ?>
                <!--<div class="wa-stat-item">-->
                <!--    <span class="wa-stat-num"><?= str_pad($waJamPuncak['jam'],2,'0',STR_PAD_LEFT) ?>:00</span>-->
                <!--    <span class="wa-stat-lbl">Jam puncak (<?= $waJamPuncak['n'] ?> pesan)</span>-->
                <!--</div>-->
                <?php endif; ?>
            </div>
            <div class="card-body-app" style="padding-bottom:1rem;">
                <div style="position:relative;height:200px;"><canvas id="chartWa"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Chart per jam hari ini -->
    <div class="col-12 col-lg-6">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">
                    <i class="fa-solid fa-clock me-1" style="color:var(--blue)"></i>
                    Aktivitas Per Jam — Hari Ini
                </span>
                <div style="display:flex;gap:1rem;font-size:0.72rem;color:var(--text3);">
                    <span><span style="display:inline-block;width:10px;height:3px;background:var(--blue);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Aktual</span>
                    <span><span style="display:inline-block;width:10px;height:0;border-top:2px dashed var(--orange);vertical-align:middle;margin-right:4px;"></span>Rata-rata jam</span>
                </div>
            </div>
            <div class="card-body-app" style="padding-bottom:1rem;padding-top:0.875rem;">
                <div style="position:relative;height:200px;"><canvas id="chartJam"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Recent Presensi ═══ -->
<div class="sec-head">
    <div class="sec-head-title">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--blue)"></i>
        Presensi Terbaru Hari Ini
    </div>
    <a href="/info" class="btn-app btn-ghost" style="font-size:0.75rem;padding:0.25rem 0.7rem;">
        Lihat semua <i class="fa-solid fa-arrow-right ms-1"></i>
    </a>
</div>
<div class="card-app mb-3">
    <?php if (empty($recentPresensi)): ?>
    <div style="padding:2.5rem;text-align:center;color:var(--text3);font-size:0.85rem;">
        <i class="fa-solid fa-inbox fa-2x" style="opacity:0.3;display:block;margin-bottom:0.75rem;"></i>
        Belum ada presensi hari ini.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Waktu</th><th>Siswa</th><th>Kelas</th>
                    <th>Status</th><th>Catatan</th><th>DUDIKA</th><th>Pembimbing</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentPresensi as $p):
                    $ket  = strtolower($p['ket']);
                    $ts   = strtotime($p['timestamp']);
                    $icon = match($ket) {'masuk'=>'circle-check','izin'=>'clock','sakit'=>'heart-pulse','libur'=>'umbrella-beach',default=>'circle'};
                ?>
                <tr class="row-<?= $ket ?>">
                    <td style="white-space:nowrap;color:var(--text2);font-size:0.78rem;">
                        <span class="time-dot <?= $ket ?>"></span><?= date('H:i',$ts) ?>
                    </td>
                    <td>
                        <a href="/info/<?= urlencode($p['nis']) ?>" style="font-weight:600;color:var(--text);text-decoration:none;">
                            <?= htmlspecialchars($p['namasiswa']) ?>
                        </a>
                        <div style="font-size:0.68rem;color:var(--text3);font-family:monospace;"><?= htmlspecialchars($p['nis']) ?></div>
                    </td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.72rem;font-weight:600;"><?= htmlspecialchars($p['kelas']) ?></span></td>
                    <td><span class="ket-pill <?= $ket ?>"><i class="fa-solid fa-<?= $icon ?>"></i> <?= htmlspecialchars($p['ket']) ?></span></td>
                    <td style="font-size:0.78rem;color:var(--text2);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['catatan']??'-') ?></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($p['nama_dudika']??'-') ?></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($p['nama_pembimbing']??'-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</div><!-- end padding -->

<?php
$content = ob_get_clean();

$chartLabels   = json_encode($chartLabels);
$chartData     = json_encode($chartData);
$chartAvgData  = json_encode($chartAvgData);
$waLabels      = json_encode($waLabels);
$waData        = json_encode($waData);
$waAvgData     = json_encode($waAvgData);
$jamLabels     = json_encode($jamLabels);
$jamAktual     = json_encode($jamAktual);
$jamAvg        = json_encode($jamAvg);
$statMasuk     = (int)$statMasuk;
$statIzin      = (int)$statIzin;
$statSakit     = (int)$statSakit;
$statLibur     = (int)$statLibur;
$belumChart    = (int)($totalSiswa - $hadirHariIni);

$extraJs = <<<JSEOF
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
    const grid   = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tick   = () => isDark() ? '#64748b' : '#94a3b8';
    const charts = [];

    const HARI_PANJANG  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const HARI_SINGKAT  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

    function formatLabelTgl(tglStr, panjang) {
        const d   = new Date(tglStr + 'T00:00:00');
        const dow = panjang ? HARI_PANJANG[d.getDay()] : HARI_SINGKAT[d.getDay()];
        const dm  = d.getDate().toString().padStart(2,'0') + '/' + (d.getMonth()+1).toString().padStart(2,'0');
        return panjang ? [dow, dm] : [dow, dm];
    }

    function baseScales() {
        return {
            x: {
                grid:{color:grid()},
                ticks:{
                    color:tick(), font:{size:10},
                    callback: function(val, idx) {
                        const lbl = this.getLabelForValue(val);
                        const lebar = this.chart.width;
                        return formatLabelTgl(lbl, lebar > 500);
                    }
                }
            },
            y: { grid:{color:grid()}, ticks:{color:tick(),font:{size:10}}, beginAtZero:true }
        };
    }

    function jamScales() {
        return {
            x: { grid:{color:grid()}, ticks:{color:tick(),font:{size:10}} },
            y: { grid:{color:grid()}, ticks:{color:tick(),font:{size:10}}, beginAtZero:true }
        };
    }

    // ── Chart 1: Presensi 7 hari + rata-rata ──
    const c1 = document.getElementById('chartPresensi');
    if (c1) {
        const avgData = {$chartAvgData};
        const ch = new Chart(c1, {
            type: 'bar',
            data: {
                labels: {$chartLabels},
                datasets: [
                    {
                        label: 'Aktual',
                        data: {$chartData},
                        backgroundColor: 'rgba(79,142,247,0.25)',
                        borderColor: '#4f8ef7',
                        borderWidth: 2,
                        borderRadius: 5,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Rata-rata hari',
                        data: avgData,
                        type: 'line',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointRadius: 4,
                        pointBackgroundColor: '#f59e0b',
                        fill: false,
                        tension: 0.3,
                        order: 1,
                        spanGaps: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + (ctx.raw ?? 'N/A') + ' siswa'
                        }
                    }
                },
                scales: baseScales()
            }
        });
        charts.push(ch);
    }

    // ── Chart 2: Pie status ──
    const c2 = document.getElementById('chartPie');
    if (c2) {
        new Chart(c2, {
            type: 'doughnut',
            data: {
                labels: ['Masuk','Izin','Sakit','Libur','Belum'],
                datasets: [{
                    data: [{$statMasuk},{$statIzin},{$statSakit},{$statLibur},{$belumChart}],
                    backgroundColor: [
                        'rgba(34,197,94,0.85)',
                        'rgba(79,142,247,0.85)',
                        'rgba(245,158,11,0.85)',
                        'rgba(100,116,139,0.5)',
                        'rgba(100,116,139,0.2)',
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.raw + ' siswa' } }
                }
            }
        });
    }

    // ── Chart 3: WA bot 7 hari + rata-rata ──
    const c3 = document.getElementById('chartWa');
    if (c3) {
        const ch = new Chart(c3, {
            type: 'bar',
            data: {
                labels: {$waLabels},
                datasets: [
                    {
                        label: 'Aktual',
                        data: {$waData},
                        backgroundColor: 'rgba(34,197,94,0.25)',
                        borderColor: '#22c55e',
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Rata-rata hari',
                        data: {$waAvgData},
                        type: 'line',
                        borderColor: '#f97316',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointRadius: 4,
                        pointBackgroundColor: '#f97316',
                        fill: false,
                        tension: 0.3,
                        order: 1,
                        spanGaps: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + (c.raw ?? 'N/A') + ' pesan' } }
                },
                scales: baseScales()
            }
        });
        charts.push(ch);
    }

    // ── Chart 4: Per jam hari ini + rata-rata jam ──
    const c4 = document.getElementById('chartJam');
    if (c4) {
        const ch = new Chart(c4, {
            type: 'bar',
            data: {
                labels: {$jamLabels},
                datasets: [
                    {
                        label: 'Aktual hari ini',
                        data: {$jamAktual},
                        backgroundColor: 'rgba(79,142,247,0.25)',
                        borderColor: '#4f8ef7',
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Rata-rata jam',
                        data: {$jamAvg},
                        type: 'line',
                        borderColor: '#f97316',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointRadius: 3,
                        pointBackgroundColor: '#f97316',
                        fill: false,
                        tension: 0.3,
                        order: 1,
                        spanGaps: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + (c.raw ?? 'N/A') + ' pesan' } }
                },
                scales: jamScales()
            }
        });
        charts.push(ch);
    }

    // Update warna chart saat theme toggle
    document.getElementById('themeToggle')?.addEventListener('click', () => {
        setTimeout(() => {
            charts.forEach(ch => {
                if (ch.options.scales) {
                    ['x','y'].forEach(axis => {
                        if (ch.options.scales[axis]) {
                            ch.options.scales[axis].grid.color = grid();
                            ch.options.scales[axis].ticks.color = tick();
                        }
                    });
                    ch.update();
                }
            });
        }, 50);
    });
})();
</script>
JSEOF;

$activePage = 'home';
require BASE_PATH . '/app/Views/layouts/public.php';