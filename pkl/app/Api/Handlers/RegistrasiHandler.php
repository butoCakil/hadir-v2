<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;
use App\Models\WabotSessionModel;

class RegistrasiHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private string $adminNumber;
    private bool $simulatorMode;

    public function __construct(
        Database $db,
        WaSender $sender,
        WabotSessionModel $session,
        string $adminNumber,
        bool $simulatorMode = false
    ) {
        $this->db            = $db;
        $this->sender        = $sender;
        $this->session       = $session;
        $this->adminNumber   = $adminNumber;
        $this->simulatorMode = $simulatorMode;
    }

    public function handle(string $number, string $pushName, string $message): ?string
    {
        $message = preg_replace('/\s+/', ' ', trim($message));
        $message = str_replace(['"', "'", ';'], '', $message);
        $parts   = explode(' ', $message);

        if (count($parts) !== 2) {
            return "❗ *Format REG tidak valid.*\n\n📌 Gunakan format:\n`REG <spasi> NIS`\n\nContoh:\n`REG 1234`";
        }

        $nis = trim($parts[1]);

        if (!ctype_digit($nis)) {
            return "❗ *Format pesan tidak sesuai.*\n\nKata kedua harus berupa angka, tapi yang kamu kirim adalah:\n\n$nis\n\nMohon koreksi dan coba kirim ulang ya.";
        }

        // ── Cek apakah nomor pengirim ini sudah terdaftar untuk siswa lain ──
        $nomorSudahTerdaftar = $this->db->queryOne(
            "SELECT nama, nis FROM datasiswa WHERE nohp = ? LIMIT 1",
            [$number]
        );
        $gantiNomor      = false;
        $namaTerdaftar   = '';
        $nisTerdaftar    = '';
        if ($nomorSudahTerdaftar) {
            $gantiNomor    = true;
            $namaTerdaftar = $nomorSudahTerdaftar['nama'];
            $nisTerdaftar  = $nomorSudahTerdaftar['nis'];
        }

        // ── Cek NIS di database ──
        $siswa = $this->db->queryOne(
            "SELECT nama, kelas, jur, nohp FROM datasiswa WHERE nis LIKE ? LIMIT 1",
            ["%$nis%"]
        );

        if (!$siswa) {
            return "❗ *NIS $nis tidak terdaftar.*\n\nSilakan hubungi *Admin* atau *Pembimbing* untuk informasi lebih lanjut.\n\n📲 Ketik `7` atau `admin` untuk langsung menghubungi admin.";
        }

        $nama    = $siswa['nama'];
        $kelas   = $siswa['kelas'];
        $jur     = $siswa['jur'];
        $nohpLama = $siswa['nohp'];

        // ── Skenario 1: Nomor ini sudah terdaftar untuk NIS yang sama ──
        if ($nohpLama === $number) {
            return "ℹ️ *Nomor ini ($number) sudah terdaftar sebelumnya.*\n\n"
                . "👤 *Nama:* $nama  \n🏫 *Kelas:* $kelas\n\n"
                . "✅ Karena sudah terdaftar, kamu *tidak perlu mengulang pendaftaran (reg)*.\n"
                . "Silakan langsung melakukan *presensi* seperti biasa.\n\n"
                . "Terima kasih 🙏";
        }

        // ── Skenario 3: Nomor ini sudah dipakai siswa lain — tidak ada konfirmasi ──
        if ($gantiNomor) {
            return "🚫 *NOMOR INI SUDAH TERDAFTAR!*\n\n"
                . "📱 Nomor         : *$number*\n"
                . "👤 Nama terdaftar: *$namaTerdaftar*\n"
                . "🆔 NIS           : *$nisTerdaftar*\n\n"
                . "✅ Nomor ini *sudah bisa melakukan presensi* tanpa harus mendaftar ulang.\n\n"
                . "🔄 Jika kamu ingin mengganti nomor ini untuk user lain:\n"
                . "✍️ *Balas dengan ketik:*\n"
                . "`unreg` – untuk *melepaskan nomor dari data sebelumnya*\n\n"
                . "Setelah itu kamu bisa *daftar ulang kembali.*\n";
        }

        // ── Simpan pending untuk semua kasus yang butuh konfirmasi ──
        $this->session->setPending($number, [
            'type'  => 'confirm_reg',
            'nis'   => $nis,
            'nama'  => $nama,
            'kelas' => $kelas,
            'waktu' => date('Y-m-d H:i:s'),
        ]);

        // ── Skenario 2: NIS belum punya nomor sama sekali ──
        if (empty($nohpLama)) {
            $msg  = "❓ *Apakah data berikut benar milik kamu?*\n";
            $msg .= "✍️ *Balas dengan ketik:*\n";
            $msg .= "`ya` – untuk *konfirmasi dan lanjut mendaftar*\n";
            $msg .= "`tidak` – jika *data salah* dan ingin registrasi ulang*\n\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "📄 *DATA DITEMUKAN:*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "👤 Nama  : *$nama*\n";
            $msg .= "🏫 Kelas : *$kelas*\n";
            $msg .= "🆔 NIS   : *$nis*\n";
            $msg .= "🔰 Status: *Belum Terdaftar*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "⏳ *Catatan: Nomor kamu sedang DITANGGUHKAN dan TIDAK BISA PRESENSI sebelum ada konfirmasi.*\n";
            return trim($msg);
        }

        // ── Skenario 4: NIS sudah punya nomor lain ──
        $msg  = "❓ *Apakah data berikut benar milik kamu dan kamu ingin mengganti nomor?*\n";
        $msg .= "✍️ *Balas dengan ketik:*\n";
        $msg .= "`ya` – untuk *konfirmasi ganti nomor*\n";
        $msg .= "`tidak` – jika *data salah* dan ingin *registrasi ulang*\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📄 *DATA DITEMUKAN:*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 Nama  : *$nama*\n";
        $msg .= "🏫 Kelas : *$kelas*\n";
        $msg .= "🆔 NIS   : *$nis*\n";
        $msg .= "📞 Nomor yg terdaftar sebelumnya : *$nohpLama*\n";
        $msg .= "🔄 Status: *Terdaftar dengan Nomor Berbeda*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "⏳ *Catatan: Nomor kamu sedang DITANGGUHKAN dan TIDAK BISA PRESENSI sebelum ada konfirmasi.*\n";
        return trim($msg);
    }

    /**
     * Handle konfirmasi YA — update nomor + notif nomor lama
     */
    public function handleKonfirmasiYa(string $number, string $pushName, array $pending): string
    {
        $nis = $pending['nis'];

        $siswa = $this->db->queryOne(
            "SELECT nama, kelas, jur, nohp FROM datasiswa WHERE nis = ? LIMIT 1",
            [$nis]
        );
        if (!$siswa) {
            return "❌ Data tidak ditemukan. Silakan ulangi pendaftaran.";
        }

        $nama     = $siswa['nama'];
        $kelas    = $siswa['kelas'];
        $jur      = $siswa['jur'] ?? '';
        $nohpLama = $siswa['nohp'];

        $nohpBaru = WaSender::normalisasi62ke0($number);

        // Update nomor
        $this->db->execute(
            "UPDATE datasiswa SET nohp = ?, encryp = ? WHERE nis = ?",
            [$nohpBaru, $number, $nis]
        );

        // Kirim notif ke nomor lama jika berbeda
        if (!empty($nohpLama) && $nohpLama !== $nohpBaru && !$this->simulatorMode) {
            $pesanLama = "🔔 *Pemberitahuan*\n\n"
                . "Nomor kamu ini ($nohpLama) telah digantikan oleh nomor baru ($nohpBaru) atas nama:\n"
                . "👤 *$nama*\n🏫 *Kelas:* $kelas\n\n"
                . "Jika kamu merasa tidak melakukan ini, segera hubungi admin.\n"
                . "atau daftarkan kembali nomor kamu.";
            $this->sender->send($nohpLama, $pesanLama, null);
        }

        // Notif ke admin
        if (!$this->simulatorMode) {
            $adminMsg = "✅ *Registrasi Berhasil*\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Nama  : $nama\n"
                . "Kelas : $kelas\n"
                . "NIS   : $nis\n"
                . "No WA : $nohpBaru\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->sender->send($this->adminNumber, $adminMsg, null);
        }

        return "✅ *Pendaftaran Berhasil!*\n\n"
            . "Nomor *$nohpBaru* telah didaftarkan atas nama:\n"
            . "👤 *$nama*  \n🏫 *Kelas:* $kelas\n\n"
            . "Sekarang kamu bisa melakukan presensi PKL.\n\n"
            . "Ketik `1` untuk panduan presensi.";
    }
}