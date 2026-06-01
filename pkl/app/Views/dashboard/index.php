<?php
ob_start();
?>

<div class="page-header">
    <div class="page-subtitle">Ringkasan Hari Ini — <?= date('l, d F Y') ?></div>
</div>

<!-- ═══ Stat Cards (seperti home) ═══ -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalSiswa) ?></div>
                <div class="stat-label">Total Siswa PKL</div>
                <div class="stat-sub-row" style="display:flex;gap:0.4rem;margin-top:0.25rem;flex-wrap:wrap;">
                    <span style="font-size:0.62rem;font-weight:600;padding:0.1rem 0.35rem;border-radius:3px;background:var(--green-bg);color:var(--green);"><?= $sudahWa ?>✓ WA</span>
                    <span style="font-size:0.62rem;font-weight:600;padding:0.1rem 0.35rem;border-radius:3px;background:var(--red-bg);color:var(--red);"><?= $belumWa ?>✗</span>
                </div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.15rem;"><?= $totalSiswa > 0 ? round($sudahWa/$totalSiswa*100) : 0 ?>% terdaftar WA</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($presensiHariIni) ?></div>
                <div class="stat-label">Total Hadir</div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.15rem;"><?= $totalSiswa > 0 ? round($presensiHariIni/$totalSiswa*100) : 0 ?>% dari <?= $totalSiswa ?> siswa</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-door-open"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statMasuk) ?></div>
                <div class="stat-label">Masuk Hari Ini</div>
                <div style="font-size:0.65rem;color:var(--text3);margin-top:0.15rem;"><?= $presensiHariIni > 0 ? round($statMasuk/$presensiHariIni*100) : 0 ?>% dari hadir</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--yellow-bg);color:var(--yellow);"><i class="fa-solid fa-heart-pulse"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statSakit) ?></div>
                <div class="stat-label">Sakit Hari Ini</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($statIzin) ?></div>
                <div class="stat-label">Izin Hari Ini</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--orange-bg);color:var(--orange);"><i class="fa-solid fa-building-user"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalDudika) ?></div>
                <div class="stat-label">DUDIKA</div>
                <div style="font-size:0.62rem;font-weight:600;padding:0.1rem 0.35rem;border-radius:3px;background:var(--orange-bg);color:var(--orange);display:inline-block;margin-top:0.25rem;"><?= $totalPembimbing ?> pembimbing</div>
            </div>
        </div>
    </div>

</div>

<!-- ═══ Row 2: Chart Presensi + Status Doughnut ═══ -->
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Aktivitas Presensi 14 Hari Terakhir</span>
                <span style="font-size:0.75rem;color:var(--text3);">jumlah siswa per hari</span>
            </div>
            <div class="card-body-app" style="padding-bottom:1rem;">
                <div style="position:relative;height:200px;"><canvas id="chartPresensi"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">Status Hari Ini</span>
                <span style="font-size:0.72rem;color:var(--text3);"><?= $presensiHariIni ?>/<?= $totalSiswa ?></span>
            </div>
            <div class="card-body-app">
                <div style="position:relative;height:160px;"><canvas id="chartStatus"></canvas></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.4rem;margin-top:0.875rem;">
                    <?php foreach([
                        ['Masuk',$statMasuk,'var(--green)'],
                        ['Izin',$statIzin,'var(--blue)'],
                        ['Sakit',$statSakit,'var(--yellow)'],
                        ['Libur',$statLibur,'var(--text2)'],
                        ['Belum',$totalSiswa-$presensiHariIni,'var(--text3)'],
                    ] as [$lbl,$val,$clr]): ?>
                    <div style="display:flex;align-items:center;gap:0.4rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $clr ?>;flex-shrink:0;"></span>
                        <span style="font-size:0.72rem;color:var(--text2);"><?= $lbl ?>:</span>
                        <span style="font-size:0.72rem;font-weight:700;color:<?= $clr ?>;"><?= $val ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Row 3: WA Bot per jam + Top Perintah ═══ -->
