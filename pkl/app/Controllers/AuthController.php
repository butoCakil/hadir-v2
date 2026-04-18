<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class AuthController
{
    // ==========================================
    // GET /login
    // ==========================================

    public function showLogin(): void
    {
        // Jika sudah login, redirect ke dashboard
        Auth::guest();

        Response::view('auth/login', [
            'title'     => 'Login',
            'csrfField' => Auth::csrfField(),
        ]);
    }

    // ==========================================
    // POST /login
    // ==========================================

    public function login(): void
    {
        Auth::guest();
        Auth::verifyCsrf();

        $username = Request::post('username');
        $password = Request::post('password');

        // Validasi input tidak kosong
        if (empty($username) || empty($password)) {
            Response::view('auth/login', [
                'title'     => 'Login',
                'error'     => 'Username dan password tidak boleh kosong.',
                'csrfField' => Auth::csrfField(),
            ]);
            return;
        }

        // Cari user di database
        $db   = Database::getInstance();
        $user = $db->queryOne(
            "SELECT id, username, password, akses FROM user WHERE username = ? LIMIT 1",
            [$username]
        );

        // Verifikasi password dengan bcrypt
        if (!$user || !password_verify($password, $user['password'])) {
            // Delay 1 detik untuk cegah brute force
            sleep(1);

            Response::view('auth/login', [
                'title'     => 'Login',
                'error'     => 'Username atau password salah.',
                'csrfField' => Auth::csrfField(),
            ]);
            return;
        }

        // Login berhasil — simpan session
        Auth::login([
            'id'       => $user['id'],
            'username' => $user['username'],
            'akses'    => $user['akses'],
        ]);

        // Simpan akses ke session
        $_SESSION['akses'] = $user['akses'];

        // Redirect ke halaman sebelumnya atau dashboard
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);

        Response::redirect($redirect);
    }

    // ==========================================
    // GET /logout
    // ==========================================

    public function logout(): void
    {
        Auth::logout();
        // Response::redirect('/home');
        Response::redirect('https://pklbos.smknbansari.sch.id');
    }
}