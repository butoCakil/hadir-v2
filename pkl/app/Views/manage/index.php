<?php
ob_start();

$extraCss = <<<CSS
<style>
/* Tab navigation */
.tab-nav { display:flex; gap:0.25rem; background:var(--bg3); border:1px solid var(--border); border-radius:10px; padding:4px; margin-bottom:1.25rem; flex-wrap:wrap; }
.tab-btn { flex:1; padding:0.45rem 0.75rem; border-radius:7px; font-size:0.8rem; font-weight:600; color:var(--text2); background:transparent; border:none; cursor:pointer; transition:all 0.15s; text-align:center; white-space:nowrap; }
.tab-btn.active { background:var(--bg2); color:var(--text); box-shadow:0 1px 4px rgba(0,0,0,0.15); }
.tab-pane { display:none; }
.tab-pane.active { display:block; }

/* Inline editable table */
.edit-table { width:100%; border-collapse:collapse; }
.edit-table thead th { background:var(--bg3); color:var(--text3); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; padding:0.6rem 0.75rem; border-bottom:1px solid var(--border); font-weight:700; white-space:nowrap; }
.edit-table tbody tr { border-bottom:1px solid var(--border); }
.edit-table tbody tr:last-child { border-bottom:none; }
.edit-table tbody tr:hover { background:var(--bg3); }
.edit-table tbody td { padding:0.45rem 0.5rem; font-size:0.84rem; vertical-align:middle; }

/* Editable cell */
.editable {
    padding: 0.3rem 0.5rem;
    border-radius: 5px;
    border: 1px solid transparent;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
    min-width: 80px;
    display: inline-block;
    white-space: nowrap;
}
.editable:hover { border-color: var(--border2); background: var(--bg3); }
.editable:focus { border-color: var(--blue); background: var(--bg3); box-shadow: 0 0 0 2px var(--blue-bg); }
.editable.saving { border-color: var(--yellow); opacity: 0.7; }
.editable.saved  { border-color: var(--green); background: var(--green-bg); }
.editable.error  { border-color: var(--red);   background: var(--red-bg); }

/* Upload zone */
.upload-zone {
    border: 2px dashed var(--border2);
    border-radius: 12px;
    padding: 2.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg3);
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--blue);
    background: var(--blue-bg);
}
.upload-zone i { font-size: 2.5rem; color: var(--text3); margin-bottom: 0.75rem; display: block; }
.upload-zone .uz-title { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem; }
.upload-zone .uz-sub { font-size: 0.78rem; color: var(--text3); }

/* Result alert */
.result-box { border-radius: 8px; padding: 0.875rem 1.25rem; font-size: 0.85rem; margin-top: 1rem; display:none; }
.result-box.success { background: var(--green-bg); border: 1px solid rgba(34,197,94,0.25); color: var(--green); }
.result-box.error   { background: var(--red-bg);   border: 1px solid rgba(239,68,68,0.25);   color: var(--red); }
.result-box.warning { background: var(--yellow-bg); border: 1px solid rgba(245,158,11,0.25); color: var(--yellow); }

/* Sinkron status */
.sinkron-item { display:flex; align-items:center; gap:0.5rem; padding:0.4rem 0; border-bottom:1px solid var(--border); font-size:0.82rem; }
.sinkron-item:last-child { border-bottom:none; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--bg2); border:1px solid var(--border); border-radius:12px; padding:1.5rem; width:100%; max-width:420px; }
.modal-box h3 { margin:0 0 1.25rem; font-size:0.95rem; font-weight:700; }
.modal-field { margin-bottom:0.85rem; }
.modal-field label { font-size:0.72rem; font-weight:600; color:var(--text3); display:block; margin-bottom:0.3rem; }
.modal-field input { width:100%; padding:0.45rem 0.65rem; border-radius:6px; border:1px solid var(--border2); background:var(--bg3); color:var(--text); font-size:0.83rem; }
.modal-actions { display:flex; gap:0.75rem; margin-top:1.25rem; }
</style>
CSS;
?>

<!-- Stat mini -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body"><div class="stat-value"><?= $totalSiswa ?></div><div class="stat-label">Total Siswa</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-bg);color:var(--green);"><i class="fa-solid fa-person-chalkboard"></i></div>
            <div class="stat-body"><div class="stat-value"><?= $totalPembimbing ?></div><div class="stat-label">Pembimbing</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--purple-bg);color:var(--purple);"><i class="fa-solid fa-chalkboard-user"></i></div>
            <div class="stat-body"><div class="stat-value"><?= $totalWalikelas ?></div><div class="stat-label">Wali Kelas</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--yellow-bg);color:var(--yellow);"><i class="fa-solid fa-building"></i></div>
            <div class="stat-body"><div class="stat-value"><?= $totalDudika ?></div><div class="stat-label">DUDIKA</div></div>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('upload')">
        <i class="fa-solid fa-file-excel me-1"></i> Upload Siswa
    </button>
    <button class="tab-btn" onclick="switchTab('pembimbing')">
        <i class="fa-solid fa-person-chalkboard me-1"></i> Pembimbing
    </button>
    <button class="tab-btn" onclick="switchTab('walikelas')">
        <i class="fa-solid fa-chalkboard-user me-1"></i> Wali Kelas
    </button>
    <button class="tab-btn" onclick="switchTab('dudika')">
        <i class="fa-solid fa-building me-1"></i> DUDIKA
    </button>
    <button class="tab-btn" onclick="switchTab('sinkron')">
    <i class="fa-solid fa-rotate me-1"></i> Sinkronisasi
    </button>
    <button class="tab-btn" onclick="switchTab('periode')">
        <i class="fa-solid fa-calendar-alt me-1"></i> Periode PKL
    </button>
</div>

