<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Api\WaSender;

class CronController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==========================================
    // GET /admin/cron/reminder
    // Preview dry-run — tidak kirim WA, tidak insert DB
    // ==========================================
    public function reminderPreview(): void
    {
        Auth::required();

        $_GET['akses'] = '1'; // aktifkan mode ujicoba di cron
        ob_start();
        require BASE_PATH . '/cron/cron_reminder.php';
        $output = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="id"><head>';
        echo '<meta charset="UTF-8">';
        echo '<title>Preview Cron Reminder</title>';
        echo '<style>
            body { font-family: monospace; background: #111; color: #eee; padding: 2rem; }
            pre  { background: #1e1e1e; padding: 1.5rem; border-radius: 8px;
                   overflow-x: auto; white-space: pre-wrap; word-break: break-word; }
            .btn { display: inline-block; margin-top: 1.5rem; padding: .6rem 1.4rem;
                   background: #e53e3e; color: #fff; border: none; border-radius: 6px;
                   text-decoration: none; font-size: .9rem; cursor: pointer; }
            .btn-back { background: #4a5568; margin-right: .5rem; }
            h2 { color: #68d391; }
        </style>';
        echo '</head><body>';
        echo '<h2>🔍 Preview Cron Reminder (Dry-run)</h2>';
        echo '<p>Data siswa yang <strong>akan</strong> dikirimi WA pengingat. Tidak ada WA terkirim, tidak ada data tersimpan.</p>';
        echo '<pre>' . htmlspecialchars($output) . '</pre>';
        echo '<a href="/dashboard" class="btn btn-back">← Dashboard</a>';
        echo '<form method="POST" action="/admin/cron/reminder/run" style="display:inline">';
        echo '<input type="hidden" name="_csrf" value="' . Auth::csrfToken() . '">';
        echo '<button type="submit" class="btn"
              onclick="return confirm(\'Kirim WA pengingat ke semua siswa yang belum presensi?\')">
              🚀 Jalankan & Kirim WA</button>';
        echo '</form>';
        echo '</body></html>';
    }

    // ==========================================
    // POST /admin/cron/reminder/run
    // Eksekusi sungguhan — kirim WA, insert DB
    // ==========================================
    public function reminderRun(): void
    {
        Auth::required();
        Auth::verifyCsrf();

        // Tanpa $_GET['akses'] → mode produksi
        ob_start();
        require BASE_PATH . '/cron/cron_reminder.php';
        $output = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="id"><head>';
        echo '<meta charset="UTF-8">';
        echo '<title>Cron Reminder — Selesai</title>';
        echo '<style>
            body { font-family: monospace; background: #111; color: #eee; padding: 2rem; }
            pre  { background: #1e1e1e; padding: 1.5rem; border-radius: 8px;
                   overflow-x: auto; white-space: pre-wrap; word-break: break-word; }
            .btn { display: inline-block; margin-top: 1.5rem; padding: .6rem 1.4rem;
                   background: #4a5568; color: #fff; border-radius: 6px;
                   text-decoration: none; font-size: .9rem; }
            h2   { color: #fc8181; }
        </style>';
        echo '</head><body>';
        echo '<h2>✅ Cron Reminder Dijalankan</h2>';
        echo '<pre>' . htmlspecialchars($output) . '</pre>';
        echo '<a href="/dashboard" class="btn">← Kembali ke Dashboard</a>';
        echo '</body></html>';
    }
}