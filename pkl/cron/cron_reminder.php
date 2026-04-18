<?php

/**
 * Cron Notifikasi PKL — SMK Negeri Bansari
 *
 * Menangani 4 jenis notifikasi:
 * A. Reminder presensi ke siswa (sore)
 * B. Alert siswa belum presensi ke pembimbing (harian)
 * C. Rekap mingguan ke pembimbing
 * D. Rekap mingguan ke wali kelas
 *
 * Jalankan via cPanel Cron Job:
 *   php /home/dvttaulx/pkl/cron/cron_reminder.php
 *
 * Preview (dry-run, tidak kirim WA):
 *   GET https://dev.masbendz.com/admin/cron/reminder
 *
 * Run (kirim WA sungguhan):
 *   POST https://dev.masbendz.com/admin/cron/reminder/run
 */

// ==========================================
// BOOTSTRAP
// ==========================================

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        if (!isset($_ENV[trim($key)])) {
            $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }
}

date_default_timezone_set('Asia/Jakarta');

$ujicoba = !empty($_GET['akses'] ?? null);

if ($ujicoba) {
    ini_set('max_execution_time', 0);
    ignore_user_abort(true);
}

// ==========================================
// KONSTANTA
// ==========================================

if (!defined('CRON_LOG_FILE')) {
    define('CRON_LOG_FILE', BASE_PATH . '/storage/logs/cron_reminder.log');
}
if (!defined('CRON_PENDING_FILE')) {
    define('CRON_PENDING_FILE', BASE_PATH . '/storage/tangguhan.json');
}

if (!is_dir(dirname(CRON_LOG_FILE))) mkdir(dirname(CRON_LOG_FILE), 0755, true);
if (!is_dir(dirname(CRON_PENDING_FILE))) mkdir(dirname(CRON_PENDING_FILE), 0755, true);

// ==========================================
// INISIALISASI
// ==========================================

require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Api/WaSender.php';

use App\Core\Database;
use App\Api\WaSender;

$db          = Database::getInstance();
$wa          = new WaSender();
$numberAdmin = $_ENV['WA_ADMIN_NUMBER'] ?? '';

// ==========================================
// HELPER: AMBIL SETTING
// ==========================================

