<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class SetHandler
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
        $this->adminNumbers = [WaSender::normalisasi62ke0($adminNumber)];
    }

    public function handle(string $number, string $pushName, string $message): ?string
    {
        if (!in_array($number, $this->adminNumbers)) {
            return "🚫 Perintah `set` hanya dapat digunakan oleh admin.";
        }

        // Format: set <NIS> <nohp>
        if (!preg_match('/^set\s+(\d{4,})\s+([\d\s\+\-]+)/i', $message, $matches)) {
            return "❌ Format tidak valid.\n\nGunakan: `set <NIS> <NoHP>`\nContoh: `set 1234 08812345678`";
        }

        $nis       = trim($matches[1]);
        $nohpInput = preg_replace('/[\s\-]/', '', trim($matches[2]));

        // Normalisasi nomor
        if (str_starts_with($nohpInput, '+62')) {
            $nohpInput = '0' . substr($nohpInput, 3);
        } elseif (str_starts_with($nohpInput, '62')) {
            $nohpInput = '0' . substr($nohpInput, 2);
        }

        // Validasi format nomor
        if (!preg_match('/^08\d{7,12}$/', $nohpInput)) {
            return "❌ Format nomor HP tidak valid.\nGunakan contoh: `set 1234 08812345678`";
        }

        // Cek NIS di database
        $siswa = $this->db->queryOne(
            "SELECT nama, kelas, jur, nohp FROM datasiswa WHERE nis = ? LIMIT 1",
            [$nis]
        );

        if (!$siswa) {
            return "⚠️ NIS *$nis* tidak ditemukan dalam database.";
        }

        // Update nohp dan encryp
        $affected = $this->db->execute(
            "UPDATE datasiswa SET nohp = ?, encryp = ? WHERE nis = ?",
            [$nohpInput, $number, $nis]
        );

        if ($affected !== false) {
            $nama  = $siswa['nama'];
            $kelas = $siswa['kelas'];
            $jur   = $siswa['jur'];

            // Notif ke nomor baru (siswa)
            $notifSiswa = "✅ *Data nomor WA berhasil diperbaiki oleh Admin!*\n\n"
                . "👤 Nama    : $nama\n"
                . "🏫 Kelas   : $kelas\n"
                . "🆔 NIS     : $nis\n"
                . "📱 No. HP  : $nohpInput\n\n"
                . "Silakan lakukan presensi seperti biasa.";
            $this->sender->send($nohpInput, $notifSiswa, null);

            return "✅ Data berhasil diperbaiki!\n\nNama: *$nama*\nKelas: *$kelas*\nJurusan: *$jur*\nNo. HP: *$nohpInput*\n\nSiswa sudah bisa melakukan presensi.";
        }

        return "❌ Gagal menyimpan data. Coba lagi nanti.";
    }
}