<!-- ═══ Tab: Upload Siswa ═══ -->
<div id="tab-upload" class="tab-pane active">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title">Upload Data Siswa dari Excel</span>
        </div>
        <div class="card-body-app">
            <div style="font-size:0.82rem;color:var(--text2);margin-bottom:1.25rem;line-height:1.7;">
                <strong>1 file Excel — semua data sekaligus</strong><br>
                Kolom yang dideteksi otomatis: <code>Nama Pembimbing</code>, <code>Nama Dudika</code>, <code>Alamat Dudika</code>, <code>No Telepon Dudika</code>, <code>Nama Siswa</code>, <code>NIS Siswa</code>, <code>Kelas</code>.<br>
                Pola <em>merge baris</em> otomatis dideteksi — data DUDIKA & Pembimbing di baris pertama, baris berikutnya boleh kosong.<br>
                Upload akan memperbarui: <strong>datasiswa</strong>, <strong>penempatan</strong>, dan <strong>datapembimbing</strong> sekaligus.
            </div>

            <!-- Download template -->
            <a href="/manage/template-excel" class="btn-app btn-ghost mb-3" style="display:inline-flex;">
                <i class="fa-solid fa-file-arrow-down" style="color:var(--green);"></i>
                Download Template Excel
            </a>

            <!-- Drop zone -->
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                <i class="fa-solid fa-file-arrow-up"></i>
                <div class="uz-title">Klik atau seret file Excel ke sini</div>
                <div class="uz-sub">.xlsx atau .xls — maksimal 10MB</div>
            </div>
            <input type="file" id="fileInput" accept=".xlsx,.xls" style="display:none;">

            <!-- Progress -->
            <div id="uploadProgress" style="display:none;margin-top:1rem;">
                <div style="background:var(--border);border-radius:4px;height:6px;overflow:hidden;">
                    <div id="progressBar" style="height:100%;background:var(--blue);border-radius:4px;width:0%;transition:width 0.3s;"></div>
                </div>
                <div style="font-size:0.78rem;color:var(--text3);margin-top:0.4rem;" id="progressText">Mengupload...</div>
            </div>

            <div class="result-box" id="uploadResult"></div>
        </div>
    </div>
</div>

