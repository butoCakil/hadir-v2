<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Terjadi Kesalahan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg:#0f1117; --surface:#161b27; --border:#242d40;
            --text:#e2e8f0; --muted:#64748b; --orange:#f97316;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            background:var(--bg); color:var(--text);
            font-family:'Segoe UI',sans-serif;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
        }
        .card {
            background:var(--surface); border:1px solid var(--border);
            border-radius:16px; padding:3rem 2.5rem; text-align:center;
            max-width:440px; width:90%;
        }
        .code {
            font-size:5rem; font-weight:800; color:var(--orange);
            line-height:1; margin-bottom:0.5rem; letter-spacing:-2px;
        }
        .icon { font-size:2rem; margin-bottom:1rem; opacity:0.4; }
        h1 { font-size:1.25rem; font-weight:600; margin-bottom:0.5rem; }
        p { color:var(--muted); font-size:0.875rem; line-height:1.6; margin-bottom:2rem; }
        .btn {
            display:inline-flex; align-items:center; gap:0.5rem;
            background:var(--orange); color:white; border-radius:8px;
            padding:0.6rem 1.25rem; font-size:0.875rem; font-weight:600;
            text-decoration:none; transition:opacity 0.15s;
        }
        .btn:hover { opacity:0.85; }
        .footer { margin-top:2rem; font-size:0.75rem; color:var(--muted); }
        <?php if (!empty($message)): ?>
        .detail {
            margin-top:1.5rem; background:rgba(249,115,22,0.08);
            border:1px solid rgba(249,115,22,0.2); border-radius:8px;
            padding:0.75rem 1rem; font-size:0.75rem; color:#fdba74;
            font-family:monospace; text-align:left; word-break:break-word;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h1>Terjadi Kesalahan Server</h1>
        <p>Maaf, ada sesuatu yang salah di sisi server.<br>Kesalahan telah dicatat. Silakan coba beberapa saat lagi.</p>
        <a href="/" class="btn">
            <i class="fa-solid fa-rotate-right"></i> Coba Lagi
        </a>
        <?php if (!empty($message)): ?>
        <div class="detail"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="footer">PKL SMKN Bansari &mdash; Sistem Presensi</div>
    </div>
</body>
</html>