<div class="row g-3 mb-3">

    <!-- WA Bot per jam -->
    <div class="col-12 col-lg-8">
        <div class="card-app h-100">
            <div class="card-header-app">
                <span class="card-title">
                    <i class="fa-brands fa-whatsapp me-1" style="color:var(--green)"></i>
                    Aktivitas WA Bot Hari Ini
                    <?php if ($sesiAdminAktif > 0): ?>
                    <span style="background:var(--green-bg);color:var(--green);border:1px solid rgba(34,197,94,0.25);border-radius:20px;font-size:0.65rem;padding:0.1rem 0.45rem;font-weight:600;margin-left:6px;">
                        <i class="fa-solid fa-circle" style="font-size:0.45rem;"></i> <?= $sesiAdminAktif ?> sesi
                    </span>
                    <?php endif; ?>
                </span>
                <div style="display:flex;gap:1rem;font-size:0.72rem;color:var(--text3);">
                    <span><span style="display:inline-block;width:10px;height:3px;background:var(--green);border-radius:2px;vertical-align:middle;margin-right:3px;"></span>Aktual</span>
                    <span><span style="display:inline-block;width:10px;height:0;border-top:2px dashed var(--orange);vertical-align:middle;margin-right:3px;"></span>Rata-rata</span>
                </div>
            </div>
            <div class="card-body-app" style="padding-bottom:0.75rem;">
                <div style="position:relative;height:160px;"><canvas id="chartWaBot"></canvas></div>
            </div>
            <div style="border-top:1px solid var(--border);">
                <div style="padding:0.5rem 1.25rem;font-size:0.68rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">
                    Pesan Terbaru · <?= $waBotHariIni ?> pesan hari ini
                </div>
                <?php if (empty($waPesanTerbaru)): ?>
                <div style="padding:1.25rem;text-align:center;color:var(--text3);font-size:0.82rem;">Belum ada aktivitas.</div>
                <?php else: ?>
                <div style="max-height:160px;overflow-y:auto;">
                    <?php foreach ($waPesanTerbaru as $w): ?>
                    <div style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.45rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div style="width:26px;height:26px;border-radius:50%;background:var(--green-bg);color:var(--green);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr($w['nama'],0,1)) ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;gap:0.5rem;">
                                <span style="font-weight:600;font-size:0.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($w['nama']) ?>
                                    <?php if ($w['kelas']): ?><span style="color:var(--blue);font-size:0.66rem;margin-left:4px;"><?= htmlspecialchars($w['kelas']) ?></span><?php endif; ?>
                                </span>
                                <span style="font-size:0.63rem;color:var(--text3);white-space:nowrap;"><?= date('H:i', strtotime($w['timestamp'])) ?></span>
                            </div>
                            <div style="font-size:0.74rem;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($w['msg'] ?? '-') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Perintah Bot — 1 kartu, 2 tab -->
    <div class="col-12 col-lg-4">
        <div class="card-app h-100" style="display:flex;flex-direction:column;">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-ranking-star me-1" style="color:var(--yellow)"></i>Top Perintah Bot</span>
            </div>
            <!-- Tab buttons -->
            <div style="display:flex;border-bottom:1px solid var(--border);">
                <button onclick="switchTopTab('hari')" id="tabHari"
                    style="flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:var(--blue-bg);color:var(--blue);cursor:pointer;border-bottom:2px solid var(--blue);">
                    Hari Ini
                </button>
                <button onclick="switchTopTab('all')" id="tabAll"
                    style="flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:transparent;color:var(--text2);cursor:pointer;border-bottom:2px solid transparent;">
                    Semua Waktu
                </button>
            </div>
            <!-- Tab: Hari Ini -->
            <div id="topTabHari" style="flex:1;overflow-y:auto;padding:0.75rem;">
                <?php if (empty($topBotHariIni)): ?>
                <div style="text-align:center;color:var(--text3);font-size:0.8rem;padding:1.5rem 0;">Belum ada data hari ini.</div>
                <?php else:
                    $maxH = max(array_column($topBotHariIni, 'total')) ?: 1;
                    $clrs = ['var(--yellow)','var(--blue)','var(--green)','var(--orange)','var(--purple)','var(--red)','var(--text2)','var(--text3)'];
                    foreach ($topBotHariIni as $i => $t):
                        $pct = round($t['total'] / $maxH * 100);
                        $clr = $clrs[$i] ?? 'var(--text3)';
                ?>
                <div style="margin-bottom:0.55rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.18rem;">
                        <span style="font-size:0.78rem;font-weight:600;color:var(--text);display:flex;align-items:center;gap:4px;">
                            <?php if ($i===0): ?><i class="fa-solid fa-trophy" style="color:var(--yellow);font-size:0.65rem;"></i><?php endif; ?>
                            <?= htmlspecialchars($t['perintah']) ?>
                        </span>
                        <span style="font-size:0.72rem;font-weight:700;color:<?= $clr ?>;"><?= $t['total'] ?></span>
                    </div>
                    <div class="progress-app" style="height:4px;">
                        <div class="progress-bar-app" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <!-- Tab: All Time -->
            <div id="topTabAll" style="flex:1;overflow-y:auto;padding:0.75rem;display:none;">
                <?php if (empty($topBotAllTime)): ?>
                <div style="text-align:center;color:var(--text3);font-size:0.8rem;padding:1.5rem 0;">Belum ada data.</div>
                <?php else:
                    $maxA = max(array_column($topBotAllTime, 'total')) ?: 1;
                    $clrs = ['var(--blue)','var(--green)','var(--yellow)','var(--orange)','var(--purple)','var(--red)','var(--text2)','var(--text3)'];
                    foreach ($topBotAllTime as $i => $t):
                        $pct = round($t['total'] / $maxA * 100);
                        $clr = $clrs[$i] ?? 'var(--text3)';
                ?>
                <div style="margin-bottom:0.55rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.18rem;">
                        <span style="font-size:0.78rem;font-weight:600;color:var(--text);display:flex;align-items:center;gap:4px;">
                            <?php if ($i===0): ?><i class="fa-solid fa-crown" style="color:var(--blue);font-size:0.65rem;"></i><?php endif; ?>
                            <?= htmlspecialchars($t['perintah']) ?>
                        </span>
                        <span style="font-size:0.72rem;font-weight:700;color:<?= $clr ?>;"><?= number_format($t['total']) ?></span>
                    </div>
                    <div class="progress-app" style="height:4px;">
                        <div class="progress-bar-app" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ═══ Row 4: Rekap kelas — full ═══ -->