<!-- ═══ Tab: Pembimbing ═══ -->
<div id="tab-pembimbing" class="tab-pane">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title">Data Pembimbing</span>
            <span style="font-size:0.75rem;color:var(--text3);">Klik sel untuk edit langsung</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="tabelPembimbing" class="edit-table" style="width:100%">
                <thead>
                    <tr><th>#</th><th>NIP</th><th>Nama</th><th>No. WA</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pembimbing as $i => $p): ?>
                    <tr data-id="<?= $p['id'] ?>" data-table="pembimbing">
                        <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                        <td><span class="editable" contenteditable="true" data-field="nip"><?= htmlspecialchars($p['nip']) ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nama" style="min-width:160px;"><?= htmlspecialchars($p['nama']) ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nohp" data-type="nohp"><?= htmlspecialchars($p['nohp'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="ket" style="min-width:120px;"><?= htmlspecialchars($p['ket'] ?? '') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ Tab: Wali Kelas ═══ -->
<div id="tab-walikelas" class="tab-pane">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title">Data Wali Kelas</span>
            <span style="font-size:0.75rem;color:var(--text3);">Klik sel untuk edit langsung</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="tabelWalikelas" class="edit-table" style="width:100%">
                <thead>
                    <tr><th>#</th><th>NIP</th><th>Nama</th><th>Kelas</th><th>No. WA</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($walikelas as $i => $w): ?>
                    <tr data-id="<?= $w['id'] ?>" data-table="walikelas">
                        <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                        <td><span class="editable" contenteditable="true" data-field="nip"><?= htmlspecialchars($w['nip'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nama" style="min-width:160px;"><?= htmlspecialchars($w['nama']) ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="kelas"><?= htmlspecialchars($w['kelas'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nohp" data-type="nohp"><?= htmlspecialchars($w['nohp'] ?? '') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ Tab: DUDIKA ═══ -->
<div id="tab-dudika" class="tab-pane">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title">Data DUDIKA</span>
            <span style="font-size:0.75rem;color:var(--text3);">Klik sel untuk edit langsung</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="tabelDudika" class="edit-table" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama DUDIKA</th>
                        <!--<th>Kode</th>-->
                        <th>Alamat</th>
                        <th>Link Map</th>
                        <th>No. Telepon</th>
                        <th>Nama Owner</th>
                        <th>Pembimbing</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dudika as $i => $d): ?>
                    <tr data-id="<?= $d['id'] ?>" data-table="dudika">
                        <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                        <td><span class="editable" contenteditable="true" data-field="nama" style="max-width:200px;"><?= htmlspecialchars($d['nama']) ?></span></td>
                        <!--<td style="font-family:monospace;font-size:0.72rem;color:var(--text3);"><?= htmlspecialchars($d['kode'] ?? '') ?></td>-->
                        <td><span class="editable" contenteditable="true" data-field="alamat" style="min-width:140px;max-width:200px;white-space:normal;word-break:break-word;"><?= htmlspecialchars($d['alamat'] ?? '') ?></span></td>
                        <td style="text-align:center;">
                            <?php if (!empty($d['link_map'])): ?>
                            <a href="<?= htmlspecialchars($d['link_map']) ?>" target="_blank"
                               style="color:var(--blue);font-size:1.1rem;" title="<?= htmlspecialchars($d['link_map']) ?>">
                                <i class="fa-solid fa-location-dot"></i>
                            </a>
                            <?php else:
                                $query = urlencode(($d['nama'] ?? '') . ' ' . ($d['alamat'] ?? ''));
                                $searchUrl = 'https://www.google.com/maps/search/?api=1&query=' . $query;
                            ?>
                            <a href="<?= $searchUrl ?>" target="_blank"
                               style="color:var(--text3);font-size:1rem;" title="Cari di Google Maps: <?= htmlspecialchars($d['nama']) ?>">
                                <i class="fa-solid fa-location-dot"></i>
                            </a>
                            <?php endif; ?>
                            <input type="text" class="link-map-input" data-id="<?= $d['id'] ?>"
                                   value="<?= htmlspecialchars($d['link_map'] ?? '') ?>"
                                   placeholder="Paste link map..."
                                   style="display:none;margin-top:0.3rem;width:180px;padding:0.25rem 0.4rem;font-size:0.72rem;border-radius:5px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);">
                        </td>
                        <td><span class="editable" contenteditable="true" data-field="nomor_telepon" data-type="nohp"><?= htmlspecialchars($d['nomor_telepon'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nama_owner" style="min-width:120px;"><?= htmlspecialchars($d['nama_owner'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="nama_pembimbing" style="min-width:140px;"><?= htmlspecialchars($d['nama_pembimbing'] ?? '') ?></span></td>
                        <td><span class="editable" contenteditable="true" data-field="keterangan" style="min-width:120px;"><?= htmlspecialchars($d['keterangan'] ?? '') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ Tab: Sinkronisasi ═══ -->
<div id="tab-sinkron" class="tab-pane">
    <div class="row g-3">

        <!-- Sinkron Pembimbing -->
        <div class="col-12 col-lg-4">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-person-chalkboard me-1" style="color:var(--blue)"></i> Sinkron Pembimbing</span>
                </div>
                <div class="card-body-app">
                    <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1rem;line-height:1.6;">
                        Preview nama pembimbing di <strong>penempatan</strong> yang tidak cocok dengan <strong>datapembimbing</strong>. Termasuk saran nama yang mirip.
                    </p>
                    <button class="btn-app btn-primary-app" id="btnSinkron" onclick="previewSinkronPembimbing()">
                        <i class="fa-solid fa-eye"></i> Preview Sinkronisasi
                    </button>
                    <div class="result-box" id="sinkronResult" style="margin-top:1rem;"></div>
                    <div id="sinkronDetail"></div>
                </div>
            </div>
        </div>

        <!-- Sinkron Siswa -->
        <div class="col-12 col-lg-4">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-users me-1" style="color:var(--green)"></i> Sinkron Data Siswa</span>
                </div>
                <div class="card-body-app">
                    <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1rem;line-height:1.6;">
                        Update <strong>nama</strong> dan <strong>kelas</strong> di tabel <code>datasiswa</code>
                        berdasarkan data di tabel <code>penempatan</code>. Preview dulu sebelum dieksekusi.
                    </p>
                    <button class="btn-app btn-primary-app" id="btnPreviewSiswa" onclick="previewSinkronSiswa()">
                        <i class="fa-solid fa-eye"></i> Preview Perubahan
                    </button>
                    <div class="result-box" id="siswaResult" style="margin-top:1rem;"></div>
                    <div id="siswaDetail" style="margin-top:0.75rem;"></div>
                </div>
            </div>
        </div>

        <!-- Sinkron DUDI -->
        <div class="col-12 col-lg-4">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-building me-1" style="color:var(--yellow)"></i> Sinkron Data DUDI</span>
                </div>
                <div class="card-body-app">
                    <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1rem;line-height:1.6;">
                        Tambahkan DUDI baru dari <strong>penempatan periode aktif</strong> ke tabel <code>datadudi</code>.
                        DUDI yang sudah ada <strong>tidak akan ditimpa</strong>.
                    </p>
                    <button class="btn-app btn-primary-app" id="btnPreviewDudi" onclick="previewSinkronDudi()">
                        <i class="fa-solid fa-eye"></i> Preview DUDI Baru
                    </button>
                    <div class="result-box" id="dudiResult" style="margin-top:1rem;"></div>
                    <div id="dudiDetail" style="margin-top:0.75rem;"></div>
                </div>
            </div>
        </div>

        <!-- Cek Duplikat DUDI -->
        <div class="col-12">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-copy me-1" style="color:var(--red)"></i> Cek Duplikat DUDI</span>
                    <span style="font-size:0.75rem;color:var(--text3);">Centang pasangan yang ingin digabung, biarkan kosong untuk diabaikan</span>
                </div>
                <div class="card-body-app">
                    <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1rem;line-height:1.6;">
                        Cari nama DUDI yang mirip. Centang <strong>mana yang dipertahankan</strong> pada setiap pasangan, lalu eksekusi sekaligus. Pasangan yang tidak dicentang akan <strong>dibiarkan</strong>.
                    </p>
                    <button class="btn-app btn-primary-app" id="btnCekDuplikat" onclick="cekDuplikatDudi()">
                        <i class="fa-solid fa-magnifying-glass"></i> Cek Duplikat
                    </button>
                    <div class="result-box" id="duplikatResult" style="margin-top:1rem;"></div>
                    <div id="duplikatDetail" style="margin-top:0.75rem;"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ═══ Tab: Periode PKL ═══ -->
<div id="tab-periode" class="tab-pane">
    <div class="row g-3">

        <!-- Form tambah periode -->
        <div class="col-12 col-lg-4">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-plus me-1" style="color:var(--green)"></i> Tambah Periode Baru</span>
                </div>
                <div class="card-body-app">
                    <div style="margin-bottom:0.85rem;">
                        <label style="font-size:0.78rem;font-weight:600;color:var(--text2);display:block;margin-bottom:0.3rem;">Nama Periode</label>
                        <input type="text" id="inputNamaPeriode" placeholder="cth: PKL Juli - November 2026"
                            style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                    </div>
                    <div style="margin-bottom:0.85rem;">
                        <label style="font-size:0.78rem;font-weight:600;color:var(--text2);display:block;margin-bottom:0.3rem;">Tanggal Mulai</label>
                        <input type="date" id="inputTanggalMulai"
                            style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:0.78rem;font-weight:600;color:var(--text2);display:block;margin-bottom:0.3rem;">Tanggal Selesai</label>
                        <input type="date" id="inputTanggalSelesai"
                            style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                    </div>
                    <button class="btn-app btn-primary-app" style="width:100%;" onclick="tambahPeriode()">
                        <i class="fa-solid fa-plus"></i> Tambah Periode
                    </button>
                    <div class="result-box" id="periodeFormResult" style="margin-top:0.75rem;"></div>
                </div>
            </div>
        </div>

        <!-- Daftar periode -->
        <div class="col-12 col-lg-8">
            <div class="card-app">
                <div class="card-header-app">
                    <span class="card-title"><i class="fa-solid fa-calendar-alt me-1" style="color:var(--blue)"></i> Daftar Periode PKL</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="edit-table" id="tabelPeriode" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Periode</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabelPeriodeTbody">
                            <?php foreach ($periode as $i => $p): ?>
                            <tr id="periode-row-<?= $p['id'] ?>">
                                <td style="color:var(--text3);font-size:0.72rem;"><?= $i+1 ?></td>
                                <td style="font-weight:600;font-size:0.84rem;"><?= htmlspecialchars($p['nama_periode']) ?></td>
                                <td style="font-size:0.82rem;"><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
                                <td style="font-size:0.82rem;"><?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
                                <td>
                                    <?php if ($p['aktif']): ?>
                                        <span style="background:var(--green-bg);color:var(--green);border-radius:20px;padding:0.15rem 0.65rem;font-size:0.72rem;font-weight:700;">● AKTIF</span>
                                    <?php else: ?>
                                        <span style="background:var(--bg3);color:var(--text3);border-radius:20px;padding:0.15rem 0.65rem;font-size:0.72rem;">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="/arsip/<?= $p['id'] ?>" style="font-size:0.72rem;padding:0.25rem 0.6rem;background:var(--purple-bg);color:var(--purple);border:1px solid rgba(168,85,247,0.2);border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;margin-right:0.25rem;">
                                        <i class="fa-solid fa-box-archive"></i> Arsip
                                    </a>
                                    <button class="btn-app btn-ghost" style="font-size:0.72rem;padding:0.25rem 0.6rem;margin-right:0.25rem;"
                                        onclick="bukaModalEditPeriode(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_periode'], ENT_QUOTES) ?>', '<?= $p['tanggal_mulai'] ?>', '<?= $p['tanggal_selesai'] ?>')">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <?php if (!$p['aktif']): ?>
                                    <button class="btn-app btn-ghost" style="font-size:0.72rem;padding:0.25rem 0.6rem;"
                                        onclick="aktifkanPeriode(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_periode']) ?>')">
                                        <i class="fa-solid fa-check"></i> Aktifkan
                                    </button>
                                    <button class="btn-app" style="font-size:0.72rem;padding:0.25rem 0.6rem;background:var(--red-bg);color:var(--red);border:1px solid rgba(239,68,68,0.2);"
                                        onclick="hapusPeriode(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_periode']) ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php else: ?>
                                    <span style="font-size:0.72rem;color:var(--text3);">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Edit Periode -->
<div class="modal-overlay" id="modalEditPeriode">
    <div class="modal-box">
        <h3><i class="fa-solid fa-pen me-1" style="color:var(--blue)"></i> Edit Periode</h3>
        <input type="hidden" id="editPeriodeId">
        <div class="modal-field">
            <label>Nama Periode</label>
            <input type="text" id="editNamaPeriode" placeholder="cth: PKL Juli - November 2026">
        </div>
        <div class="modal-field">
            <label>Tanggal Mulai</label>
            <input type="date" id="editTanggalMulai">
        </div>
        <div class="modal-field">
            <label>Tanggal Selesai</label>
            <input type="date" id="editTanggalSelesai">
        </div>
        <div class="result-box" id="editPeriodeResult"></div>
        <div class="modal-actions">
            <button class="btn-app btn-primary-app" onclick="simpanEditPeriode()">
                <i class="fa-solid fa-check"></i> Simpan
            </button>
            <button class="btn-app btn-ghost" onclick="tutupModalEditPeriode()">Batal</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
// ── Tab switching ──────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.currentTarget.classList.add('active');

    // Init DataTable lazily
    if (name === 'pembimbing' && !$.fn.DataTable.isDataTable('#tabelPembimbing')) {
        $('#tabelPembimbing').DataTable({ pageLength: 25, order: [[2,'asc']], columnDefs:[{orderable:false,targets:[0,1,2,3,4]}] });
    }
    if (name === 'walikelas' && !$.fn.DataTable.isDataTable('#tabelWalikelas')) {
        $('#tabelWalikelas').DataTable({ pageLength: 25, order: [[3,'asc']], columnDefs:[{orderable:false,targets:[0,1,2,3,4]}] });
    }
    if (name === 'dudika' && !$.fn.DataTable.isDataTable('#tabelDudika')) {
        $('#tabelDudika').DataTable({ pageLength: 25, order: [[1,'asc']], columnDefs:[{orderable:false,targets:[0,1,2,3,4]}] });
    }
}

// ── Inline edit ────────────────────────────────────────────
document.querySelectorAll('.editable').forEach(cell => {
    let original = cell.textContent.trim();

    cell.addEventListener('focus', () => { original = cell.textContent.trim(); });

    cell.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); cell.blur(); }
        if (e.key === 'Escape') { cell.textContent = original; cell.blur(); }
    });

    cell.addEventListener('blur', () => {
        let value = cell.textContent.trim();

        // Filter nohp
        if (cell.dataset.type === 'nohp') {
            value = value.replace(/\D/g, '');
            cell.textContent = value;
        }

        if (value === original) return; // tidak ada perubahan

        const row   = cell.closest('tr');
        const table = row.dataset.table;
        const field = cell.dataset.field;

        let body = new FormData();
        body.append('field', field);
        body.append('value', value);

        let url = '';
        if (table === 'pembimbing') {
            url = '/manage/update-pembimbing';
            body.append('id', row.dataset.id);
        } else if (table === 'walikelas') {
            url = '/manage/update-walikelas';
            body.append('id', row.dataset.id);
        } else if (table === 'dudika') {
            url = '/manage/update-dudika';
            body.append('id', row.dataset.id);
        }

        cell.classList.add('saving');
        cell.contentEditable = 'false';

        fetch(url, { method: 'POST', body })
            .then(r => r.json())
            .then(res => {
                cell.classList.remove('saving');
                cell.contentEditable = 'true';
                if (res.status === 'success') {
                    original = value;
                    cell.classList.add('saved');
                    setTimeout(() => cell.classList.remove('saved'), 1500);
                } else {
                    cell.textContent = original;
                    cell.classList.add('error');
                    setTimeout(() => cell.classList.remove('error'), 2000);
                }
            })
            .catch(() => {
                cell.classList.remove('saving');
                cell.contentEditable = 'true';
                cell.textContent = original;
                cell.classList.add('error');
                setTimeout(() => cell.classList.remove('error'), 2000);
            });
    });
});

