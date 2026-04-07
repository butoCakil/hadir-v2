<?php
ob_start();

$extraCss = <<<CSS
<style>
.pw-wrap {
    max-width: 480px;
    margin: 2rem auto;
}

/* Step cards */
.step-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 1rem;
}
.step-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.step-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--blue);
    color: white;
    font-size: 0.78rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.step-num.done  { background: var(--green); }
.step-num.warn  { background: var(--yellow); }
.step-title { font-weight: 700; font-size: 0.9rem; }
.step-body  { padding: 1.25rem; }

/* Mode toggle */
.mode-toggle {
    display: flex;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 3px;
    gap: 3px;
    margin-bottom: 1.25rem;
}
.mode-btn {
    flex: 1;
    padding: 0.45rem 0.5rem;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text2);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}
.mode-btn.active {
    background: var(--bg2);
    color: var(--blue);
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.mode-btn.lupa-active {
    background: var(--bg2);
    color: var(--yellow);
}

/* Keterangan buttons */
.ket-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.ket-btn {
    padding: 0.7rem 0.5rem;
    border-radius: 8px;
    border: 2px solid var(--border);
    background: var(--bg3);
    color: var(--text2);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}
.ket-btn:hover { border-color: var(--blue); color: var(--blue); }
.ket-btn.selected-masuk  { border-color: var(--green);  background: var(--green-bg);  color: var(--green); }
.ket-btn.selected-izin   { border-color: var(--blue);   background: var(--blue-bg);   color: var(--blue); }
.ket-btn.selected-sakit  { border-color: var(--yellow); background: var(--yellow-bg); color: var(--yellow); }
.ket-btn.selected-libur  { border-color: var(--gray);   background: var(--gray-bg);  color: var(--gray); }

/* Kamera */
.camera-wrap {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: #000;
    aspect-ratio: 4/3;
    margin-bottom: 0.75rem;
}
#pwVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1);
}
#pwPreview {
    width: 100%;
    border-radius: 8px;
    border: 2px solid var(--green);
    margin-bottom: 0.75rem;
    display: none;
}
.btn-capture {
    width: 100%;
    padding: 0.65rem;
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: opacity 0.15s;
}
.btn-capture:hover { opacity: 0.88; }
.btn-retake {
    width: 100%;
    padding: 0.5rem;
    background: var(--bg3);
    color: var(--text2);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.82rem;
    cursor: pointer;
    margin-top: 0.5rem;
}

/* Info siswa box */
.siswa-box {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
}
.siswa-box .sn { font-weight: 700; font-size: 1rem; margin-bottom: 0.2rem; }
.siswa-box .sm { color: var(--text2); font-size: 0.78rem; }

/* Alert result */
.pw-alert {
    border-radius: 8px;
    padding: 0.875rem 1rem;
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: none;
}
.pw-alert.success { background: var(--green-bg); border: 1px solid rgba(34,197,94,0.25); color: var(--green); }
.pw-alert.error   { background: var(--red-bg);   border: 1px solid rgba(239,68,68,0.25);   color: var(--red); }
.pw-alert.warning { background: var(--yellow-bg); border: 1px solid rgba(245,158,11,0.25); color: var(--yellow); }
</style>
CSS;
?>

