<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;
use App\Models\WabotSessionModel;

class CekHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private string $tahun;

    public function __construct(Database $db, WaSender $sender, WabotSessionModel $session)
    {
        $this->db      = $db;
        $this->sender  = $sender;
        $this->session = $session;
        $this->tahun   = date('Y');
    }

    public function handle(string $number, string $pushName, string $message): ?string
    {
        $pesan  = explode(' ', strtolower(trim($message)));
        $sub1   = $pesan[1] ?? '';
        $sub2   = $pesan[2] ?? '';
        $nohp62 = WaSender::normalisasi0ke62($number);

        // Cek apakah ada sesi rekap aktif (langkah multi-step)
        $sesiRekap = $this->session->getRekapStep($number);
        if ($sesiRekap) {
            return $this->lanjutRekapStep($number, $message, $sesiRekap);
        }

        // cek rekap (menu interaktif)
        if (strtolower(trim($message)) === 'cek rekap') {
            return $this->mulaiMenuRekap($number);
        }

        // cek rekap <NIS/nohp/kelas>
        if ($sub1 === 'rekap' && $sub2) {
            return $this->rekapIndividu($number, $sub2);
        }

        // cek presensi <NIS/nohp>
        if ($sub1 === 'presensi' && $sub2) {
            return $this->cekPresensiIndividu($sub2);
        }

        // cek wa — statistik akses
        if ($sub1 === 'wa') {
            return $this->statistikWA();
        }

        // cek presensi (tanpa target — statistik hari ini)
        if ($sub1 === 'presensi' && !$sub2) {
            return $this->statistikPresensi();
        }

        // cek <NIS/nohp> — cari data
        if ($sub1 && is_numeric($sub1)) {
            return $this->cariData($number, $sub1);
        }

        // cek (sendiri) — rekap pribadi
        if (strtolower(trim($message)) === 'cek') {
            return $this->rekapPribadi($number);
        }

        return null;
    }

    // ─── Rekap pribadi siswa ───────────────────────────────────────────────
    private function rekapPribadi(string $number): string
    {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);

        // Cek apakah pembimbing
        $pembimbing = $this->db->queryOne(
            "SELECT nip, nama FROM datapembimbing WHERE nohp = ? LIMIT 1",
            [$nohp62]
        );
        if ($pembimbing) {
            return $this->rekapPembimbing($number, $pembimbing);
        }

        $siswa = $this->db->queryOne(
            "SELECT nis, nama, kelas, nohp FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$nohp0, $nohp62]
        );

        if (!$siswa) {
            return "🚫 *Nomor WhatsApp ini belum terdaftar* di sistem presensi.\n\n"
                . "📌 Jika kamu adalah siswa, ketik:\n`REG<spasi>NIS`\n\n"
                . "Jika mengalami kendala, hubungi admin dengan ketik `admin` atau `7`.";
        }

        $nis   = $siswa['nis'];
        $nama  = $siswa['nama'];
        $kelas = $siswa['kelas'];
        $nohp  = $siswa['nohp'];

        // Ambil semua presensi
        $rows = $this->db->query(
            "SELECT DATE(timestamp) as tanggal, ket FROM presensi WHERE nis = ? ORDER BY tanggal ASC",
            [$nis]
        );

        if (empty($rows)) {
            return "📋 *Rekap Presensi*\n\nNama: $nama\nKelas: $kelas\nNIS: $nis\nNo HP: $nohp\n\nTidak ditemukan data presensi.";
        }

        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];
        $data = []; $bulanLabels = [];
        $counter = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];

        foreach ($rows as $r) {
            $tgl = date('j', strtotime($r['tanggal']));
            $bln = date('n', strtotime($r['tanggal']));
            $ket = strtolower($r['ket']);
            $data[$tgl][$bln] = $ikonMap[$ket] ?? '❌';
            $bulanLabels[$bln] = date('F', strtotime($r['tanggal']));
            if (isset($counter[$ket])) $counter[$ket]++;
        }
        ksort($bulanLabels);

        $text  = "```\n📋 Rekap Presensi\n";
        $text .= "Nama : $nama\nKelas: $kelas\nNIS  : $nis\n\n";
        $text .= "Masuk : {$counter['masuk']} x\n";
        $text .= "Izin  : {$counter['izin']} x\n";
        $text .= "Sakit : {$counter['sakit']} x\n";
        $text .= "Libur : {$counter['libur']} x\n\n";
        $text .= str_pad("Tgl", 5);
        foreach ($bulanLabels as $bln => $namaBln) {
            $text .= str_pad(substr($namaBln, 0, 3), 5);
        }
        $text .= "\n";
        for ($tgl = 1; $tgl <= 31; $tgl++) {
            $text .= str_pad($tgl, 5);
            foreach (array_keys($bulanLabels) as $bln) {
                $text .= str_pad($data[$tgl][$bln] ?? '-', 5);
            }
            $text .= "\n";
        }
        $text .= "```\n";
        $text .= "Keterangan:\n✅ = Masuk\n🔵 = Izin\n🟡 = Sakit\n🔴 = Libur\n❌ = Tidak Presensi\n\n";

        $config = require BASE_PATH . '/config/app.php';
        $webUrl = rtrim($config['url'] ?? 'https://pklbos.smknbansari.sch.id', '/');
        $text  .= "📊 Rekap kehadiranmu bisa dilihat melalui link berikut:\n\n";
        $text  .= "🔗 $webUrl/?akses=detail&nis=$nis\n\n";
        $text  .= "📌 Silakan buka link di atas untuk melihat detail kehadiranmu.";

        return $text;
    }

    // ─── Rekap untuk pembimbing ────────────────────────────────────────────
    private function rekapPembimbing(string $number, array $pembimbing): string
    {
        $namaPembimbing = $pembimbing['nama'];
        $nohp62 = WaSender::normalisasi0ke62($number);

        $penempatan = $this->db->query(
            "SELECT * FROM penempatan WHERE nama_pembimbing = ?",
            [$namaPembimbing]
        );

        if (empty($penempatan)) {
            return "📋 Tidak ada siswa yang dibimbing oleh *$namaPembimbing*.";
        }

        // Kelompokkan per DUDI
        $dataDudi = [];
        foreach ($penempatan as $row) {
            $dataDudi[$row['nama_dudika']][] = [
                'nama'  => $row['nama_siswa'],
                'nis'   => $row['nis_siswa'],
                'kelas' => $row['kelas'],
            ];
        }

        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];
        $tanggal7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal7Hari[] = date('Y-m-d', strtotime("-$i days"));
        }

        $msg  = "📱 *Nomor Ini Terdaftar Sebagai Pembimbing*\n\n";
        $msg .= "👨‍🏫 *Nama:* $namaPembimbing\n";
        if (!empty($pembimbing['nip'])) $msg .= "🆔 *NIP:* {$pembimbing['nip']}\n";
        $msg .= "\n📋 *Daftar DUDI dan Siswa + Rekap Kehadiran 7 Hari:*\n```";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "      ";
        foreach ($tanggal7Hari as $tgl) $msg .= $this->hariIndo($tgl) . " ";
        $msg .= "\n      ";
        foreach ($tanggal7Hari as $tgl) $msg .= date('d', strtotime($tgl)) . "  ";
        $msg .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        $i = 1;
        foreach ($dataDudi as $dudi => $siswaList) {
            $msg .= "$i. $dudi\n";
            $j = 1;
            foreach ($siswaList as $siswaData) {
                $nis   = $siswaData['nis'];
                $kelas = $siswaData['kelas'];
                $siswaNama = $siswaData['nama'];

                $rowNohp = $this->db->queryOne("SELECT nohp FROM datasiswa WHERE nis = ? LIMIT 1", [$nis]);
                $nohp = $rowNohp['nohp'] ?? '-Belum Reg-';

                $rekap = "";
                foreach ($tanggal7Hari as $tgl) {
                    $hari     = date('D', strtotime($tgl));
                    $isWeekend = in_array($hari, ['Sat','Sun']);
                    $pr = $this->db->queryOne(
                        "SELECT ket FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
                        [$nis, $tgl]
                    );
                    if ($pr) {
                        $rekap .= ($ikonMap[strtolower($pr['ket'])] ?? '❌') . " ";
                    } else {
                        $rekap .= ($isWeekend ? '➖' : '❌') . " ";
                    }
                }
                $msg .= "   $j) $siswaNama\n      ($kelas | $nis | $nohp)\n      $rekap\n";
                $j++;
            }
            $i++;
        }

        $msg .= "Keterangan:\n✅ = Masuk\n🔵 = Izin\n🟡 = Sakit\n🔴 = Libur\n➖ = Libur Weekend\n❌ = Tidak Presensi\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $msg .= "```\n";
        $msg .= "*📌 Layanan Presensi PKL untuk Pembimbing*\n\n";
        $msg .= "Berikut perintah yang tersedia:\n\n";
        $msg .= "1️⃣ `cek`\n    ➜ Lihat status nomor Anda.\n\n";
        $msg .= "2️⃣ `cek <NIS/NoHP>`\n    ➜ Lihat data siswa.\n    Contoh: `cek 1234` atau `cek 089123456789`\n\n";
        $msg .= "3️⃣ `cek rekap`\n    ➜ Lihat rekap *Kelas*, *Pembimbing*, atau *DUDI*.\n\n";
        $msg .= "4️⃣ `cek presensi <NIS/NoHP>`\n    ➜ Presensi individu hari ini.\n    Contoh: `cek presensi 1234`\n\n";
        $msg .= "5️⃣ `cek rekap <NIS/NoHP>`\n    ➜ Rekap semua presensi individu.\n    Contoh: `cek rekap 1234`\n\n";
        $msg .= "6️⃣ `cek rekap <KELAS>`\n    ➜ Rekap presensi per kelas.\n    Contoh: `cek rekap xiat1`\n\n";
        $msg .= "💡 *Tips*: Gunakan huruf kecil tanpa spasi untuk kode kelas.\n";
        $msg .= "📢 Ada data yang salah? Beri tahu Admin ➜ Balas dengan ketik `Admin`.";

        return $msg;
    }

    // ─── Rekap individu (NIS/nohp/kelas) ──────────────────────────────────
    private function rekapIndividu(string $number, string $input): string
    {
        $input = trim($input);

        if (is_numeric($input)) {
            if (strlen($input) < 10) {
                $nis = $input;
            } else {
                $target = WaSender::normalisasi62ke0($input);
                $row = $this->db->queryOne("SELECT nis FROM datasiswa WHERE nohp = ? LIMIT 1", [$target]);
                if (!$row) return "❌ Nomor HP tidak ditemukan dalam data siswa.";
                $nis = $row['nis'];
            }

            return $this->rekapByNis($nis);
        }

        // Berdasarkan kelas: xiat1, xiidkv2, dst
        $inputLower = strtolower($input);
        if (preg_match('/^(xi|xii)(at|dkv|te)(\d+)$/', $inputLower, $m)) {
            $kelas = strtoupper($m[1]) . ' ' . strtoupper($m[2]) . ' ' . $m[3];
            return $this->rekapKelas($kelas);
        }

        return "Format tidak valid. Gunakan NIS, NoHP, atau kode kelas (contoh: `xiat1`, `xiidkv2`).";
    }

    private function rekapByNis(string $nis): string
    {
        $siswa = $this->db->queryOne("SELECT nama, kelas, nohp FROM datasiswa WHERE nis = ? LIMIT 1", [$nis]);
        if (!$siswa) return "❌ NIS tidak ditemukan dalam data siswa.";

        $nama  = $siswa['nama'];
        $kelas = $siswa['kelas'];
        $nohp  = $siswa['nohp'];

        $rows = $this->db->query(
            "SELECT DATE(timestamp) as tanggal, ket FROM presensi WHERE nis = ? ORDER BY tanggal ASC",
            [$nis]
        );

        if (empty($rows)) {
            return "📋 *Rekap Presensi*\n\nNama: $nama\nKelas: $kelas\nNIS: $nis\nNo HP: $nohp\n\nTidak ditemukan data presensi.";
        }

        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];
        $data = []; $bulanLabels = [];
        $counter = ['masuk'=>0,'izin'=>0,'sakit'=>0,'libur'=>0];

        foreach ($rows as $r) {
            $tgl = date('j', strtotime($r['tanggal']));
            $bln = date('n', strtotime($r['tanggal']));
            $ket = strtolower($r['ket']);
            $data[$tgl][$bln] = $ikonMap[$ket] ?? '❌';
            $bulanLabels[$bln] = date('F', strtotime($r['tanggal']));
            if (isset($counter[$ket])) $counter[$ket]++;
        }
        ksort($bulanLabels);

        $text  = "```\n📋 Rekap Presensi\n";
        $text .= "Nama : $nama\nKelas: $kelas\nNIS  : $nis\n\n";
        $text .= "Masuk : {$counter['masuk']} x\nIzin  : {$counter['izin']} x\nSakit : {$counter['sakit']} x\nLibur : {$counter['libur']} x\n\n";
        $text .= str_pad("Tgl", 5);
        foreach ($bulanLabels as $bln => $namaBln) $text .= str_pad(substr($namaBln, 0, 3), 5);
        $text .= "\n";
        for ($tgl = 1; $tgl <= 31; $tgl++) {
            $text .= str_pad($tgl, 5);
            foreach (array_keys($bulanLabels) as $bln) $text .= str_pad($data[$tgl][$bln] ?? '-', 5);
            $text .= "\n";
        }
        $text .= "```\nKeterangan:\n✅ = Masuk\n🔵 = Izin\n🟡 = Sakit\n🔴 = Libur\n❌ = Tidak Presensi";
        return $text;
    }

    private function rekapKelas(string $kelas): string
    {
        return $this->rekapGrid("Kelas: $kelas", "SELECT * FROM datasiswa WHERE kelas = ? ORDER BY nama", [$kelas]);
    }

    private function rekapGrid(string $judul, string $sql, array $params): string
    {
        $rows = $this->db->query($sql, $params);
        if (empty($rows)) return "Tidak ditemukan data siswa untuk '$judul'.";

        $dates = [];
        for ($i = 5; $i >= 0; $i--) $dates[] = date('Y-m-d', strtotime("-$i days"));

        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];
        $presensiData = [];

        foreach ($rows as $row) {
            $nis = $row['nis'];
            $presensiData[$nis] = [
                'nama'      => $row['nama'],
                'kelas'     => $row['kelas'],
                'nohp'      => $row['nohp'] ?: '-',
                'nis'       => $nis,
                'kehadiran' => [],
            ];
            foreach ($dates as $tgl) {
                $hari = date('N', strtotime($tgl));
                $presensiData[$nis]['kehadiran'][$tgl] = ($hari >= 6) ? '➖' : '❌';
            }
        }

        $nisList = array_map(fn($n) => "'" . $n . "'", array_keys($presensiData));
        $inNIS   = implode(",", $nisList);
        $prRows  = $this->db->query(
            "SELECT nis, DATE(timestamp) as tanggal, LOWER(ket) as ket FROM presensi WHERE nis IN ($inNIS) AND DATE(timestamp) >= ?",
            [$dates[0]]
        );
        foreach ($prRows as $r) {
            if (isset($presensiData[$r['nis']])) {
                $presensiData[$r['nis']]['kehadiran'][$r['tanggal']] = $ikonMap[$r['ket']] ?? '❌';
            }
        }

        $text  = "```\n📅 Rekap presensi 6 hari terakhir:\n$judul\n";
        $text .= "Keterangan:\n✅=Masuk 🔵=Izin 🟡=Sakit 🔴=Libur ➖=Weekend ❌=Absen\n\n";
        $text .= str_pad("No", 4) . str_pad("Nama & Kelas", 18);
        foreach ($dates as $d) $text .= date('d', strtotime($d)) . "'";
        $text .= "\n";
        $text .= str_repeat(" ", 4) . str_repeat(" ", 18);
        foreach ($dates as $d) $text .= $this->hariIndo2($d) . " ";
        $text .= "\n" . str_repeat("-", 40) . "\n";

        $no = 1;
        foreach ($presensiData as $siswa) {
            $text .= str_pad($no++, 4);
            $text .= str_pad(substr($siswa['nama'], 0, 18), 18);
            foreach ($dates as $d) $text .= ($siswa['kehadiran'][$d] ?? '❌') . " ";
            $text .= "\n";
            $kelasStr = $siswa['kelas'] . " | " . $siswa['nis'] . " | " . $siswa['nohp'];
            $text .= str_repeat(' ', 4) . str_pad($kelasStr, 20) . "\n";
        }
        $text .= "```";
        return $text;
    }

    // ─── Cek presensi individu hari ini ───────────────────────────────────
    private function cekPresensiIndividu(string $target): string
    {
        $target = trim($target);
        if (!is_numeric($target)) {
            return "❌ *Format Salah*\n\nGunakan format:\n`cek presensi <NIS/NoHP>`\n\nContoh:\n- `cek presensi 1234`\n- `cek presensi 6281234567890`";
        }

        if (strlen($target) <= 10) {
            $row = $this->db->queryOne(
                "SELECT * FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
                [$target, date('Y-m-d')]
            );
        } else {
            $target0 = WaSender::normalisasi62ke0($target);
            $row = $this->db->queryOne(
                "SELECT p.* FROM presensi p JOIN datasiswa d ON p.nis = d.nis WHERE d.nohp = ? AND DATE(p.timestamp) = ? LIMIT 1",
                [$target0, date('Y-m-d')]
            );
        }

        return $row
            ? "✅ *Sudah presensi hari ini.*\n\n👍"
            : "❌ *Belum presensi hari ini.*\n\n😊";
    }

    // ─── Cari data (NIS/nohp) ─────────────────────────────────────────────
    private function cariData(string $number, string $target): string
    {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);

        if (strlen($target) <= 10) {
            // Kemungkinan NIS
            $siswa = $this->db->queryOne("SELECT * FROM datasiswa WHERE nis = ? LIMIT 1", [$target]);
            if ($siswa) {
                $nohp = $siswa['nohp'] ?? "Tidak ada nomor WA";
                return "✅ *NIS ditemukan!*\n\n👤 *Nama:* {$siswa['nama']}\n🏫 *Kelas:* {$siswa['kelas']}\n🆔 *NIS:* {$siswa['nis']}\n📱 *No WA:* $nohp\n\n📌 Data ini terdaftar di sistem presensi.";
            }
        }

        // Kemungkinan nomor HP
        $target0 = WaSender::normalisasi62ke0($target);
        $siswa = $this->db->queryOne("SELECT * FROM datasiswa WHERE nohp = ? LIMIT 1", [$target0]);
        if ($siswa) {
            return "✅ *Nomor ditemukan!*\n\n📱 *Nomor:* $target0\n👤 *Nama:* {$siswa['nama']}\n🏫 *Kelas:* {$siswa['kelas']}\n🆔 *NIS:* {$siswa['nis']}\n\n📌 Data ini terdaftar di sistem presensi.";
        }

        // Coba pembimbing
        $target62 = WaSender::normalisasi0ke62($target0);
        $pembimbing = $this->db->queryOne("SELECT * FROM datapembimbing WHERE nohp = ? LIMIT 1", [$target62]);
        if ($pembimbing) {
            return "✅ *Nomor ditemukan!*\n\n📱 *Nomor:* $target62\n👤 *Nama:* {$pembimbing['nama']}\n🆔 *NIP:* {$pembimbing['nip']}\n\n📌 Data ini terdaftar di sistem presensi sebagai Pembimbing PKL {$this->tahun}.";
        }

        return "❌ *Nomor atau NIS tidak ditemukan di sistem.*\n\n📌 Pastikan Anda sudah terdaftar dengan benar.\nJika belum, silakan lakukan pendaftaran terlebih dahulu atau hubungi admin untuk bantuan.";
    }

    // ─── Statistik presensi hari ini ──────────────────────────────────────
    private function statistikPresensi(): string
    {
        $hadir = $this->db->queryOne(
            "SELECT COUNT(DISTINCT nis) as hadir FROM presensi WHERE DATE(timestamp) = CURDATE()"
        )['hadir'] ?? 0;
        $total = $this->db->queryOne("SELECT COUNT(*) as total FROM datasiswa")['total'] ?? 0;
        $belum = $total - $hadir;
        $pHadir = $total > 0 ? round(($hadir/$total)*100, 2) : 0;
        $pBelum = $total > 0 ? round(($belum/$total)*100, 2) : 0;

        $punyaNohp = $this->db->queryOne(
            "SELECT COUNT(*) as n FROM datasiswa WHERE nohp IS NOT NULL AND TRIM(nohp) != ''"
        )['n'] ?? 0;
        $belumNohp = $total - $punyaNohp;
        $pNohp = $total > 0 ? round(($punyaNohp/$total)*100, 2) : 0;
        $pBelumNohp = $total > 0 ? round(($belumNohp/$total)*100, 2) : 0;

        // Grafik per jam hari ini
        $jamRows = $this->db->query(
            "SELECT HOUR(timestamp) AS jam, COUNT(DISTINCT nis) AS jumlah FROM presensi WHERE DATE(timestamp) = CURDATE() GROUP BY jam"
        );
        $dataJam = array_fill(0, 24, 0);
        foreach ($jamRows as $r) $dataJam[(int)$r['jam']] = (int)$r['jumlah'];
        $maxJam = max($dataJam) ?: 1;

        $grafik = "";
        foreach ($dataJam as $jam => $jumlah) {
            if ($jumlah > 0) {
                $bar = str_repeat("█", round(($jumlah/$maxJam)*20));
                $grafik .= str_pad($jam,2,'0',STR_PAD_LEFT) . ":00 → $bar $jumlah\n";
            }
        }

        // Status hari ini
        $statusRows = $this->db->query(
            "SELECT ket, COUNT(*) AS jumlah FROM presensi WHERE DATE(timestamp) = CURDATE() GROUP BY ket"
        );
        $ikonMap = ['masuk'=>'✅','izin'=>'🔵','sakit'=>'🟡','libur'=>'🔴'];
        $maxStatus = 0;
        foreach ($statusRows as $r) $maxStatus = max($maxStatus, $r['jumlah']);
        $grafikStatus = "";
        foreach ($statusRows as $r) {
            $ikon = $ikonMap[$r['ket']] ?? '❓';
            $bar  = $maxStatus > 0 ? str_repeat("█", round(($r['jumlah']/$maxStatus)*20)) : '';
            $grafikStatus .= sprintf("%s %-6s → %-2s %d\n", $ikon, ucfirst($r['ket']), $bar, $r['jumlah']);
        }

        $tanggal = date('d M Y');
        $pukul   = date('H:i:s');

        $msg  = "📊 *Rekap Presensi*\nPer: $tanggal Pukul $pukul\n\n";
        $msg .= "```\n";
        $msg .= "👥 Total siswa     : $total siswa\n";
        $msg .= "✅ Sudah presensi  : $hadir siswa ($pHadir%)\n";
        $msg .= "❌ Belum presensi  : $belum siswa ($pBelum%)\n";
        $msg .= "📱 WA terdaftar    : $punyaNohp siswa ($pNohp%)\n";
        $msg .= "⚠️ WA tak terdaftar: $belumNohp siswa ($pBelumNohp%)\n";
        $msg .= "```\n\n";
        $msg .= "📈 *Aktivitas Presensi per Jam (Hari Ini)*\n";
        $msg .= "```\n$grafik```\n";
        if ($grafikStatus) $msg .= "```\n📊 Jumlah Presensi Hari Ini:\n$grafikStatus```";
        return $msg;
    }

    // ─── Statistik akses WA ───────────────────────────────────────────────
    private function statistikWA(): string
    {
        $rows = $this->db->query(
            "SELECT HOUR(timestamp) AS hour, COUNT(*) AS count FROM tmp GROUP BY hour ORDER BY hour"
        );
        $counts = array_fill(0, 24, 0);
        $total  = 0;
        foreach ($rows as $r) { $counts[(int)$r['hour']] = (int)$r['count']; $total += (int)$r['count']; }

        $msg = "📊 Persentase Akses WA per Jam (All Time)\n\n";
        for ($h = 0; $h < 24; $h++) {
            $pct = $total > 0 ? ($counts[$h]/$total)*100 : 0;
            if ($pct > 0) {
                $bar = str_repeat("█", round($pct/100*30));
                $msg .= str_pad($h,2,'0',STR_PAD_LEFT) . ":00 → $bar " . number_format($pct,1) . "% ({$counts[$h]})\n";
            }
        }

        // Hari ini
        $rowsToday = $this->db->query(
            "SELECT HOUR(timestamp) AS hour, COUNT(*) AS count FROM tmp WHERE DATE(timestamp) = CURDATE() GROUP BY hour ORDER BY hour"
        );
        $countsToday = array_fill(0, 24, 0);
        $totalToday  = 0;
        foreach ($rowsToday as $r) { $countsToday[(int)$r['hour']] = (int)$r['count']; $totalToday += (int)$r['count']; }

        $msg .= "\n📊 Persentase Akses WA per Jam (Hari Ini)\n\n";
        for ($h = 0; $h < 24; $h++) {
            $pct = $totalToday > 0 ? ($countsToday[$h]/$totalToday)*100 : 0;
            if ($pct > 0) {
                $bar = str_repeat("█", round($pct/100*30));
                $msg .= str_pad($h,2,'0',STR_PAD_LEFT) . ":00 → $bar " . number_format($pct,1) . "% ({$countsToday[$h]})\n";
            }
        }
        if ($totalToday === 0) $msg .= "Tidak ada data akses WA hari ini.";

        return $msg;
    }

    // ─── Menu rekap interaktif (multi-step) ───────────────────────────────
    private function mulaiMenuRekap(string $number): string
    {
        $this->session->setRekapStep($number, ['step' => ['cek rekap'], 'menu' => 'rekap', 'sublist' => []]);

        $msg  = "📊 *Menu Rekap Presensi*\n\nSilakan pilih salah satu menu berikut:\n\n";
        $msg .= "01. Rekap berdasarkan *KELAS*\n";
        $msg .= "02. Rekap berdasarkan *PEMBIMBING*\n";
        $msg .= "03. Rekap berdasarkan *DUDI*\n\n";
        $msg .= "🔁 *Balas dengan hanya angka 2 digit*, contoh: `01`";
        return $msg;
    }

    private function lanjutRekapStep(string $number, string $message, array $sesi): string
    {
        $input = strtolower(trim($message));
        $step  = $sesi['step'] ?? [];

        if (count($step) === 1) {
            // Pilihan utama 01/02/03
            if ($input === '01') {
                $rows = $this->db->query("SELECT DISTINCT kelas FROM datasiswa ORDER BY kelas ASC");
                $text = "Pilih kelas untuk rekap:\n";
                $list = [];
                $i = 1;
                foreach ($rows as $r) {
                    $kode = "1" . str_pad($i, 2, "0", STR_PAD_LEFT);
                    $text .= "$kode {$r['kelas']}\n";
                    $list[$kode] = $r['kelas'];
                    $i++;
                }
                $sesi['step'][]   = $input;
                $sesi['sublist']  = $list;
                $this->session->setRekapStep($number, $sesi);
                return $text . "\nBalas dengan ketik 3 digit kode di depan nama kelas.";
            } elseif ($input === '02') {
                $rows = $this->db->query("SELECT id, nama FROM datapembimbing ORDER BY nama ASC");
                $text = "Pilih pembimbing untuk rekap:\n";
                $list = [];
                $i = 1;
                foreach ($rows as $r) {
                    $kode = "2" . str_pad($i, 2, "0", STR_PAD_LEFT);
                    $text .= "$kode {$r['nama']}\n";
                    $list[$kode] = $r['nama'];
                    $i++;
                }
                $sesi['step'][]   = $input;
                $sesi['sublist']  = $list;
                $this->session->setRekapStep($number, $sesi);
                return $text . "\nBalas dengan ketik 3 digit kode di depan nama pembimbing.";
            } elseif ($input === '03') {
                $rows = $this->db->query(
                    "SELECT DISTINCT nama_dudika FROM penempatan WHERE nama_dudika IS NOT NULL AND nama_dudika != '' ORDER BY nama_dudika ASC"
                );
                $text = "Pilih DUDI untuk rekap:\n";
                $list = [];
                $i = 1;
                foreach ($rows as $r) {
                    $kode = "3" . str_pad($i, 2, "0", STR_PAD_LEFT);
                    $text .= "$kode {$r['nama_dudika']}\n";
                    $list[$kode] = $r['nama_dudika'];
                    $i++;
                }
                $sesi['step'][]   = $input;
                $sesi['sublist']  = $list;
                $this->session->setRekapStep($number, $sesi);
                return $text . "\nBalas dengan ketik 3 digit kode di depan nama DUDI.";
            } else {
                $this->session->clearRekapStep($number);
                return "⚠️ *Pilihan Tidak Valid!*\n\nBalas dengan `cek rekap` untuk mengulang.";
            }
        }

        if (count($step) === 2) {
            $sublist  = $sesi['sublist'] ?? [];
            $selected = $sublist[$input] ?? null;
            $this->session->clearRekapStep($number);

            if (!$selected) {
                return "❌ *Pilihan Tidak Valid!*\n\nBalas `cek rekap` untuk mengulang.";
            }

            if (str_starts_with($input, '1')) {
                return $this->rekapGrid("Kelas: $selected",
                    "SELECT * FROM datasiswa WHERE kelas = ? ORDER BY nama", [$selected]);
            } elseif (str_starts_with($input, '2')) {
                return $this->rekapGrid("Pembimbing: $selected",
                    "SELECT d.* FROM datasiswa d JOIN penempatan p ON d.nis = p.nis_siswa WHERE p.nama_pembimbing = ? ORDER BY d.nama",
                    [$selected]);
            } elseif (str_starts_with($input, '3')) {
                return $this->rekapGrid("DUDI: $selected",
                    "SELECT d.* FROM datasiswa d JOIN penempatan p ON d.nis = p.nis_siswa WHERE p.nama_dudika = ? ORDER BY d.nama",
                    [$selected]);
            }
        }

        $this->session->clearRekapStep($number);
        return "⚠️ Sesi rekap tidak valid. Balas `cek rekap` untuk mengulang.";
    }

    // ─── Helpers ──────────────────────────────────────────────────────────
    private function hariIndo(string $tgl): string
    {
        $map = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
        return $map[date('D', strtotime($tgl))] ?? date('D', strtotime($tgl));
    }

    private function hariIndo2(string $tgl): string
    {
        $map = ['Sun'=>'Mi','Mon'=>'Sn','Tue'=>'Sl','Wed'=>'Ra','Thu'=>'Ka','Fri'=>'Ju','Sat'=>'Sb'];
        return $map[date('D', strtotime($tgl))] ?? date('D', strtotime($tgl));
    }
}
