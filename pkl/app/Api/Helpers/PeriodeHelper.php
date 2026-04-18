<?php

namespace App\Api\Helpers;

use App\Core\Database;

class PeriodeHelper
{
    /**
     * Cek apakah tanggal (format Y-m-d) valid untuk presensi
     * berdasarkan periode aktif + toleransi dari pengaturan.
     *
     * Return array:
     * ['valid' => bool, 'pesan' => string|null, 'warning' => bool]
     */
    public static function cekTanggalValid(string $tanggal): array
    {
        $db = Database::getInstance();

        $periode = $db->queryOne("SELECT * FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        if (!$periode) {
            return ['valid' => false, 'pesan' => 'Tidak ada periode PKL yang aktif saat ini.', 'warning' => false];
        }

        $tolSebelum = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sebelum'")['value'] ?? 0);
        $tolSesudah = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sesudah'")['value'] ?? 0);

        $tglCheck  = strtotime($tanggal);
        $tglMulai  = strtotime($periode['tanggal_mulai']);
        $tglSelesai= strtotime($periode['tanggal_selesai']);

        $batasMulai  = strtotime("-{$tolSebelum} days", $tglMulai);
        $batasAkhir  = strtotime("+{$tolSesudah} days", $tglSelesai);

        if ($tglCheck < $batasMulai) {
            $tglMulaiFormatted = date('d M Y', $tglMulai);
            $pesan = $tolSebelum > 0
                ? "Presensi belum bisa dilakukan. Periode PKL dimulai {$tglMulaiFormatted} (toleransi {$tolSebelum} hari)."
                : "Presensi belum bisa dilakukan. Periode PKL dimulai {$tglMulaiFormatted}.";
            return ['valid' => false, 'pesan' => $pesan, 'warning' => false];
        }

        if ($tglCheck > $batasAkhir) {
            $tglSelesaiFormatted = date('d M Y', $tglSelesai);
            $pesan = $tolSesudah > 0
                ? "Periode PKL telah berakhir pada {$tglSelesaiFormatted} (toleransi {$tolSesudah} hari)."
                : "Periode PKL telah berakhir pada {$tglSelesaiFormatted}.";
            return ['valid' => false, 'pesan' => $pesan, 'warning' => false];
        }

        // Dalam toleransi tapi di luar periode resmi
        $warning = ($tglCheck < $tglMulai || $tglCheck > $tglSelesai);
        $pesan   = $warning ? '⚠️ Presensi di luar rentang periode resmi — tercatat sebagai toleransi.' : null;

        return ['valid' => true, 'pesan' => $pesan, 'warning' => $warning];
    }
}