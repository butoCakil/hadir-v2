<?php

namespace App\Core;

class Auth
{
    private static bool $started = false;

    // ==========================================
    // Session Initialization
    // ==========================================

    public static function start(): void
    {
        if (self::$started) return;

        $config = require BASE_PATH . '/config/app.php';

        session_name($config['session']['name']);

        session_set_cookie_params([
            'lifetime' => $config['session']['lifetime'],
            'path'     => '/',
            'secure'   => true,   // hanya HTTPS
            'httponly' => true,   // tidak bisa diakses JavaScript
            'samesite' => 'Strict',
        ]);

        session_start();
        self::$started = true;

        // Regenerate session ID setiap 30 menit untuk cegah session fixation
        if (!isset($_SESSION['_last_regenerate'])) {
            self::regenerate();
        } elseif (time() - $_SESSION['_last_regenerate'] > 1800) {
            self::regenerate();
        }
    }

    private static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regenerate'] = time();
    }

    // ==========================================
    // Login & Logout
    // ==========================================

    public static function login(array $user): void
    {
        self::regenerate();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['logged_in'] = true;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();

        // Hapus cookie session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
    }

    // ==========================================
    // Cek Status Login
    // ==========================================

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function user(): array|null
    {
        self::start();
        if (!self::check()) return null;

        return [
            'id'       => $_SESSION['user_id']  ?? null,
            'username' => $_SESSION['username']  ?? null,
        ];
    }

    // ==========================================
    // Middleware — pakai di awal setiap Controller
    // ==========================================

    /**
     * Paksa login — redirect ke halaman login jika belum login
     * Contoh pemakaian: Auth::required();
     */
    public static function required(): void
    {
        self::start();
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            Response::redirect('/login');
        }
    }

    /**
     * Paksa guest — redirect ke dashboard jika sudah login
     * Contoh pemakaian di halaman login: Auth::guest();
     */
    public static function guest(): void
    {
        self::start();
        if (self::check()) {
            Response::redirect('/');
        }
    }

    // ==========================================
    // CSRF Protection
    // ==========================================

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(): void
    {
        self::start();
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }
}