<div class="pw-wrap">

    <!-- Step 1: Input NIS -->
    <div class="step-card" id="step1">
        <div class="step-header">
            <div class="step-num" id="num1">1</div>
            <div class="step-title">Masukkan NIS</div>
        </div>
        <div class="step-body">
            <div class="pw-alert" id="alertNis"></div>
            <div style="display:flex;gap:0.5rem;">
                <input type="number" id="nisInput" class="form-control"
                       placeholder="Contoh: 2890" style="font-size:1.1rem;font-weight:600;">
                <button class="btn-app btn-primary-app" onclick="cekNis()" id="btnCekNis"
                        style="white-space:nowrap;">
                    <i class="fa-solid fa-magnifying-glass"></i> Cek
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Info + Form Presensi (hidden awalnya) -->
    <div class="step-card" id="step2" style="display:none;">
        <div class="step-header">
            <div class="step-num" id="num2">2</div>
            <div class="step-title" id="step2Title">Form Presensi</div>
        </div>
        <div class="step-body">

            <!-- Info siswa -->
            <div class="siswa-box" id="siswaBox"></div>

            <!-- Alert sudah presensi -->
            <div class="pw-alert" id="alertSudah"></div>

            <!-- Alert batal -->
            <div class="pw-alert" id="alertBatal"></div>

            <!-- Form presensi -->
            <div id="formPresensi">
                <!-- Mode toggle -->
                <div class="mode-toggle">
                    <button class="mode-btn active" id="btnHariIni" onclick="setMode('hariini')">
                        <i class="fa-solid fa-calendar-day me-1"></i> Hari Ini
                    </button>
                    <button class="mode-btn" id="btnLupa" onclick="setMode('lupa')">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Lupa Absen
                    </button>
                </div>

                <!-- Tanggal (mode lupa) -->
                <div id="tanggalGroup" style="display:none;margin-bottom:1rem;">
                    <label class="form-label">Tanggal Presensi</label>
                    <input type="date" id="tanggalInput" class="form-control"
                           max="<?= date('Y-m-d', strtotime('-1 day')) ?>"
                           min="2025-07-17">
                </div>

                <!-- Keterangan -->
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <div class="ket-grid">
                        <button type="button" class="ket-btn" onclick="pilihKet('Masuk')" id="ketMasuk">
                            <i class="fa-solid fa-door-open me-1"></i> Masuk
                        </button>
                        <button type="button" class="ket-btn" onclick="pilihKet('Izin')" id="ketIzin">
                            <i class="fa-solid fa-clock me-1"></i> Izin
                        </button>
                        <button type="button" class="ket-btn" onclick="pilihKet('Sakit')" id="ketSakit">
                            <i class="fa-solid fa-heart-pulse me-1"></i> Sakit
                        </button>
                        <button type="button" class="ket-btn" onclick="pilihKet('Libur')" id="ketLibur">
                            <i class="fa-solid fa-umbrella-beach me-1"></i> Libur
                        </button>
                    </div>
                </div>

                <!-- Kamera (hanya Masuk) -->
                <div id="kameraSection" style="display:none;margin-bottom:1rem;">
                    <label class="form-label">Foto Selfie <span style="color:var(--red);font-size:0.75rem;">* Wajib untuk Masuk</span></label>
                    <div class="camera-wrap" id="cameraWrap">
                        <video id="pwVideo" autoplay playsinline muted></video>
                    </div>
                    <img id="pwPreview" src="" alt="Preview">
                    <button type="button" class="btn-capture" id="btnCapture" onclick="ambilFoto()">
                        <i class="fa-solid fa-camera"></i> Ambil Foto
                    </button>
                    <button type="button" class="btn-retake" id="btnRetake" style="display:none;" onclick="ulangFoto()">
                        <i class="fa-solid fa-rotate-left me-1"></i> Ulangi Foto
                    </button>
                </div>

                <!-- Catatan -->
                <div class="mb-3">
                    <label class="form-label">Catatan Kegiatan <span style="color:var(--text3);font-size:0.75rem;">(opsional)</span></label>
                    <textarea id="catatanInput" class="form-control" rows="2"
                              placeholder="Kegiatan hari ini..."></textarea>
                </div>

                <!-- Tombol simpan -->
                <button class="btn-app btn-primary-app" style="width:100%;padding:0.75rem;font-size:0.95rem;"
                        id="btnSimpan" onclick="simpanPresensi()">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Presensi
                </button>
            </div>

        </div>
    </div>

    <!-- Step 3: Hasil -->
    <div class="step-card" id="step3" style="display:none;">
        <div class="step-header">
            <div class="step-num done">✓</div>
            <div class="step-title">Presensi Berhasil</div>
        </div>
        <div class="step-body" id="step3Body"></div>
    </div>

</div>

<?php
$content = ob_get_clean();

$extraJs = <<<'JS'
<script>
// ── State ──────────────────────────────────────────────────
let state = {
    nis: '',
    siswa: null,
    mode: 'hariini',
    ket: '',
    fotoBlob: null,
    stream: null,
};

