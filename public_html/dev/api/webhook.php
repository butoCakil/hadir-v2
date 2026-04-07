<?php

/**
 * Webhook endpoint untuk Whacenter
 * URL: https://dev.masbendz.com/api/webhook.php
 *
 * Daftarkan URL ini di dashboard Whacenter sebagai webhook URL.
 */

declare(strict_types=1);

// ── Bootstrap ──
define('BASE_PATH', dirname(__DIR__, 3) . '/pkl');
// Hasil: /home/dvttaulx/pkl

// Load .env
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Config
$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

// Error logging
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/wabot.log');

// Autoloader
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    if (!str_starts_with($class, $prefix)) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

// ── Hanya terima POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// ── Baca payload ──
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

// ── Proses ──
try {
    $handler = new \App\Api\WabotHandler();
    $handler->handle($data);
    http_response_code(200);
    echo 'OK';
} catch (\Throwable $e) {
    error_log('[WEBHOOK ERROR] ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo 'Error';
}
