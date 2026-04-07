<?php
/**
 * Layout utama — digunakan oleh semua halaman admin
 * Variabel yang tersedia: $title, $activePage, $content (via ob_start)
 */
$activePage = $activePage ?? '';
$user       = $user ?? ['username' => 'Admin'];
$navItems   = [
    ['href' => '/',           'label' => 'Dashboard',  'icon' => 'fa-gauge',       'key' => 'dashboard'],
    ['href' => '/siswa',      'label' => 'Siswa',      'icon' => 'fa-users',       'key' => 'siswa'],
    ['href' => '/penempatan', 'label' => 'Penempatan', 'icon' => 'fa-building',    'key' => 'penempatan'],
    ['href' => '/presensi',   'label' => 'Presensi',   'icon' => 'fa-clipboard-check', 'key' => 'presensi'],
    ['href' => '/simulator',  'label' => 'Simulator',  'icon' => 'fa-comments',    'key' => 'simulator'],
    ['href' => '/manage',    'label' => 'Manage Data', 'icon' => 'fa-database',     'key' => 'manage'],
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> — PKL SMKN Bansari</title>
    <link rel="shortcut icon" href="/assets/img/SMKNBansari.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <?php if (!empty($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

<!-- Navbar -->
<nav class="app-navbar" id="appNavbar">
    <div class="navbar-inner">

        <!-- Brand -->
        <a href="/" class="navbar-brand">
            <img src="/assets/img/SMKNBansari.png" alt="SMKN Bansari"
                 style="height:28px;width:28px;border-radius:4px;object-fit:cover;">
            <span>PKL SMKN Bansari</span>
        </a>

        <!-- Nav links (desktop) -->
        <div class="navbar-links" id="navbarLinks">
            <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['href'] ?>"
               class="nav-link<?= $activePage === $item['key'] ? ' active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Right side -->
        <div class="navbar-right">
            <!-- Theme toggle -->
            <button class="theme-toggle" id="themeToggle" title="Toggle tema">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>

            <!-- User -->
            <span class="nav-user">
                <i class="fa-solid fa-circle-user"></i>
                <?= htmlspecialchars($user['username'] ?? 'Admin') ?>
            </span>

            <!-- Logout -->
            <a href="/logout" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>

            <!-- Mobile hamburger -->
            <button class="hamburger" id="hamburger" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="mobile-menu" id="mobileMenu">
        <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="mobile-nav-link<?= $activePage === $item['key'] ? ' active' : '' ?>">
            <i class="fa-solid <?= $item['icon'] ?>"></i>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Page content -->
<div class="page-wrapper">
    <?= $content ?? '' ?>
</div>

<!-- Footer -->
<footer class="app-footer">
    Sistem Presensi PKL · SMK Negeri Bansari · <?= date('Y') ?>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="/assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>
</body>
</html>