// ── Step 1: Cek NIS ───────────────────────────────────────
async function cekNis() {
    const nis = document.getElementById('nisInput').value.trim();
    if (!nis) { showAlert('alertNis','error','Masukkan NIS terlebih dahulu.'); return; }

    const btn = document.getElementById('btnCekNis');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const fd = new FormData();
        fd.append('nis', nis);
        const res = await fetch('/presensi-web/cek-nis', { method:'POST', body:fd });
        const data = await res.json();

        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Cek';

        if (data.status !== 'success') {
            showAlert('alertNis', 'error', data.message);
            return;
        }

        hideAlert('alertNis');
        state.nis   = nis;
        state.siswa = data.data.siswa;
        tampilkanStep2(data.data.siswa, data.data.sudah_presensi);

    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Cek';
        showAlert('alertNis', 'error', 'Gagal menghubungi server.');
    }
}

document.getElementById('nisInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') cekNis();
});

// ── Step 2: Tampilkan form ────────────────────────────────
function tampilkanStep2(siswa, sudah) {
    document.getElementById('step2').style.display = 'block';
    document.getElementById('num1').classList.add('done');
    document.getElementById('num1').textContent = '✓';

    // Info siswa
    const mapJur = {'TE':'Teknik Elektronika','AT':'Agribisnis Tanaman','DKV':'Desain Komunikasi Visual'};
    const jur = mapJur[siswa.jur] || siswa.jur || '-';
    document.getElementById('siswaBox').innerHTML = `
        <div class="sn">${siswa.nama}</div>
        <div class="sm">NIS: ${siswa.nis} &nbsp;·&nbsp; ${siswa.kelas} &nbsp;·&nbsp; ${jur}</div>
        ${siswa.nama_dudika ? `<div class="sm" style="margin-top:3px;"><i class="fa-solid fa-building" style="font-size:0.65rem;margin-right:4px;"></i>${siswa.nama_dudika}</div>` : ''}
        ${siswa.nama_pembimbing ? `<div class="sm"><i class="fa-solid fa-person-chalkboard" style="font-size:0.65rem;margin-right:4px;"></i>${siswa.nama_pembimbing}</div>` : ''}
        <a href="/cek/${siswa.nis}" target="_blank"
           style="display:inline-block;margin-top:0.5rem;color:var(--blue);text-decoration:none;opacity:0.8;">
            <i class="fa-solid fa-chart-bar" style="margin-right:3px;"></i>Lihat Rekap Presensi
        </a>
    `;

    if (sudah) {
        const ketColor = {Masuk:'var(--green)',Izin:'var(--blue)',Sakit:'var(--yellow)',Libur:'var(--text2)'};
        showAlert('alertSudah', 'warning',
            `<strong>Sudah presensi hari ini</strong><br>
            Keterangan: <span style="color:${ketColor[sudah.ket]||'inherit'};font-weight:700;">${sudah.ket}</span> &nbsp;·&nbsp; Pukul ${sudah.waktu}<br>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">
                <button onclick="batalPresensi('${state.nis}', '${new Date().toISOString().slice(0,10)}')"
                    style="padding:0.4rem 1rem;background:var(--red);color:white;border:none;border-radius:7px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-trash me-1"></i> Batal Presensi
                </button>
                <button onclick="aktifkanLupaMode()"
                    style="padding:0.4rem 1rem;background:var(--yellow-bg);color:var(--yellow);border:1px solid rgba(245,158,11,0.25);border-radius:7px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Lupa Absen
                </button>
                <a href="/cek/${state.nis}" target="_blank"
                    style="padding:0.4rem 1rem;background:var(--bg3);color:var(--text2);border:1px solid var(--border);border-radius:7px;font-size:0.8rem;font-weight:600;text-decoration:none;">
                    <i class="fa-solid fa-eye me-1"></i> Rekap
                </a>
            </div>`
        );
        document.getElementById('formPresensi').style.display = 'none';
    } else {
        hideAlert('alertSudah');
        document.getElementById('formPresensi').style.display = 'block';
    }

    // Scroll ke step 2
    setTimeout(() => document.getElementById('step2').scrollIntoView({behavior:'smooth',block:'start'}), 100);
}

// ── Aktifkan lupa mode dari kondisi sudah presensi ────────
function aktifkanLupaMode() {
    hideAlert('alertSudah');
    document.getElementById('formPresensi').style.display = 'block';
    setMode('lupa');
    document.getElementById('btnLupa').click();
    // Scroll ke form
    setTimeout(() => document.getElementById('formPresensi').scrollIntoView({behavior:'smooth',block:'start'}), 100);
}