if (!function_exists('getSetting')) {
    function getSetting(Database $db, string $key, string $default = ''): string
    {
        $row = $db->queryOne("SELECT `value` FROM pengaturan WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }
}

// ==========================================
// HELPER: LOG
// ==========================================

if (!function_exists('tulisLog')) {
    function tulisLog(string $pesan, bool $baru = false): void
    {
        file_put_contents(CRON_LOG_FILE, $pesan . "\n", $baru ? 0 : FILE_APPEND);
    }
}

// ==========================================
// HELPER: NAMA HARI
// ==========================================

if (!function_exists('hariIndonesia')) {
    function hariIndonesia(string $tanggal): string
    {
        $map = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'];
        return $map[date('D', strtotime($tanggal))] ?? '';
    }
}

// ==========================================
// HELPER: CATAT LIBUR OTOMATIS
// ==========================================

if (!function_exists('catatLibur')) {
    function catatLibur(Database $db, string $nis, string $nama, string $kelas, int $periodeId): ?string
    {
        global $ujicoba;
        if ($ujicoba) return null;

        $tgl      = date('Y-m-d');
        $existing = $db->queryOne("SELECT ket FROM presensi WHERE nis = ? AND DATE(timestamp) = ?", [$nis, $tgl]);
        if ($existing) return null;

        $affected = $db->execute(
            "INSERT INTO presensi (periode_id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp)
             VALUES (?, ?, ?, ?, 'Libur', '', '', '', 'AUTO', NOW())",
            [$periodeId, $nis, $nama, $kelas]
        );

        if ($affected > 0) {
            tulisLog("-> " . date('Y-m-d H:i:s') . " == Libur otomatis: [{$nama}]");
            return "✅ Hai {$nama} ({$kelas}), hari ini kamu dicatat *libur* oleh sistem.\n\nAbaikan jika benar libur, hubungi admin jika tidak.";
        }
        return null;
    }
}

// ==========================================
// HELPER: FORMAT REKAP
// ==========================================

if (!function_exists('formatRekap')) {
    function formatRekap(array $data): string
    {
        return "✅ {$data['masuk']}M  🟡 {$data['izin']}I  🔴 {$data['sakit']}S  ⚫ {$data['libur']}L";
    }
}

// ==========================================
// AMBIL SETTING NOTIFIKASI
// ==========================================

$jamSekarang = date('H:i');
$hariSekarang = (int)date('N'); // 1=Sen..7=Min
$tglHariIni   = date('Y-m-d');
$hariNama     = hariIndonesia($tglHariIni);

$notifSiswaAktif      = getSetting($db, 'notif_siswa_aktif', '1') === '1';
$notifSiswaJam        = getSetting($db, 'notif_siswa_jam', '16:00');
$notifAlertAktif      = getSetting($db, 'notif_alert_aktif', '1') === '1';
$notifAlertJam        = getSetting($db, 'notif_alert_jam', '10:00');
$notifPembimbingAktif = getSetting($db, 'notif_pembimbing_aktif', '1') === '1';
$notifPembimbingJam   = getSetting($db, 'notif_pembimbing_jam', '08:00');
$notifPembimbingHari  = (int)getSetting($db, 'notif_pembimbing_hari', '1');
$notifWalikelasAktif  = getSetting($db, 'notif_walikelas_aktif', '1') === '1';
$notifWalikelasJam    = getSetting($db, 'notif_walikelas_jam', '08:00');
$notifWalikelasHari   = (int)getSetting($db, 'notif_walikelas_hari', '1');

// Periode aktif
$periodeAktif = $db->queryOne("SELECT * FROM periode_pkl WHERE aktif = 1 LIMIT 1");
$periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;

// ==========================================
// TENTUKAN NOTIFIKASI APA YANG JALAN SEKARANG
// ==========================================

$jalankanSiswa      = $notifSiswaAktif      && ($ujicoba || $jamSekarang >= $notifSiswaJam);
$jalankanAlert      = $notifAlertAktif      && ($ujicoba || $jamSekarang >= $notifAlertJam);
$jalankanPembimbing = $notifPembimbingAktif && ($ujicoba || ($hariSekarang === $notifPembimbingHari && $jamSekarang >= $notifPembimbingJam));
$jalankanWalikelas  = $notifWalikelasAktif  && ($ujicoba || ($hariSekarang === $notifWalikelasHari  && $jamSekarang >= $notifWalikelasJam));

$startTime = date('Y-m-d H:i:s');
tulisLog("=== CRON MULAI: {$startTime} (ujicoba=" . ($ujicoba ? 'ya' : 'tidak') . ") ===", true);
tulisLog("Jam: {$jamSekarang} | Hari: {$hariNama} ({$hariSekarang})");
tulisLog("Notifikasi aktif — Siswa:{$notifSiswaJam} Alert:{$notifAlertJam} Pembimbing:{$notifPembimbingJam}(H{$notifPembimbingHari}) WaliKelas:{$notifWalikelasJam}(H{$notifWalikelasHari})");

// ==========================================
// AMBIL DATA SISWA PERIODE AKTIF
// ==========================================

$semuaSiswa = $db->query(
    "SELECT ds.nis, ds.nama, ds.kelas, ds.nohp,
            pen.nama_pembimbing, pen.nama_dudika
     FROM datasiswa ds
     LEFT JOIN penempatan pen ON pen.nis_siswa = ds.nis AND pen.periode_id = ?
     WHERE ds.periode_id = ?",
    [$periodeId, $periodeId]
);

$semuaNis = array_column($semuaSiswa, 'nis');

$pesanKirim  = []; // untuk dry-run output
$totalKirim  = 0;

if (empty($semuaNis)) {
    tulisLog("Tidak ada data siswa di periode aktif.");
    if ($ujicoba) { echo "Tidak ada siswa di periode aktif.\n"; }
    if (php_sapi_name() === 'cli') exit;
    return;
}

$inPlaceholder = implode(',', array_fill(0, count($semuaNis), '?'));

// Bulk query presensi
$tglKemarin     = date('Y-m-d', strtotime('-1 day'));
$tglMingguLalu  = date('Y-m-d', strtotime('-7 days'));
$tgl2MingguLalu = date('Y-m-d', strtotime('-14 days'));

$presensiHariIni    = [];
$presensiKemarin    = [];
$presensiMingguLalu = [];
$presensi2MingguLalu= [];

foreach ($db->query("SELECT nis, ket FROM presensi WHERE nis IN ({$inPlaceholder}) AND DATE(timestamp) = ? AND periode_id = ?",
    [...$semuaNis, $tglHariIni, $periodeId]) as $r) $presensiHariIni[$r['nis']] = strtolower($r['ket']);

foreach ($db->query("SELECT nis, ket FROM presensi WHERE nis IN ({$inPlaceholder}) AND DATE(timestamp) = ? AND periode_id = ?",
    [...$semuaNis, $tglKemarin, $periodeId]) as $r) $presensiKemarin[$r['nis']] = strtolower($r['ket']);

foreach ($db->query("SELECT nis, ket FROM presensi WHERE nis IN ({$inPlaceholder}) AND DATE(timestamp) = ? AND periode_id = ?",
    [...$semuaNis, $tglMingguLalu, $periodeId]) as $r) $presensiMingguLalu[$r['nis']] = strtolower($r['ket']);

foreach ($db->query("SELECT nis, ket FROM presensi WHERE nis IN ({$inPlaceholder}) AND DATE(timestamp) = ? AND periode_id = ?",
    [...$semuaNis, $tgl2MingguLalu, $periodeId]) as $r) $presensi2MingguLalu[$r['nis']] = strtolower($r['ket']);

// ==========================================
// A. REMINDER SISWA
// ==========================================

if ($jalankanSiswa) {
    tulisLog("\n--- [A] REMINDER SISWA ---");
    $statusMasuk  = ['masuk','izin','sakit'];
    $pendingData  = file_exists(CRON_PENDING_FILE)
        ? (json_decode(file_get_contents(CRON_PENDING_FILE), true) ?: [])
        : [];

    foreach ($semuaSiswa as $siswa) {
        $nis   = $siswa['nis'];
        $nama  = $siswa['nama'];
        $kelas = $siswa['kelas'];
        $nohp  = $siswa['nohp'];

        if (empty($nohp)) continue;
        if (isset($presensiHariIni[$nis])) continue; // sudah presensi

        $stKemarin     = $presensiKemarin[$nis]     ?? '';
        $stMingguLalu  = $presensiMingguLalu[$nis]  ?? '';
        $st2MingguLalu = $presensi2MingguLalu[$nis] ?? '';

        $sendmsg = '';

        // Sabtu/Minggu — cek pola libur
        if (in_array($hariSekarang, [6, 7])) {
            if (
                ($stMingguLalu === 'libur' && $st2MingguLalu === 'libur') ||
                (empty($stMingguLalu) && empty($st2MingguLalu))
            ) {
                $msg = catatLibur($db, $nis, $nama, $kelas, $periodeId);
                if ($msg && !$ujicoba) {
                    $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$nama,'kelas'=>$kelas,'pesan'=>$msg,'tipe'=>'libur_auto'];
                }
                continue;
            }
        }

        // Hari biasa — cek pola libur rutin
        if (
            $stMingguLalu === 'libur' && $st2MingguLalu === 'libur' &&
            !in_array($hariSekarang, [6, 7])
        ) {
            $msg = catatLibur($db, $nis, $nama, $kelas, $periodeId);
            if ($msg && !$ujicoba) {
                $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$nama,'kelas'=>$kelas,'pesan'=>$msg,'tipe'=>'libur_auto'];
            }
            continue;
        }

        // Pesan kontekstual
        if ($stKemarin === 'masuk' && $stMingguLalu === 'masuk') {
            $sendmsg = "📌 Halo *{$nama}*, sepertinya kamu belum presensi hari ini ({$hariNama}).\nJangan lupa presensi ya! 😊";
        } elseif ($stKemarin === 'sakit') {
            $sendmsg = "😷 Halo *{$nama}*, kemarin kamu sakit. Semoga sudah membaik!\nJangan lupa presensi hari ini jika sudah sehat.";
        } elseif ($stKemarin === 'izin') {
            $sendmsg = "📄 Halo *{$nama}*, kemarin kamu izin.\nJangan lupa presensi hari ini ya!";
        } elseif ($stKemarin === 'libur') {
            $sendmsg = "🌴 Halo *{$nama}*, kemarin libur.\nJangan lupa presensi hari ini ya!";
        } else {
            $sendmsg = "📌 Halo *{$nama}*, kamu belum presensi hari ini ({$hariNama}).\nSegera presensi ya! 😊";
        }

        $pendingData[$nohp] = [
            'type'  => 'confirm_libur',
            'nis'   => $nis,
            'nama'  => $nama,
            'kelas' => $kelas,
            'waktu' => date('Y-m-d H:i:s'),
        ];

        $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$nama,'kelas'=>$kelas,'pesan'=>$sendmsg,'tipe'=>'reminder_siswa'];
        tulisLog("Antri reminder: [{$nama}] {$nohp}");
    }

    if (!$ujicoba) {
        file_put_contents(CRON_PENDING_FILE, json_encode($pendingData, JSON_PRETTY_PRINT));
    }
}

