<?php

declare(strict_types=1);

ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ==========================================
// 1. KONSTANTA PATH
// ==========================================

define('BASE_PATH', dirname(__DIR__, 2) . '/pkl');
// Hasil: /home/dvttaulx/pkl

define('PUBLIC_PATH', __DIR__);
// Hasil: /home/dvttaulx/public_html/dev

// ==========================================
// 2. LOAD .env
// ==========================================

$envFile = BASE_PATH . '/.env';

if (!file_exists($envFile)) {
    http_response_code(500);
    die('Environment file tidak ditemukan.');
}

// Parse .env manual — tanpa library, ringan untuk shared hosting
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Lewati baris komentar
    if (str_starts_with(trim($line), '#')) continue;

    if (str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// ==========================================
// 3. KONFIGURASI ERROR & TIMEZONE
// ==========================================

$appConfig = require BASE_PATH . '/config/app.php';

date_default_timezone_set($appConfig['timezone']);

if ($appConfig['env'] === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Log error ke file, bukan ke browser
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

// ==========================================
// 4. AUTOLOADER — tanpa Composer (manual)
// ==========================================

spl_autoload_register(function (string $class): void {
    // Namespace App\Core\Database → app/Core/Database.php
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (!str_starts_with($class, $prefix)) return;

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// ==========================================
// 5. SECURITY HEADERS
// ==========================================

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');

// ==========================================
// 6. ROUTING
// ==========================================

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SiswaController;
use App\Controllers\PenempatanController;
use App\Controllers\PresensiController;
use App\Controllers\SimulatorController;
use App\Controllers\HomeController;
use App\Controllers\CekController;
use App\Controllers\DudiController;
use App\Controllers\ManageController;
use App\Controllers\PengaturanController;
use App\Controllers\LogsController;
use App\Controllers\ArsipController;
use App\Controllers\PresensiWebController;
use App\Core\Response;
use App\Controllers\CronController;
use App\Controllers\PanduanController;

$router = new Router();

// ---- Simulator ----
$router->get('/simulator',      [SimulatorController::class, 'index']);
$router->post('/simulator/send', [SimulatorController::class, 'send']);

// ---- Presensi ----
$router->get('/presensi', [PresensiController::class, 'index']);

// ---- Penempatan ----
$router->get('/penempatan',                        [PenempatanController::class, 'index']);
$router->get('/penempatan/detail/{dudika}',        [PenempatanController::class, 'detail']);

// ---- Siswa ----
$router->get('/siswa',                    [SiswaController::class, 'index']);
$router->get('/siswa/{nis}',              [SiswaController::class, 'detail']);
$router->post('/siswa/update-nohp',       [SiswaController::class, 'updateNohp']);
$router->post('/siswa/update-dudika',     [SiswaController::class, 'updateDudika']);
$router->post('/siswa/update-pembimbing', [SiswaController::class, 'updatePembimbing']);

// ---- Auth ----
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ---- Root ----
$router->get('/', function () {
    \App\Core\Auth::start();
    if (\App\Core\Auth::check()) {
        \App\Core\Response::redirect('/dashboard');
    } else {
        \App\Core\Response::redirect('/home');
    }
});

// ---- Dashboard ----
$router->get('/dashboard', [DashboardController::class, 'index']);

// ---- Home (Publik) ----
$router->get('/home', [HomeController::class, 'index']);

// ---- Cek (Publik) ----
$router->get('/cek',        [CekController::class, 'index']);
$router->get('/cek/{nis}',  [CekController::class, 'detail']);
$router->get('/info',        [CekController::class, 'index']);
$router->get('/info/{nis}',  [CekController::class, 'detail']);

// ---- DUDI (Publik) ----
$router->get('/dudi',           [DudiController::class, 'index']);
$router->get('/dudi/{kode}',    [DudiController::class, 'detail']);
$router->post('/dudi/wa',       [DudiController::class, 'redirectWa']);

// ---- Manage Data ----
$router->get('/manage',                     [ManageController::class, 'index']);
$router->post('/manage/update-pembimbing',  [ManageController::class, 'updatePembimbing']);
$router->post('/manage/update-walikelas',   [ManageController::class, 'updateWalikelas']);
$router->post('/manage/update-dudika',      [ManageController::class, 'updateDudika']);
$router->post('/manage/upload-penempatan', [ManageController::class, 'uploadPenempatan']);
$router->post('/manage/sinkron-pembimbing', [ManageController::class, 'sinkronPembimbing']);
$router->post('/manage/sinkron-pembimbing-preview', [ManageController::class, 'sinkronPembimbingPreview']);
$router->post('/manage/sinkron-siswa-preview', [ManageController::class, 'sinkronSiswaPreview']);
$router->post('/manage/sinkron-siswa-exec',  [ManageController::class, 'sinkronSiswaExec']);
$router->post('/manage/sinkron-dudi-preview',[ManageController::class, 'sinkronDudiPreview']);
$router->post('/manage/sinkron-dudi-exec',   [ManageController::class, 'sinkronDudiExec']);
$router->get('/manage/template-excel', [ManageController::class, 'templateExcel']);
$router->get('/manage/template-excel', [ManageController::class, 'templateExcel']);
$router->post('/manage/periode-tambah',   [ManageController::class, 'periodeTambah']);
$router->post('/manage/periode-aktifkan', [ManageController::class, 'periodeAktifkan']);
$router->post('/manage/periode-hapus',    [ManageController::class, 'periodeHapus']);
$router->post('/manage/cek-duplikat-dudi', [ManageController::class, 'cekDuplikatDudi']);
$router->post('/manage/merge-dudi',         [ManageController::class, 'mergeDudi']);
$router->post('/manage/periode-edit', [ManageController::class, 'periodeEdit']);

// ---- Pengaturan ----
$router->get('/pengaturan',          [PengaturanController::class, 'index']);
$router->post('/pengaturan/simpan',  [PengaturanController::class, 'simpan']);
$router->post('/pengaturan/password',[PengaturanController::class, 'gantiPassword']);
$router->post('/pengaturan/notifikasi', [PengaturanController::class, 'notifikasi']);
$router->post('/pengaturan/gateway',    [PengaturanController::class, 'gateway']);
$router->post('/pengaturan/wa-config',  [PengaturanController::class, 'updateWaConfig']);

// ---- Arsip Periode ----
$router->get('/arsip',                          [ArsipController::class, 'index']);
$router->get('/arsip/{id}',                     [ArsipController::class, 'dashboard']);
$router->get('/arsip/{id}/siswa',               [ArsipController::class, 'siswa']);
$router->get('/arsip/{id}/siswa/{nis}',         [ArsipController::class, 'detailSiswa']);
$router->get('/arsip/{id}/rekap',               [ArsipController::class, 'rekap']);
$router->get('/arsip/{id}/rekap/export-excel',  [ArsipController::class, 'exportExcel']);

$router->get('/presensi-web',         [PresensiWebController::class, 'index']);
$router->post('/presensi-web/cek-nis',[PresensiWebController::class, 'cekNis']);
$router->post('/presensi-web/simpan', [PresensiWebController::class, 'simpan']);
$router->post('/presensi-web/batal',  [PresensiWebController::class, 'batal']);

// ---- Presensi Admin ----
$router->post('/presensi/input',  [PresensiController::class, 'input']);
$router->post('/presensi/edit',   [PresensiController::class, 'edit']);
$router->post('/presensi/hapus',  [PresensiController::class, 'hapus']);

// ---- Logs ----
$router->get('/logs',       [LogsController::class, 'index']);
$router->get('/logs/raw',   [LogsController::class, 'raw']);
$router->post('/logs/clear',[LogsController::class, 'clear']);

// ---- Cron (Admin only) ----
$router->get('/admin/cron/reminder',      [CronController::class, 'reminderPreview']);
$router->post('/admin/cron/reminder/run', [CronController::class, 'reminderRun']);

// ---- Test route (hapus setelah selesai testing) ----
$router->get('/ping', function () {
    Response::json(['status' => 'ok', 'time' => date('Y-m-d H:i:s')]);
});

$router->get('/panduan', [PanduanController::class, 'index']);

// ---- Dispatch ----
$router->dispatch();