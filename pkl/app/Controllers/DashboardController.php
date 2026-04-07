<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

class DashboardController
{
    public function index(): void
    {
        Auth::required();

        $db    = Database::getInstance();
        $today = date('Y-m-d');

        // ── Stat cards ──
        $totalSiswa      = (int)($db->queryOne("SELECT COUNT(*) as n FROM datasiswa")['n'] ?? 0);
        $sudahWa         = (int)($db->queryOne("SELECT COUNT(*) as n FROM datasiswa WHERE nohp IS NOT NULL AND nohp != ''")['n'] ?? 0);
        $belumWa         = $totalSiswa - $sudahWa;
        $totalDudika     = (int)($db->queryOne("SELECT COUNT(DISTINCT nama_dudika) as n FROM penempatan")['n'] ?? 0);
        $totalPembimbing = (int)($db->queryOne("SELECT COUNT(*) as n FROM datapembimbing")['n'] ?? 0);
        $presensiHariIni = (int)($db->queryOne(
            "SELECT COUNT(DISTINCT nis) as n FROM presensi WHERE DATE(timestamp) = ?", [$today]
        )['n'] ?? 0);

        // ── Status hari ini ──
        $statusRows = $db->query(
            "SELECT LOWER(ket) as ket, COUNT(*) as n FROM presensi WHERE DATE(timestamp) = ? GROUP BY ket", [$today]
        );
        $statMap = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
        foreach ($statusRows as $r) {
            if (isset($statMap[$r['ket']])) $statMap[$r['ket']] = (int)$r['n'];
        }

        // ── Chart 14 hari ──
        $chartLabels = [];
        $chartData   = [];
        for ($i = 13; $i >= 0; $i--) {
            $tgl    = date('Y-m-d', strtotime("-$i days"));
            $jumlah = (int)($db->queryOne(
                "SELECT COUNT(DISTINCT nis) as n FROM presensi WHERE DATE(timestamp) = ?", [$tgl]
            )['n'] ?? 0);
            $chartLabels[] = date('d/m', strtotime($tgl));
            $chartData[]   = $jumlah;
        }

        // ── Rekap per kelas hari ini ──
        $kelasRows  = $db->query("SELECT kelas, COUNT(*) as total FROM datasiswa GROUP BY kelas ORDER BY kelas");
        $rekapKelas = [];
        foreach ($kelasRows as $r) {
            $hadir = (int)($db->queryOne(
                "SELECT COUNT(DISTINCT nis) as n FROM presensi WHERE kelas = ? AND DATE(timestamp) = ?",
                [$r['kelas'], $today]
            )['n'] ?? 0);
            $rekapKelas[] = ['kelas'=>$r['kelas'], 'total'=>(int)$r['total'], 'hadir'=>$hadir];
        }

        // ── Siswa belum presensi ──
        $belumPresensi = $db->query(
            "SELECT d.nis, d.nama, d.kelas, d.nohp, p.nama_dudika, p.nama_pembimbing
             FROM datasiswa d
             LEFT JOIN penempatan p ON d.nis = p.nis_siswa
             WHERE d.nis NOT IN (SELECT DISTINCT nis FROM presensi WHERE DATE(timestamp) = ?)
             ORDER BY d.kelas, d.nama LIMIT 100",
            [$today]
        );

        // ── WA Bot hari ini ──
        $waBotHariIni   = (int)($db->queryOne("SELECT COUNT(*) as n FROM tmp WHERE DATE(timestamp) = ?", [$today])['n'] ?? 0);
        $sesiAdminAktif = (int)($db->queryOne("SELECT COUNT(*) as n FROM wabot_admin_session")['n'] ?? 0);

        // WA per jam (aktual + rata-rata hari yang sama)
        $waPerJam    = [];
        $waJamAvg    = [];
        $dowToday    = (int)date('N');
        $waJamRows   = $db->query(
            "SELECT HOUR(timestamp) as jam, COUNT(*) as n FROM tmp WHERE DATE(timestamp) = ? GROUP BY jam ORDER BY jam",
            [$today]
        );
        $jamMap = array_fill(0, 24, 0);
        foreach ($waJamRows as $r) $jamMap[(int)$r['jam']] = (int)$r['n'];

        for ($h = 6; $h <= 22; $h++) {
            $waPerJam[] = ['jam' => str_pad($h,2,'0',STR_PAD_LEFT).':00', 'n' => $jamMap[$h]];
            $avgJam = $db->queryOne(
                "SELECT AVG(cnt) as avg FROM (
                    SELECT DATE(timestamp) as tgl, COUNT(*) as cnt
                    FROM tmp
                    WHERE DAYOFWEEK(timestamp) = ? AND HOUR(timestamp) = ? AND DATE(timestamp) < ?
                    GROUP BY DATE(timestamp) HAVING cnt > 0
                ) as sub",
                [$dowToday, $h, $today]
            );
            $waJamAvg[] = $avgJam && $avgJam['avg'] ? round((float)$avgJam['avg'], 1) : null;
        }

        // Pesan terbaru
        $waPesanTerbaru = $db->query(
            "SELECT t.number, t.msg, t.timestamp,
                    COALESCE(d.nama, 'Tidak dikenal') as nama, d.kelas
             FROM tmp t
             LEFT JOIN datasiswa d ON t.number = d.nohp
             WHERE DATE(t.timestamp) = ?
             ORDER BY t.timestamp DESC LIMIT 10",
            [$today]
        );

        // ── Top perintah bot hari ini ──
        $topBotHariIni = $db->query(
            "SELECT
                LOWER(SUBSTRING_INDEX(TRIM(msg), ' ', 1)) as perintah,
                COUNT(*) as total
             FROM tmp
             WHERE DATE(timestamp) = ?
               AND msg IS NOT NULL AND msg != ''
             GROUP BY perintah
             ORDER BY total DESC
             LIMIT 8",
            [$today]
        );

        // ── Top perintah bot sepanjang masa ──
        $topBotAllTime = $db->query(
            "SELECT
                LOWER(SUBSTRING_INDEX(TRIM(msg), ' ', 1)) as perintah,
                COUNT(*) as total
             FROM tmp
             WHERE msg IS NOT NULL AND msg != ''
             GROUP BY perintah
             ORDER BY total DESC
             LIMIT 8"
        );

        Response::view('dashboard/index', [
            'title'           => 'Dashboard',
            'user'            => Auth::user(),
            'totalSiswa'      => $totalSiswa,
            'sudahWa'         => $sudahWa,
            'belumWa'         => $belumWa,
            'presensiHariIni' => $presensiHariIni,
            'totalDudika'     => $totalDudika,
            'totalPembimbing' => $totalPembimbing,
            'statMasuk'       => $statMap['masuk'],
            'statIzin'        => $statMap['izin'],
            'statSakit'       => $statMap['sakit'],
            'statLibur'       => $statMap['libur'],
            'chartLabels'     => $chartLabels,
            'chartData'       => $chartData,
            'rekapKelas'      => $rekapKelas,
            'belumPresensi'   => $belumPresensi,
            'waBotHariIni'    => $waBotHariIni,
            'sesiAdminAktif'  => $sesiAdminAktif,
            'waPerJam'        => $waPerJam,
            'waJamAvg'        => $waJamAvg,
            'waPesanTerbaru'  => $waPesanTerbaru,
            'topBotHariIni'   => $topBotHariIni,
            'topBotAllTime'   => $topBotAllTime,
        ]);
    }
}