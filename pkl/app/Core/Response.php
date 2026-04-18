<?php

namespace App\Core;

class Response
{
    // ==========================================
    // Redirect
    // ==========================================

   public static function redirect(string $path): void
    {
        // Kalau sudah URL lengkap, langsung redirect
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            header('Location: ' . $path);
            exit;
        }
    
        $config = require BASE_PATH . '/config/app.php';
        $base   = rtrim($config['url'], '/');
        $path   = '/' . ltrim($path, '/');
        header('Location: ' . $base . $path);
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }

    // ==========================================
    // JSON Response (untuk API / AJAX)
    // ==========================================

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'OK'): void
    {
        self::json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function error(string $message = 'Error', int $status = 400): void
    {
        self::json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }

    // ==========================================
    // Render View
    // ==========================================

    /**
     * Load file view dengan data yang di-extract sebagai variabel lokal
     *
     * Contoh:
     * Response::view('dashboard/index', ['siswa' => $siswa])
     */
    public static function view(string $view, array $data = []): void
    {
        $file = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            error_log("[VIEW] File tidak ditemukan: $file");
            http_response_code(500);
            die('View tidak ditemukan: ' . htmlspecialchars($view));
        }

        extract($data, EXTR_SKIP);
        require $file;
    }

    // ==========================================
    // HTTP Status
    // ==========================================

    public static function abort(int $code = 404): void
    {
        http_response_code($code);

        $view = BASE_PATH . '/app/Views/errors/' . $code . '.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '<h1>Error ' . $code . '</h1>';
        }
        exit;
    }
}