// ==========================================
// B. ALERT PEMBIMBING HARIAN
// ==========================================

if ($jalankanAlert) {
    tulisLog("\n--- [B] ALERT PEMBIMBING HARIAN ---");

    // Kelompokkan siswa belum presensi per pembimbing
    $belumPerPembimbing = [];
    foreach ($semuaSiswa as $siswa) {
        $nis        = $siswa['nis'];
        $pembimbing = $siswa['nama_pembimbing'] ?? '';
        if (empty($pembimbing)) continue;
        if (isset($presensiHariIni[$nis])) continue; // sudah presensi

        if (!isset($belumPerPembimbing[$pembimbing])) {
            $belumPerPembimbing[$pembimbing] = [];
        }
        $belumPerPembimbing[$pembimbing][] = $siswa;
    }

    if (empty($belumPerPembimbing)) {
        tulisLog("Semua siswa sudah presensi — alert pembimbing tidak dikirim.");
    } else {
        $listPembimbing = $db->query(
            "SELECT nama, nohp FROM datapembimbing WHERE nohp IS NOT NULL AND nohp != '' ORDER BY nama ASC"
        );
        $nohpPembimbing = array_column($listPembimbing, 'nohp', 'nama');

        foreach ($belumPerPembimbing as $namaPembimbing => $siswaBelum) {
            $nohp = $nohpPembimbing[$namaPembimbing] ?? null;
            if (!$nohp) { tulisLog("Skip {$namaPembimbing} — no HP tidak ada"); continue; }

            $pesan  = "⚠️ *Alert Presensi PKL*\n";
            $pesan .= "📅 {$hariNama}, " . date('d M Y') . "\n\n";
            $pesan .= "*{$namaPembimbing}*, berikut siswa bimbingan Anda yang *belum presensi* hari ini:\n\n";

            foreach ($siswaBelum as $i => $s) {
                $no = $i + 1;
                $pesan .= "{$no}. *{$s['nama']}* ({$s['kelas']})\n";
            }

            $pesan .= "\n_Total: " . count($siswaBelum) . " siswa_\n";
            $pesan .= "\nMohon ingatkan siswa untuk segera melakukan presensi. 🙏";

            $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$namaPembimbing,'kelas'=>'-','pesan'=>$pesan,'tipe'=>'alert_pembimbing'];
            tulisLog("Antri alert ke pembimbing: [{$namaPembimbing}] — " . count($siswaBelum) . " siswa");
        }
    }
}

