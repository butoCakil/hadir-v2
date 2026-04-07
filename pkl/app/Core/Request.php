<?php

namespace App\Core;

class Request
{
    // ==========================================
    // Method & URL
    // ==========================================

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // ==========================================
    // GET params — dari URL query string
    // Contoh: /siswa?kelas=XII
    // ==========================================

    public static function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? self::sanitize($_GET[$key]) : $default;
    }

    public static function allGet(): array
    {
        return array_map([self::class, 'sanitize'], $_GET);
    }

    // ==========================================
    // POST params — dari form submit
    // ==========================================

    public static function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? self::sanitize($_POST[$key]) : $default;
    }

    public static function allPost(): array
    {
        return array_map([self::class, 'sanitize'], $_POST);
    }

    // ==========================================
    // Input — otomatis deteksi GET atau POST
    // ==========================================

    public static function input(string $key, mixed $default = null): mixed
    {
        if (isset($_POST[$key])) {
            return self::sanitize($_POST[$key]);
        }
        if (isset($_GET[$key])) {
            return self::sanitize($_GET[$key]);
        }
        return $default;
    }

    // ==========================================
    // File Upload
    // ==========================================

    public static function file(string $key): array|null
    {
        return $_FILES[$key] ?? null;
    }

    public static function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ==========================================
    // Validasi sederhana
    // ==========================================

    /**
     * Pastikan field yang dibutuhkan ada dan tidak kosong
     * Return array error, kosong jika valid
     *
     * Contoh:
     * $errors = Request::validate(['username', 'password']);
     * if (!empty($errors)) { ... }
     */
    public static function validate(array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            $value = self::input($field);
            if ($value === null || $value === '') {
                $errors[$field] = "Field '$field' tidak boleh kosong.";
            }
        }
        return $errors;
    }

    // ==========================================
    // JSON body (untuk API/webhook)
    // ==========================================

    public static function json(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    // ==========================================
    // Sanitasi input
    // ==========================================

    private static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }

        // Trim whitespace
        $value = trim($value);

        // Hapus null bytes
        $value = str_replace("\0", '', $value);

        return $value;
    }

    // ==========================================
    // Helper: ambil IP user
    // ==========================================

    public static function ip(): string
    {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';
    }
}