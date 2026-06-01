<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;

class HomeController
{
    public function index(): void
    {
        $db    = Database::getInstance();
        $today = date('Y-m-d');
        $dow   = (int)date('N');

        // Cek status login sebelum ada output
        \App\Core\Auth::start();
        $isLoggedIn = \App\Core\Auth::check();

        // ── Periode aktif ──
        // Cek periode inti (tanpa toleransi)
        $periodeAktif = $db->queryOne(
            "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai FROM periode_pkl 
            WHERE aktif = 1 AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1"
        );

        // Jika tidak dalam periode inti, cek apakah dalam masa toleransi
        $dalamToleransi = false;
        $periodeTolerasi = null;
        if (!$periodeAktif) {
            $periodeBase = $db->queryOne(
                "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai FROM periode_pkl WHERE aktif = 1 LIMIT 1"
            );
            if ($periodeBase) {
                $tolSebelum = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sebelum'")['value'] ?? 0);
                $tolSesudah = (int)($db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = 'toleransi_sesudah'")['value'] ?? 0);
                $today      = strtotime(date('Y-m-d'));
                $batasMulai = strtotime("-{$tolSebelum} days", strtotime($periodeBase['tanggal_mulai']));
                $batasAkhir = strtotime("+{$tolSesudah} days", strtotime($periodeBase['tanggal_selesai']));
                if ($today >= $batasMulai && $today <= $batasAkhir) {
                    $dalamToleransi  = true;
                    $periodeTolerasi = $periodeBase;
                    $sisaHariToleransi = (int)ceil(($batasAkhir - $today) / 86400);
                }
            }
        }

        $periodeId = $periodeAktif ? (int)$periodeAktif['id'] : ($periodeTolerasi ? (int)$periodeTolerasi['id'] : 0);

        // Jika tidak aktif: cek apakah periode (aktif=1) sudah lewat atau belum mulai,
        // lalu cari periode berikutnya
        $periodeBerikutnya = null;
        $adaPeriodeLewat   = false;
        if (!$periodeAktif) {
            $adaPeriodeLewat = (bool)$db->queryOne(
                "SELECT id FROM periode_pkl WHERE aktif = 1 AND tanggal_selesai < CURDATE() LIMIT 1"
            );

            $periodeBerikutnya = $db->queryOne(
                "SELECT tanggal_mulai FROM periode_pkl 
                WHERE tanggal_mulai > CURDATE() ORDER BY tanggal_mulai ASC LIMIT 1"
            );
        }
        
        // ── Stat cards ──
        $totalSiswa      = (int)($db->queryOne("SELECT COUNT(*) as n FROM datasiswa WHERE periode_id = ?", [$periodeId])['n'] ?? 0);
        $sudahWa         = (int)($db->queryOne("SELECT COUNT(*) as n FROM datasiswa WHERE periode_id = ? AND nohp IS NOT NULL AND nohp != ''", [$periodeId])['n'] ?? 0);
        $belumWa         = $totalSiswa - $sudahWa;
        $totalDudika     = (int)($db->queryOne("SELECT COUNT(DISTINCT p.nama_dudika) as n FROM penempatan p INNER JOIN datasiswa ds ON ds.nis = p.nis_siswa WHERE ds.periode_id = ?", [$periodeId])['n'] ?? 0);
        $totalPembimbing = (int)($db->queryOne("SELECT COUNT(*) as n FROM datapembimbing")['n'] ?? 0);
        $hadirHariIni    = (int)($db->queryOne(
            "SELECT COUNT(DISTINCT nis) as n FROM presensi WHERE DATE(timestamp) = ? AND periode_id = ?", [$today, $periodeId]
        )['n'] ?? 0);

        // ── Status detail hari ini ──
        $statusRows = $db->query(
            "SELECT LOWER(ket) as ket, COUNT(*) as n FROM presensi WHERE DATE(timestamp) = ? AND periode_id = ? GROUP BY ket",
            [$today, $periodeId]
        );
        $statMap = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
        foreach ($statusRows as $r) {
            if (isset($statMap[$r['ket']])) $statMap[$r['ket']] = (int)$r['n'];
        }

        // ── Grafik 7 hari presensi ──
        $chartLabels    = [];
        $chartData      = [];
        $chartAvgData   = []; // rata-rata hari yang sama dalam sejarah
        $chartDow       = []; // day-of-week tiap titik

        for ($i = 6; $i >= 0; $i--) {
            $tgl    = date('Y-m-d', strtotime("-$i days"));
            $dowTgl = (int)date('N', strtotime($tgl));
            $n = (int)($db->queryOne(
                "SELECT COUNT(DISTINCT nis) as n FROM presensi WHERE DATE(timestamp) = ? AND periode_id = ?", [$tgl, $periodeId]
            )['n'] ?? 0);
            $chartLabels[] = $tgl;
            $chartData[]   = $n;
            $chartDow[]    = $dowTgl;

            // Rata-rata hari yang sama (misal semua Senin), abaikan hari yang n=0
            $avgRow = $db->queryOne(
                "SELECT AVG(cnt) as avg FROM (
                    SELECT DATE(timestamp) as tgl, COUNT(DISTINCT nis) as cnt
                    FROM presensi
                    WHERE DAYOFWEEK(timestamp) = DAYOFWEEK(?)
                      AND DATE(timestamp) < ?
                    GROUP BY DATE(timestamp)
                    HAVING cnt > 0
                ) as sub",
                [$tgl, $tgl]
            );
            $chartAvgData[] = $avgRow && $avgRow['avg'] ? round((float)$avgRow['avg'], 1) : null;
        }

        // ── Grafik WA bot 7 hari (chat activity) + rata-rata hari ──
        $waLabels   = [];
        $waData     = [];
        $waAvgData  = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-$i days"));
            $n   = (int)($db->queryOne(
                "SELECT COUNT(*) as n FROM tmp WHERE DATE(timestamp) = ?", [$tgl]
            )['n'] ?? 0);
            $waLabels[] = $tgl;
            $waData[]   = $n;

            // Rata-rata hari yang sama, abaikan hari nol
            $avgWa = $db->queryOne(
                "SELECT AVG(cnt) as avg FROM (
                    SELECT DATE(timestamp) as tgl, COUNT(*) as cnt
                    FROM tmp
                    WHERE DAYOFWEEK(timestamp) = DAYOFWEEK(?)
                      AND DATE(timestamp) < ?
                    GROUP BY DATE(timestamp)
                    HAVING cnt > 0
                ) as sub",
                [$tgl, $tgl]
            );
            $waAvgData[] = $avgWa && $avgWa['avg'] ? round((float)$avgWa['avg'], 1) : null;
        }

        // ── Grafik per jam hari ini (aktual) + rata-rata per jam ──
        $jamLabels  = [];
        $jamAktual  = [];
        $jamAvg     = [];
        $dowToday   = (int)date('N'); // 1=Sen..7=Min

        for ($h = 6; $h <= 22; $h++) {
            $jamLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';

            // Aktual hari ini jam h
            $aktual = (int)($db->queryOne(
                "SELECT COUNT(*) as n FROM tmp
                 WHERE DATE(timestamp) = ? AND HOUR(timestamp) = ?",
                [$today, $h]
            )['n'] ?? 0);
            $jamAktual[] = $aktual;

            // Rata-rata jam yang sama di hari yang sama (DOW), abaikan nol
            $avgJam = $db->queryOne(
                "SELECT AVG(cnt) as avg FROM (
                    SELECT DATE(timestamp) as tgl, COUNT(*) as cnt
                    FROM tmp
                    WHERE DAYOFWEEK(timestamp) = ?
                      AND HOUR(timestamp) = ?
                      AND DATE(timestamp) < ?
                    GROUP BY DATE(timestamp)
                    HAVING cnt > 0
                ) as sub",
                [$dowToday, $h, $today]
            );
            $jamAvg[] = $avgJam && $avgJam['avg'] ? round((float)$avgJam['avg'], 1) : null;
        }

        // Statistik WA hari ini
        $waBotHariIni = (int)($db->queryOne(
            "SELECT COUNT(*) as n FROM tmp WHERE DATE(timestamp) = ?", [$today]
        )['n'] ?? 0);
        $waJamPuncak = $db->queryOne(
            "SELECT HOUR(timestamp) as jam, COUNT(*) as n FROM tmp
             WHERE DATE(timestamp) = ? GROUP BY jam ORDER BY n DESC LIMIT 1",
            [$today]
        );
        $waPengirimUnik = (int)($db->queryOne(
            "SELECT COUNT(DISTINCT number) as n FROM tmp WHERE DATE(timestamp) = ?", [$today]
        )['n'] ?? 0);

        // ── Recent presensi hari ini ──
        $recentPresensi = $db->query(
            "SELECT p.nis, p.namasiswa, p.kelas, p.ket, p.catatan, p.timestamp,
                    pen.nama_dudika, pen.nama_pembimbing
             FROM presensi p
             LEFT JOIN penempatan pen ON p.nis = pen.nis_siswa
             WHERE DATE(p.timestamp) = ? AND p.periode_id = ?
             ORDER BY p.timestamp DESC
             LIMIT 15",
            [$today, $periodeId]
        );

        $waBotNumber = $_ENV['WA_BOT_NUMBER'] ?? '6287754446580';

        Response::view('home/index', [
            'title'           => 'Sistem Presensi PKL',
            'totalSiswa'      => $totalSiswa,
            'sudahWa'         => $sudahWa,
            'belumWa'         => $belumWa,
            'totalDudika'     => $totalDudika,
            'totalPembimbing' => $totalPembimbing,
            'hadirHariIni'    => $hadirHariIni,
            'statMasuk'       => $statMap['masuk'],
            'statIzin'        => $statMap['izin'],
            'statSakit'       => $statMap['sakit'],
            'statLibur'       => $statMap['libur'],
            'chartLabels'     => $chartLabels,
            'chartData'       => $chartData,
            'chartAvgData'    => $chartAvgData,
            'waLabels'        => $waLabels,
            'waData'          => $waData,
            'waAvgData'       => $waAvgData,
            'jamLabels'       => $jamLabels,
            'jamAktual'       => $jamAktual,
            'jamAvg'          => $jamAvg,
            'waBotHariIni'    => $waBotHariIni,
            'waJamPuncak'     => $waJamPuncak,
            'waPengirimUnik'  => $waPengirimUnik,
            'recentPresensi'  => $recentPresensi,
            'waBotNumber'     => $waBotNumber,
            'today'           => $today,
            'isLoggedIn'      => $isLoggedIn,
            'periodeAktif'      => $periodeAktif,
            'periodeBerikutnya' => $periodeBerikutnya,
            'adaPeriodeLewat'   => $adaPeriodeLewat,
            'dalamToleransi'    => $dalamToleransi,
            'periodeTolerasi'   => $periodeTolerasi,
            'sisaHariToleransi'    => $sisaHariToleransi ?? 0,
        ]);
    }
}