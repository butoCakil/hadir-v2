<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panduan Presensi PKL — SMK Negeri Bansari</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root {
  --green: #00b37e;
  --green-dark: #007a54;
  --green-light: #e6f9f2;
  --green-glow: rgba(0,179,126,0.18);
  --wa: #25d366;
  --wa-dark: #128c7e;
  --bg: #f0f4f8;
  --surface: #ffffff;
  --surface2: #f7fafc;
  --text: #1a2332;
  --text2: #4a5568;
  --text3: #718096;
  --border: #e2e8f0;
  --radius: 16px;
  --shadow: 0 4px 24px rgba(0,0,0,0.07);
  --shadow-lg: 0 12px 48px rgba(0,0,0,0.12);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  line-height: 1.65;
  overflow-x: hidden;
}

/* HERO */
.hero {
  background: linear-gradient(135deg, #0a1628 0%, #0d2137 50%, #0a2518 100%);
  color: white;
  padding: 4rem 1.5rem 5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(0,179,126,0.2) 0%, transparent 70%);
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(0,179,126,0.15);
  border: 1px solid rgba(0,179,126,0.3);
  color: #4ade98;
  border-radius: 100px;
  padding: 0.35rem 1rem;
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
  position: relative;
  animation: fadeDown 0.6s ease both;
}
.hero h1 {
  font-size: clamp(1.8rem, 5vw, 3rem);
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 1rem;
  position: relative;
  animation: fadeDown 0.6s ease 0.1s both;
}
.hero h1 span { color: #4ade98; }
.hero p {
  font-size: 1rem;
  color: rgba(255,255,255,0.7);
  max-width: 500px;
  margin: 0 auto 2rem;
  position: relative;
  animation: fadeDown 0.6s ease 0.2s both;
}
.hero-cta {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  flex-wrap: wrap;
  position: relative;
  animation: fadeDown 0.6s ease 0.3s both;
}
.btn-hero {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 100px;
  font-weight: 700;
  font-size: 0.88rem;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-hero.primary { background: var(--green); color: white; box-shadow: 0 4px 20px rgba(0,179,126,0.4); }
.btn-hero.primary:hover { background: var(--green-dark); transform: translateY(-2px); }
.btn-hero.secondary { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px); }
.btn-hero.secondary:hover { background: rgba(255,255,255,0.18); transform: translateY(-2px); }

/* Floating dots decoration */
.hero-dots {
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  pointer-events: none;
  overflow: hidden;
}
.dot {
  position: absolute;
  width: 6px; height: 6px;
  border-radius: 50%;
  background: rgba(0,179,126,0.3);
  animation: float linear infinite;
}

/* WAVE */
.wave {
  display: block;
  width: 100%;
  height: 60px;
  margin-top: -1px;
}

/* CONTAINER */
.container { max-width: 860px; margin: 0 auto; padding: 0 1.25rem; }

/* SECTION */
.section { padding: 3rem 0; }
.section-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--green-light);
  color: var(--green-dark);
  border-radius: 100px;
  padding: 0.3rem 0.9rem;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.75rem;
}
.section-title {
  font-size: 1.6rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  color: var(--text);
}
.section-sub {
  color: var(--text3);
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

/* STEP CARDS */
.steps { display: flex; flex-direction: column; gap: 1rem; }
.step-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem 1.5rem;
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
  box-shadow: var(--shadow);
  transition: transform 0.2s, box-shadow 0.2s;
  animation: fadeUp 0.5s ease both;
}
.step-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.step-num {
  width: 40px; height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--green), var(--green-dark));
  color: white;
  font-weight: 800;
  font-size: 1rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px var(--green-glow);
}
.step-body h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.35rem; }
.step-body p { font-size: 0.85rem; color: var(--text2); }

/* CMD */
.cmd {
  display: inline-block;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.82rem;
  font-weight: 600;
  background: #1a2332;
  color: #4ade98;
  padding: 0.15rem 0.55rem;
  border-radius: 6px;
  white-space: nowrap;
}