// ==========================================
// C. REKAP MINGGUAN PEMBIMBING
// ==========================================

if ($jalankanPembimbing) {
    tulisLog("\n--- [C] REKAP MINGGUAN PEMBIMBING ---");

    // Ambil presensi seminggu lalu (Senin s/d Minggu kemarin)
    $seninLalu  = date('Y-m-d', strtotime('last monday -7 days'));
    $mingguLalu = date('Y-m-d', strtotime($seninLalu . ' +6 days'));

    $listPembimbing = $db->query(
        "SELECT nama, nohp FROM datapembimbing WHERE nohp IS NOT NULL AND nohp != '' ORDER BY nama ASC"
    );

    foreach ($listPembimbing as $pb) {
        $namaPembimbing = $pb['nama'];
        $nohp           = $pb['nohp'];

        // Ambil siswa bimbingan di periode aktif
        $siswaBimbingan = $db->query(
            "SELECT ds.nis, ds.nama, ds.kelas
             FROM datasiswa ds
             INNER JOIN penempatan pen ON pen.nis_siswa = ds.nis AND pen.nama_pembimbing = ?
             WHERE ds.periode_id = ?
             ORDER BY ds.kelas ASC, ds.nama ASC",
            [$namaPembimbing, $periodeId]
        );

        if (empty($siswaBimbingan)) continue;

        $nisBimbingan = array_column($siswaBimbingan, 'nis');
        $inPh = implode(',', array_fill(0, count($nisBimbingan), '?'));

        $rekapRows = $db->query(
            "SELECT nis,
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
             FROM presensi
             WHERE nis IN ({$inPh}) AND periode_id = ?
               AND DATE(timestamp) BETWEEN ? AND ?
             GROUP BY nis",
            [...$nisBimbingan, $periodeId, $seninLalu, $mingguLalu]
        );
        $rekapMap = array_column($rekapRows, null, 'nis');

        $pesan  = "📊 *Rekap Mingguan PKL*\n";
        $pesan .= "📅 " . date('d M', strtotime($seninLalu)) . " — " . date('d M Y', strtotime($mingguLalu)) . "\n\n";
        $pesan .= "Yth. *{$namaPembimbing}*,\nBerikut rekap presensi siswa bimbingan Anda:\n\n";

        foreach ($siswaBimbingan as $i => $s) {
            $no    = $i + 1;
            $rekap = $rekapMap[$s['nis']] ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
            $pesan .= "{$no}. *{$s['nama']}* ({$s['kelas']})\n";
            $pesan .= "   " . formatRekap($rekap) . "\n";
        }

        $pesan .= "\nM=Masuk I=Izin S=Sakit L=Libur\n";
        $pesan .= "_Terima kasih atas perhatian Bapak/Ibu._ 🙏";

        $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$namaPembimbing,'kelas'=>'-','pesan'=>$pesan,'tipe'=>'rekap_pembimbing'];
        tulisLog("Antri rekap pembimbing: [{$namaPembimbing}] — " . count($siswaBimbingan) . " siswa");
    }
}