// ── Upload Excel ───────────────────────────────────────────
const zone    = document.getElementById('uploadZone');
const fileIn  = document.getElementById('fileInput');
const result  = document.getElementById('uploadResult');
const progWrap= document.getElementById('uploadProgress');
const progBar = document.getElementById('progressBar');
const progTxt = document.getElementById('progressText');

['dragover','dragleave','drop'].forEach(evt => zone.addEventListener(evt, e => e.preventDefault()));
zone.addEventListener('dragover',  () => zone.classList.add('dragover'));
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) doUpload(file);
});
fileIn.addEventListener('change', () => { if (fileIn.files[0]) doUpload(fileIn.files[0]); });

function doUpload(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx','xls'].includes(ext)) {
        showResult(result, 'error', 'Hanya file .xlsx atau .xls yang diizinkan.');
        return;
    }

    const fd = new FormData();
    fd.append('file', file);

    progWrap.style.display = 'block';
    progBar.style.width = '0%';
    progTxt.textContent = 'Mengupload: ' + file.name;
    result.style.display = 'none';

    const xhr = new XMLHttpRequest();
    xhr.upload.onprogress = e => {
        if (e.lengthComputable) {
            const pct = Math.round(e.loaded / e.total * 80);
            progBar.style.width = pct + '%';
            progTxt.textContent = 'Mengupload... ' + pct + '%';
        }
    };
    xhr.onload = () => {
        progBar.style.width = '100%';
        progTxt.textContent = 'Selesai.';
        try {
            const res = JSON.parse(xhr.responseText);
            if (res.status === 'success') {
                const d = res.data;
                showResult(result, 'success',
                    `✅ ${res.message}<br>` +
                    `<small>
                        <strong>Siswa:</strong> +${d.siswa.inserted} baru · ${d.siswa.updated} update · ${d.siswa.skipped} skip &nbsp;|&nbsp;
                        <strong>Penempatan:</strong> +${d.penempatan.inserted} baru · ${d.penempatan.updated} update &nbsp;|&nbsp;
                        <strong>Pembimbing:</strong> +${d.pembimbing.inserted} baru
                    </small>`
                );
            } else {
                showResult(result, 'error', '❌ ' + res.message);
            }
        } catch(e) {
            showResult(result, 'error', '❌ Respons tidak valid dari server.');
        }
        setTimeout(() => { progWrap.style.display = 'none'; }, 2000);
    };
    xhr.onerror = () => {
        showResult(result, 'error', '❌ Koneksi gagal. Coba lagi.');
        progWrap.style.display = 'none';
    };
    xhr.open('POST', '/manage/upload-penempatan');
    xhr.send(fd);
}

