<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

class PengaturanController
{
    private Database $db;

    public function __construct()
    {
        Auth::required();
        $this->db = Database::getInstance();
    }

    private function getSetting(string $key, string $default = '0'): string
    {
        $row = $this->db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }

    private function setSetting(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO pengaturan (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = ?",
            [$key, $value, $value]
        );
    }

    // ==========================================
    // GET /pengaturan
    // ==========================================
    public function index(): void
    {
        $settings = [
            'toleransi_sebelum'      => $this->getSetting('toleransi_sebelum', '0'),
            'toleransi_sesudah'      => $this->getSetting('toleransi_sesudah', '0'),
            'notif_siswa_aktif'      => $this->getSetting('notif_siswa_aktif', '1'),
            'notif_siswa_jam'        => $this->getSetting('notif_siswa_jam', '16:00'),
            'notif_alert_aktif'      => $this->getSetting('notif_alert_aktif', '1'),
            'notif_alert_jam'        => $this->getSetting('notif_alert_jam', '10:00'),
            'notif_pembimbing_aktif' => $this->getSetting('notif_pembimbing_aktif', '1'),
            'notif_pembimbing_jam'   => $this->getSetting('notif_pembimbing_jam', '08:00'),
            'notif_pembimbing_hari'  => $this->getSetting('notif_pembimbing_hari', '1'),
            'notif_walikelas_aktif'  => $this->getSetting('notif_walikelas_aktif', '1'),
            'notif_walikelas_jam'    => $this->getSetting('notif_walikelas_jam', '08:00'),
            'notif_walikelas_hari'   => $this->getSetting('notif_walikelas_hari', '1'),
            // Gateway
            'gateway_wa_mode'        => $this->getSetting('gateway_wa_mode',  'auto'),
            'gateway_wa_aktif'       => $this->getSetting('gateway_wa_aktif', '1'),
            'gateway_web_mode'       => $this->getSetting('gateway_web_mode', 'auto'),
            'gateway_web_aktif'      => $this->getSetting('gateway_web_aktif','1'),
        ];

        $periodeAktif = $this->db->queryOne("SELECT * FROM periode_pkl WHERE aktif = 1 LIMIT 1");

        $config      = require BASE_PATH . '/config/app.php';
        $webhookUrl  = rtrim($config['url'], '/') . '/api/webhook.php';
        $deviceIdRaw = $config['wa']['device_id'] ?? '';
        $adminNoRaw  = $config['wa']['admin_number'] ?? '';

        // Sensor: tampilkan sebagian saja
        $deviceIdMasked = $this->maskValue($deviceIdRaw);
        $adminNoMasked  = $this->maskValue($adminNoRaw);

        Response::view('pengaturan/index', [
            'title'           => 'Pengaturan',
            'user'            => Auth::user(),
            'settings'        => $settings,
            'periodeAktif'    => $periodeAktif,
            'webhookUrl'      => $webhookUrl,
            'deviceIdMasked'  => $deviceIdMasked,
            'adminNoMasked'   => $adminNoMasked,
        ]);
    }

    // ==========================================
    // POST /pengaturan/simpan
    // ==========================================
    public function simpan(): void
    {
        $toleransiSebelum = max(0, (int)($_POST['toleransi_sebelum'] ?? 0));
        $toleransiSesudah = max(0, (int)($_POST['toleransi_sesudah'] ?? 0));

        $this->setSetting('toleransi_sebelum', (string)$toleransiSebelum);
        $this->setSetting('toleransi_sesudah', (string)$toleransiSesudah);

        Response::success([], 'Pengaturan berhasil disimpan.');
    }

    // ==========================================
    // POST /pengaturan/gateway
    // ==========================================
    public function gateway(): void
    {
        $waMode   = $_POST['gateway_wa_mode']   ?? 'auto';
        $waAktif  = isset($_POST['gateway_wa_aktif'])  ? '1' : '0';
        $webMode  = $_POST['gateway_web_mode']  ?? 'auto';
        $webAktif = isset($_POST['gateway_web_aktif']) ? '1' : '0';

        if (!in_array($waMode,  ['auto', 'manual'])) $waMode  = 'auto';
        if (!in_array($webMode, ['auto', 'manual'])) $webMode = 'auto';

        $this->setSetting('gateway_wa_mode',   $waMode);
        $this->setSetting('gateway_wa_aktif',  $waAktif);
        $this->setSetting('gateway_web_mode',  $webMode);
        $this->setSetting('gateway_web_aktif', $webAktif);

        Response::success([], 'Pengaturan gateway berhasil disimpan.');
    }