/* CMD BLOCK — improved for mobile */
.cmd-block {
  background: #1a2332;
  border-radius: 12px;
  padding: 0;
  margin: 0.75rem 0;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.06);
}
.cmd-block-header {
  background: rgba(255,255,255,0.05);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 0.5rem 1rem;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem;
  color: rgba(255,255,255,0.35);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.cmd-block-header .dot-red   { width:10px;height:10px;border-radius:50%;background:#ff5f57; }
.cmd-block-header .dot-yellow{ width:10px;height:10px;border-radius:50%;background:#febc2e; }
.cmd-block-header .dot-green { width:10px;height:10px;border-radius:50%;background:#28c840; }
.cmd-block-body {
  padding: 1.1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.cmd-example {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.cmd-example-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem;
  color: #4a6380;
  font-style: italic;
}
.cmd-example-line {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.82rem;
  color: #a3e4c8;
  line-height: 1.5;
  word-break: break-word;
}
.cmd-example-line .kw { color: #4ade98; font-weight: 600; }

/* CMD TABLE */
.cmd-table {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
  margin-bottom: 1.5rem;
}
.cmd-table-header {
  background: linear-gradient(135deg, #1a2332, #0d2137);
  color: white;
  padding: 0.875rem 1.25rem;
  font-weight: 700;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.cmd-table-header i { color: #4ade98; }
.cmd-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid var(--border);
  transition: background 0.15s;
}
.cmd-row:last-child { border-bottom: none; }
.cmd-row:hover { background: var(--surface2); }
.cmd-name { min-width: 180px; flex-shrink: 0; }
.cmd-desc { font-size: 0.83rem; color: var(--text2); }

/* CARDS GRID */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.info-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  box-shadow: var(--shadow);
  transition: transform 0.2s, box-shadow 0.2s;
  animation: fadeUp 0.5s ease both;
}
.info-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.info-card-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem;
  margin-bottom: 0.875rem;
}
.info-card h3 { font-size: 0.9rem; font-weight: 700; margin-bottom: 0.4rem; }
.info-card p { font-size: 0.8rem; color: var(--text2); line-height: 1.6; }

/* ALERT */
.alert {
  border-radius: 12px;
  padding: 1rem 1.25rem;
  font-size: 0.85rem;
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  margin: 1rem 0;
}
.alert-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
.alert.warning { background: #fff8e6; border: 1px solid #fcd34d; color: #78350f; }
.alert.info { background: #eff6ff; border: 1px solid #93c5fd; color: #1e3a5f; }
.alert.success { background: #f0fdf4; border: 1px solid #86efac; color: #14532d; }

/* REKAP BADGE */
.rekap-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 0.75rem;
  margin: 1rem 0;
}
.rekap-badge {
  border-radius: 12px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.82rem;
  font-weight: 600;
  border: 1px solid transparent;
}
.rekap-badge.masuk  { background: #f0fdf4; border-color: #86efac; color: #14532d; }
.rekap-badge.izin   { background: #eff6ff; border-color: #93c5fd; color: #1e3a8a; }
.rekap-badge.sakit  { background: #fff7ed; border-color: #fdba74; color: #7c2d12; }
.rekap-badge.libur  { background: #f9fafb; border-color: #d1d5db; color: #374151; }
.rekap-badge.kosong { background: #fef2f2; border-color: #fca5a5; color: #7f1d1d; }

/* PENGINGAT */
.reminder-box {
  background: linear-gradient(135deg, #0a1628, #0d2137);
  border-radius: var(--radius);
  padding: 1.75rem;
  color: white;
  position: relative;
  overflow: hidden;
  margin: 1.5rem 0;
}
.reminder-box::before {
  content: '';
  position: absolute;
  top: -40px; right: -40px;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0,179,126,0.15), transparent 70%);
}
.reminder-box h3 { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: #4ade98; }
.reminder-options { display: flex; flex-direction: column; gap: 0.6rem; }
.reminder-opt {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255,255,255,0.06);
  border-radius: 10px;
  padding: 0.6rem 0.875rem;
  font-size: 0.83rem;
}
.reminder-opt .cmd { background: rgba(0,179,126,0.2); color: #4ade98; }
.reminder-opt-desc { color: rgba(255,255,255,0.75); }

/* FAQ */
.faq { display: flex; flex-direction: column; gap: 0.75rem; }
.faq-item {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow);
}
.faq-q {
  padding: 1rem 1.25rem;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background 0.15s;
  user-select: none;
}
.faq-q:hover { background: var(--surface2); }
.faq-q i { transition: transform 0.2s; color: var(--text3); font-size: 0.8rem; }
.faq-q.open i { transform: rotate(180deg); color: var(--green); }
.faq-a {
  padding: 0 1.25rem;
  font-size: 0.85rem;
  color: var(--text2);
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease, padding 0.3s;
}
.faq-a.open { max-height: 300px; padding: 0 1.25rem 1rem; }

/* WA BUTTON */
.wa-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  background: var(--wa);
  color: white;
  border-radius: 100px;
  padding: 0.75rem 1.5rem;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  box-shadow: 0 4px 20px rgba(37,211,102,0.35);
  transition: all 0.2s;
}
.wa-btn:hover { background: var(--wa-dark); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(37,211,102,0.4); }

/* FOOTER */
.footer {
  background: #1a2332;
  color: rgba(255,255,255,0.6);
  text-align: center;
  padding: 2rem 1.5rem;
  font-size: 0.82rem;
}
.footer a { color: #4ade98; text-decoration: none; }
.footer strong { color: white; }

/* NAVBAR */
.navbar {
  position: sticky;
  top: 0;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  z-index: 100;
  padding: 0.75rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.navbar-brand {
  font-weight: 800;
  font-size: 0.9rem;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}
.navbar-brand .dot-green { color: var(--green); }
.navbar-links { display: flex; gap: 1.25rem; }
.navbar-links a {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text3);
  text-decoration: none;
  transition: color 0.15s;
}
.navbar-links a:hover { color: var(--green); }
.navbar-back {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--green);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

/* DIVIDER */
.divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
  margin: 2rem 0;
}

/* ===========================
   WA CHAT SIMULATOR
   =========================== */
.wa-demo {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(0,0,0,0.15);
  max-width: 420px;
  margin: 1.5rem auto;
  font-family: -apple-system, 'Segoe UI', sans-serif;
}
.wa-demo-bar {
  background: #075e54;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: white;
}
.wa-demo-avatar {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: #25d366;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.wa-demo-info { flex: 1; }
.wa-demo-info strong { display: block; font-size: 0.88rem; }
.wa-demo-info span { font-size: 0.72rem; opacity: 0.75; }
.wa-demo-body {
  background: #e5ddd5;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23e5ddd5'/%3E%3Cpath d='M10 10h3v3h-3zM30 10h3v3h-3zM50 10h3v3h-3zM70 10h3v3h-3zM90 10h3v3h-3zM20 20h3v3h-3zM40 20h3v3h-3zM60 20h3v3h-3zM80 20h3v3h-3z' fill='%23d5ccc4' opacity='0.4'/%3E%3C/svg%3E");
  padding: 1rem 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  min-height: 220px;
}
/* Bubble from user (right) */
.wa-bubble-out {
  align-self: flex-end;
  background: #dcf8c6;
  border-radius: 12px 2px 12px 12px;
  padding: 0.55rem 0.85rem 0.35rem;
  max-width: 80%;
  position: relative;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.wa-bubble-out::after {
  content: '';
  position: absolute;
  top: 0; right: -7px;
  width: 0; height: 0;
  border-left: 7px solid #dcf8c6;
  border-bottom: 7px solid transparent;
}
/* Bubble from bot (left) */
.wa-bubble-in {
  align-self: flex-start;
  background: white;
  border-radius: 2px 12px 12px 12px;
  padding: 0.55rem 0.85rem 0.35rem;
  max-width: 82%;
  position: relative;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.wa-bubble-in::before {
  content: '';
  position: absolute;
  top: 0; left: -7px;
  width: 0; height: 0;
  border-right: 7px solid white;
  border-bottom: 7px solid transparent;
}
.wa-bubble-text {
  font-size: 0.83rem;
  color: #303030;
  line-height: 1.45;
  white-space: pre-line;
  word-break: break-word;
}
.wa-bubble-text .wa-cmd {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.8rem;
  font-weight: 700;
  color: #075e54;
}
.wa-bubble-text .wa-bold { font-weight: 700; }
.wa-bubble-text .wa-emoji { font-style: normal; }
.wa-bubble-meta {
  font-size: 0.65rem;
  color: #8e8e8e;
  text-align: right;
  margin-top: 0.2rem;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.25rem;
}
.wa-bubble-out .wa-bubble-meta { color: #6aad5e; }
.wa-check { color: #53bdeb; font-size: 0.7rem; }
.wa-day-divider {
  text-align: center;
  font-size: 0.7rem;
  color: #6d6d6d;
  background: rgba(255,255,255,0.75);
  border-radius: 100px;
  padding: 0.2rem 0.75rem;
  align-self: center;
}
.wa-bubble-photo {
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 0.25rem;
}
.wa-photo-placeholder {
  width: 100%;
  height: 110px;
  background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  font-size: 2rem;
  border-radius: 6px;
}
.wa-photo-placeholder span {
  font-size: 0.72rem;
  color: #2e7d32;
  font-weight: 600;
  font-family: -apple-system, sans-serif;
}
.wa-demo-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.wa-tab {
  padding: 0.4rem 0.9rem;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  border: 2px solid var(--green);
  background: white;
  color: var(--green);
  transition: all 0.2s;
}
.wa-tab.active {
  background: var(--green);
  color: white;
}
.wa-scenario { display: none; }
.wa-scenario.active { display: flex; flex-direction: column; gap: 0.6rem; }

/* Bot number box */
.bot-number-box {
  background: var(--surface);
  border: 1.5px solid var(--border);
  border-radius: 14px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  box-shadow: var(--shadow);
  margin: 0.75rem 0 1.25rem;
}
.bot-number-info { display: flex; align-items: center; gap: 0.75rem; }
.bot-number-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  background: #f0fdf4;
  color: var(--wa);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}
.bot-number-label { font-size: 0.75rem; color: var(--text3); font-weight: 600; margin-bottom: 0.15rem; }
.bot-number-val { font-size: 1rem; font-weight: 800; color: var(--text); font-family: 'JetBrains Mono', monospace; }

/* ANIMATIONS */
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-20px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes float {
  0% { transform: translateY(100vh) scale(0); opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { transform: translateY(-100px) scale(1.2); opacity: 0; }
}

/* MOBILE */
@media (max-width: 600px) {
  .navbar-links { display: none; }
  .cards-grid { grid-template-columns: 1fr; }
  .rekap-grid { grid-template-columns: repeat(2, 1fr); }
  .cmd-row { flex-direction: column; align-items: flex-start; gap: 0.35rem; }
  .cmd-name { min-width: unset; }
  .bot-number-box { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="/home" class="navbar-brand">
    <i class="fa-solid fa-graduation-cap" style="color:var(--green)"></i>
    PKL <span class="dot-green">SMKN Bansari</span>
  </a>
  <div class="navbar-links">
    <a href="#wa-bot">WA Bot</a>
    <a href="#presensi-web">Presensi Web</a>
    <a href="#pengingat">Pengingat</a>
    <a href="#faq">FAQ</a>
  </div>
  <a href="https://pklbos.smknbansari.sch.id/" class="navbar-back">
    <i class="fa-solid fa-arrow-left"></i> Kembali
  </a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-dots" aria-hidden="true">
    <div class="dot" style="left:10%;animation-duration:8s;animation-delay:0s"></div>
    <div class="dot" style="left:25%;animation-duration:12s;animation-delay:2s"></div>
    <div class="dot" style="left:50%;animation-duration:10s;animation-delay:1s"></div>
    <div class="dot" style="left:70%;animation-duration:9s;animation-delay:3s"></div>
    <div class="dot" style="left:88%;animation-duration:11s;animation-delay:0.5s"></div>
  </div>
  <div style="position:relative;">
    <div class="hero-badge">
      <i class="fa-solid fa-book-open"></i>
      Panduan Penggunaan
    </div>
    <h1>Sistem Presensi PKL<br><span>SMK Negeri Bansari</span></h1>
    <p>Panduan lengkap cara presensi via WhatsApp dan web. Mudah, cepat, dan bisa dilakukan dari mana saja.</p>
    <div class="hero-cta">
      <a href="#wa-bot" class="btn-hero primary">
        <i class="fa-brands fa-whatsapp"></i> Panduan WA Bot
      </a>
      <a href="#presensi-web" class="btn-hero secondary">
        <i class="fa-solid fa-globe"></i> Panduan Web
      </a>
    </div>
  </div>
</section>
<svg class="wave" viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
  <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#f0f4f8"/>
</svg>

<!-- SECTION: WA BOT -->
<section class="section" id="wa-bot">
  <div class="container">
    <div class="section-label"><i class="fa-brands fa-whatsapp"></i> WhatsApp Bot</div>
    <h2 class="section-title">Presensi via WhatsApp</h2>
    <p class="section-sub">Bot WA akan membalas pesanmu secara otomatis. Pastikan format perintah benar agar sistem bisa memproses.</p>

    <!-- Langkah awal -->
    <div class="section-label" style="margin-bottom:0.75rem;"><i class="fa-solid fa-play"></i> Mulai dari sini</div>
    <div class="steps">
      <!-- STEP 1 — nomor dengan tombol WA -->
      <div class="step-card" style="animation-delay:0.0s">
        <div class="step-num">1</div>
        <div class="step-body" style="width:100%;">
          <h3>Simpan nomor WA Bot</h3>
          <p style="margin-bottom:0.75rem;">Simpan nomor bot di kontakmu lalu mulai chat. Tanpa nomor tersimpan, bot tidak akan merespons.</p>
          <div class="bot-number-box">
            <div class="bot-number-info">
              <div class="bot-number-icon"><i class="fa-brands fa-whatsapp"></i></div>
              <div>
                <div class="bot-number-label">Nomor WA Bot Presensi</div>
                <div class="bot-number-val">+62 877-5444-6580</div>
              </div>
            </div>
            <a href="https://wa.me/6287754446580" target="_blank" class="wa-btn" style="font-size:0.82rem;padding:0.6rem 1.1rem;">
              <i class="fa-brands fa-whatsapp"></i> Simpan &amp; Chat
            </a>
          </div>
        </div>
      </div>

      <div class="step-card" style="animation-delay:0.1s">
        <div class="step-num">2</div>
        <div class="step-body">
          <h3>Daftarkan nomor WA-mu</h3>
          <p>Kirim perintah <span class="cmd">reg &lt;NIS&gt;</span> — contoh: <span class="cmd">reg 2801</span><br>
          Sistem akan menampilkan datamu. Balas <span class="cmd">ya</span> untuk konfirmasi, atau <span class="cmd">tidak</span> jika salah NIS.</p>
        </div>
      </div>
      <div class="step-card" style="animation-delay:0.2s">
        <div class="step-num">3</div>
        <div class="step-body">
          <h3>Lakukan presensi harian</h3>
          <p>Kirim foto selfie di lokasi PKL dengan caption <span class="cmd">masuk &lt;catatan kegiatan&gt;</span><br>
          Contoh: <span class="cmd">masuk Memasang instalasi panel listrik</span></p>
        </div>
      </div>
      <div class="step-card" style="animation-delay:0.3s">
        <div class="step-num">4</div>
        <div class="step-body">
          <h3>Tunggu konfirmasi bot</h3>
          <p>Bot akan membalas dengan pesan <strong>✅ Presensi Berhasil</strong> beserta detail waktu dan keteranganmu.</p>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <!-- ===== WA CHAT DEMO ===== -->
    <div class="section-label"><i class="fa-solid fa-mobile-screen"></i> Simulasi Chat WA</div>
    <p class="section-sub" style="margin-bottom:1rem;">Pilih skenario untuk melihat seperti apa tampilan chat di WhatsApp.</p>

    <div class="wa-demo-tabs">
      <button class="wa-tab active" onclick="switchTab('reg')">📋 Daftar</button>
      <button class="wa-tab" onclick="switchTab('masuk')">📸 Presensi Masuk</button>
      <button class="wa-tab" onclick="switchTab('izin')">📝 Izin / Sakit</button>
      <button class="wa-tab" onclick="switchTab('lupa')">🕐 Lupa Presensi</button>
      <button class="wa-tab" onclick="switchTab('cek')">📊 Cek Rekap</button>
    </div>

    <div class="wa-demo">
      <!-- Bar atas ala WA -->
      <div class="wa-demo-bar">
        <div class="wa-demo-avatar"><i class="fa-brands fa-whatsapp"></i></div>
        <div class="wa-demo-info">
          <strong>Bot Presensi PKL</strong>
          <span>+62 877-5444-6580 · online</span>
        </div>
        <i class="fa-solid fa-ellipsis-vertical" style="opacity:0.6;font-size:0.9rem;"></i>
      </div>
      <!-- Body chat -->
      <div class="wa-demo-body">
        <div class="wa-day-divider">Hari ini</div>

        <!-- SKENARIO: DAFTAR -->
        <div class="wa-scenario active" id="sc-reg">
          <div class="wa-bubble-out">
            <div class="wa-bubble-text"><span class="wa-cmd">reg 2801</span></div>
            <div class="wa-bubble-meta">08.01 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">━━━━━━━━━━━━━━━━━━━━
          📄 <span class="wa-bold">DATA DITEMUKAN:</span>
          ━━━━━━━━━━━━━━━━━━━━
          👤 Nama  : <span class="wa-bold">Ahmad Fauzan</span>
          🏫 Kelas : <span class="wa-bold">XII TE 1</span>
          🆔 NIS   : <span class="wa-bold">2801</span>

          ❓ <span class="wa-bold">Apakah data ini benar milik kamu?</span>
          ✍️ Balas dengan ketik:
          <span class="wa-cmd">ya</span> – untuk konfirmasi pendaftaran
          <span class="wa-cmd">tidak</span> – jika data salah</div>
            <div class="wa-bubble-meta">08.01</div>
          </div>
          <div class="wa-bubble-out">
            <div class="wa-bubble-text"><span class="wa-cmd">ya</span></div>
            <div class="wa-bubble-meta">08.02 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">✅ <span class="wa-bold">Pendaftaran Berhasil!</span>

          Nomor <span class="wa-bold">08xxx-xxxx-xxxx</span> telah didaftarkan atas nama:
          👤 <span class="wa-bold">Ahmad Fauzan</span>
          🏫 <span class="wa-bold">Kelas:</span> XII TE 1

          Sekarang kamu bisa melakukan presensi PKL.

          Ketik <span class="wa-cmd">1</span> untuk panduan presensi.</div>
            <div class="wa-bubble-meta">08.02</div>
          </div>
        </div>

        <!-- SKENARIO: PRESENSI MASUK -->
        <div class="wa-scenario" id="sc-masuk">
          <div class="wa-bubble-out">
            <div class="wa-bubble-photo">
              <div class="wa-photo-placeholder">🤳<span>Foto Selfie di Lokasi PKL</span></div>
            </div>
            <div class="wa-bubble-text"><span class="wa-cmd">masuk</span> Memasang instalasi panel listrik</div>
            <div class="wa-bubble-meta">07.45 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">✅ Presensi Berhasil

          🗓️ Status   : Masuk
          📝 Catatan  : Memasang instalasi panel listrik
          👤 Nama     : Ahmad Fauzan
          🏫 Kelas    : XII TKJ 1

          ⏰ Waktu    : Kamis, 17 April 2026
          Pukul 07:45:00

          📊 Lihat rekap presensi kamu, bisa balas dengan ketik <span class="wa-cmd">cek</span> atau klik link ini:
          pklbos.smknbansari.sch.id/?akses=detail&nis=2801

          ℹ️ Fitur Lupa Absen sudah aktif.
          Balas dengan ketik <span class="wa-cmd">2</span> untuk petunjuk penggunaannya.

          ℹ️ Fitur Batal Absen sudah aktif.
          Balas dengan ketik <span class="wa-cmd">batal</span> untuk petunjuk penggunaannya.</div>
            <div class="wa-bubble-meta">07.45</div>
          </div>
        </div>

        <!-- SKENARIO: IZIN / SAKIT -->
        <div class="wa-scenario" id="sc-izin">
          <div class="wa-bubble-out">
            <div class="wa-bubble-text"><span class="wa-cmd">sakit</span> Demam sejak semalam, izin tidak masuk</div>
            <div class="wa-bubble-meta">07.30 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">✅ Presensi Berhasil

          🗓️ Status   : Sakit
          📝 Catatan  : Demam sejak semalam
          👤 Nama     : Ahmad Fauzan
          🏫 Kelas    : XII TKJ 1

          ⏰ Waktu    : Kamis, 17 April 2026
          Pukul 07:30:00

          🌼 Semoga cepat sembuh dan bisa kembali beraktivitas seperti biasa.
          Tetap jaga kesehatan ya 💪

          📊 Lihat rekap presensi kamu, bisa balas dengan ketik <span class="wa-cmd">cek</span> atau klik link ini:
          pklbos.smknbansari.sch.id/?akses=detail&nis=2801

          ℹ️ Fitur Lupa Absen sudah aktif.
          Balas dengan ketik <span class="wa-cmd">2</span> untuk petunjuk penggunaannya.

          ℹ️ Fitur Batal Absen sudah aktif.
          Balas dengan ketik <span class="wa-cmd">batal</span> untuk petunjuk penggunaannya.</div>
            <div class="wa-bubble-meta">07.30</div>
          </div>
          <div class="wa-bubble-out" style="margin-top:0.5rem;">
            <div class="wa-bubble-text"><span class="wa-cmd">izin</span> Ada keperluan keluarga hari ini</div>
            <div class="wa-bubble-meta">08.00 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">✅ <span class="wa-bold">Presensi Berhasil!</span>

<span class="wa-bold">Keterangan:</span> Izin
<span class="wa-bold">Catatan   :</span> Ada keperluan keluarga hari ini</div>
            <div class="wa-bubble-meta">08.00</div>
          </div>
        </div>

        <!-- SKENARIO: LUPA PRESENSI -->
        <div class="wa-scenario" id="sc-lupa">
          <div class="wa-bubble-out">
            <div class="wa-bubble-photo">
              <div class="wa-photo-placeholder">🤳<span>Foto Selfie kemarin</span></div>
            </div>
            <div class="wa-bubble-text"><span class="wa-cmd">lupa Masuk 16-04-2026</span> Input data inventaris lab</div>
            <div class="wa-bubble-meta">09.10 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">✅ Lupa Absen Berhasil Dicatat

              📅 Tanggal    : Rabu, 16 Apr 2026
              📝 Keterangan : Masuk
              🙍 Nama       : Ahmad Fauzan
              🏫 Kelas      : XII TKJ 1
              🗒️ Catatan    : Input data inventaris lab
              🔑 Kode       : LA3F2
              📊 Pemakaian  : 1 dari 2 kali</div>
            <div class="wa-bubble-meta">09.10</div>
            <div class="wa-bubble-text">⚠️ <span class="wa-bold">Perhatian:</span> Fitur lupa presensi hanya bisa digunakan <span class="wa-bold">2x per hari</span> dan untuk hari <span class="wa-bold">sebelumnya saja.</span></div>
            <div class="wa-bubble-meta">09.10</div>
          </div>
        </div>

        <!-- SKENARIO: CEK REKAP -->
        <div class="wa-scenario" id="sc-cek">
          <div class="wa-bubble-out">
            <div class="wa-bubble-text"><span class="wa-cmd">cek</span></div>
            <div class="wa-bubble-meta">10.00 <i class="fa-solid fa-check-double wa-check"></i></div>
          </div>
          <div class="wa-bubble-in">
            <div class="wa-bubble-text">📋 Rekap Presensi
              Nama : Ahmad Fauzan
              Kelas: XII TKJ 1
              NIS  : 2801

              Masuk : 12 x
              Izin  : 1 x
              Sakit : 1 x
              Libur : 2 x

              Tgl  Apr
              1    ✅
              2    ✅
              3    ✅
              4    ✅
              5    ✅
              6    ➖
              7    ➖
              ...
              16   🔴
              17   🟡
              ...

              Keterangan:
              ✅ = Masuk  🔵 = Izin
              🟡 = Sakit  🔴 = Libur
              ❌ = Tidak Presensi

              📊 Rekap kehadiranmu bisa dilihat di:
              🔗 pklbos.smknbansari.sch.id/?akses=detail&nis=2801

              📌 Silakan buka link di atas untuk melihat detail kehadiranmu.</div>
            <div class="wa-bubble-meta">10.00</div>
          </div>
        </div>

      </div><!-- end wa-demo-body -->
    </div><!-- end wa-demo -->

    <div class="divider"></div>

    <!-- Perintah Siswa -->
    <div class="section-label"><i class="fa-solid fa-user-graduate"></i> Perintah Siswa</div>
    <div class="cmd-table">
      <div class="cmd-table-header"><i class="fa-solid fa-terminal"></i> Daftar Perintah</div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">info</span></div><div class="cmd-desc">Tampilkan menu utama layanan</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">reg &lt;nis&gt;</span></div><div class="cmd-desc">Daftarkan nomor WA ke sistem — wajib dilakukan pertama kali</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">masuk &lt;catatan&gt;</span></div><div class="cmd-desc">Presensi masuk — <strong>wajib kirim foto selfie</strong> sebagai caption</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">izin &lt;catatan&gt;</span></div><div class="cmd-desc">Presensi izin tidak masuk</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">sakit &lt;catatan&gt;</span></div><div class="cmd-desc">Presensi sakit tidak masuk</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">libur &lt;catatan&gt;</span></div><div class="cmd-desc">Presensi libur</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">lupa masuk &lt;tgl&gt; &lt;catatan&gt;</span></div><div class="cmd-desc">Presensi untuk hari sebelumnya (maks. 2x per hari, wajib foto)</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">batal &lt;tgl&gt;</span></div><div class="cmd-desc">Batalkan/hapus presensi di tanggal tertentu</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">cek</span></div><div class="cmd-desc">Lihat rekap presensimu</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">admin</span></div><div class="cmd-desc">Hubungi admin (aktifkan sesi chat dengan admin)</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">unreg</span></div><div class="cmd-desc">Hapus nomor WA dari sistem</div></div>
    </div>

    <div class="alert warning">
      <span class="alert-icon">⚠️</span>
      <div><strong>Format tanggal Lupa Presensi:</strong> gunakan format <span class="cmd">DD-MM-YYYY</span> — contoh: <span class="cmd">22-04-2026</span>. Lupa presensi hanya untuk hari <strong>sebelumnya</strong>, bukan hari ini atau masa depan.</div>
    </div>

    <!-- Contoh format — diperbaiki tampilan mobile -->
    <div class="section-label" style="margin-top:2rem;"><i class="fa-solid fa-code"></i> Contoh Format Perintah</div>

    <div class="cmd-block">
      <div class="cmd-block-header">
        <div class="dot-red"></div>
        <div class="dot-yellow"></div>
        <div class="dot-green"></div>
        <span style="margin-left:0.25rem;">Contoh perintah WA</span>
      </div>
      <div class="cmd-block-body">
        <div class="cmd-example">
          <div class="cmd-example-label"># Presensi masuk — kirim sebagai caption foto selfie</div>
          <div class="cmd-example-line"><span class="kw">masuk</span> Memasang instalasi panel listrik</div>
        </div>
        <div class="cmd-example">
          <div class="cmd-example-label"># Presensi izin</div>
          <div class="cmd-example-line"><span class="kw">izin</span> Ada acara keluarga hari ini</div>
        </div>
        <div class="cmd-example">
          <div class="cmd-example-label"># Presensi sakit</div>
          <div class="cmd-example-line"><span class="kw">sakit</span> Demam sejak semalam</div>
        </div>
        <div class="cmd-example">
          <div class="cmd-example-label"># Lupa presensi kemarin — juga wajib kirim foto sebagai caption</div>
          <div class="cmd-example-line"><span class="kw">lupa masuk 16-04-2026</span> Input data alat lab</div>
        </div>
        <div class="cmd-example">
          <div class="cmd-example-label"># Batalkan presensi di tanggal tertentu</div>
          <div class="cmd-example-line"><span class="kw">batal</span> 17-04-2026</div>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <!-- Perintah Pembimbing -->
    <div class="section-label"><i class="fa-solid fa-chalkboard-teacher"></i> Perintah Pembimbing</div>
    <div class="cmd-table">
      <div class="cmd-table-header"><i class="fa-solid fa-terminal"></i> Perintah Khusus Pembimbing</div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">cek rekap</span></div><div class="cmd-desc">Rekap presensi semua siswa bimbingan</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">cek rekap &lt;nis&gt;</span></div><div class="cmd-desc">Rekap presensi siswa tertentu</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">input &lt;nis&gt; masuk</span></div><div class="cmd-desc">Input presensi hari ini untuk siswa</div></div>
      <div class="cmd-row"><div class="cmd-name"><span class="cmd">cari &lt;kata kunci&gt;</span></div><div class="cmd-desc">Cari data siswa/DUDI</div></div>
    </div>
  </div>
</section>

<!-- SECTION: PRESENSI WEB -->
<section class="section" id="presensi-web" style="background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="section-label"><i class="fa-solid fa-globe"></i> Presensi Web</div>
    <h2 class="section-title">Presensi Lewat Website</h2>
    <p class="section-sub">Alternatif presensi jika tidak bisa via WA. Buka di browser HP atau laptop.</p>

    <div class="cards-grid">
      <div class="info-card" style="animation-delay:0s">
        <div class="info-card-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fa-solid fa-globe"></i></div>
        <h3>Buka Website PKL</h3>
        <p>Akses <strong>pklbos.smknbansari.sch.id</strong> lalu pilih tombol <strong>Presensi Web</strong> di halaman utama.</p>
      </div>
      <div class="info-card" style="animation-delay:0.1s">
        <div class="info-card-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-id-card"></i></div>
        <h3>Masukkan NIS</h3>
        <p>Ketik NIS-mu dan klik Cek. Data siswa akan muncul otomatis.</p>
      </div>
      <div class="info-card" style="animation-delay:0.2s">
        <div class="info-card-icon" style="background:#fef9c3;color:#ca8a04;"><i class="fa-solid fa-camera"></i></div>
        <h3>Ambil Foto Selfie</h3>
        <p>Untuk keterangan <strong>Masuk</strong>, wajib ambil foto selfie menggunakan kamera browser.</p>
      </div>
      <div class="info-card" style="animation-delay:0.3s">
        <div class="info-card-icon" style="background:#fdf4ff;color:#9333ea;"><i class="fa-solid fa-paper-plane"></i></div>
        <h3>Kirim Presensi</h3>
        <p>Pilih keterangan (Masuk/Izin/Sakit/Libur), tambah catatan, lalu klik Simpan.</p>
      </div>
    </div>

    <div class="alert info">
      <span class="alert-icon">💡</span>
      <div>Presensi web juga mendukung <strong>Lupa Absen</strong> — pilih tanggal sebelumnya saat mengisi form. Maksimal 2x per hari.</div>
    </div>
  </div>
</section>

<!-- SECTION: PENGINGAT -->
<section class="section" id="pengingat">
  <div class="container">
    <div class="section-label"><i class="fa-solid fa-bell"></i> Notifikasi Otomatis</div>
    <h2 class="section-title">Sistem Pengingat</h2>
    <p class="section-sub">Bot akan mengirimkan pengingat otomatis jika kamu belum presensi. Cukup balas dengan salah satu pilihan berikut.</p>

    <div class="reminder-box">
      <h3><i class="fa-solid fa-bell" style="margin-right:0.35rem;"></i> Saat menerima pesan pengingat, balas dengan:</h3>
      <div class="reminder-options">
        <div class="reminder-opt">
          <span class="cmd">ya</span>
          <span class="reminder-opt-desc">→ Otomatis tercatat sebagai <strong>libur</strong></span>
        </div>
        <div class="reminder-opt">
          <span class="cmd">tidak</span>
          <span class="reminder-opt-desc">→ Harus tetap presensi manual dengan foto</span>
        </div>
        <div class="reminder-opt">
          <span class="cmd">sakit</span>
          <span class="reminder-opt-desc">→ Otomatis tercatat sebagai <strong>sakit</strong></span>
        </div>
        <div class="reminder-opt">
          <span class="cmd">izin</span>
          <span class="reminder-opt-desc">→ Otomatis tercatat sebagai <strong>izin</strong></span>
        </div>
        <div class="reminder-opt">
          <span class="cmd">libur</span>
          <span class="reminder-opt-desc">→ Otomatis tercatat sebagai <strong>libur</strong></span>
        </div>
      </div>
    </div>

    <div class="alert success">
      <span class="alert-icon">📊</span>
      <div>Pembimbing dan wali kelas akan mendapatkan <strong>rekap presensi mingguan</strong> setiap Senin pagi secara otomatis.</div>
    </div>
  </div>
</section>

<!-- SECTION: REKAP -->
<section class="section" style="background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="section-label"><i class="fa-solid fa-chart-bar"></i> Keterangan Rekap</div>
    <h2 class="section-title">Simbol Status Presensi</h2>
    <p class="section-sub">Rekap presensi menampilkan status harianmu dengan kode warna berikut.</p>
    <div class="rekap-grid">
      <div class="rekap-badge masuk"><i class="fa-solid fa-circle-check"></i> Masuk (M)</div>
      <div class="rekap-badge izin"><i class="fa-solid fa-clock"></i> Izin (I)</div>
      <div class="rekap-badge sakit"><i class="fa-solid fa-heart-pulse"></i> Sakit (S)</div>
      <div class="rekap-badge libur"><i class="fa-solid fa-umbrella-beach"></i> Libur (L)</div>
      <div class="rekap-badge kosong"><i class="fa-solid fa-xmark"></i> Tidak Presensi</div>
    </div>
    <p style="font-size:0.82rem;color:var(--text3);">Lihat rekap lengkapmu di <a href="https://pklbos.smknbansari.sch.id" target="_blank" style="color:var(--green);font-weight:600;">pklbos.smknbansari.sch.id</a> — buka menu <strong>Data Presensi</strong> dan masukkan NIS untuk melihat kalender dan tabel riwayat presensi.</p>
  </div>
</section>

<!-- SECTION: FAQ -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-label"><i class="fa-solid fa-circle-question"></i> FAQ</div>
    <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
    <p class="section-sub">Belum menemukan jawaban? Hubungi admin via WA bot dengan perintah <span class="cmd">admin</span>.</p>

    <div class="faq">
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Bagaimana jika presensi masuk tapi lupa kirim foto?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Tidak masalah! Kirim dulu pesan <span class="cmd">masuk &lt;catatan&gt;</span> tanpa foto — bot akan meminta foto. Kirimkan foto sesudahnya dan presensi akan tersimpan.
          <br><br>Atau kirim foto terlebih dahulu tanpa caption, bot akan meminta keterangan. Balas dengan <span class="cmd">masuk &lt;catatan&gt;</span>.
        </div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Nomor WA saya berganti, apa yang harus dilakukan?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Hubungi admin dengan perintah <span class="cmd">admin</span> dari nomor lama (jika masih bisa) atau minta admin untuk mereset nomor via sistem. Admin dapat menggunakan perintah <span class="cmd">set &lt;NIS&gt; &lt;NomorHP baru&gt;</span>.
        </div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Kenapa presensi di luar tanggal periode tidak bisa?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Sistem hanya menerima presensi dalam rentang periode PKL aktif. Jika ada kebutuhan khusus (PKL mundur/maju), hubungi admin untuk pengaturan toleransi tanggal.
        </div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Apakah presensi web dan WA bot datanya sama?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Ya, keduanya tersimpan di database yang sama. Tidak bisa presensi dua kali di hari yang sama — baik via WA maupun web.
        </div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Bot tidak membalas pesan saya, apa yang harus dilakukan?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Pastikan nomor bot tersimpan di kontakmu. Coba kirim perintah <span class="cmd">info</span> terlebih dahulu. Jika masih tidak merespons, tunggu beberapa menit lalu coba lagi atau hubungi admin sekolah.
        </div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Bagaimana cara melihat rekap presensi saya?
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-a">
          Ada dua cara: kirim <span class="cmd">cek</span> ke WA bot untuk mendapat link rekap, atau langsung buka <a href="https://pklbos.smknbansari.sch.id" target="_blank" style="color:var(--green);">pklbos.smknbansari.sch.id</a> lalu pilih menu <strong>Data Presensi</strong> dan masukkan NIS-mu.
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <div style="text-align:center;">
      <p style="font-size:0.9rem;color:var(--text2);margin-bottom:1.25rem;">Masih ada pertanyaan? Chat langsung dengan admin via WA Bot.</p>
      <a href="https://wa.me/6287754446580?text=admin" class="wa-btn" target="_blank">
        <i class="fa-brands fa-whatsapp"></i> Chat WA Bot — ketik "admin"
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <p><strong>Sistem Presensi PKL</strong> — SMK Negeri Bansari</p>
  <p style="margin-top:0.35rem;">
    <a href="/home">Beranda</a> &nbsp;·&nbsp;
    <a href="https://pklbos.smknbansari.sch.id" target="_blank">Data Presensi</a> &nbsp;·&nbsp;
    <a href="https://pklbos.smknbansari.sch.id" target="_blank">Presensi Web</a>
  </p>
  <p style="margin-top:0.75rem;font-size:0.75rem;opacity:0.5;">© 2025/2026 SMK Negeri Bansari</p>
</footer>

<script>
function toggleFaq(el) {
  el.classList.toggle('open');
  const ans = el.nextElementSibling;
  ans.classList.toggle('open');
}

function switchTab(scenario) {
  // Update tabs
  document.querySelectorAll('.wa-tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');
  // Update scenarios
  document.querySelectorAll('.wa-scenario').forEach(s => s.classList.remove('active'));
  document.getElementById('sc-' + scenario).classList.add('active');
}
</script>
</body>
</html>