// ── Sinkronisasi Pembimbing — Preview ─────────────────────
function previewSinkronPembimbing() {
    const btn = document.getElementById('btnSinkron');
    const res = document.getElementById('sinkronResult');
    const det = document.getElementById('sinkronDetail');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...';
    det.innerHTML = '';
    res.style.display = 'none';

    fetch('/manage/sinkron-pembimbing-preview', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Sinkronisasi';

            if (data.status !== 'success') {
                showResult(res, 'error', '❌ ' + data.message);
                return;
            }

            const d = data.data;

            if (d.jumlah_tidak_cocok === 0) {
                showResult(res, 'success', '✅ ' + data.message);
                det.innerHTML = '';
                return;
            }

            showResult(res, 'warning', `⚠️ ${data.message}`);

            let html = `
            <div style="margin-top:0.75rem;background:var(--bg2);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                <div style="padding:0.6rem 1rem;border-bottom:1px solid var(--border);font-size:0.75rem;font-weight:700;color:var(--text3);">
                    NAMA TIDAK COCOK (${d.jumlah_tidak_cocok}) — Sudah cocok: ${d.sudah_cocok}
                </div>`;

            d.tidak_cocok.forEach(item => {
                html += `
                <div style="padding:0.65rem 1rem;border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow);font-size:0.8rem;"></i>
                        <span style="font-size:0.83rem;font-weight:600;color:var(--red);">${item.nama_penempatan}</span>
                    </div>`;
                if (item.kandidat.length > 0) {
                    html += `<div style="font-size:0.72rem;color:var(--text3);margin-bottom:0.25rem;">Kandidat dari datapembimbing:</div>`;
                    item.kandidat.forEach(k => {
                        html += `<span style="display:inline-block;margin:2px;background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.5rem;font-size:0.72rem;">${k.nama} <span style="opacity:0.6;">${k.pct}%</span></span>`;
                    });
                } else {
                    html += `<div style="font-size:0.72rem;color:var(--text3);">Tidak ada kandidat yang mirip.</div>`;
                }
                html += `</div>`;
            });

            html += `</div>
            <div style="margin-top:0.5rem;font-size:0.75rem;color:var(--text3);">
                <i class="fa-solid fa-info-circle"></i> Perbaiki nama pembimbing di tab <strong>Pembimbing</strong> atau <strong>DUDIKA</strong> secara inline.
            </div>`;

            det.innerHTML = html;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Sinkronisasi';
            showResult(res, 'error', '❌ Gagal menghubungi server.');
        });
}

