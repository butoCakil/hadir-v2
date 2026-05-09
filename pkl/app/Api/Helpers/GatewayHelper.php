<?php

namespace App\Api\Helpers;

use App\Core\Database;

class GatewayHelper
{
    /**
     * Cek apakah gateway presensi terbuka.
     * 
     * @param string $channel  'wa' atau 'web'
     * @return array [
     *   'buka'        => bool,
     *   'mode'        => 'auto'|'manual',
     *   'toleransi'   => bool,   // true = dalam masa toleransi (bukan periode inti)
     *   'periode'     => array|null,
     *   'periodeBerikutnya' => array|null,
     *   'pesan'       => string|null,
     * ]
     */
    public static function cek(string $channel = 'wa'): array
    {
        $db      = Database::getInstance();
        $channel = in_array($channel, ['wa', 'web']) ? $channel : 'wa';

        $mode  = $db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'gateway_{$channel}_mode'")['value']  ?? 'auto';
        $aktif = $db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'gateway_{$channel}_aktif'")['value'] ?? '1';

        // Mode manual — ikuti flag saja
        if ($mode === 'manual') {
            return [
                'buka'              => $aktif === '1',
                'mode'              => 'manual',
                'toleransi'         => false,
                'periode'           => null,
                'periodeBerikutnya' => null,
                'pesan'             => null,
            ];
        }

        // Mode auto — ikuti periode + toleransi
        $periode = $db->queryOne(
            "SELECT * FROM periode_pkl WHERE aktif = 1 LIMIT 1"
        );

        $periodeBerikutnya = null;
        if (!$periode) {
            $periodeBerikutnya = $db->queryOne(
                "SELECT * FROM periode_pkl WHERE tanggal_mulai > CURDATE() ORDER BY tanggal_mulai ASC LIMIT 1"
            );
            return [
                'buka'              => false,
                'mode'              => 'auto',
                'toleransi'         => false,
                'periode'           => null,
                'periodeBerikutnya' => $periodeBerikutnya,
                'pesan'             => null,
            ];
        }

        $tolSebelum = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sebelum'")['value'] ?? 0);
        $tolSesudah = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sesudah'")['value'] ?? 0);

        $today      = strtotime(date('Y-m-d'));
        $tglMulai   = strtotime($periode['tanggal_mulai']);
        $tglSelesai = strtotime($periode['tanggal_selesai']);
        $batasMulai = strtotime("-{$tolSebelum} days", $tglMulai);
        $batasAkhir = strtotime("+{$tolSesudah} days", $tglSelesai);

        $dalamToleransi = ($today >= $batasMulai && $today <= $batasAkhir);
        $dalamPeriodeInti = ($today >= $tglMulai && $today <= $tglSelesai);

        if (!$dalamToleransi) {
            // Sudah lewat batas toleransi — cari periode berikutnya
            $periodeBerikutnya = $db->queryOne(
                "SELECT * FROM periode_pkl WHERE tanggal_mulai > CURDATE() ORDER BY tanggal_mulai ASC LIMIT 1"
            );
        }

        return [
            'buka'              => $dalamToleransi,
            'mode'              => 'auto',
            'toleransi'         => $dalamToleransi && !$dalamPeriodeInti,
            'periode'           => $periode,
            'periodeBerikutnya' => $periodeBerikutnya ?? null,
            'pesan'             => null,
        ];
    }

    /**
     * Format tanggal Indonesia singkat: "17 April 2026"
     */
    public static function formatTgl(string $tgl): string
    {
        $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                  '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                  '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $d = date('d', strtotime($tgl));
        $m = $bulan[date('m', strtotime($tgl))] ?? date('m', strtotime($tgl));
        $y = date('Y', strtotime($tgl));
        return "$d $m $y";
    }
}