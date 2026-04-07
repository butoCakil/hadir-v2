/* ============================================================
   app.js — PKL SMKN Bansari
   ============================================================ */

// ── Theme toggle ──────────────────────────────────────────────
(function () {
    const html      = document.documentElement;
    const toggleBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const STORAGE_KEY = 'pkl-theme';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        if (themeIcon) {
            themeIcon.className = theme === 'dark'
                ? 'fa-solid fa-moon'
                : 'fa-solid fa-sun';
        }
        localStorage.setItem(STORAGE_KEY, theme);
    }

    // Inisialisasi dari localStorage atau default dark
    const saved = localStorage.getItem(STORAGE_KEY) || 'dark';
    applyTheme(saved);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const current = html.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
})();

// ── Mobile hamburger ──────────────────────────────────────────
(function () {
    const hamburger  = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');

    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', function () {
            mobileMenu.classList.toggle('open');
        });
    }
})();

// ── DataTables default config (bahasa Indonesia) ──────────────
$(document).ready(function () {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            search:      'Cari:',
            lengthMenu:  'Tampilkan _MENU_ data',
            info:        'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty:   'Tidak ada data',
            zeroRecords: 'Tidak ada data ditemukan.',
            paginate:    { previous: '‹', next: '›' },
        },
        pageLength:    25,
        responsive:    true,
    });
});