// ── Sinkronisasi Siswa — Preview ──────────────────────────
function previewSinkronSiswa() {
    const btn = document.getElementById('btnPreviewSiswa');
    const res = document.getElementById('siswaResult');
    const det = document.getElementById('siswaDetail');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengambil preview...';
    det.innerHTML = '';
    res.style.display = 'none';

    fetch('/manage/sinkron-siswa-preview', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Perubahan';

            if (data.status !== 'success') {
                showResult(res, 'error', '❌ ' + data.message);
                return;
            }

            const d = data.data;

            if (d.total === 0) {
                showResult(res, 'success', '✅ ' + data.message);
                det.innerHTML = '';
                return;
            }

            showResult(res, 'warning', `⚠️ ${data.message} — Periksa dan konfirmasi di bawah.`);

            // Tabel preview
            let html = `
            <div style="margin-top:0.75rem;background:var(--bg2);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                <div style="padding:0.6rem 1rem;border-bottom:1px solid var(--border);font-size:0.75rem;font-weight:700;color:var(--text3);">
                    PREVIEW PERUBAHAN (${d.total} siswa)
                </div>
                <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                    <thead>
                        <tr style="background:var(--bg3);">
                            <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;">NIS</th>
                            <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;">Field</th>
                            <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;">Data Lama</th>
                            <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;">Data Baru</th>
                        </tr>
                    </thead>
                    <tbody>`;

            d.changes.forEach(c => {
                Object.entries(c.diff).forEach(([field, val]) => {
                    html += `
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:0.45rem 0.75rem;font-family:monospace;font-size:0.75rem;color:var(--text3);">${c.nis}</td>
                        <td style="padding:0.45rem 0.75rem;"><span style="background:var(--blue-bg);color:var(--blue);border-radius:4px;padding:0.1rem 0.4rem;font-size:0.7rem;font-weight:600;">${field}</span></td>
                        <td style="padding:0.45rem 0.75rem;color:var(--red);text-decoration:line-through;font-size:0.78rem;">${val.lama}</td>
                        <td style="padding:0.45rem 0.75rem;color:var(--green);font-weight:600;font-size:0.78rem;">${val.baru}</td>
                    </tr>`;
                });
            });

            html += `</tbody></table></div>`;

            // Tombol konfirmasi
            html += `
            <div style="padding:0.875rem 1rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;align-items:center;">
                <button class="btn-app btn-success-app" id="btnExecSiswa" onclick="eksekusiSinkronSiswa()">
                    <i class="fa-solid fa-check"></i> Konfirmasi & Eksekusi
                </button>
                <button class="btn-app btn-ghost" onclick="document.getElementById('siswaDetail').innerHTML='';document.getElementById('siswaResult').style.display='none';">
                    Batal
                </button>
                <span style="font-size:0.75rem;color:var(--text3);">Perubahan tidak dapat dibatalkan setelah dieksekusi.</span>
            </div>
            </div>`;

            // No match
            if (d.no_match.length > 0) {
                html += `<div style="margin-top:0.5rem;font-size:0.75rem;color:var(--text3);">
                    <i class="fa-solid fa-info-circle"></i> ${d.no_match.length} NIS di penempatan tidak ditemukan di datasiswa (dilewati).
                </div>`;
            }

            det.innerHTML = html;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Perubahan';
            showResult(res, 'error', '❌ Gagal menghubungi server.');
        });
}

// ── Sinkronisasi Siswa — Eksekusi ─────────────────────────
function eksekusiSinkronSiswa() {
    const btn = document.getElementById('btnExecSiswa');
    const res = document.getElementById('siswaResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengeksekusi...';

    fetch('/manage/sinkron-siswa-exec', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(res, 'success', '✅ ' + data.message);
                document.getElementById('siswaDetail').innerHTML = '';
            } else {
                showResult(res, 'error', '❌ ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi & Eksekusi';
            }
        })
        .catch(() => {
            showResult(res, 'error', '❌ Gagal menghubungi server.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi & Eksekusi';
        });
}

// ── Helper ─────────────────────────────────────────────────
function showResult(el, type, msg) {
    el.className = 'result-box ' + type;
    el.innerHTML = msg;
    el.style.display = 'block';
}

// ── Periode PKL ───────────────────────────────────────────
function tambahPeriode() {
    const nama    = document.getElementById('inputNamaPeriode').value.trim();
    const mulai   = document.getElementById('inputTanggalMulai').value;
    const selesai = document.getElementById('inputTanggalSelesai').value;
    const res     = document.getElementById('periodeFormResult');

    if (!nama || !mulai || !selesai) {
        showResult(res, 'error', '❌ Semua field wajib diisi.'); return;
    }

    const fd = new FormData();
    fd.append('nama_periode', nama);
    fd.append('tanggal_mulai', mulai);
    fd.append('tanggal_selesai', selesai);

    fetch('/manage/periode-tambah', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(res, 'success', '✅ ' + data.message);
                document.getElementById('inputNamaPeriode').value = '';
                document.getElementById('inputTanggalMulai').value = '';
                document.getElementById('inputTanggalSelesai').value = '';
                setTimeout(() => location.reload(), 1000);
            } else {
                showResult(res, 'error', '❌ ' + data.message);
            }
        })
        .catch(() => showResult(res, 'error', '❌ Gagal menghubungi server.'));
}