// ── Mode toggle ────────────────────────────────────────────
function setMode(mode) {
    state.mode = mode;
    const tg = document.getElementById('tanggalGroup');
    const bi = document.getElementById('btnHariIni');
    const bl = document.getElementById('btnLupa');
    if (mode === 'lupa') {
        tg.style.display = 'block';
        bi.classList.remove('active');
        bl.classList.add('active','lupa-active');
        bl.classList.remove('active');
        bl.classList.add('lupa-active');
    } else {
        tg.style.display = 'none';
        bl.classList.remove('lupa-active');
        bi.classList.add('active');
    }
}

// ── Pilih keterangan ──────────────────────────────────────
function pilihKet(ket) {
    state.ket = ket;
    ['Masuk','Izin','Sakit','Libur'].forEach(k => {
        const btn = document.getElementById('ket'+k);
        btn.className = 'ket-btn';
        if (k === ket) btn.classList.add('selected-'+k.toLowerCase());
    });

    const kameraSection = document.getElementById('kameraSection');
    if (ket === 'Masuk') {
        kameraSection.style.display = 'block';
        startCamera();
    } else {
        kameraSection.style.display = 'none';
        stopCamera();
        state.fotoBlob = null;
    }
}

// ── Kamera ────────────────────────────────────────────────
async function startCamera() {
    if (state.stream) return;
    try {
        state.stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}});
        const video = document.getElementById('pwVideo');
        video.srcObject = state.stream;
        document.getElementById('cameraWrap').style.display = 'block';
        document.getElementById('pwPreview').style.display = 'none';
        document.getElementById('btnCapture').style.display = 'flex';
        document.getElementById('btnRetake').style.display = 'none';
    } catch(e) {
        showAlert('alertBatal','error','Tidak bisa mengakses kamera: ' + e.message);
    }
}

function stopCamera() {
    if (state.stream) {
        state.stream.getTracks().forEach(t => t.stop());
        state.stream = null;
    }
}