// ==========================================
// D. REKAP MINGGUAN WALI KELAS
// ==========================================

if ($jalankanWalikelas) {
    tulisLog("\n--- [D] REKAP MINGGUAN WALI KELAS ---");

    $seninLalu  = date('Y-m-d', strtotime('last monday -7 days'));
    $mingguLalu = date('Y-m-d', strtotime($seninLalu . ' +6 days'));

    $listWalikelas = $db->query(
        "SELECT nama, kelas, nohp FROM datawalikelas WHERE nohp IS NOT NULL AND nohp != '' ORDER BY kelas ASC"
    );

    foreach ($listWalikelas as $wk) {
        $namaWk = $wk['nama'];
        $kelas  = $wk['kelas'];
        $nohp   = $wk['nohp'];

        $siswaKelas = $db->query(
            "SELECT ds.nis, ds.nama, pen.nama_pembimbing, pen.nama_dudika
             FROM datasiswa ds
             LEFT JOIN penempatan pen ON pen.nis_siswa = ds.nis AND pen.periode_id = ?
             WHERE ds.periode_id = ? AND ds.kelas = ?
             ORDER BY ds.nama ASC",
            [$periodeId, $periodeId, $kelas]
        );

        if (empty($siswaKelas)) continue;

        $nisKelas = array_column($siswaKelas, 'nis');
        $inPh = implode(',', array_fill(0, count($nisKelas), '?'));

        $rekapRows = $db->query(
            "SELECT nis,
                SUM(CASE WHEN LOWER(ket)='masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN LOWER(ket)='izin'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN LOWER(ket)='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN LOWER(ket)='libur' THEN 1 ELSE 0 END) as libur
             FROM presensi
             WHERE nis IN ({$inPh}) AND periode_id = ?
               AND DATE(timestamp) BETWEEN ? AND ?
             GROUP BY nis",
            [...$nisKelas, $periodeId, $seninLalu, $mingguLalu]
        );
        $rekapMap = array_column($rekapRows, null, 'nis');

        $pesan  = "📊 *Rekap Mingguan PKL — {$kelas}*\n";
        $pesan .= "📅 " . date('d M', strtotime($seninLalu)) . " — " . date('d M Y', strtotime($mingguLalu)) . "\n\n";
        $pesan .= "Yth. *{$namaWk}* (Wali Kelas {$kelas}),\n";
        $pesan .= "Berikut rekap presensi siswa kelas Anda:\n\n";

        foreach ($siswaKelas as $i => $s) {
            $no    = $i + 1;
            $rekap = $rekapMap[$s['nis']] ?? ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];
            $pesan .= "{$no}. *{$s['nama']}*\n";
            $pesan .= "   " . formatRekap($rekap) . "\n";
            if (!empty($s['nama_dudika'])) {
                $pesan .= "   📍 {$s['nama_dudika']}\n";
            }
        }

        $pesan .= "\nM=Masuk I=Izin S=Sakit L=Libur\n";
        $pesan .= "_Terima kasih atas perhatian Bapak/Ibu._ 🙏";

        $pesanKirim[] = ['nohp'=>$nohp,'nama'=>$namaWk,'kelas'=>$kelas,'pesan'=>$pesan,'tipe'=>'rekap_walikelas'];
        tulisLog("Antri rekap walikelas: [{$namaWk}] kelas {$kelas} — " . count($siswaKelas) . " siswa");
    }
}

