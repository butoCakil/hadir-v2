<?php ob_start();

$extraCss = <<<CSS
<style>
.setting-section { margin-bottom:1.5rem; }
.setting-label { font-size:0.78rem; font-weight:600; color:var(--text2); display:block; margin-bottom:0.3rem; }
.setting-desc { font-size:0.72rem; color:var(--text3); margin-bottom:0.5rem; line-height:1.5; }
.setting-input { padding:0.45rem 0.65rem; border-radius:6px; border:1px solid var(--border2); background:var(--bg3); color:var(--text); font-size:0.83rem; width:120px; }
.setting-input-time { padding:0.45rem 0.65rem; border-radius:6px; border:1px solid var(--border2); background:var(--bg3); color:var(--text); font-size:0.83rem; width:100px; }
.toggle-wrap { display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem; }
.toggle { position:relative; width:40px; height:22px; flex-shrink:0; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:var(--border2); border-radius:22px; cursor:pointer; transition:0.2s; }
.toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:white; border-radius:50%; transition:0.2s; }
.toggle input:checked + .toggle-slider { background:var(--blue); }
.toggle input:checked + .toggle-slider:before { transform:translateX(18px); }
.notif-card { background:var(--bg3); border:1px solid var(--border); border-radius:8px; padding:1rem; margin-bottom:0.75rem; }
.notif-card-title { font-size:0.82rem; font-weight:700; margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem; }
.notif-row { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; margin-top:0.5rem; }
.notif-row label { font-size:0.75rem; color:var(--text3); }
select.setting-select { padding:0.4rem 0.65rem; border-radius:6px; border:1px solid var(--border2); background:var(--bg3); color:var(--text); font-size:0.82rem; }
.gateway-mode-btn { padding:0.4rem 1rem; border-radius:6px; border:1px solid var(--border2); background:var(--bg3); color:var(--text2); font-size:0.82rem; font-weight:600; cursor:pointer; transition:all 0.15s; display:inline-flex; align-items:center; gap:0.35rem; }
.gateway-mode-btn.gm-active { background:var(--blue-bg); color:var(--blue); border-color:rgba(79,142,247,0.4); }
</style>
CSS;
?>

