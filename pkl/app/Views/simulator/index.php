<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WA Bot Simulator — PKL SMKN Bansari</title>
    <link rel="shortcut icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg:#0f1117; --surface:#161b27; --surface2:#1c2333; --border:#242d40; --border2:#2d3a52;
            --text:#e2e8f0; --muted:#64748b; --faint:#334155; --blue:#4f8ef7;
            --green:#22c55e; --yellow:#f59e0b; --orange:#f97316; --slate:#94a3b8;
            --wa-bg:#0b141a; --wa-panel:#202c33;
            --wa-bubble-out:#005c4b; --wa-bubble-in:#202c33;
            --wa-green:#00a884; --wa-text:#e9edef; --wa-muted:#8696a0;
            --navbar-h: 46px;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--bg); color:var(--text); font-family:'Segoe UI',sans-serif; }

        /* Navbar — fixed height */
        .navbar {
            position:fixed; top:0; left:0; right:0;
            height:var(--navbar-h);
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:0 1.25rem;
            display:flex; justify-content:space-between; align-items:center;
            z-index:100;
        }
        .navbar-brand { font-weight:700; font-size:0.9rem; color:var(--text); display:flex; align-items:center; gap:0.5rem; }
        .nav-links { display:flex; gap:1.25rem; }
        .nav-links a { color:var(--muted); font-size:0.82rem; text-decoration:none; }
        .nav-links a:hover { color:var(--blue); }
        .nav-right { display:flex; align-items:center; gap:1rem; }
        .btn-logout { background:transparent; border:1px solid var(--blue); color:var(--blue); border-radius:6px; font-size:0.75rem; padding:0.25rem 0.65rem; text-decoration:none; }
        .btn-logout:hover { background:var(--blue); color:white; }

        /* Main wrapper — full viewport minus navbar */
        .main {
            position:fixed;
            top:var(--navbar-h);
            left:0; right:0; bottom:0;
            display:grid;
            grid-template-columns:1fr 420px;
        }

        /* ══ WA PANEL ══ */
        .wa-panel {
            background:var(--wa-bg);
            display:grid;
            grid-template-rows:auto 1fr auto;
            overflow:hidden;
        }

        .wa-header {
            background:var(--wa-panel);
            padding:0.75rem 1rem;
            display:flex; align-items:center; gap:0.75rem;
            border-bottom:1px solid #2a3942;
        }
        .wa-avatar { width:38px; height:38px; border-radius:50%; background:var(--wa-green); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; color:white; flex-shrink:0; }
        .wa-contact-name { font-size:0.9rem; font-weight:600; color:var(--wa-text); }
        .wa-contact-status { font-size:0.72rem; color:var(--wa-muted); }

        .wa-messages {
            overflow-y:auto;
            padding:1rem;
            display:flex; flex-direction:column; gap:0.4rem;
        }
        .wa-messages::-webkit-scrollbar { width:5px; }
        .wa-messages::-webkit-scrollbar-thumb { background:#2a3942; border-radius:3px; }

        .bubble-wrap { display:flex; margin-bottom:0.25rem; }
        .bubble-wrap.out { justify-content:flex-end; }
        .bubble-wrap.in  { justify-content:flex-start; }
        .bubble { max-width:72%; padding:0.5rem 0.75rem 0.3rem; border-radius:8px; font-size:0.83rem; line-height:1.45; white-space:pre-wrap; word-break:break-word; }
        .bubble.out { background:var(--wa-bubble-out); color:var(--wa-text); border-top-right-radius:2px; }
        .bubble.in  { background:var(--wa-bubble-in); color:var(--wa-text); border-top-left-radius:2px; }
        .bubble.error { background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; }
        .bubble-time { font-size:0.62rem; color:var(--wa-muted); text-align:right; margin-top:0.25rem; }
        .bubble-typing { display:flex; gap:4px; padding:0.5rem 0.75rem; }
        .typing-dot { width:7px; height:7px; border-radius:50%; background:var(--wa-muted); animation:typing 1.2s infinite; }
        .typing-dot:nth-child(2) { animation-delay:0.2s; }
        .typing-dot:nth-child(3) { animation-delay:0.4s; }
        @keyframes typing { 0%,60%,100%{opacity:0.3} 30%{opacity:1} }
        .bubble-system { text-align:center; margin:0.5rem 0; }
        .bubble-system span { background:rgba(11,20,26,0.7); color:var(--wa-muted); font-size:0.68rem; padding:0.2rem 0.75rem; border-radius:8px; }

        .wa-input-area {
            background:var(--wa-panel);
            padding:0.75rem 1rem;
            border-top:1px solid #2a3942;
        }
        .sender-config { display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:0.6rem; }
        .sender-config input { background:#2a3942; border:1px solid #3b4a54; color:var(--wa-text); border-radius:6px; padding:0.35rem 0.6rem; font-size:0.78rem; width:100%; }
        .sender-config input::placeholder { color:var(--wa-muted); }
        .sender-config input:focus { outline:none; border-color:var(--wa-green); }
        .msg-input-row { display:flex; gap:0.5rem; align-items:flex-end; }
        .msg-textarea { flex:1; background:#2a3942; border:1px solid #3b4a54; color:var(--wa-text); border-radius:8px; padding:0.5rem 0.75rem; font-size:0.83rem; resize:none; min-height:38px; max-height:100px; font-family:inherit; line-height:1.4; }
        .msg-textarea::placeholder { color:var(--wa-muted); }
        .msg-textarea:focus { outline:none; border-color:var(--wa-green); }
        .btn-send { background:var(--wa-green); border:none; color:white; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; font-size:0.95rem; }
        .btn-send:hover { background:#008f72; }
        .btn-send:disabled { background:#2a3942; cursor:not-allowed; }
        .media-row { display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem; }
        .media-toggle { display:flex; align-items:center; gap:0.4rem; font-size:0.75rem; color:var(--wa-muted); cursor:pointer; user-select:none; }
        .media-toggle input[type=checkbox] { accent-color:var(--wa-green); width:14px; height:14px; }
        .media-url-input { flex:1; background:#2a3942; border:1px solid #3b4a54; color:var(--wa-text); border-radius:6px; padding:0.3rem 0.6rem; font-size:0.75rem; display:none; }
        .media-url-input.show { display:block; }
        .media-url-input:focus { outline:none; border-color:var(--wa-green); }
        .quick-cmds { display:flex; flex-wrap:wrap; gap:0.35rem; margin-top:0.5rem; }
        .quick-btn { background:#2a3942; border:1px solid #3b4a54; color:var(--wa-muted); border-radius:14px; padding:0.2rem 0.6rem; font-size:0.7rem; cursor:pointer; }
        .quick-btn:hover { border-color:var(--wa-green); color:var(--wa-green); }

        /* ══ LOG PANEL ══ */
        .log-panel {
            background:var(--surface);
            border-left:1px solid var(--border);
            display:grid;
            grid-template-rows:auto 1fr;
            overflow:hidden;
        }
        .log-header {
            padding:0.75rem 1rem;
            border-bottom:1px solid var(--border);
            display:flex; justify-content:space-between; align-items:center;
        }
        .log-title { font-size:0.82rem; font-weight:600; color:var(--text); display:flex; align-items:center; gap:0.5rem; }
        .log-clear { background:transparent; border:1px solid var(--border2); color:var(--muted); border-radius:5px; font-size:0.7rem; padding:0.2rem 0.6rem; cursor:pointer; }
        .log-clear:hover { border-color:var(--orange); color:var(--orange); }

        /* ★ KEY: log-entries mengisi sisa tinggi dan scroll sendiri */
        .log-entries {
            overflow-y:auto;
            padding:0.75rem;
            display:flex;
            flex-direction:column;
            gap:0.75rem;
        }
        .log-entries::-webkit-scrollbar { width:4px; }
        .log-entries::-webkit-scrollbar-thumb { background:var(--border2); border-radius:2px; }

        .log-entry { background:var(--surface2); border:1px solid var(--border); border-radius:8px; overflow:hidden; font-size:0.75rem; flex-shrink:0; }
        .log-entry-header { padding:0.4rem 0.75rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); }
        .log-entry-dir { font-weight:700; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; }
        .log-entry-dir.req { color:var(--blue); }
        .log-entry-dir.res { color:var(--green); }
        .log-entry-dir.err { color:var(--orange); }
        .log-entry-time { color:var(--muted); font-size:0.65rem; }
        .log-entry-body { padding:0.5rem 0.75rem; color:var(--slate); font-family:monospace; white-space:pre-wrap; word-break:break-word; max-height:160px; overflow-y:auto; line-height:1.45; }
        .log-entry-body::-webkit-scrollbar { width:3px; }
        .log-entry-body::-webkit-scrollbar-thumb { background:var(--border2); }
        .log-empty { color:var(--muted); text-align:center; padding:2rem; font-size:0.8rem; }
        .status-badge { padding:0.1rem 0.4rem; border-radius:3px; font-size:0.65rem; font-weight:700; }
        .status-badge.ok  { background:rgba(34,197,94,0.15); color:var(--green); }
        .status-badge.err { background:rgba(239,68,68,0.15); color:#ef4444; }

        @media (max-width:768px) {
            .main { grid-template-columns:1fr; }
            .log-panel { display:none; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div style="display:flex;align-items:center;gap:2rem;">
        <span class="navbar-brand">
            <i class="fa-solid fa-graduation-cap" style="color:var(--blue)"></i>
            PKL SMKN Bansari
        </span>
        <div class="nav-links">
            <a href="/">Dashboard</a>
            <a href="/siswa">Siswa</a>
            <a href="/presensi">Presensi</a>
            <a href="/simulator" style="color:var(--wa-green);"><i class="fa-brands fa-whatsapp"></i> Simulator</a>
        </div>
    </div>
    <div class="nav-right">
        <span style="color:var(--muted);font-size:0.8rem;"><i class="fa-solid fa-user-circle me-1"></i><?= htmlspecialchars($user['username']??'Admin') ?></span>
        <a href="/logout" class="btn-logout"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
    </div>
</nav>

<div class="main">

    <!-- WA Chat Panel -->
    <div class="wa-panel">
        <div class="wa-header">
            <div class="wa-avatar">🤖</div>
            <div>
                <div class="wa-contact-name">WA Bot Simulator</div>
                <div class="wa-contact-status" id="botStatus">Siap menerima pesan</div>
            </div>
            <div style="margin-left:auto;">
                <button onclick="clearChat()" style="background:transparent;border:none;color:var(--wa-muted);cursor:pointer;font-size:0.85rem;" title="Clear chat">
                    <i class="fa-solid fa-broom"></i>
                </button>
            </div>
        </div>

        <div class="wa-messages" id="waMessages">
            <div class="bubble-system"><span>WA Bot Simulator — <?= date('d M Y') ?></span></div>
            <div class="bubble-system"><span>Pesan dikirim ke webhook, respons muncul sebagai balasan bot.</span></div>
        </div>

        <div class="wa-input-area">
            <div class="sender-config">
                <input type="text" id="senderNumber" placeholder="Nomor (08xxx)" value="082241863393">
                <input type="text" id="senderName" placeholder="Nama pengirim" value="Simulator User">
            </div>
            <div class="msg-input-row">
                <textarea id="msgInput" class="msg-textarea" placeholder="Ketik pesan..." rows="1" onkeydown="handleEnter(event)"></textarea>
                <button class="btn-send" id="btnSend" onclick="sendMessage()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <div class="media-row">
                <label class="media-toggle">
                    <input type="checkbox" id="hasMedia" onchange="toggleMedia()">
                    <i class="fa-solid fa-image" style="font-size:0.8rem;"></i> Sertakan Foto
                </label>
                <input type="text" id="mediaUrl" class="media-url-input" placeholder="URL foto (opsional)">
            </div>
            <div class="quick-cmds">
                <span style="font-size:0.65rem;color:var(--wa-muted);align-self:center;">Cepat:</span>
                <?php foreach (['info','1','2','3','4','cek','masuk Kegiatan hari ini','lupa Masuk 01-04-2026 Kegiatan','reg 9999','7','admin'] as $cmd): ?>
                <button class="quick-btn" onclick="setMsg('<?= addslashes($cmd) ?>')"><?= htmlspecialchars($cmd) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Log Panel -->
    <div class="log-panel">
        <div class="log-header">
            <div class="log-title">
                <i class="fa-solid fa-terminal" style="color:var(--blue)"></i>
                Request / Response Log
            </div>
            <button class="log-clear" onclick="clearLog()"><i class="fa-solid fa-trash"></i> Clear</button>
        </div>
        <div class="log-entries" id="logEntries">
            <div class="log-empty" id="logEmpty">
                <i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:0.5rem;opacity:0.3;"></i>
                Log akan muncul di sini.
            </div>
        </div>
    </div>

</div>

<script>
const WEBHOOK_URL = '/simulator/send';
let isSending = false;

const textarea = document.getElementById('msgInput');
textarea.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function setMsg(text) {
    textarea.value = text;
    textarea.focus();
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
}

function toggleMedia() {
    document.getElementById('mediaUrl').classList.toggle('show', document.getElementById('hasMedia').checked);
}

async function sendMessage() {
    if (isSending) return;
    const number   = document.getElementById('senderNumber').value.trim();
    const name     = document.getElementById('senderName').value.trim();
    const message  = textarea.value.trim();
    const hasMedia = document.getElementById('hasMedia').checked;
    const mediaUrl = document.getElementById('mediaUrl').value.trim()
        || (hasMedia ? 'https://api.whacenter.com/api/media?path=SIMULATOR_DUMMY.jpg' : '');

    if (!message && !hasMedia) { textarea.focus(); return; }

    const payload = {
        pushName: name || 'Simulator',
        from: number || '08000000000',
        to: '082241863393',
        message_type: hasMedia ? 'media' : 'text',
        message: message,
        media: hasMedia ? mediaUrl : '',
        is_group: false,
        timestamp: new Date().toISOString().replace('T',' ').substring(0,19),
        id_group: '',
        source: 'WHACENTER',
    };

    addBubble('out', message || '📷 [Foto]', new Date());
    textarea.value = '';
    textarea.style.height = 'auto';

    const typingId = addTyping();
    setStatus('Mengetik...');
    setBtnDisabled(true);
    isSending = true;
    addLog('req', 'POST → webhook', JSON.stringify(payload, null, 2));

    try {
        const res  = await fetch(WEBHOOK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        removeTyping(typingId);

        if (data.status === 200) {
            addBubble('in', data.reply || '(tidak ada balasan)', new Date());
            addLog('res', '200 OK', data.reply || '');
        } else {
            addBubble('in', '⚠️ Error: ' + (data.reply || data.status), new Date(), true);
            addLog('err', 'Error ' + data.status, data.reply || '');
        }
    } catch (err) {
        removeTyping(typingId);
        addBubble('in', '❌ Gagal terhubung ke webhook.', new Date(), true);
        addLog('err', 'Network Error', err.message);
    }

    setStatus('Siap menerima pesan');
    setBtnDisabled(false);
    isSending = false;
}

function addBubble(dir, text, time, isError = false) {
    const messages = document.getElementById('waMessages');
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap ' + dir;
    const timeStr = time.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
    wrap.innerHTML = `<div class="bubble ${dir}${isError?' error':''}">
        ${escapeHtml(text).replace(/\n/g,'<br>')}
        <div class="bubble-time">${timeStr}${dir==='out'?' <i class="fa-solid fa-check-double" style="font-size:0.6rem;color:#53bdeb;"></i>':''}</div>
    </div>`;
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
}

function addTyping() {
    const messages = document.getElementById('waMessages');
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap in';
    const id = 'typing-' + Date.now();
    wrap.id = id;
    wrap.innerHTML = `<div class="bubble in"><div class="bubble-typing">
        <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
    </div></div>`;
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
    return id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function clearChat() {
    document.getElementById('waMessages').innerHTML =
        `<div class="bubble-system"><span>WA Bot Simulator — <?= date('d M Y') ?></span></div>
         <div class="bubble-system"><span>Chat dikosongkan.</span></div>`;
}

function addLog(dir, label, body) {
    const entries = document.getElementById('logEntries');
    const empty   = document.getElementById('logEmpty');
    if (empty) empty.remove();

    const now = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    const dirClass = dir==='req'?'req':dir==='res'?'res':'err';
    const dirLabel = dir==='req'?'→ Request':dir==='res'?'← Response':'✕ Error';
    const statusClass = dir==='err'?'err':'ok';

    entry.innerHTML = `
        <div class="log-entry-header">
            <span class="log-entry-dir ${dirClass}">${dirLabel}</span>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <span class="log-entry-time">${now}</span>
                <span class="status-badge ${statusClass}">${label}</span>
            </div>
        </div>
        <div class="log-entry-body">${escapeHtml(body)}</div>`;

    entries.insertBefore(entry, entries.firstChild);

    // Hapus entry lama jika lebih dari 30
    const all = entries.querySelectorAll('.log-entry');
    if (all.length > 30) all[all.length - 1].remove();

    entries.scrollTop = 0;
}

function clearLog() {
    document.getElementById('logEntries').innerHTML =
        '<div class="log-empty" id="logEmpty"><i class="fa-solid fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:0.5rem;opacity:0.3;"></i>Log dikosongkan.</div>';
}

function setStatus(text) { document.getElementById('botStatus').textContent = text; }
function setBtnDisabled(v) { document.getElementById('btnSend').disabled = v; }
function escapeHtml(text) {
    return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>