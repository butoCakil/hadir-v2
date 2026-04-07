<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class UnregHandler
{
    private Database $db;
    private bool $simulatorMode;

    public function __construct(Database $db, bool $simulatorMode = false)
    {
        $this->db            = $db;
        $this->simulatorMode = $simulatorMode;
    }

    public function handle(string $number, string $message): ?string
    {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);

        $siswa = $this->db->queryOne(
            "SELECT nama, kelas, nis, nohp FROM datasiswa WHERE nohp LIKE ? LIMIT 1",
            ["%$nohp0%"]
        );

        if (!$siswa) {
            return "❗ Nomor WA ini belum terdaftar.\n\nUntuk mendaftarkan nomor,\nBalas dengan ketik `reg<spasi>NIS`\n\nAtau silakan hubungi Admin atau Pembimbing jika ada kendala.";
        }

        $nama     = $siswa['nama'];
        $kelas    = $siswa['kelas'];
        $nis      = $siswa['nis'];
        $nohpLama = $siswa['nohp'];

        if (empty($nohpLama)) {
            return "📄 Data Siswa:\n\n👤 Nama  : *$nama*\n🏫 Kelas : *$kelas*\n🆔 NIS   : *$nis*\n\n❗ Nomor WA Anda belum terdaftar dalam sistem presensi.\n\nSilakan daftar dengan cara mengetik:\nREG<spasi>NIS\n\nJika mengalami kendala, hubungi admin untuk bantuan.";
        }

        // Hapus (set NULL) nomor HP — skip jika simulator
        if (!$this->simulatorMode) {
            $this->db->execute(
                "UPDATE datasiswa SET nohp = NULL WHERE nohp LIKE ?",
                ["%$nohpLama%"]
            );
        }

        $variasiPesan = [
            "✅ Nomor WA *($nohpLama)* berhasil dihapus dari sistem.",
            "🗑️ Nomor *$nohpLama* telah dihapus dari data presensi.",
            "Nomor lama *$nohpLama* sudah tidak terdaftar lagi.",
            "⚠️ Nomor *$nohpLama* sudah dihapus. Tidak bisa digunakan untuk presensi.",
            "✂️ Nomor *$nohpLama* telah dicabut dari sistem presensi.",
            "✔️ Penghapusan nomor *$nohpLama* berhasil.",
            "Nomor WA *$nohpLama* sudah kami hapus sesuai permintaan.",
            "ℹ️ Info: Nomor *$nohpLama* tidak lagi terhubung ke sistem presensi.",
            "🎯 Selesai. Nomor *$nohpLama* sudah dihapus dari sistem.",
            "🧹 Nomor WA *$nohpLama* telah dibersihkan dari database.",
        ];

        return $variasiPesan[array_rand($variasiPesan)];
    }
}