<div class="row g-3">

    <!-- Toleransi Periode -->
    <div class="col-12 col-lg-6">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-calendar-days me-1" style="color:var(--blue)"></i> Toleransi Periode Presensi</span>
            </div>
            <div class="card-body-app">
                <?php if ($periodeAktif): ?>
                <div style="background:var(--blue-bg);border:1px solid rgba(59,130,246,0.2);border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.82rem;">
                    <strong style="color:var(--blue);">Periode Aktif:</strong>
                    <span style="color:var(--text2);margin-left:0.5rem;"><?= htmlspecialchars($periodeAktif['nama_periode']) ?></span><br>
                    <span style="color:var(--text3);font-size:0.75rem;">
                        <?= date('d M Y', strtotime($periodeAktif['tanggal_mulai'])) ?> —
                        <?= date('d M Y', strtotime($periodeAktif['tanggal_selesai'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1.25rem;line-height:1.6;">
                    Atur berapa hari toleransi presensi di luar rentang periode aktif.
                </p>
                <div class="setting-section">
                    <label class="setting-label">Toleransi Sebelum Periode Mulai</label>
                    <div class="setting-desc">Siswa boleh presensi berapa hari sebelum tanggal mulai. (0 = tidak ada toleransi)</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="number" id="inputToleransiSebelum" class="setting-input" min="0" max="60"
                               value="<?= (int)$settings['toleransi_sebelum'] ?>">
                        <span style="font-size:0.82rem;color:var(--text3);">hari</span>
                    </div>
                </div>
                <div class="setting-section">
                    <label class="setting-label">Toleransi Setelah Periode Selesai</label>
                    <div class="setting-desc">Siswa boleh presensi berapa hari setelah tanggal selesai. (0 = tidak ada toleransi)</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="number" id="inputToleransiSesudah" class="setting-input" min="0" max="60"
                               value="<?= (int)$settings['toleransi_sesudah'] ?>">
                        <span style="font-size:0.82rem;color:var(--text3);">hari</span>
                    </div>
                </div>
                <button class="btn-app btn-primary-app" onclick="simpanPengaturan()">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <div class="result-box" id="settingResult" style="margin-top:0.75rem;"></div>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="col-12 col-lg-6">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-solid fa-lock me-1" style="color:var(--yellow)"></i> Ganti Password Admin</span>
            </div>
            <div class="card-body-app">
                <div class="setting-section">
                    <label class="setting-label">Password Lama</label>
                    <input type="password" id="inputPasswordLama"
                           style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                </div>
                <div class="setting-section">
                    <label class="setting-label">Password Baru</label>
                    <div class="setting-desc">Minimal 8 karakter.</div>
                    <input type="password" id="inputPasswordBaru"
                           style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                </div>
                <div class="setting-section">
                    <label class="setting-label">Konfirmasi Password Baru</label>
                    <input type="password" id="inputKonfirmasi"
                           style="width:100%;padding:0.45rem 0.65rem;border-radius:6px;border:1px solid var(--border2);background:var(--bg3);color:var(--text);font-size:0.83rem;">
                </div>
                <button class="btn-app" style="background:var(--yellow-bg);color:var(--yellow);border:1px solid rgba(245,158,11,0.2);" onclick="gantiPassword()">
                    <i class="fa-solid fa-key"></i> Ganti Password
                </button>
                <div class="result-box" id="passwordResult" style="margin-top:0.75rem;"></div>
            </div>
        </div>
    </div>

    <!-- Pengaturan Notifikasi WA -->
    <div class="col-12">
        <div class="card-app">
            <div class="card-header-app">
                <span class="card-title"><i class="fa-brands fa-whatsapp me-1" style="color:var(--green)"></i> Pengaturan Notifikasi WhatsApp</span>
                <span style="font-size:0.75rem;color:var(--text3);">Semua notifikasi dikirim via WA bot</span>
            </div>
            <div class="card-body-app">
                <div class="row g-3">

                    <!-- Reminder Siswa -->
                    <div class="col-12 col-md-6">
                        <div class="notif-card">
                            <div class="notif-card-title">
                                <i class="fa-solid fa-bell" style="color:var(--blue);"></i>
                                Reminder Presensi ke Siswa
                            </div>
                            <div class="setting-desc">
                                Kirim pengingat ke siswa yang belum presensi. Hanya siswa yang terdeteksi aktif di hari tersebut yang diingatkan.
                            </div>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="notifSiswaAktif" <?= $settings['notif_siswa_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Aktifkan</span>
                            </div>
                            <div class="notif-row">
                                <div>
                                    <label>Jam Kirim</label>
                                    <input type="time" id="notifSiswaJam" class="setting-input-time"
                                           value="<?= htmlspecialchars($settings['notif_siswa_jam']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Pembimbing Harian -->
                    <div class="col-12 col-md-6">
                        <div class="notif-card">
                            <div class="notif-card-title">
                                <i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow);"></i>
                                Alert Siswa Belum Presensi ke Pembimbing
                            </div>
                            <div class="setting-desc">
                                Kirim daftar siswa yang belum presensi ke pembimbing masing-masing setiap hari.
                            </div>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="notifAlertAktif" <?= $settings['notif_alert_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Aktifkan</span>
                            </div>
                            <div class="notif-row">
                                <div>
                                    <label>Jam Kirim</label>
                                    <input type="time" id="notifAlertJam" class="setting-input-time"
                                           value="<?= htmlspecialchars($settings['notif_alert_jam']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekap Pembimbing Mingguan -->
                    <div class="col-12 col-md-6">
                        <div class="notif-card">
                            <div class="notif-card-title">
                                <i class="fa-solid fa-person-chalkboard" style="color:var(--purple);"></i>
                                Rekap Mingguan ke Pembimbing
                            </div>
                            <div class="setting-desc">
                                Kirim rekap kehadiran seminggu ke tiap pembimbing untuk siswa bimbingannya.
                            </div>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="notifPembimbingAktif" <?= $settings['notif_pembimbing_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Aktifkan</span>
                            </div>
                            <div class="notif-row">
                                <div>
                                    <label>Hari Kirim</label>
                                    <select id="notifPembimbingHari" class="setting-select">
                                        <?php
                                        $hariList = ['1'=>'Senin','2'=>'Selasa','3'=>'Rabu','4'=>'Kamis','5'=>'Jumat','6'=>'Sabtu','7'=>'Minggu'];
                                        foreach ($hariList as $val => $lbl):
                                        ?>
                                        <option value="<?= $val ?>" <?= $settings['notif_pembimbing_hari'] === $val ? 'selected' : '' ?>>
                                            <?= $lbl ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Jam Kirim</label>
                                    <input type="time" id="notifPembimbingJam" class="setting-input-time"
                                           value="<?= htmlspecialchars($settings['notif_pembimbing_jam']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekap Wali Kelas Mingguan -->
                    <div class="col-12 col-md-6">
                        <div class="notif-card">
                            <div class="notif-card-title">
                                <i class="fa-solid fa-chalkboard-user" style="color:var(--green);"></i>
                                Rekap Mingguan ke Wali Kelas
                            </div>
                            <div class="setting-desc">
                                Kirim rekap kehadiran seminggu ke tiap wali kelas untuk siswa di kelasnya.
                            </div>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="notifWalikelasAktif" <?= $settings['notif_walikelas_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Aktifkan</span>
                            </div>
                            <div class="notif-row">
                                <div>
                                    <label>Hari Kirim</label>
                                    <select id="notifWalikelasHari" class="setting-select">
                                        <?php foreach ($hariList as $val => $lbl): ?>
                                        <option value="<?= $val ?>" <?= $settings['notif_walikelas_hari'] === $val ? 'selected' : '' ?>>
                                            <?= $lbl ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Jam Kirim</label>
                                    <input type="time" id="notifWalikelasJam" class="setting-input-time"
                                           value="<?= htmlspecialchars($settings['notif_walikelas_jam']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div style="margin-top:1rem;">
                    <button class="btn-app btn-primary-app" onclick="simpanNotifikasi()">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Notifikasi
                    </button>
                    <div class="result-box" id="notifResult" style="margin-top:0.75rem;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Gateway Presensi -->
<div class="col-12">
    <div class="card-app">
        <div class="card-header-app">
            <span class="card-title"><i class="fa-solid fa-door-open me-1" style="color:var(--orange)"></i> Gateway Presensi</span>
            <span style="font-size:0.75rem;color:var(--text3);">Kontrol akses presensi WA dan Web</span>
        </div>
        <div class="card-body-app">
            <p style="font-size:0.83rem;color:var(--text2);margin-bottom:1.25rem;line-height:1.6;">
                Mode <strong>Auto</strong>: akses presensi mengikuti rentang periode aktif + toleransi yang telah diatur.<br>
                Mode <strong>Manual</strong>: admin menentukan sendiri apakah presensi dibuka atau ditutup, terlepas dari periode.
            </p>
            <div class="row g-3">

                <!-- Gateway WA -->
                <div class="col-12 col-md-6">
                    <div class="notif-card">
                        <div class="notif-card-title">
                            <i class="fa-brands fa-whatsapp" style="color:var(--green);"></i>
                            Presensi via WA Bot
                        </div>
                        <div class="setting-section">
                            <label class="setting-label">Mode</label>
                            <div style="display:flex;gap:0.5rem;">
                                <button type="button" class="gateway-mode-btn <?= $settings['gateway_wa_mode'] === 'auto' ? 'gm-active' : '' ?>"
                                        id="gw_wa_auto" onclick="setGatewayMode('wa','auto')">
                                    <i class="fa-solid fa-rotate"></i> Auto
                                </button>
                                <button type="button" class="gateway-mode-btn <?= $settings['gateway_wa_mode'] === 'manual' ? 'gm-active' : '' ?>"
                                        id="gw_wa_manual" onclick="setGatewayMode('wa','manual')">
                                    <i class="fa-solid fa-hand"></i> Manual
                                </button>
                            </div>
                        </div>
                        <div class="setting-section" id="gw_wa_manual_section" style="<?= $settings['gateway_wa_mode'] === 'manual' ? '' : 'display:none;' ?>">
                            <label class="setting-label">Status Manual</label>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="gatewayWaAktif" <?= $settings['gateway_wa_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Buka Presensi WA</span>
                            </div>
                        </div>
                        <input type="hidden" id="gatewayWaMode" value="<?= htmlspecialchars($settings['gateway_wa_mode']) ?>">
                    </div>
                </div>

                <!-- Gateway Web -->
                <div class="col-12 col-md-6">
                    <div class="notif-card">
                        <div class="notif-card-title">
                            <i class="fa-solid fa-globe" style="color:var(--yellow);"></i>
                            Presensi via Web
                        </div>
                        <div class="setting-section">
                            <label class="setting-label">Mode</label>
                            <div style="display:flex;gap:0.5rem;">
                                <button type="button" class="gateway-mode-btn <?= $settings['gateway_web_mode'] === 'auto' ? 'gm-active' : '' ?>"
                                        id="gw_web_auto" onclick="setGatewayMode('web','auto')">
                                    <i class="fa-solid fa-rotate"></i> Auto
                                </button>
                                <button type="button" class="gateway-mode-btn <?= $settings['gateway_web_mode'] === 'manual' ? 'gm-active' : '' ?>"
                                        id="gw_web_manual" onclick="setGatewayMode('web','manual')">
                                    <i class="fa-solid fa-hand"></i> Manual
                                </button>
                            </div>
                        </div>
                        <div class="setting-section" id="gw_web_manual_section" style="<?= $settings['gateway_web_mode'] === 'manual' ? '' : 'display:none;' ?>">
                            <label class="setting-label">Status Manual</label>
                            <div class="toggle-wrap">
                                <label class="toggle">
                                    <input type="checkbox" id="gatewayWebAktif" <?= $settings['gateway_web_aktif'] === '1' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size:0.82rem;font-weight:600;">Buka Presensi Web</span>
                            </div>
                        </div>
                        <input type="hidden" id="gatewayWebMode" value="<?= htmlspecialchars($settings['gateway_web_mode']) ?>">
                    </div>
                </div>

            </div>
            <div style="margin-top:1rem;">
                <button class="btn-app btn-primary-app" onclick="simpanGateway()">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Gateway
                </button>
                <div class="result-box" id="gatewayResult" style="margin-top:0.75rem;"></div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
function simpanPengaturan() {
    const sebelum = document.getElementById('inputToleransiSebelum').value;
    const sesudah = document.getElementById('inputToleransiSesudah').value;
    const res     = document.getElementById('settingResult');

    const fd = new FormData();
    fd.append('toleransi_sebelum', sebelum);
    fd.append('toleransi_sesudah', sesudah);

    fetch('/pengaturan/simpan', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            res.className    = 'result-box ' + (data.status === 'success' ? 'success' : 'error');
            res.innerHTML    = (data.status === 'success' ? '✅ ' : '❌ ') + data.message;
            res.style.display = 'block';
        })
        .catch(() => { res.className='result-box error'; res.innerHTML='❌ Gagal menghubungi server.'; res.style.display='block'; });
}

function simpanNotifikasi() {
    const res = document.getElementById('notifResult');
    const fd  = new FormData();

    fd.append('notif_siswa_aktif',      document.getElementById('notifSiswaAktif').checked      ? '1' : '0');
    fd.append('notif_siswa_jam',        document.getElementById('notifSiswaJam').value);
    fd.append('notif_alert_aktif',      document.getElementById('notifAlertAktif').checked      ? '1' : '0');
    fd.append('notif_alert_jam',        document.getElementById('notifAlertJam').value);
    fd.append('notif_pembimbing_aktif', document.getElementById('notifPembimbingAktif').checked  ? '1' : '0');
    fd.append('notif_pembimbing_hari',  document.getElementById('notifPembimbingHari').value);
    fd.append('notif_pembimbing_jam',   document.getElementById('notifPembimbingJam').value);
    fd.append('notif_walikelas_aktif',  document.getElementById('notifWalikelasAktif').checked   ? '1' : '0');
    fd.append('notif_walikelas_hari',   document.getElementById('notifWalikelasHari').value);
    fd.append('notif_walikelas_jam',    document.getElementById('notifWalikelasJam').value);

    fetch('/pengaturan/notifikasi', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            res.className    = 'result-box ' + (data.status === 'success' ? 'success' : 'error');
            res.innerHTML    = (data.status === 'success' ? '✅ ' : '❌ ') + data.message;
            res.style.display = 'block';
        })
        .catch(() => { res.className='result-box error'; res.innerHTML='❌ Gagal menghubungi server.'; res.style.display='block'; });
}

function setGatewayMode(channel, mode) {
    document.getElementById('gateway' + (channel === 'wa' ? 'Wa' : 'Web') + 'Mode').value = mode;
    ['auto','manual'].forEach(m => {
        const btn = document.getElementById('gw_' + channel + '_' + m);
        btn.classList.toggle('gm-active', m === mode);
    });
    const manualSection = document.getElementById('gw_' + channel + '_manual_section');
    manualSection.style.display = mode === 'manual' ? 'block' : 'none';
}

function simpanGateway() {
    const res = document.getElementById('gatewayResult');
    const fd  = new FormData();

    fd.append('gateway_wa_mode',   document.getElementById('gatewayWaMode').value);
    fd.append('gateway_wa_aktif',  document.getElementById('gatewayWaAktif')?.checked ? '1' : '0');
    fd.append('gateway_web_mode',  document.getElementById('gatewayWebMode').value);
    fd.append('gateway_web_aktif', document.getElementById('gatewayWebAktif')?.checked ? '1' : '0');

    fetch('/pengaturan/gateway', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            res.className    = 'result-box ' + (data.status === 'success' ? 'success' : 'error');
            res.innerHTML    = (data.status === 'success' ? '✅ ' : '❌ ') + data.message;
            res.style.display = 'block';
        })
        .catch(() => { res.className='result-box error'; res.innerHTML='❌ Gagal menghubungi server.'; res.style.display='block'; });
}

function gantiPassword() {
    const lama       = document.getElementById('inputPasswordLama').value;
    const baru       = document.getElementById('inputPasswordBaru').value;
    const konfirmasi = document.getElementById('inputKonfirmasi').value;
    const res        = document.getElementById('passwordResult');

    const fd = new FormData();
    fd.append('password_lama', lama);
    fd.append('password_baru', baru);
    fd.append('konfirmasi',    konfirmasi);

    fetch('/pengaturan/password', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            res.className    = 'result-box ' + (data.status === 'success' ? 'success' : 'error');
            res.innerHTML    = (data.status === 'success' ? '✅ ' : '❌ ') + data.message;
            res.style.display = 'block';
            if (data.status === 'success') setTimeout(() => window.location.href = '/login', 1500);
        })
        .catch(() => { res.className='result-box error'; res.innerHTML='❌ Gagal menghubungi server.'; res.style.display='block'; });
}
</script>
JS;

$activePage = 'pengaturan';
require BASE_PATH . '/app/Views/layouts/app.php';