<div class="card-app mb-3">
    <div class="card-header-app">
        <span class="card-title">Rekap Per Kelas — Hari Ini</span>
        <a href="/presensi" style="font-size:0.75rem;color:var(--blue);text-decoration:none;">Detail <i class="fa-solid fa-arrow-right ms-1" style="font-size:0.65rem;"></i></a>
    </div>
    <div class="card-body-app" style="padding:0.75rem;">
        <div class="row g-2">
            <?php foreach ($rekapKelas as $r):
                $pct   = $r['total'] > 0 ? round($r['hadir']/$r['total']*100) : 0;
                $color = $pct >= 80 ? 'var(--green)' : ($pct >= 50 ? 'var(--yellow)' : 'var(--red)');
            ?>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="rekap-mini">
                    <div class="rm-title"><?= htmlspecialchars($r['kelas']) ?></div>
                    <div class="rm-sub"><?= $r['total'] ?> siswa · <?= $r['hadir'] ?> hadir</div>
                    <div class="progress-app"><div class="progress-bar-app" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
                    <div style="font-size:0.7rem;color:<?= $color ?>;margin-top:0.2rem;font-weight:600;"><?= $pct ?>%</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ═══ Row 5: Belum presensi — full ═══ -->
<div class="card-app">
    <div class="card-header-app">
        <span class="card-title">Belum Presensi Hari Ini</span>
        <span style="background:var(--red-bg);color:var(--red);border:1px solid rgba(239,68,68,0.2);border-radius:20px;font-size:0.72rem;padding:0.15rem 0.55rem;font-weight:600;">
            <?= count($belumPresensi) ?> siswa
        </span>
    </div>
    <?php if (empty($belumPresensi)): ?>
    <div style="padding:2rem;text-align:center;color:var(--text3);font-size:0.82rem;">
        <i class="fa-solid fa-circle-check" style="font-size:2rem;color:var(--green);display:block;margin-bottom:0.5rem;"></i>
        Semua siswa sudah presensi hari ini!
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table id="tabelBelumPresensi" class="table table-sm mb-0" style="width:100%">
            <thead>
                <tr><th>Nama</th><th>Kelas</th><th>No. WA</th><th>Pembimbing</th><th>DUDIKA</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($belumPresensi as $s): ?>
                <tr>
                    <td style="font-weight:500;"><?= htmlspecialchars($s['nama']) ?></td>
                    <td><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.72rem;font-weight:600;"><?= htmlspecialchars($s['kelas']) ?></span></td>
                    <td>
                        <?php if (!empty($s['nohp'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $s['nohp'])) ?>" style="font-size:0.78rem;color:var(--green); text-decoration:none;"><i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($s['nohp']) ?></a>
                        <?php else: ?><span style="font-size:0.75rem;color:var(--red);">Belum daftar</span><?php endif; ?>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text2);"><?= htmlspecialchars($s['nama_pembimbing'] ?? '-') ?></td>
                    <td style="font-size:0.78rem;color:var(--text2);"><?= htmlspecialchars($s['nama_dudika'] ?? '-') ?></td>
                    <td><a href="/siswa/<?= urlencode($s['nis']) ?>" class="btn-app btn-ghost btn-icon"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<?php
$content = ob_get_clean();

