<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class BatalHandler
{
    private Database $db;
    private WaSender $sender;
    private string $adminNumber;
    private array $adminNumbers;

    public function __construct(Database $db, WaSender $sender, string $adminNumber)
    {
        $this->db           = $db;
        $this->sender       = $sender;
        $this->adminNumber  = $adminNumber;

        // Daftar nomor admin (format 08xxx)
        $this->adminNumbers = [
            WaSender::normalisasi62ke0($adminNumber),
        ];
    }

    /**
     * Handle pesan "batal <tanggal>" atau "batal <nohp> <tanggal>"
     */
    public function handle(string $number, string $pushName, string $message): ?string
    {
        $message = preg_replace('/\s+/', ' ', trim($message));
        $parts   = explode(' ', $message);
        $isAdmin = in_array($number, $this->adminNumbers);

        // Ketik "batal" saja — tampilkan panduan
        if (count($parts) === 1) {
            return $this->pesanPanduan($isAdmin);
        }

        // Siswa: batal <tanggal>
        if (count($parts) === 2) {
            $ambilTanggal = $parts[1];
            $tanggal      = $this->parseTanggal($ambilTanggal);

            if (!$tanggal) {
                return "❌ Format tanggal tidak valid.\n\n"
                    . "Gunakan format: `DD-MM-YYYY`\n"
                    . "Contoh: `batal 27-07-2025`";
            }

            // Cari siswa berdasarkan nomor
            $siswa = $this->db->queryOne(
                "SELECT nama, nis, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
                [$number, WaSender::normalisasi0ke62($number)]
            );

            if (!$siswa) {
                return "❌ Nomor Anda tidak terdaftar.\n\n"
                    . "Daftar dengan format:\n`REG<spasi>NIS`";
            }

            $nis   = $siswa['nis'];
            $nama  = $siswa['nama'];
            $kelas = $siswa['kelas'];

            $affected = $this->db->execute(
                "DELETE FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
                [$nis, $tanggal]
            );

            if ($affected > 0) {
                // Notif ke admin
                $nohp62 = WaSender::normalisasi0ke62($number);
                $notif  = "🗑️ *Batal Presensi*\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
                    . "Dari: $nama ($kelas)\n"
                    . "$nohp62 ~ $pushName\n"
                    . "Tanggal: $ambilTanggal\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
                $this->sender->send($this->adminNumber, $notif, null);

                return "✅ Presensi tanggal *$ambilTanggal* berhasil dibatalkan.\n\n"
                    . "👤 Nama  : $nama\n"
                    . "🏫 Kelas : $kelas\n"
                    . "🆔 NIS   : $nis\n\n"
                    . "Silakan lakukan presensi ulang jika diperlukan.";
            } else {
                return "⚠️ Tidak ada presensi atas nama *$nama* ($kelas) di tanggal *$ambilTanggal*.\n"
                    . "Silakan periksa kembali.";
            }
        }

        // Admin: batal <nohp_siswa> <tanggal>
        if (count($parts) === 3) {
            if (!$isAdmin) {
                return "❌ Akses ditolak.\n\n"
                    . "Hanya admin yang bisa membatalkan presensi siswa lain.\n\n"
                    . "Untuk membatalkan presensi sendiri:\n"
                    . "`batal <tanggal>`\n"
                    . "Contoh: `batal 27-07-2025`";
            }

            $nohpTarget   = WaSender::normalisasi62ke0($parts[1]);
            $ambilTanggal = $parts[2];
            $tanggal      = $this->parseTanggal($ambilTanggal);

            if (!$tanggal) {
                return "❌ Format tanggal tidak valid.\n\n"
                    . "Gunakan: `batal <nohp_siswa> <tanggal>`\n"
                    . "Contoh: `batal 08123456789 27-07-2025`";
            }

            $siswa = $this->db->queryOne(
                "SELECT nama, nis, kelas FROM datasiswa WHERE nohp = ? LIMIT 1",
                [$nohpTarget]
            );

            if (!$siswa) {
                return "❌ Nomor HP siswa tidak ditemukan: *$nohpTarget*";
            }

            $nis   = $siswa['nis'];
            $nama  = $siswa['nama'];
            $kelas = $siswa['kelas'];

            $affected = $this->db->execute(
                "DELETE FROM presensi WHERE nis = ? AND DATE(timestamp) = ?",
                [$nis, $tanggal]
            );

            if ($affected > 0) {
                // Notif ke siswa
                $notifSiswa = "✅ Data presensi tanggal *$ambilTanggal* telah dibatalkan oleh Admin.\n\n"
                    . "👤 Nama  : $nama\n"
                    . "🏫 Kelas : $kelas\n"
                    . "🆔 NIS   : $nis\n\n"
                    . "🛠️ Silakan lakukan presensi ulang jika diperlukan.";
                $this->sender->send($nohpTarget, $notifSiswa, null);

                return "✅ Presensi *$nama* (NIS: $nis, Kelas: $kelas)\n"
                    . "pada tanggal *$ambilTanggal* berhasil dibatalkan.";
            } else {
                return "⚠️ Tidak ada presensi atas nama *$nama* ($kelas) di tanggal *$ambilTanggal*.\n"
                    . "Silakan periksa kembali.";
            }
        }

        return $this->pesanPanduan($isAdmin);
    }

    private function parseTanggal(string $raw): ?string
    {
        // DD-MM-YYYY atau DD/MM/YYYY atau DD.MM.YYYY
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2,4})$/', $raw, $m)) {
            $d  = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $y  = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            return checkdate((int)$mo, (int)$d, (int)$y) ? "$y-$mo-$d" : null;
        }
        return null;
    }

    private function pesanPanduan(bool $isAdmin): string
    {
        if ($isAdmin) {
            return "ℹ️ *Format Batal / Hapus Presensi*\n\n"
                . "Sebagai Admin, tersedia dua format:\n\n"
                . "1️⃣ Batalkan presensi siswa:\n"
                . "`batal <nomor_wa_siswa> <tanggal>`\n"
                . "Contoh: `batal 6281234567890 27-07-2025`\n\n"
                . "2️⃣ Batalkan presensi sendiri:\n"
                . "`batal <tanggal>`\n"
                . "Contoh: `batal 27-07-2025`\n\n"
                . "📝 Format tanggal: DD-MM-YYYY";
        }

        return "ℹ️ *Format Batal / Hapus Presensi*\n\n"
            . "📌 Gunakan format:\n"
            . "`batal <tanggal>`\n\n"
            . "Contoh: `batal 27-07-2025`\n\n"
            . "📝 Format tanggal: DD-MM-YYYY";
    }
}