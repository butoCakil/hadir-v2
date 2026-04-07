<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class JurnalHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function handle(string $number, string $message): ?string
    {
        $parts  = explode(' ', trim($message));
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);

        // jurnal saja — siswa lihat jurnalnya sendiri
        if (count($parts) == 1) {
            $siswa = $this->db->queryOne(
                "SELECT nis, nama, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
                [$nohp0, $nohp62]
            );

            if ($siswa) {
                return $this->buildJurnal($siswa['nis'], $siswa['nama'], $siswa['kelas']);
            }

            // Cek pembimbing
            $pembimbing = $this->db->queryOne(
                "SELECT id FROM datapembimbing WHERE nohp = ? LIMIT 1",
                [$nohp62]
            );
            if ($pembimbing) {
                return "Format perintah untuk pembimbing: `jurnal <nis/noHP>`";
            }

            return "Nomor tidak terdaftar sebagai siswa atau pembimbing.";
        }

        // jurnal <NIS/nohp> — pembimbing lihat jurnal siswa
        if (count($parts) == 2) {
            $queryKey = $parts[1];

            // Cek apakah pengirim adalah pembimbing
            $pembimbing = $this->db->queryOne(
                "SELECT id FROM datapembimbing WHERE nohp = ? LIMIT 1",
                [$nohp62]
            );

            if (!$pembimbing) {
                return "Anda bukan pembimbing PKL. Nomor tidak terdaftar sebagai pembimbing.";
            }

            $siswa = $this->db->queryOne(
                "SELECT nis, nama, kelas FROM datasiswa WHERE nis = ? OR nohp = ? LIMIT 1",
                [$queryKey, $queryKey]
            );

            if (!$siswa) {
                return "Data siswa tidak ditemukan dengan NIS/noHP: $queryKey";
            }

            return $this->buildJurnal($siswa['nis'], $siswa['nama'], $siswa['kelas']);
        }

        return "Format perintah jurnal salah.\nGunakan:\n1. `jurnal`\n2. `jurnal <nis/noHP>`";
    }

    private function buildJurnal(string $nis, string $nama, string $kelas): string
    {
        $rows = $this->db->query(
            "SELECT ket, catatan, timestamp FROM presensi WHERE nis = ? ORDER BY timestamp ASC",
            [$nis]
        );

        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];

        $text  = "```\n";
        $text .= "Rekap Jurnal PKL\n";
        $text .= "Nama : $nama\n";
        $text .= "NIS  : $nis\n";
        $text .= "Kelas: $kelas\n\n";
        $text .= sprintf("%-4s %-11s %-6s %-3s %s\n", "No.", "Tanggal", "Jam", "Ket", "Catatan");
        $text .= str_repeat("-", 40) . "\n";

        if (empty($rows)) {
            $text .= "Belum ada data presensi.\n```";
            return $text;
        }

        $no = 1;
        foreach ($rows as $row) {
            $dt  = new \DateTime($row['timestamp']);
            $tgl = $dt->format('d-m-Y');
            $jam = $dt->format('H:i');
            $ket = strtolower($row['ket']);
            $ikon = $ikonMap[$ket] ?? '❓';
            $catatan = $row['catatan'] ?? '';

            $maxFirst = 15;
            $maxNext  = 35;
            $indent   = 5;

            $firstLine = $this->cutWord($catatan, $maxFirst);
            $rest      = mb_substr($catatan, mb_strlen($firstLine));
            $wrapped   = wordwrap(trim($rest), $maxNext, "\n", false);
            $restLines = $wrapped ? explode("\n", $wrapped) : [];

            $text .= sprintf("%-4d %-11s %-6s %-3s %s\n", $no, $tgl, $jam, $ikon, $firstLine);
            foreach ($restLines as $line) {
                $text .= str_repeat(" ", $indent) . $line . "\n";
            }
            $no++;
        }

        $text .= "\nKeterangan:\n✅ = Masuk\n🔵 = Izin\n🟡 = Sakit\n🔴 = Libur\n";
        $text .= "```";
        return $text;
    }

    private function cutWord(string $text, int $maxLen): string
    {
        if (mb_strlen($text) <= $maxLen) return $text;
        $cut = mb_substr($text, 0, $maxLen);
        $pos = mb_strrpos($cut, ' ');
        return $pos !== false ? mb_substr($cut, 0, $pos) : $cut;
    }
}