    // ==========================================
    // POST /pengaturan/notifikasi
    // ==========================================
    public function notifikasi(): void
    {
        $keys = [
            'notif_siswa_aktif',
            'notif_siswa_jam',
            'notif_alert_aktif',
            'notif_alert_jam',
            'notif_pembimbing_aktif',
            'notif_pembimbing_jam',
            'notif_pembimbing_hari',
            'notif_walikelas_aktif',
            'notif_walikelas_jam',
            'notif_walikelas_hari',
        ];

        foreach ($keys as $key) {
            $value = trim($_POST[$key] ?? '0');

            // Validasi jam format H:i
            if (str_ends_with($key, '_jam')) {
                if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
                    Response::error("Format jam tidak valid untuk $key.", 400); return;
                }
            }

            // Validasi hari 1-7
            if (str_ends_with($key, '_hari')) {
                $value = (string)max(1, min(7, (int)$value));
            }

            // Validasi aktif 0/1
            if (str_ends_with($key, '_aktif')) {
                $value = in_array($value, ['0','1']) ? $value : '0';
            }

            $this->setSetting($key, $value);
        }

        Response::success([], 'Pengaturan notifikasi berhasil disimpan.');
    }

    // ==========================================
    // POST /pengaturan/password
    // ==========================================
    public function gantiPassword(): void
    {
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $konfirmasi   = $_POST['konfirmasi']     ?? '';

        if (!$passwordLama || !$passwordBaru || !$konfirmasi) {
            Response::error('Semua field wajib diisi.', 400); return;
        }
        if (strlen($passwordBaru) < 8) {
            Response::error('Password baru minimal 8 karakter.', 400); return;
        }
        if ($passwordBaru !== $konfirmasi) {
            Response::error('Konfirmasi password tidak cocok.', 400); return;
        }

        $user = Auth::user();
        $row  = $this->db->queryOne("SELECT password FROM user WHERE username = ?", [$user['username']]);

        if (!$row || !password_verify($passwordLama, $row['password'])) {
            Response::error('Password lama tidak benar.', 400); return;
        }

        $hash = password_hash($passwordBaru, PASSWORD_BCRYPT);
        $this->db->query("UPDATE user SET password = ? WHERE username = ?", [$hash, $user['username']]);

        Response::success([], 'Password berhasil diubah. Silakan login ulang.');
    }

    // ==========================================
    // Helper: sensor nilai (tampilkan 4 char awal, sisanya *)
    // ==========================================
    // Sensor Device ID: 4 awal + 6 akhir, tengah bintang (pertahankan tanda -)
    private function maskValue(string $value): string
    {
        // Cek apakah UUID (ada tanda -)
        if (str_contains($value, '-')) {
            $parts = explode('-', $value); // misal: 933bbd2c-8931-421c-8432-8e1ba9b3d795
            $raw   = str_replace('-', '', $value);
            $len   = strlen($raw);
            if ($len <= 10) return $value;
            $masked = substr($raw, 0, 4) . str_repeat('*', $len - 10) . substr($raw, -6);
            // Sisipkan kembali tanda - sesuai posisi UUID (8-4-4-4-12)
            $pos    = [8, 12, 16, 20];
            $result = '';
            $offset = 0;
            for ($i = 0; $i < strlen($masked); $i++) {
                if (in_array($i, $pos)) $result .= '-';
                $result .= $masked[$i];
            }
            return $result;
        }

        // Nomor/string biasa: 4 awal + 4 akhir
        $len = strlen($value);
        if ($len <= 8) return str_repeat('*', $len);
        return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
    }

    // ==========================================
    // POST /pengaturan/wa-config
    // ==========================================
    public function updateWaConfig(): void
    {
        $deviceId   = trim($_POST['wa_device_id']    ?? '');
        $adminNumber = trim($_POST['wa_admin_number'] ?? '');

        if (empty($deviceId) || empty($adminNumber)) {
            Response::error('Device ID dan Nomor Admin tidak boleh kosong.'); return;
        }

        $adminNumber = preg_replace('/\D/', '', $adminNumber);

        $envPath = BASE_PATH . '/.env';
        if (!file_exists($envPath) || !is_writable($envPath)) {
            Response::error('File .env tidak dapat diakses atau ditulis.'); return;
        }

        $content = file_get_contents($envPath);

        // Replace nilai WA_DEVICE_ID dan WA_ADMIN_NUMBER
        $content = preg_replace('/^WA_DEVICE_ID=.*/m',    'WA_DEVICE_ID=' . $deviceId,    $content);
        $content = preg_replace('/^WA_ADMIN_NUMBER=.*/m', 'WA_ADMIN_NUMBER=' . $adminNumber, $content);

        file_put_contents($envPath, $content);

        Response::success([], 'Konfigurasi WA berhasil disimpan. Reload halaman untuk melihat perubahan.');
    }
}
