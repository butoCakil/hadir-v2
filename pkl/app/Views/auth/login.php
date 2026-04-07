<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?> — PKL SMKN Bansari</title>
    <link rel="shortcut icon" href="/assets/img/SMKNBansari.png" type="image/x-icon">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-wrap {
            width: 100%;
            max-width: 400px;
        }
        .login-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo img { height: 64px; }
        .login-title {
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.25rem;
            color: var(--text);
        }
        .login-subtitle {
            color: var(--text3);
            font-size: 0.83rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .alert-error {
            background: var(--red-bg);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .input-group-app {
            position: relative;
            margin-bottom: 1.1rem;
        }
        .input-group-app label {
            display: block;
            color: var(--text2);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .input-group-app .input-icon {
            position: absolute;
            left: 0.75rem;
            bottom: 0.6rem;
            color: var(--text3);
            font-size: 0.85rem;
            pointer-events: none;
        }
        .input-group-app input {
            width: 100%;
            padding: 0.55rem 0.75rem 0.55rem 2.2rem;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .input-group-app input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 2px var(--blue-bg);
        }
        .input-group-app input::placeholder { color: var(--text3); }
        .btn-login {
            width: 100%;
            padding: 0.6rem;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: opacity 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-login:hover { opacity: 0.88; }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: var(--text3);
        }
        /* Theme toggle pojok kanan atas */
        .theme-btn {
            position: fixed;
            top: 1rem; right: 1rem;
            width: 36px; height: 36px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text2);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            transition: background 0.15s;
        }
        .theme-btn:hover { background: var(--border); }
    </style>
</head>
<body>

    <!-- Theme toggle -->
    <button class="theme-btn" id="themeToggle" title="Toggle tema">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

    <div class="login-wrap">
        <a href="/home" style="display:inline-flex;align-items:center;gap:0.4rem;color:var(--text2);text-decoration:none;font-size:0.85rem;font-weight:600;margin-bottom:0.75rem;transition:color 0.15s;"
           onmouseover="this.style.color='var(--blue)'" onmouseout="this.style.color='var(--text2)'">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <div class="login-card">
            <div class="login-logo">
                <img src="/assets/img/SMKNBansari.png" alt="SMKN Bansari"
                     style="border-radius:50%;object-fit:cover;border:2px solid var(--border);"
                     onerror="this.style.display='none'">
            </div>
            <div class="login-title">Sistem Presensi PKL</div>
            <div class="login-subtitle">SMK Negeri Bansari</div>

            <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form action="/login" method="POST" autocomplete="off">
                <?= $csrfField ?>

                <div class="input-group-app">
                    <label for="username">Username</label>
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username" required autofocus>
                </div>

                <div class="input-group-app">
                    <label for="password">Password</label>
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </button>
            </form>
        </div>
        <div class="login-footer">Sistem Presensi PKL · SMK Negeri Bansari · <?= date('Y') ?></div>
    </div>

    <script>
    (function () {
        const html = document.documentElement;
        const btn  = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');
        const KEY  = 'pkl-theme';
        function apply(t) {
            html.setAttribute('data-theme', t);
            icon.className = t === 'dark' ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            localStorage.setItem(KEY, t);
        }
        apply(localStorage.getItem(KEY) || 'dark');
        btn.addEventListener('click', () => apply(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
    })();
    </script>
</body>
</html>