function aktifkanPeriode(id, nama) {
    if (!confirm(`Aktifkan periode "${nama}"?\n\nPeriode aktif saat ini akan dinonaktifkan.`)) return;

    const fd = new FormData();
    fd.append('id', id);

    fetch('/manage/periode-aktifkan', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(() => alert('❌ Gagal menghubungi server.'));
}

function hapusPeriode(id, nama) {
    if (!confirm(`Hapus periode "${nama}"?\n\nPeriode yang sudah memiliki data tidak bisa dihapus.`)) return;

    const fd = new FormData();
    fd.append('id', id);

    fetch('/manage/periode-hapus', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('periode-row-' + id)?.remove();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(() => alert('❌ Gagal menghubungi server.'));
}

// ── Sinkronisasi DUDI — Preview ───────────────────────────
function previewSinkronDudi() {
    const btn = document.getElementById('btnPreviewDudi');
    const res = document.getElementById('dudiResult');
    const det = document.getElementById('dudiDetail');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...';
    det.innerHTML = '';
    res.style.display = 'none';

    fetch('/manage/sinkron-dudi-preview', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview DUDI Baru';

            if (data.status !== 'success') {
                showResult(res, 'error', '❌ ' + data.message); return;
            }

            const d = data.data;

            if (d.jumlah_baru === 0) {
                showResult(res, 'success', '✅ ' + data.message);
                det.innerHTML = ''; return;
            }

            showResult(res, 'warning', `⚠️ ${data.message} — Sudah ada: ${d.sudah_ada}`);

            let html = `
            <div style="margin-top:0.75rem;background:var(--bg2);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                <div style="padding:0.6rem 1rem;border-bottom:1px solid var(--border);font-size:0.75rem;font-weight:700;color:var(--text3);">
                    DUDI BARU (${d.jumlah_baru})
                </div>`;

            d.baru.forEach(item => {
                html += `
                <div style="padding:0.55rem 1rem;border-bottom:1px solid var(--border);font-size:0.82rem;">
                    <strong>${item.nama_dudika}</strong>
                    <span style="color:var(--text3);font-size:0.72rem;margin-left:0.5rem;">${item.alamat_dudika || ''}</span>
                </div>`;
            });

            html += `</div>
            <div style="padding:0.875rem 0;display:flex;gap:0.75rem;align-items:center;">
                <button class="btn-app btn-success-app" id="btnExecDudi" onclick="eksekusiSinkronDudi()">
                    <i class="fa-solid fa-check"></i> Tambahkan ke DUDI
                </button>
                <button class="btn-app btn-ghost" onclick="document.getElementById('dudiDetail').innerHTML='';document.getElementById('dudiResult').style.display='none';">
                    Batal
                </button>
            </div>`;

            det.innerHTML = html;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview DUDI Baru';
            showResult(res, 'error', '❌ Gagal menghubungi server.');
        });
}

function eksekusiSinkronDudi() {
    const btn = document.getElementById('btnExecDudi');
    const res = document.getElementById('dudiResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

    fetch('/manage/sinkron-dudi-exec', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(res, 'success', '✅ ' + data.message);
                document.getElementById('dudiDetail').innerHTML = '';
            } else {
                showResult(res, 'error', '❌ ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Tambahkan ke DUDI';
            }
        })
        .catch(() => {
            showResult(res, 'error', '❌ Gagal menghubungi server.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Tambahkan ke DUDI';
        });
}

// ── Link map edit ─────────────────────────────────────────
document.querySelectorAll('.link-map-input').forEach(input => {
    const td = input.closest('td');

    // Klik icon → tampilkan input
    td.querySelector('a, span').addEventListener('click', e => {
        if (e.target.closest('a')) e.preventDefault();
        input.style.display = 'block';
        input.focus();
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.style.display = 'none'; }
    });

    input.addEventListener('blur', () => {
        const value   = input.value.trim();
        const id      = input.dataset.id;
        const original = input.defaultValue;

        if (value === original) { input.style.display = 'none'; return; }

        const fd = new FormData();
        fd.append('id', id);
        fd.append('field', 'link_map');
        fd.append('value', value);

        fetch('/manage/update-dudika', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    input.defaultValue = value;
                    // Update icon
                    const icon = td.querySelector('a, span');
                    if (value) {
                        icon.outerHTML = `<a href="${value}" target="_blank" style="color:var(--blue);font-size:1.1rem;" title="${value}"><i class="fa-solid fa-location-dot"></i></a>`;
                    } else {
                        icon.outerHTML = `<span style="color:var(--border2);font-size:1rem;" title="Link map belum diisi"><i class="fa-solid fa-location-dot"></i></span>`;
                    }
                    // Re-attach click listener
                    td.querySelector('a, span').addEventListener('click', e => {
                        if (e.target.closest('a')) e.preventDefault();
                        input.style.display = 'block';
                        input.focus();
                    });
                }
                input.style.display = 'none';
            })
            .catch(() => { input.style.display = 'none'; });
    });
});

