<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class InputHandler
{
    private Database $db;
    private WaSender $sender;
    private string $awalPKL = '2025-07-01';
    private string $akhirPKL = '2025-12-31';

    public function __construct(Database $db, WaSender $sender)
    {
        $this->db     = $db;
        $this->sender = $sender;
    }

    public function handle(string $number, string $message): ?string
    {
        $parts = preg_split('/\s+/', trim($message));

        if (count($parts) < 3) {
            return "⚠️ *Format Salah!*\n\n"
                . "📅 *Untuk hari ini:*\n"
                . "`input <nis/noHP> <keterangan>`\n\n"
                . "📆 *Untuk rentang tanggal:*\n"
                . "`input <nis/noHP> <keterangan> <tgl_awal> <tgl_akhir>`\n\n"
                . "ℹ️ *Keterangan:* masuk, izin, sakit, libur.";
        }

        // Cek apakah pengirim adalah pembimbing
        $nohp62 = WaSender::normalisasi0ke62($number);
        $pembimbing = $this->db->queryOne(
            "SELECT kode FROM datapembimbing WHERE nohp = ? LIMIT 1",
            [$nohp62]
        );

        if (!$pembimbing) {
            return "⚠️ *Akses Ditolak*\n"
                . "Nomor Anda ($nohp62) tidak terdaftar sebagai *Pembimbing* dalam sistem.\n\n"
                . "📌 Jika Anda merasa ini keliru, silakan hubungi admin untuk verifikasi.";
        }

        $kodePembimbing = $pembimbing['kode'];
        $targetId   = trim($parts[1]);
        $keterangan = strtolower(trim($parts[2]));
        $validKet   = ['masuk', 'izin', 'sakit', 'libur'];

        if (!in_array($keterangan, $validKet)) {
            return "⚠️ Keterangan '$keterangan' tidak valid!\n📌 Gunakan salah satu dari: masuk, izin, sakit, libur.\n📌 Pastikan ejaan benar.";
        }

        // Ambil data siswa
        $siswa = $this->db->queryOne(
            "SELECT nis, nama, kelas, nohp FROM datasiswa WHERE nis = ? OR nohp = ? LIMIT 1",
            [$targetId, $targetId]
        );

        if (!$siswa) {
            return "⚠️ *Data Tidak Ditemukan*\n"
                . "Tidak ada siswa dengan NIS/NoHP: `$targetId`.\n\n"
                . "📌 Pastikan NIS/NoHP yang dimasukkan benar.";
        }

        $nis       = $siswa['nis'];
        $namaSiswa = $siswa['nama'];
        $kelas     = $siswa['kelas'];
        $nohpSiswa = $siswa['nohp'];

        // Tentukan daftar tanggal
        $tanggalList = [];

        if (count($parts) == 3) {
            $tanggalList[] = date('Y-m-d');
        } elseif (count($parts) == 4) {
            $tgl = $this->parseTanggal($parts[3]);
            if (!$tgl) return "❌ Format tanggal tidak valid: `{$parts[3]}`\nGunakan format: DD-MM-YYYY";
            $tanggalList[] = $tgl;
        } elseif (count($parts) >= 5) {
            $tglAwal  = $this->parseTanggal($parts[3]);
            $tglAkhir = $this->parseTanggal($parts[4]);
            if (!$tglAwal || !$tglAkhir) return "Format tanggal tidak valid.";
            if ($tglAkhir < $tglAwal) return "Tanggal akhir tidak boleh sebelum tanggal awal.";
            if ($tglAwal < $this->awalPKL || $tglAkhir > $this->akhirPKL) {
                return "Tanggal di luar rentang yang diizinkan ({$this->awalPKL} - {$this->akhirPKL}).";
            }
            $cur = strtotime($tglAwal);
            $end = strtotime($tglAkhir);
            while ($cur <= $end) {
                $tanggalList[] = date('Y-m-d', $cur);
                $cur = strtotime('+1 day', $cur);
            }
        }

        // Proses tiap tanggal
        $resultList = [];
        foreach ($tanggalList as $tgl) {
            $existing = $this->db->queryOne(
                "SELECT ket FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
                [$nis, $tgl]
            );

            if (!$existing) {
                $ts = $tgl . " 07:00:00";
                $this->db->execute(
                    "INSERT INTO presensi (nis, namasiswa, kelas, ket, catatan, link, kode, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$nis, $namaSiswa, $kelas, $keterangan, '', '', $kodePembimbing, $ts]
                );
                $resultList[] = ['tanggal' => $this->formatTanggalIndo($tgl), 'status' => ucfirst($keterangan), 'lama' => null];
            } else {
                $resultList[] = ['tanggal' => $this->formatTanggalIndo($tgl), 'status' => 'Sudah terisi', 'lama' => ucfirst($existing['ket'])];
            }
        }

        if (empty($resultList)) {
            return "⚠️ *Tidak Ada Presensi Baru*\nData presensi mungkin sudah tercatat sebelumnya.";
        }

        $sendmsg  = "✅ *Presensi Berhasil Dicatat*\n";
        $sendmsg .= "──────────────────────\n";
        $sendmsg .= "👤 *Nama*  : $namaSiswa\n";
        $sendmsg .= "🆔 *NIS*   : $nis\n";
        $sendmsg .= "📅 *Detail Presensi Baru:*\n";

        $no = 1;
        foreach ($resultList as $row) {
            if ($row['lama'] === null) {
                $sendmsg .= "$no. {$row['tanggal']} — {$row['status']}\n";
            } else {
                $sendmsg .= "$no. {$row['tanggal']} — {$row['status']} ({$row['lama']})\n";
            }
            $no++;
        }

        // Notif ke siswa
        if ($nohpSiswa) {
            $this->sender->send($nohpSiswa, $sendmsg, null);
        }

        return $sendmsg;
    }

    private function parseTanggal(string $raw): ?string
    {
        $raw = str_replace(['/', '.'], '-', $raw);
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2,4})$/', $raw, $m)) {
            $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $y = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            return checkdate((int)$mo, (int)$d, (int)$y) ? "$y-$mo-$d" : null;
        }
        return null;
    }

    private function formatTanggalIndo(string $tgl): string
    {
        $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $d = date('j', strtotime($tgl));
        $m = (int)date('n', strtotime($tgl));
        $y = date('Y', strtotime($tgl));
        return "$d {$bulan[$m]} $y";
    }
}