// ==========================================
// MODE UJI COBA — tampilkan dan stop
// ==========================================

if ($ujicoba) {
    tulisLog(date('Y-m-d H:i:s') . " - UJI COBA SELESAI");

    echo "Tanggal   : {$tglHariIni} ({$hariNama})\n";
    echo "Jam       : {$jamSekarang}\n";
    echo "Periode   : " . ($periodeAktif['nama_periode'] ?? '-') . "\n";
    echo "Total siswa: " . count($semuaSiswa) . "\n";
    echo "Sudah presensi: " . count($presensiHariIni) . "\n\n";

    echo "=== SETTING NOTIFIKASI ===\n";
    echo "A. Reminder Siswa  : " . ($notifSiswaAktif ? "AKTIF jam {$notifSiswaJam}" : "NONAKTIF") . " | Jalan: " . ($jalankanSiswa ? 'YA' : 'TIDAK') . "\n";
    echo "B. Alert Pembimbing: " . ($notifAlertAktif ? "AKTIF jam {$notifAlertJam}" : "NONAKTIF") . " | Jalan: " . ($jalankanAlert ? 'YA' : 'TIDAK') . "\n";
    echo "C. Rekap Pembimbing: " . ($notifPembimbingAktif ? "AKTIF hari {$notifPembimbingHari} jam {$notifPembimbingJam}" : "NONAKTIF") . " | Jalan: " . ($jalankanPembimbing ? 'YA' : 'TIDAK') . "\n";
    echo "D. Rekap Walikelas : " . ($notifWalikelasAktif ? "AKTIF hari {$notifWalikelasHari} jam {$notifWalikelasJam}" : "NONAKTIF") . " | Jalan: " . ($jalankanWalikelas ? 'YA' : 'TIDAK') . "\n\n";

    echo "=== PESAN YANG AKAN DIKIRIM (" . count($pesanKirim) . ") ===\n\n";

    if (empty($pesanKirim)) {
        echo "-- Tidak ada pesan yang akan dikirim --\n";
    } else {
        foreach ($pesanKirim as $i => $item) {
            echo ($i+1) . ". [{$item['tipe']}] {$item['nama']} ({$item['kelas']}) — {$item['nohp']}\n";
            echo "   Pesan: " . substr($item['pesan'], 0, 100) . "...\n\n";
        }
    }

    if (php_sapi_name() === 'cli') exit;
    return;
}

// ==========================================
// PRODUKSI — KIRIM WA
// ==========================================

if (empty($pesanKirim)) {
    tulisLog("Tidak ada pesan yang perlu dikirim.");
    if (php_sapi_name() === 'cli') exit;
    return;
}

$wa->send($numberAdmin,
    "📨 Cron notifikasi dimulai.\n" .
    "🕒 {$startTime}\n" .
    "📬 " . count($pesanKirim) . " pesan akan dikirim."
);

sleep(5);

$footer  = "\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$footer .= "📝 _Sistem Presensi PKL_ *SMK Negeri Bansari*";

foreach ($pesanKirim as $item) {
    // Cek ulang untuk reminder siswa
    if ($item['tipe'] === 'reminder_siswa') {
        $sudah = $db->queryOne(
            "SELECT id FROM presensi WHERE nis IN (SELECT nis FROM datasiswa WHERE nohp = ? AND periode_id = ?) AND DATE(timestamp) = ?",
            [$item['nohp'], $periodeId, $tglHariIni]
        );
        if ($sudah) {
            tulisLog("SKIP (sudah presensi): [{$item['nama']}]");
            sleep(2);
            continue;
        }
    }

    $wa->send($item['nohp'], $item['pesan'] . $footer);
    tulisLog("TERKIRIM [{$item['tipe']}]: [{$item['nama']}] — {$item['nohp']}");
    sleep(15);
}

$endTime  = date('Y-m-d H:i:s');
$duration = strtotime($endTime) - strtotime($startTime);

tulisLog("\n=== SELESAI: {$endTime} | Durasi: " . gmdate('H:i:s', $duration) . " ===");

$wa->send($numberAdmin,
    "✅ Cron notifikasi selesai.\n" .
    "🕒 {$startTime} → {$endTime}\n" .
    "⏳ " . gmdate('H:i:s', $duration) . "\n" .
    "📨 " . count($pesanKirim) . " pesan terkirim."
);