function ambilFoto() {
    const video  = document.getElementById('pwVideo');
    const canvas = document.createElement('canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.filter = 'brightness(1.05) contrast(1.05)';
    ctx.drawImage(video, 0, 0);

    canvas.toBlob(blob => {
        if (!blob) return;
        // Kompres jika > 200KB
        if (blob.size > 200 * 1024) {
            const img = new Image();
            img.src = URL.createObjectURL(blob);
            img.onload = () => {
                const c2 = document.createElement('canvas');
                const ratio = Math.min(800/img.width, 800/img.height, 1);
                c2.width  = img.width  * ratio;
                c2.height = img.height * ratio;
                c2.getContext('2d').drawImage(img, 0, 0, c2.width, c2.height);
                c2.toBlob(b2 => setFotoResult(b2), 'image/jpeg', 0.75);
            };
        } else {
            setFotoResult(blob);
        }
    }, 'image/jpeg', 0.85);
}

function setFotoResult(blob) {
    state.fotoBlob = blob;
    const url = URL.createObjectURL(blob);
    const preview = document.getElementById('pwPreview');
    preview.src = url;
    preview.style.display = 'block';
    document.getElementById('cameraWrap').style.display = 'none';
    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnRetake').style.display = 'block';
    stopCamera();
}

function ulangFoto() {
    state.fotoBlob = null;
    document.getElementById('pwPreview').style.display = 'none';
    document.getElementById('cameraWrap').style.display = 'block';
    document.getElementById('btnCapture').style.display = 'flex';
    document.getElementById('btnRetake').style.display = 'none';
    startCamera();
}

// ── Simpan presensi ───────────────────────────────────────
async function simpanPresensi() {
    if (!state.ket) { showAlert('alertBatal','error','Pilih keterangan presensi.'); return; }

    const tanggal = state.mode === 'lupa'
        ? document.getElementById('tanggalInput').value
        : new Date().toISOString().slice(0,10);

    if (state.mode === 'lupa' && !tanggal) {
        showAlert('alertBatal','error','Pilih tanggal untuk lupa presensi.'); return;
    }

    if (state.ket === 'Masuk' && !state.fotoBlob) {
        showAlert('alertBatal','error','Ambil foto selfie terlebih dahulu.'); return;
    }

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

    const fd = new FormData();
    fd.append('nis',     state.nis);
    fd.append('tanggal', tanggal);
    fd.append('ket',     state.ket);
    fd.append('catatan', document.getElementById('catatanInput').value);
    fd.append('mode',    state.mode);
    if (state.fotoBlob) fd.append('foto', state.fotoBlob, state.nis + '_' + tanggal + '.jpg');

    try {
        const res  = await fetch('/presensi-web/simpan', { method:'POST', body:fd });
        const data = await res.json();

        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Presensi';

        if (data.status !== 'success') {
            showAlert('alertBatal','error', data.message);
            return;
        }

        // Tampilkan step 3
        stopCamera();
        document.getElementById('formPresensi').style.display = 'none';
        tampilkanHasil(data.data);

    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Presensi';
        showAlert('alertBatal','error','Gagal menghubungi server.');
    }
}

function tampilkanHasil(d) {
    const ketColor = {Masuk:'var(--green)',Izin:'var(--blue)',Sakit:'var(--yellow)',Libur:'var(--text2)'};
    const icon     = {Masuk:'circle-check',Izin:'clock',Sakit:'heart-pulse',Libur:'umbrella-beach'};
    document.getElementById('step3').style.display = 'block';
    document.getElementById('step3Body').innerHTML = `
        <div style="text-align:center;padding:1rem 0;">
            <i class="fa-solid fa-${icon[d.ket]||'circle-check'}" style="font-size:3rem;color:${ketColor[d.ket]};margin-bottom:1rem;display:block;"></i>
            <div style="font-size:1.1rem;font-weight:700;margin-bottom:0.25rem;">${d.ket}</div>
            <div style="font-size:0.85rem;color:var(--text2);">${d.tanggal} &nbsp;·&nbsp; ${d.waktu} WIB</div>
            ${d.foto_url ? `<img src="${d.foto_url}" style="margin-top:1rem;max-width:200px;border-radius:8px;border:2px solid var(--green);">` : ''}
        </div>
        <div style="display:flex;gap:0.75rem;margin-top:1rem;">
            <button onclick="batalPresensi('${d.nis}','${new Date().toISOString().slice(0,10)}')"
                style="flex:1;padding:0.5rem;background:var(--red-bg);color:var(--red);border:1px solid rgba(239,68,68,0.25);border-radius:7px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                <i class="fa-solid fa-trash me-1"></i> Batal
            </button>
            <a href="/cek/${d.nis}" target="_blank"
                style="flex:1;padding:0.5rem;background:var(--bg3);color:var(--text2);border:1px solid var(--border);border-radius:7px;font-size:0.8rem;font-weight:600;text-decoration:none;text-align:center;">
                <i class="fa-solid fa-eye me-1"></i> Rekap
            </a>
            <button onclick="location.reload()"
                style="flex:1;padding:0.5rem;background:var(--blue-bg);color:var(--blue);border:1px solid rgba(79,142,247,0.25);border-radius:7px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                <i class="fa-solid fa-rotate-left me-1"></i> Baru
            </button>
        </div>
    `;
    document.getElementById('step3').scrollIntoView({behavior:'smooth',block:'start'});
}

// ── Batal presensi ────────────────────────────────────────
async function batalPresensi(nis, tanggal) {
    if (!confirm('Yakin ingin membatalkan presensi ini?')) return;

    const fd = new FormData();
    fd.append('nis', nis);
    fd.append('tanggal', tanggal);

    try {
        const res  = await fetch('/presensi-web/batal', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status === 'success') {
            showAlert('alertBatal','success','Presensi berhasil dibatalkan.');
            document.getElementById('step3').style.display = 'none';
            document.getElementById('formPresensi').style.display = 'block';
            hideAlert('alertSudah');
            // Reset form
            state.ket = '';
            state.fotoBlob = null;
            ['Masuk','Izin','Sakit','Libur'].forEach(k => {
                document.getElementById('ket'+k).className = 'ket-btn';
            });
            document.getElementById('kameraSection').style.display = 'none';
        } else {
            showAlert('alertBatal','error', data.message);
        }
    } catch(e) {
        showAlert('alertBatal','error','Gagal menghubungi server.');
    }
}

// ── Helper ─────────────────────────────────────────────────
function showAlert(id, type, msg) {
    const el = document.getElementById(id);
    el.className = 'pw-alert ' + type;
    el.innerHTML = msg;
    el.style.display = 'block';
}
function hideAlert(id) {
    const el = document.getElementById(id);
    el.style.display = 'none';
}
</script>
JS;

$activePage = '';
require BASE_PATH . '/app/Views/layouts/public.php';