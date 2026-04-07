<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;
use App\Models\WabotSessionModel;

class AdminHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private string $adminNumber;

    public function __construct(Database $db, WaSender $sender, WabotSessionModel $session, string $adminNumber)
    {
        $this->db          = $db;
        $this->sender      = $sender;
        $this->session     = $session;
        $this->adminNumber = $adminNumber;
    }

    /**
     * Memulai sesi hub admin
     */
    public function mulaiSesi(string $number, string $pushName): string
    {
        $nohp62 = WaSender::normalisasi0ke62($number);
        $this->session->startAdminSession($nohp62, $pushName);

        // Info pengirim
        $info   = $this->getInfoPengirim($number);
        $dari   = $info ? $info['label'] : "Nomor Tidak Terdaftar";

        $notifAdmin = "🚨 *Permintaan Hub. Admin*\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "$dari\n"
            . "$nohp62 ~ $pushName\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🛎️ Mohon dicek dan ditanggapi segera.";

        $this->sender->send($this->adminNumber, $notifAdmin, null);

        $msgs = [
            "🧑🏻‍💻 *Anda terhubung ke Admin!*\n\n🗨️ Silakan sampaikan pertanyaan. Admin akan segera membalas.\n\nKetik `info` untuk kembali ke menu utama.",
            "🙋🏻‍♂️ *Anda terhubung ke Admin!*\n\n📬 Admin siap membantu. Ketik pesan Anda.\n\nKetik `info` untuk keluar dari sesi admin.",
        ];
        return $msgs[array_rand($msgs)];
    }

    /**
     * Handle pesan dalam sesi admin (teruskan ke admin)
     */
    public function handleSesiAktif(string $number, string $pushName, string $message, ?string $mediaUrl): ?string
    {
        $nohp62 = WaSender::normalisasi0ke62($number);
        $info   = $this->getInfoPengirim($number);
        $dari   = $info ? $info['label'] : "Nomor Tidak Terdaftar";

        $adminMsg = "$dari\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "$nohp62 ~ $pushName:\n"
            . $message . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "Sesi Hub.Admin *Aktif* ✅";

        $this->sender->send($this->adminNumber, $adminMsg, $mediaUrl);
        return null; // Tidak balas ke user, admin yang balas langsung
    }

    /**
     * Handle perintah "balas <nomor> <pesan>" dari admin
     */
    public function handleBalas(string $number, string $message, ?string $mediaUrl): ?string
    {
        // Cek apakah nomor ini adalah admin
        $nohp0 = WaSender::normalisasi62ke0($number);
        if (!in_array($nohp0, $this->getListAdmin())) {
            return null;
        }

        $parts = explode(' ', $message, 3);
        if (count($parts) < 3) {
            return "Format: `balas <nomor> <pesan>`";
        }

        $tujuan = $parts[1];
        $pesan  = $parts[2];

        $this->sender->send($tujuan, $pesan, $mediaUrl);
        return "✅ Pesan terkirim ke $tujuan";
    }

    /**
     * Akhiri sesi admin (saat user ketik info/menu)
     */
    public function akhiriSesi(string $number): void
    {
        $nohp62 = WaSender::normalisasi0ke62($number);
        $this->session->endAdminSession($nohp62);
    }

    private function getInfoPengirim(string $number): ?array
    {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);

        $siswa = $this->db->queryOne(
            "SELECT nama, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$nohp0, $nohp62]
        );
        if ($siswa) {
            return ['label' => "Dari: {$siswa['nama']}\nKelas: {$siswa['kelas']}"];
        }

        $pembimbing = $this->db->queryOne(
            "SELECT nama FROM datapembimbing WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$nohp0, $nohp62]
        );
        if ($pembimbing) {
            return ['label' => "Dari Pembimbing: {$pembimbing['nama']}"];
        }

        return null;
    }

    private function getListAdmin(): array
    {
        $config = require BASE_PATH . '/config/app.php';
        $admin  = WaSender::normalisasi62ke0($config['wa']['admin_number'] ?? '');
        return array_filter([$admin]);
    }
}
