<?php ob_start();

$extraCss = <<<CSS
<style>
/* Layout */
.logs-wrap { display:grid; grid-template-columns:260px 1fr; gap:1rem; min-height:600px; }
@media(max-width:768px) { .logs-wrap { grid-template-columns:1fr; } }

/* File list */
.file-list { background:var(--bg2); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.file-list-header { padding:0.75rem 1rem; border-bottom:1px solid var(--border); font-size:0.75rem; font-weight:700; color:var(--text3); text-transform:uppercase; letter-spacing:0.05em; display:flex; justify-content:space-between; align-items:center; }
.file-item { display:block; padding:0.65rem 1rem; border-bottom:1px solid var(--border); text-decoration:none; transition:background 0.1s; cursor:pointer; }
.file-item:last-child { border-bottom:none; }
.file-item:hover { background:var(--bg3); }
.file-item.active { background:var(--blue-bg); border-left:3px solid var(--blue); }
.file-item.active .file-name { color:var(--blue); }
.file-name { font-size:0.82rem; font-weight:600; color:var(--text); margin-bottom:0.2rem; word-break:break-all; }
.file-meta { font-size:0.68rem; color:var(--text3); display:flex; gap:0.5rem; }
.file-size { background:var(--bg3); border-radius:3px; padding:0.05rem 0.3rem; font-size:0.65rem; }

/* DB tab item */
.db-tab-item { padding:0.65rem 1rem; border-bottom:1px solid var(--border); text-decoration:none; display:block; transition:background 0.1s; }
.db-tab-item:hover { background:var(--bg3); }
.db-tab-item.active { background:var(--blue-bg); border-left:3px solid var(--blue); }

/* Content panel */
.log-panel { background:var(--bg2); border:1px solid var(--border); border-radius:10px; overflow:hidden; display:flex; flex-direction:column; }
.log-panel-header { padding:0.75rem 1rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; }
.log-panel-title { font-size:0.85rem; font-weight:700; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.limit-btn { padding:0.2rem 0.5rem; border-radius:4px; font-size:0.72rem; font-weight:600; text-decoration:none; border:1px solid var(--border2); color:var(--text2); background:var(--bg3); }
.limit-btn.active { background:var(--blue); color:#fff; border-color:var(--blue); }
.log-content { font-family:monospace; font-size:0.75rem; line-height:1.6; padding:1rem; overflow:auto; flex:1; white-space:pre-wrap; word-break:break-all; background:var(--bg2); color:var(--text2); max-height:70vh; }
.log-empty { padding:3rem; text-align:center; color:var(--text3); font-size:0.85rem; }

/* Tab nav */
.log-tab-nav { display:flex; gap:0.25rem; padding:0.5rem; border-bottom:1px solid var(--border); }
.log-tab-btn { padding:0.3rem 0.75rem; border-radius:6px; font-size:0.78rem; font-weight:600; text-decoration:none; color:var(--text2); border:1px solid transparent; transition:all 0.15s; }
.log-tab-btn:hover { background:var(--bg3); }
.log-tab-btn.active { background:var(--blue); color:#fff; }

/* Log line coloring */
.log-content .line-error  { color:var(--red); }
.log-content .line-warn   { color:var(--yellow); }
.log-content .line-ok     { color:var(--green); }
.log-content .line-info   { color:var(--blue); }

/* Ext badge */
.ext-badge { font-size:0.62rem; padding:0.1rem 0.35rem; border-radius:3px; font-weight:700; text-transform:uppercase; }
.ext-log  { background:var(--blue-bg);   color:var(--blue); }
.ext-json { background:var(--yellow-bg); color:var(--yellow); }
.ext-err  { background:var(--red-bg);    color:var(--red); }
</style>
CSS;

// Build URLs helper
function logUrl(array $params = []): string {
    $base = array_merge(['tab' => 'file', 'file' => '', 'limit' => 50], $params);
    return '/logs?' . http_build_query(array_filter($base, fn($v) => $v !== ''));
}
?>

<!-- Tab nav -->
<div style="display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center;">
    <a href="<?= logUrl(['tab'=>'file','file'=>$selectedFile??'','limit'=>$limit]) ?>"
       class="log-tab-btn <?= $tab==='file'?'active':'' ?>">
        <i class="fa-solid fa-file-lines me-1"></i> File Log
    </a>
    <a href="<?= logUrl(['tab'=>'db','limit'=>$limit]) ?>"
       class="log-tab-btn <?= $tab==='db'?'active':'' ?>">
        <i class="fa-solid fa-database me-1"></i> WA Bot Activity
    </a>
    <div style="margin-left:auto;display:flex;align-items:center;gap:0.35rem;">
        <span style="font-size:0.72rem;color:var(--text3);">Tampilkan:</span>
        <?php foreach ([15,30,50,100,500] as $l): ?>
        <a href="<?= logUrl(['tab'=>$tab,'file'=>$selectedFile??'','limit'=>$l]) ?>"
           class="limit-btn <?= $limit===$l?'active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="logs-wrap">

    <!-- Kiri: file list -->
    <div class="file-list">
        <?php if ($tab === 'file'): ?>
        <div class="file-list-header">
            <span>File Log</span>
            <span style="color:var(--text2);"><?= count($logFiles) ?> file</span>
        </div>
        <?php foreach ($logFiles as $key => $f):
            $extClass = match($f['ext']) {
                'log'   => 'ext-log',
                'json'  => 'ext-json',
                default => 'ext-err',
            };
            $isActive = $selectedFile === $key;
        ?>
        <a href="<?= logUrl(['tab'=>'file','file'=>$key,'limit'=>$limit]) ?>"
           class="file-item <?= $isActive ? 'active' : '' ?>">
            <div class="file-name">
                <span class="ext-badge <?= $extClass ?>"><?= $f['ext'] ?: 'log' ?></span>
                <?= htmlspecialchars($f['name']) ?>
            </div>
            <div class="file-meta">
                <span><?= htmlspecialchars(dirname($key)) ?></span>
                <span class="file-size"><?= $f['size'] ?> KB</span>
            </div>
            <div style="font-size:0.65rem;color:var(--text3);margin-top:0.15rem;"><?= $f['modified'] ?></div>
        </a>
        <?php endforeach; ?>
        <?php if (empty($logFiles)): ?>
        <div class="log-empty">Tidak ada file log ditemukan.</div>
        <?php endif; ?>

        <?php else: ?>
        <!-- DB tab — hanya 1 item -->
        <div class="file-list-header"><span>Sumber DB</span></div>
        <a href="<?= logUrl(['tab'=>'db','limit'=>$limit]) ?>" class="db-tab-item active">
            <div class="file-name"><i class="fa-brands fa-whatsapp me-1" style="color:var(--green);"></i> tmp (WA Bot)</div>
            <div class="file-meta">Semua pesan masuk ke bot</div>
        </a>
        <?php endif; ?>
    </div>

    <!-- Kanan: content panel -->
    <div class="log-panel">
        <?php if ($tab === 'file'): ?>

        <?php if ($selectedFile && $fileContent !== null): ?>
        <div class="log-panel-header">
            <div class="log-panel-title">
                <i class="fa-solid fa-file-lines me-1" style="color:var(--blue);"></i>
                <?= htmlspecialchars($selectedFile) ?>
                <span style="font-size:0.7rem;color:var(--text3);margin-left:0.5rem;" id="fileSize">
                    <?= $logFiles[$selectedFile]['size'] ?? 0 ?> KB
                </span>
            </div>
            <button onclick="refreshLog()" class="btn-app btn-ghost" style="font-size:0.72rem;padding:0.25rem 0.65rem;">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
            <button onclick="clearLog()" class="btn-app" style="font-size:0.72rem;padding:0.25rem 0.65rem;background:var(--red-bg);color:var(--red);border:1px solid rgba(239,68,68,0.2);">
                <i class="fa-solid fa-trash"></i> Clear
            </button>
        </div>
        <div class="log-content" id="logContent"><?= htmlspecialchars($fileContent) ?></div>

        <?php else: ?>
        <div class="log-empty">
            <i class="fa-solid fa-file-lines" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:0.75rem;"></i>
            Pilih file log di sebelah kiri untuk melihat isinya.
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- DB: WA Bot Activity -->
        <div class="log-panel-header">
            <div class="log-panel-title">
                <i class="fa-brands fa-whatsapp me-1" style="color:var(--green);"></i>
                WA Bot Activity — <?= count($dbLogs) ?> pesan terbaru
            </div>
        </div>
        <div style="overflow:auto;max-height:70vh;">
            <?php if (empty($dbLogs)): ?>
            <div class="log-empty">Tidak ada data di tabel tmp.</div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:0.78rem;">
                <thead>
                    <tr style="background:var(--bg3);">
                        <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;font-weight:700;white-space:nowrap;">Waktu</th>
                        <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;font-weight:700;">Nomor</th>
                        <th style="padding:0.5rem 0.75rem;text-align:left;color:var(--text3);font-size:0.65rem;text-transform:uppercase;font-weight:700;">Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dbLogs as $i => $row): ?>
                    <tr style="border-bottom:1px solid var(--border);<?= $i%2===1?'background:var(--bg3);':'' ?>">
                        <td style="padding:0.45rem 0.75rem;color:var(--text3);white-space:nowrap;font-family:monospace;font-size:0.72rem;">
                            <?= date('d/m H:i:s', strtotime($row['timestamp'])) ?>
                        </td>
                        <td style="padding:0.45rem 0.75rem;font-family:monospace;font-size:0.72rem;color:var(--text2);">
                            <?= htmlspecialchars($row['number'] ?? '-') ?>
                        </td>
                        <td style="padding:0.45rem 0.75rem;max-width:400px;word-break:break-word;">
                            <?= htmlspecialchars($row['msg'] ?? '-') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();

$selectedFileJs  = json_encode($selectedFile ?? '');
$limitJs         = (int)$limit;

$extraJs = <<<JS
<script>
const selectedFile = {$selectedFileJs};
const currentLimit = {$limitJs};

function refreshLog() {
    if (!selectedFile) return;
    fetch('/logs/raw?file=' + encodeURIComponent(selectedFile) + '&limit=' + currentLimit)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('logContent').textContent = data.content;
                const sz = document.getElementById('fileSize');
                if (sz) sz.textContent = data.size + ' KB';
            }
        })
        .catch(() => alert('Gagal refresh log.'));
}

function clearLog() {
    if (!selectedFile) return;
    if (!confirm('Kosongkan file log ini?\\n' + selectedFile)) return;

    const fd = new FormData();
    fd.append('file', selectedFile);

    fetch('/logs/clear', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('logContent').textContent = '';
                const sz = document.getElementById('fileSize');
                if (sz) sz.textContent = '0 KB';
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(() => alert('Gagal clear log.'));
}

// Auto refresh setiap 30 detik jika ada file yang dipilih
if (selectedFile) {
    setInterval(refreshLog, 30000);
}
</script>
JS;

$activePage = 'logs';
require BASE_PATH . '/app/Views/layouts/app.php';
