<?php
$navItems = [
    ['href'=>'/info',  'label'=>'Data Presensi', 'icon'=>'fa-clipboard-check', 'key'=>'info'],
];
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PKL SMKN Bansari') ?> — PKL SMKN Bansari</title>
    <link rel="shortcut icon" href="/assets/img/SMKNBansari.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <?php if (!empty($extraCss)): ?><?= $extraCss ?><?php endif; ?>
</head>
<body>

<nav class="app-navbar" id="appNavbar">
    <div class="navbar-inner">
        <a href="/home" class="navbar-brand">
            <img src="/assets/img/SMKNBansari.png" alt="SMKN Bansari"
                 style="height:28px;width:28px;border-radius:4px;object-fit:cover;">
            <span>PKL SMKN Bansari</span>
        </a>
        <div class="navbar-links">
            <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['href'] ?>" class="nav-link<?= $activePage===$item['key']?' active':'' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="navbar-right">
            <button class="theme-toggle" id="themeToggle" title="Toggle tema">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
            <?php if (!empty($isLoggedIn)): ?>
            <a href="/dashboard" class="btn-logout" style="border-color:var(--green);color:var(--green);">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>
            <?php else: ?>
            <a href="/login" class="btn-logout">
                <i class="fa-solid fa-lock"></i>
                <span>Admin</span>
            </a>
            <?php endif; ?>
            <button class="hamburger" id="hamburger">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
        <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>" class="mobile-nav-link<?= $activePage===$item['key']?' active':'' ?>">
            <i class="fa-solid <?= $item['icon'] ?>"></i>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<div class="page-wrapper">
    <?= $content ?? '' ?>
</div>

<footer class="app-footer">
    Sistem Presensi PKL · SMK Negeri Bansari · <?= date('Y') ?>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="/assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?><?= $extraJs ?><?php endif; ?>
</body>
</html>