// ── Cek Duplikat DUDI ─────────────────────────────────────
function cekDuplikatDudi() {
    const btn = document.getElementById('btnCekDuplikat');
    const res = document.getElementById('duplikatResult');
    const det = document.getElementById('duplikatDetail');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...';
    det.innerHTML = '';
    res.style.display = 'none';

    fetch('/manage/cek-duplikat-dudi', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Cek Duplikat';

            if (data.status !== 'success') {
                showResult(res, 'error', '❌ ' + data.message); return;
            }

            const d = data.data;

            if (d.jumlah === 0) {
                showResult(res, 'success', '✅ ' + data.message);
                det.innerHTML = ''; return;
            }

            showResult(res, 'warning', `⚠️ ${data.message} — Centang yang ingin digabung, biarkan kosong untuk diabaikan.`);

            let html = `
            <div id="duplikatList" style="display:flex;flex-direction:column;gap:0.5rem;margin-top:0.75rem;">`;

            d.duplikat.forEach((pair, idx) => {
                html += `
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:8px;overflow:hidden;" id="pair-${idx}">
                    <div style="padding:0.4rem 1rem;border-bottom:1px solid var(--border);font-size:0.7rem;font-weight:700;color:var(--text3);display:flex;justify-content:space-between;align-items:center;">
                        <span>Kemiripan: <strong style="color:var(--yellow);">${pair.pct}%</strong></span>
                        <span style="color:var(--text3);">Pilih yang dipertahankan (atau biarkan kosong)</span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;">

                        <!-- Opsi A -->
                        <label style="flex:1;min-width:200px;padding:0.65rem 1rem;cursor:pointer;border-right:1px solid var(--border);display:flex;gap:0.65rem;align-items:flex-start;transition:background 0.1s;"
                               onmouseover="this.style.background='var(--bg3)'" onmouseout="this.style.background=''"
                               for="opt-${idx}-a">
                            <input type="radio" name="pair-${idx}" id="opt-${idx}-a"
                                   value="a" data-keep="${pair.a.id}" data-remove="${pair.b.id}"
                                   data-nama-keep="${pair.a.nama.replace(/"/g,'&quot;')}"
                                   data-nama-remove="${pair.b.nama.replace(/"/g,'&quot;')}"
                                   style="margin-top:3px;accent-color:var(--green);">
                            <div>
                                <div style="font-weight:600;font-size:0.83rem;">${pair.a.nama}</div>
                                <div style="font-size:0.72rem;color:var(--text3);margin-top:0.1rem;">📍 ${pair.a.alamat || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--text3);">👤 ${pair.a.nama_pembimbing || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--text3);">📱 ${pair.a.nomor_telepon || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--blue);font-weight:600;">🎓 ${pair.a.jumlah_siswa} siswa</div>
                            </div>
                        </label>

                        <!-- Opsi B -->
                        <label style="flex:1;min-width:200px;padding:0.65rem 1rem;cursor:pointer;display:flex;gap:0.65rem;align-items:flex-start;transition:background 0.1s;"
                               onmouseover="this.style.background='var(--bg3)'" onmouseout="this.style.background=''"
                               for="opt-${idx}-b">
                            <input type="radio" name="pair-${idx}" id="opt-${idx}-b"
                                   value="b" data-keep="${pair.b.id}" data-remove="${pair.a.id}"
                                   data-nama-keep="${pair.b.nama.replace(/"/g,'&quot;')}"
                                   data-nama-remove="${pair.a.nama.replace(/"/g,'&quot;')}"
                                   style="margin-top:3px;accent-color:var(--green);">
                            <div>
                                <div style="font-weight:600;font-size:0.83rem;">${pair.b.nama}</div>
                                <div style="font-size:0.72rem;color:var(--text3);margin-top:0.1rem;">📍 ${pair.b.alamat || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--text3);">👤 ${pair.b.nama_pembimbing || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--text3);">📱 ${pair.b.nomor_telepon || '-'}</div>
                                <div style="font-size:0.72rem;color:var(--blue);font-weight:600;">🎓 ${pair.b.jumlah_siswa} siswa</div>
                            </div>
                        </label>

                    </div>
                </div>`;
            });

            html += `</div>

            <!-- Tombol eksekusi -->
            <div style="margin-top:1rem;display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                <button class="btn-app btn-success-app" onclick="eksekusiMergeDudi()">
                    <i class="fa-solid fa-check"></i> Eksekusi Pilihan
                </button>
                <button class="btn-app btn-ghost" onclick="document.getElementById('duplikatDetail').innerHTML='';document.getElementById('duplikatResult').style.display='none';">
                    Batal
                </button>
                <span style="font-size:0.75rem;color:var(--text3);">Hanya pasangan yang dicentang yang akan digabung.</span>
            </div>`;

            det.innerHTML = html;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Cek Duplikat';
            showResult(res, 'error', '❌ Gagal menghubungi server.');
        });
}

async function eksekusiMergeDudi() {
    // Kumpulkan semua radio yang dipilih
    const dipilih = [];
    document.querySelectorAll('#duplikatList input[type=radio]:checked').forEach(radio => {
        dipilih.push({
            idKeep:     radio.dataset.keep,
            idRemove:   radio.dataset.remove,
            namaKeep:   radio.dataset.namaKeep,
            namaRemove: radio.dataset.namaRemove,
        });
    });

    if (dipilih.length === 0) {
        alert('Belum ada pasangan yang dipilih. Centang salah satu dari setiap pasangan yang ingin digabung.'); return;
    }

    if (!confirm(`Akan menggabung ${dipilih.length} pasangan DUDI.\n\nLanjutkan?`)) return;

    const res = document.getElementById('duplikatResult');
    let berhasil = 0, gagal = 0;

    for (const item of dipilih) {
        const fd = new FormData();
        fd.append('id_keep',   item.idKeep);
        fd.append('id_remove', item.idRemove);

        try {
            const r    = await fetch('/manage/merge-dudi', { method: 'POST', body: fd });
            const data = await r.json();
            if (data.status === 'success') berhasil++;
            else gagal++;
        } catch {
            gagal++;
        }
    }

    showResult(res, berhasil > 0 ? 'success' : 'error',
        `✅ ${berhasil} pasangan berhasil digabung${gagal > 0 ? ` · ❌ ${gagal} gagal` : ''}.`
    );

    // Refresh list
    if (berhasil > 0) {
        setTimeout(() => cekDuplikatDudi(), 800);
    }
}

// ── Edit Periode ──────────────────────────────────────────
function bukaModalEditPeriode(id, nama, mulai, selesai) {
    document.getElementById('editPeriodeId').value       = id;
    document.getElementById('editNamaPeriode').value     = nama;
    document.getElementById('editTanggalMulai').value    = mulai;
    document.getElementById('editTanggalSelesai').value  = selesai;
    document.getElementById('editPeriodeResult').style.display = 'none';
    document.getElementById('modalEditPeriode').classList.add('open');
}

function tutupModalEditPeriode() {
    document.getElementById('modalEditPeriode').classList.remove('open');
}

function simpanEditPeriode() {
    const id      = document.getElementById('editPeriodeId').value;
    const nama    = document.getElementById('editNamaPeriode').value.trim();
    const mulai   = document.getElementById('editTanggalMulai').value;
    const selesai = document.getElementById('editTanggalSelesai').value;
    const res     = document.getElementById('editPeriodeResult');

    if (!nama || !mulai || !selesai) {
        showResult(res, 'error', '❌ Semua field wajib diisi.'); return;
    }

    const fd = new FormData();
    fd.append('id',              id);
    fd.append('nama_periode',    nama);
    fd.append('tanggal_mulai',   mulai);
    fd.append('tanggal_selesai', selesai);

    fetch('/manage/periode-edit', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(res, 'success', '✅ ' + data.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showResult(res, 'error', '❌ ' + data.message);
            }
        })
        .catch(() => showResult(res, 'error', '❌ Gagal menghubungi server.'));
}

// Tutup modal jika klik di luar
document.getElementById('modalEditPeriode').addEventListener('click', function(e) {
    if (e.target === this) tutupModalEditPeriode();
});
</script>
JS;

$activePage = 'manage';
require BASE_PATH . '/app/Views/layouts/app.php';