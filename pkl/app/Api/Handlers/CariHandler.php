<?php

namespace App\Api\Handlers;

use App\Core\Database;

class CariHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function handle(string $number, string $message): ?string
    {
        $parts = explode(" ", $message, 2);

        if (count($parts) < 2 || trim($parts[1]) === '') {
            return "📚 *Panduan Pencarian Data*\n\n"
                . "Gunakan perintah berikut untuk mencari data siswa atau pembimbing:\n"
                . "▶️ Format: `cari <keyword>`\n"
                . "▶️ Contoh: `cari Andi`\n\n"
                . "🔍 Keyword bisa berupa nama, NIS, kelas, atau jurusan siswa, nama DUDI, serta nama atau NIP pembimbing, dll.";
        }

        $keyword = trim($parts[1]);
        $kw = "%$keyword%";

        // Cari siswa + penempatan
        $siswaList = $this->db->query(
            "SELECT d.*, p.nama_dudika, p.alamat_dudika, p.nomor_telepon_dudika, p.nama_pembimbing
             FROM datasiswa d
             LEFT JOIN penempatan p ON d.nis = p.nis_siswa
             WHERE d.nis LIKE ? OR d.nama LIKE ? OR d.kelas LIKE ? OR d.jur LIKE ?",
            [$kw, $kw, $kw, $kw]
        );

        // Cari pembimbing
        $pembimbingList = $this->db->query(
            "SELECT * FROM datapembimbing WHERE nama LIKE ? OR nip LIKE ? OR kode LIKE ?",
            [$kw, $kw, $kw]
        );

        // Cari penempatan/DUDI
        $dudiList = $this->db->query(
            "SELECT nama_dudika, alamat_dudika, nama_pembimbing
             FROM penempatan
             WHERE nama_dudika LIKE ? OR alamat_dudika LIKE ? OR nama_pembimbing LIKE ?
             GROUP BY nama_dudika, alamat_dudika, nama_pembimbing",
            [$kw, $kw, $kw]
        );

        $foundSiswa      = !empty($siswaList);
        $foundPembimbing = !empty($pembimbingList);
        $foundDudi       = !empty($dudiList);

        if (!$foundSiswa && !$foundPembimbing && !$foundDudi) {
            return "⚠️ Tidak ditemukan data siswa, pembimbing, maupun DUDI dengan keyword \"$keyword\".";
        }

        $sendmsg = "";

        if ($foundSiswa) {
            $sendmsg .= "=======================================\n";
            $sendmsg .= "📋 *Hasil Pencarian Data Siswa:*\n";
            $sendmsg .= "=======================================\n";
            $sendmsg .= "```\n";
            foreach ($siswaList as $row) {
                $sendmsg .= "NIS        : {$row['nis']}\n";
                $sendmsg .= "Nama       : {$row['nama']}\n";
                $sendmsg .= "Kelas      : {$row['kelas']}\n";
                $sendmsg .= "Jurusan    : {$row['jur']}\n";
                $sendmsg .= "No HP      : {$row['nohp']}\n\n";
                if (!empty($row['nama_dudika'])) {
                    $sendmsg .= "🏢 Penempatan DUDI:\n";
                    $sendmsg .= "  - Nama DUDI  : {$row['nama_dudika']}\n";
                    $sendmsg .= "  - Alamat     : {$row['alamat_dudika']}\n";
                    $sendmsg .= "  - Pembimbing : {$row['nama_pembimbing']}\n";
                }
                $sendmsg .= "───────────────\n";
            }
            $sendmsg .= "```\n";
        }

        if ($foundPembimbing) {
            $sendmsg .= "=======================================\n";
            $sendmsg .= "📋 *Hasil Pencarian Pembimbing:*\n";
            $sendmsg .= "=======================================\n";
            $sendmsg .= "```\n";
            foreach ($pembimbingList as $row) {
                $sendmsg .= "NIP      : {$row['nip']}\n";
                $sendmsg .= "Nama     : {$row['nama']}\n";
                $sendmsg .= "No HP    : {$row['nohp']}\n";

                $dudiDibimbing = $this->db->query(
                    "SELECT DISTINCT nama_dudika, alamat_dudika FROM penempatan WHERE nama_pembimbing = ?",
                    [$row['nama']]
                );
                if (!empty($dudiDibimbing)) {
                    $sendmsg .= "DUDI yang dibimbing:\n";
                    $no = 1;
                    foreach ($dudiDibimbing as $d) {
                        $sendmsg .= "  $no. {$d['nama_dudika']}\n";
                        $sendmsg .= "    Alamat : {$d['alamat_dudika']}\n\n";
                        $no++;
                    }
                } else {
                    $sendmsg .= "⚠️ Belum ada DUDI yang dibimbing.\n";
                }
                $sendmsg .= "───────────────\n";
            }
            $sendmsg .= "```\n";
        }

        if ($foundDudi) {
            $sendmsg .= "=======================================\n";
            $sendmsg .= "🏢 *Hasil Pencarian Penempatan (DUDI):*\n";
            $sendmsg .= "=======================================\n";
            $sendmsg .= "```\n";
            foreach ($dudiList as $row) {
                $sendmsg .= "Nama DUDI  : {$row['nama_dudika']}\n";
                $sendmsg .= "Alamat     : {$row['alamat_dudika']}\n";
                $sendmsg .= "Pembimbing : {$row['nama_pembimbing']}\n\n";

                $siswaDudi = $this->db->query(
                    "SELECT d.nama, d.nis, d.kelas, d.nohp FROM penempatan p JOIN datasiswa d ON p.nis_siswa = d.nis WHERE p.nama_dudika = ?",
                    [$row['nama_dudika']]
                );
                if (!empty($siswaDudi)) {
                    $sendmsg .= "👥 Siswa di DUDI ini:\n";
                    foreach ($siswaDudi as $s) {
                        $nohp = !empty($s['nohp']) ? $s['nohp'] : "-tidak ada-";
                        $sendmsg .= "- {$s['nama']}\n  (NIS: {$s['nis']}, Kelas: {$s['kelas']})\n  No WA: $nohp\n";
                    }
                } else {
                    $sendmsg .= "⚠️ Tidak ada siswa di DUDI ini.\n";
                }
                $sendmsg .= "───────────────\n";
            }
            $sendmsg .= "```";
        }

        return $sendmsg;
    }
}