$chartLabels  = json_encode($chartLabels);
$chartData    = json_encode($chartData);
$chartAvgData = json_encode($chartAvgData);
$statMasuk    = (int)$statMasuk;
$statIzin     = (int)$statIzin;
$statSakit    = (int)$statSakit;
$statLibur    = (int)$statLibur;
$belumChart   = (int)($totalSiswa - $presensiHariIni);
$waJamLabels  = json_encode(array_column($waPerJam, 'jam'));
$waJamData    = json_encode(array_column($waPerJam, 'n'));
$waJamAvg     = json_encode($waJamAvg);

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

    function makeScales() {
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

    // Chart 1: Presensi 14 hari
    const c1 = document.getElementById('chartPresensi');
    if (c1) {
        charts.push(new Chart(c1, {
            type:'bar',
            data:{labels:{$chartLabels},datasets:[
                {
                    label:'Aktual',data:{$chartData},
                    backgroundColor:'rgba(79,142,247,0.25)',borderColor:'#4f8ef7',
                    borderWidth:2,borderRadius:5,borderSkipped:false,order:2
                },
                {
                    label:'Rata-rata hari',data:{$chartAvgData},
                    type:'line',borderColor:'#f59e0b',borderWidth:2,
                    borderDash:[5,4],pointRadius:3,pointBackgroundColor:'#f59e0b',
                    fill:false,tension:0.3,order:1,spanGaps:true
                }
            ]},
            options:{responsive:true,maintainAspectRatio:false,
                interaction:{mode:'index',intersect:false},
                plugins:{legend:{display:false},
                    tooltip:{callbacks:{label:c=>' '+c.dataset.label+': '+(c.raw??'N/A')+' siswa'}}},
                scales:makeScales()}
        }));
    }

    // Chart 2: Doughnut status
    const c2 = document.getElementById('chartStatus');
    if (c2) {
        new Chart(c2, {
            type:'doughnut',
            data:{
                labels:['Masuk','Izin','Sakit','Libur','Belum'],
                datasets:[{data:[{$statMasuk},{$statIzin},{$statSakit},{$statLibur},{$belumChart}],
                    backgroundColor:['rgba(34,197,94,0.85)','rgba(79,142,247,0.85)','rgba(245,158,11,0.85)','rgba(239,68,68,0.85)','rgba(100,116,139,0.25)'],
                    borderWidth:0}]
            },
            options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.label+': '+c.raw+' siswa'}}}}
        });
    }

    // Chart 3: WA Bot per jam + rata-rata
    const c3 = document.getElementById('chartWaBot');
    if (c3) {
        charts.push(new Chart(c3, {
            type:'bar',
            data:{
                labels:{$waJamLabels},
                datasets:[
                    {
                        label:'Aktual',data:{$waJamData},
                        backgroundColor:'rgba(34,197,94,0.25)',borderColor:'#22c55e',
                        borderWidth:1.5,borderRadius:3,borderSkipped:false,order:2
                    },
                    {
                        label:'Rata-rata',data:{$waJamAvg},
                        type:'line',borderColor:'#f97316',borderWidth:2,
                        borderDash:[5,4],pointRadius:3,pointBackgroundColor:'#f97316',
                        fill:false,tension:0.3,order:1,spanGaps:true
                    }
                ]
            },
            options:{
                responsive:true,maintainAspectRatio:false,
                interaction:{mode:'index',intersect:false},
                plugins:{legend:{display:false},
                    tooltip:{callbacks:{label:c=>' '+c.dataset.label+': '+(c.raw??'N/A')+' pesan'}}},
                scales:jamScales()
            }
        }));
    }

    // Update theme
    document.getElementById('themeToggle')?.addEventListener('click', () => {
        setTimeout(() => {
            charts.forEach(ch => {
                if (ch.options.scales) {
                    ['x','y'].forEach(a => {
                        if (ch.options.scales[a]) {
                            ch.options.scales[a].grid.color = grid();
                            ch.options.scales[a].ticks.color = tick();
                        }
                    });
                    ch.update();
                }
            });
        }, 50);
    });
})();
</script>
<script>
function switchTopTab(tab) {
    const isHari = tab === 'hari';
    document.getElementById('topTabHari').style.display = isHari ? 'block' : 'none';
    document.getElementById('topTabAll').style.display  = isHari ? 'none'  : 'block';
    document.getElementById('tabHari').style.cssText = isHari
        ? 'flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:var(--blue-bg);color:var(--blue);cursor:pointer;border-bottom:2px solid var(--blue);'
        : 'flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:transparent;color:var(--text2);cursor:pointer;border-bottom:2px solid transparent;';
    document.getElementById('tabAll').style.cssText = isHari
        ? 'flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:transparent;color:var(--text2);cursor:pointer;border-bottom:2px solid transparent;'
        : 'flex:1;padding:0.45rem 0.5rem;font-size:0.75rem;font-weight:600;border:none;background:var(--blue-bg);color:var(--blue);cursor:pointer;border-bottom:2px solid var(--blue);';
}
</script>
    if (\$('#tabelBelumPresensi').length) {
        \$('#tabelBelumPresensi').DataTable({
            pageLength: 25,
            order: [[1,'asc']],
            columnDefs:[{orderable:false,targets:[5]}]
        });
    }
});
</script>
JSEOF;

$activePage = 'dashboard';
require BASE_PATH . '/app/Views/layouts/app.php';