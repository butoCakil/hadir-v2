<?php

namespace App\Api;

use App\Core\Database;
use App\Models\WabotSessionModel;
use App\Api\Handlers\PresensiHandler;
use App\Api\Handlers\LupaHandler;
use App\Api\Handlers\RegistrasiHandler;
use App\Api\Handlers\AdminHandler;
use App\Api\Handlers\InfoHandler;
use App\Api\Handlers\BatalHandler;
use App\Api\Handlers\CekHandler;
use App\Api\Handlers\SetHandler;
use App\Api\Handlers\UnregHandler;
use App\Api\Handlers\InputHandler;
use App\Api\Handlers\JurnalHandler;
use App\Api\Handlers\CariHandler;

class WabotHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private PresensiHandler $presensi;
    private LupaHandler $lupa;
    private RegistrasiHandler $registrasi;
    private AdminHandler $admin;
    private InfoHandler $info;
    private BatalHandler $batal;
    private CekHandler $cek;
    private SetHandler $set;
    private UnregHandler $unreg;
    private InputHandler $input;
    private JurnalHandler $jurnal;
    private CariHandler $cari;
    private string $adminNumber;
    private string $footer;
    private string $tahun;

    public function __construct(bool $simulatorMode = false)
    {
        $config            = require BASE_PATH . '/config/app.php';
        $this->adminNumber = $config['wa']['admin_number'] ?? '';
        $this->tahun       = date('Y');
        $this->footer      = "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n> 📝 _Sistem Presensi PKL_ *SMK Negeri Bansari*\n©️ ```{$this->tahun}```";

        $this->db         = Database::getInstance();
        $this->sender     = new WaSender();
        $this->session    = new WabotSessionModel();
        $this->presensi   = new PresensiHandler($this->db, $this->sender, $this->adminNumber);
        $this->lupa       = new LupaHandler($this->db, $this->sender, $this->session);
        $this->registrasi = new RegistrasiHandler($this->db, $this->sender, $this->session, $this->adminNumber, $simulatorMode);
        $this->admin      = new AdminHandler($this->db, $this->sender, $this->session, $this->adminNumber);
        $this->info       = new InfoHandler($this->sender);
        $this->batal      = new BatalHandler($this->db, $this->sender, $this->adminNumber);
        $this->cek        = new CekHandler($this->db, $this->sender, $this->session);
        $this->set        = new SetHandler($this->db, $this->sender, $this->adminNumber);
        $this->unreg      = new UnregHandler($this->db, $simulatorMode);
        $this->input      = new InputHandler($this->db, $this->sender);
        $this->jurnal     = new JurnalHandler($this->db);
        $this->cari       = new CariHandler($this->db);
    }

    public function handle(array $data, bool $simulatorMode = false): ?string
    {
        $source = strtoupper(trim($data['source'] ?? ''));
        if ($source !== 'WHACENTER') {
            if (!$simulatorMode) { http_response_code(403); exit('Invalid source'); }
            return '❌ Source tidak valid. Harus WHACENTER.';
        }

        $number      = $data['from']        ?? '';
        $pushName    = $data['pushName']     ?? '';
        $message     = trim($data['message'] ?? '');
        $mediaUrl    = $data['media']        ?? null;
        $messageType = $data['message_type'] ?? 'text';

        if (empty($number)) {
            if (!$simulatorMode) { http_response_code(400); exit('Missing number'); }
            return '❌ Nomor pengirim kosong.';
        }

        $number = preg_replace('/[^0-9+]/', '', $number);
        $number = WaSender::normalisasi62ke0($number);

        // Cek nomor via kolom encryp (nomor LID)
        $byEncryp = $this->db->queryOne("SELECT nohp FROM datasiswa WHERE encryp = ? LIMIT 1", [$number]);
        if ($byEncryp && !empty($byEncryp['nohp'])) $number = $byEncryp['nohp'];

        // Sanitasi URL media
        $mediaUrl = $mediaUrl ? filter_var(preg_replace('/[\x00-\x1F\x7F]/u', '', $mediaUrl), FILTER_SANITIZE_URL) : null;
        if (!$mediaUrl) $mediaUrl = null;

        // Simpan ke tabel tmp
        $this->simpanTmp($number, $message);

        // Route pesan
        $reply = $this->route($number, $pushName, $message, $mediaUrl, $messageType);

        if ($reply !== null) {
            $reply .= $this->footer;
            if ($simulatorMode) return $reply;
            $this->sender->send($number, $reply, null);
        }

        return null;
    }

    private function route(
        string $number, string $pushName, string $message,
        ?string $mediaUrl, string $messageType
    ): ?string {
        $msgLower = strtolower(trim($message));
        $nohp62   = WaSender::normalisasi0ke62($number);

        // ── 1. Live location ──
        if ($messageType === 'live-location' && $this->isValidCoordinate($message)) {
            return $this->handleLiveLocation($message, $number);
        }
        if ($messageType === 'location' && $this->isValidCoordinate($message)) {
            list($lat, $lon) = explode(",", $message);
            return "Data live-location TIDAK valid.\nIni bukan lokasi Anda sekarang\n\n🔗 https://www.google.com/maps?q=$lat,$lon";
        }

        // ── 2. Pending presensi aktif (foto dulu → ket setelahnya) ──
        $pendingPresensi = $this->session->getPendingPresensi($number);
        if ($pendingPresensi) {
            $result = $this->handlePendingPresensi($number, $pushName, $message, $mediaUrl, $pendingPresensi);
            if ($result !== null) return $result;
        }

        // ── 3. Konfirmasi pending registrasi (YA / TIDAK) ──
        $pending = $this->session->getPending($number);
        if ($pending && isset($pending['type']) && $pending['type'] !== 'pending_presensi') {
            if (preg_match('/^ya($|\s|a+$)/i', $message) || strtolower($message) === 'y' || str_starts_with(strtolower($message), 'iya')) {
                $this->session->clearPending($number);
                if ($pending['type'] === 'confirm_reg') {
                    return $this->registrasi->handleKonfirmasiYa($number, $pushName, $pending);
                }
                if ($pending['type'] === 'confirm_libur') {
                    return $this->handleKonfirmasiLibur($number, $pending);
                }
                return "✅ Dikonfirmasi.";
            }
            if (preg_match('/^tidak(\s|$)/i', $message) || in_array(strtolower($message), ['tdk', 'tak', 'no', 'cancel'])) {
                $this->session->clearPending($number);
                if ($pending['type'] === 'confirm_reg') {
                    return "⚠️ *Pendaftaran dibatalkan.*\n\nUntuk mengulangi pendaftaran, balas dengan mengetik:\n`reg <spasi> NIS`\n\nContoh:\n`reg 1234`";
                }
                if ($pending['type'] === 'confirm_libur') {
                    return "🚨 *Ingat!*\n\nKamu belum melakukan presensi hari ini.\nSegera lakukan presensi seperti biasa ya.";
                }
                return "⚠️ Dibatalkan.";
            }
        }

        // YA/TIDAK tanpa pending aktif → notif admin
        if (preg_match('/^ya($|\s|a+$)/i', $message) || strtolower($message) === 'y' || str_starts_with(strtolower($message), 'iya')) {
            $nohp62 = WaSender::normalisasi0ke62($number);
            $adminMsg = "$number ~ $pushName:\n$message\n\nSesi Hub.Admin *Tidak Aktif*\nSesi bukan Konfirmasi Reg: ya";
            $this->sender->send($this->adminNumber, $adminMsg, null);
            return null;
        }

        // ── 4. Akhiri sesi admin jika ketik info/menu ──
        $isAdminSession = $this->session->isAdminSession($nohp62);
        if ($isAdminSession && in_array($msgLower, ['info', 'menu'])) {
            $this->admin->akhiriSesi($number);
            $isAdminSession = false;
        }

        // ── 5. Dalam sesi admin — teruskan ke admin ──
        if ($isAdminSession) {
            if (in_array($msgLower, ['7', 'admin'])) {
                return "✅ Sesi admin sudah aktif.\n\nSilakan ketik pesan Anda langsung.\n\nKetik `info` untuk keluar dari sesi admin.";
            }
            // Deteksi typo lalu forward ke admin
            $typoReply = $this->detectTypo($message);
            if ($typoReply) {
                $this->notifAdmin($number, $pushName, $message, $mediaUrl, true, $typoReply);
                return $typoReply;
            }
            $this->notifAdmin($number, $pushName, $message, $mediaUrl, true);
            return null; // tidak balas — admin yang balas
        }

        // ── 6. Perintah balas (khusus admin) ──
        if (str_starts_with($msgLower, 'balas ')) {
            return $this->admin->handleBalas($number, $message, $mediaUrl);
        }

        // ── 7. Mulai sesi admin ──
        if (in_array($msgLower, ['7', 'admin'])) {
            return $this->admin->mulaiSesi($number, $pushName);
        }

        // ── 8. set <NIS> <nohp> (admin) ──
        if (preg_match('/^set\s+\d{4,}\s+[\d\s\+\-]+/i', $message)) {
            return $this->set->handle($number, $pushName, $message);
        }

        // ── 9. unreg ──
        if (str_starts_with($msgLower, 'unreg')) {
            return $this->unreg->handle($number, $message);
        }

        // ── 10. Registrasi ──
        if (str_starts_with($msgLower, 'reg')) {
            return $this->registrasi->handle($number, $pushName, $message);
        }

        // ── 11. Lupa presensi ──
        if (str_starts_with($msgLower, 'lupa')) {
            return $this->lupa->handle($number, $pushName, $message, $mediaUrl);
        }

        // ── 12. Batal presensi ──
        if (str_starts_with($msgLower, 'batal')) {
            return $this->batal->handle($number, $pushName, $message);
        }

        // ── 13. Cek (multi-level) ──
        if (str_starts_with($msgLower, 'cek') || $msgLower === '6' || $msgLower === 'rekap') {
            return $this->cek->handle($number, $pushName,
                in_array($msgLower, ['6', 'rekap']) ? 'cek' : $message
            );
        }

        // ── 14. Input (pembimbing) ──
        if (str_starts_with($msgLower, 'input')) {
            return $this->input->handle($number, $message);
        }

        // ── 15. Jurnal ──
        if (str_starts_with($msgLower, 'jurnal')) {
            return $this->jurnal->handle($number, $message);
        }

        // ── 16. Cari ──
        if (str_starts_with($msgLower, 'cari')) {
            return $this->cari->handle($number, $message);
        }

        // ── 17. Sesi rekap interaktif aktif ──
        $rekapStep = $this->session->getRekapStep($number);
        if ($rekapStep) {
            return $this->cek->handle($number, $pushName, $message);
        }

        // ── 18. Info / menu ──
        $infoReply = $this->info->handle($number, $message);
        if ($infoReply !== null) return $infoReply;

        // ── 19. # broadcast ──
        if (str_starts_with($message, '#')) {
            return $this->handleBroadcast();
        }

        // ── 20. Presensi (masuk/izin/sakit/libur) ──
        $ketValid = ['masuk', 'izin', 'sakit', 'libur'];
        $firstWord = strtolower(explode(' ', trim($message))[0] ?? '');
        if (in_array($firstWord, $ketValid)) {
            return $this->presensi->handle($number, $pushName, $message, $mediaUrl);
        }

        // ── 21. Foto tanpa caption — pending presensi ──
        if (!empty($mediaUrl) && $messageType === 'media') {
            return $this->handleFotoTanpaKapsi($number, $pushName, $message, $mediaUrl);
        }

        // ── 22. Typo detection ──
        $typoReply = $this->detectTypo($message);
        if ($typoReply) return $typoReply;

        // ── 23. Pesan bebas — anti-spam + notif admin ──
        return $this->handlePesanBebas($number, $pushName, $message, $mediaUrl);
    }

    // ─── Pending presensi: foto dulu → ket setelahnya ─────────────────────
    private function handlePendingPresensi(
        string $number, string $pushName, string $message, ?string $mediaUrl, array $pending
    ): ?string {
        $status   = $pending['status']    ?? '';
        $catatan  = $pending['catatan']   ?? '';
        $nis      = $pending['nis']        ?? '';
        $nama     = $pending['namasiswa'] ?? '';
        $kelas    = $pending['kelas']     ?? '';
        $tanggal  = date('Y-m-d');

        // Cek sudah presensi hari ini
        $existing = $this->db->queryOne(
            "SELECT ket, timestamp FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
            [$nis, $tanggal]
        );
        if ($existing) {
            $jam = date('H:i:s', strtotime($existing['timestamp']));
            $this->session->clearPendingPresensi($number);
            return "✅ Hai $nama ($nis - $kelas),\n\nPresensimu untuk hari ini pada pukul $jam *sudah tercatat* sebelumnya.\n\nJadi, tidak perlu presensi ulang ya. Terima kasih! 🙌";
        }

        // Kasus 1: Sudah ada foto di pending, user kirim teks keterangan
        if (!empty($pending['foto']) && empty($status) && !empty($message)) {
            $parts  = explode(" ", $message, 2);
            $status = strtolower(preg_replace("/[^a-z]/", "", $parts[0]));
            $catatan = isset($parts[1]) ? trim($parts[1]) : '';
            $ketValid = ['masuk', 'izin', 'sakit', 'libur'];

            if (!in_array($status, $ketValid)) {
                $typo = $this->detectTypo($message);
                if ($typo) return $typo;
                return "🚫 *Keterangan presensi* `$status` *tidak valid!*\n\n📌 Gunakan salah satu:\n- `masuk`\n- `izin`\n- `sakit`\n- `libur`";
            }

            $kode = $this->generateKode();
            $foto = $pending['foto'];
            $this->db->execute(
                "INSERT INTO presensi (nis, namasiswa, kelas, ket, catatan, link, kode) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$nis, $nama, $kelas, $status, $catatan, $foto, $kode]
            );
            $this->session->clearPendingPresensi($number);
            $this->jalankanProseschat($nis, $kode, $foto);
            return $this->pesanPresensiOK($status, $catatan, $nama, $kelas, $nis);
        }

        // Kasus 2: Pending tanpa foto, user kirim foto
        if (empty($pending['foto']) && !empty($mediaUrl)) {
            if (!empty($status)) {
                // Status sudah ada → langsung insert
                $kode = $this->generateKode();
                $this->db->execute(
                    "INSERT INTO presensi (nis, namasiswa, kelas, ket, catatan, link, kode) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$nis, $nama, $kelas, $status, $catatan, $mediaUrl, $kode]
                );
                $this->session->clearPendingPresensi($number);
                $this->jalankanProseschat($nis, $kode, $mediaUrl);
                return $this->pesanPresensiOK($status, $catatan, $nama, $kelas, $nis);
            } else {
                // Simpan foto, minta keterangan
                $pending['foto']      = $mediaUrl;
                $pending['timestamp'] = time();
                $this->session->setPendingPresensi($number, $pending);
                return "📄 Foto sudah diterima.\nSekarang silakan kirim keterangan presensi beserta catatannya.\n\nContoh: `Masuk Memasang instalasi listrik`";
            }
        }

        return null;
    }

    // ─── Foto tanpa caption → simpan pending ──────────────────────────────
    private function handleFotoTanpaKapsi(
        string $number, string $pushName, string $message, string $mediaUrl
    ): string {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $nohp62 = WaSender::normalisasi0ke62($number);
        $tanggal = date('Y-m-d');

        $siswa = $this->db->queryOne(
            "SELECT nis, nama, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$nohp0, $nohp62]
        );

        // Cek apakah ada keterangan di caption
        $firstWord = strtolower(explode(' ', trim($message))[0] ?? '');
        $ketValid  = ['masuk', 'izin', 'sakit', 'libur'];

        if ($siswa) {
            // Cek sudah presensi
            $existing = $this->db->queryOne(
                "SELECT ket, timestamp FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
                [$siswa['nis'], $tanggal]
            );
            if ($existing) {
                $jam = date('H:i:s', strtotime($existing['timestamp']));
                return "Sip!👍\n{$siswa['nama']},\nKamu sudah presensi hari ini.\nKet: {$existing['ket']}.\nJam: $jam.";
            }

            if (in_array($firstWord, $ketValid)) {
                // Caption valid → proses langsung via PresensiHandler
                return $this->presensi->handle($number, $pushName, $message, $mediaUrl);
            }

            // Foto tanpa ket → simpan pending
            $pending = [
                'type'      => 'pending_presensi',
                'nis'       => $siswa['nis'],
                'namasiswa' => $siswa['nama'],
                'kelas'     => $siswa['kelas'],
                'foto'      => $mediaUrl,
                'status'    => '',
                'catatan'   => '',
                'tanggal'   => $tanggal,
                'timestamp' => time(),
            ];
            $this->session->setPendingPresensi($number, $pending);

            return "📸 Foto sudah kami terima, tapi belum ada *keterangan kegiatan*.\n\n"
                . "📝 *Balas pesan ini dengan keterangan saja* (tanpa kirim foto lagi).\n\n"
                . "🔹 Contoh:\n"
                . "- `Masuk Memasang instalasi listrik`\n"
                . "- `Sakit Demam dan batuk`\n"
                . "- `Izin Ada acara keluarga`\n"
                . "- `Libur Tidak ada kegiatan hari ini`\n\n"
                . "✅ Setelah mengirim keterangan, presensi akan tersimpan otomatis.\n\n"
                . "ℹ️ Balas dengan:\n"
                . "1️⃣ `1` → Petunjuk presensi\n"
                . "`info` → Menu presensi\n"
                . "`admin` atau `7` → Hubungi admin";
        }

        // Pembimbing
        $pembimbing = $this->db->queryOne(
            "SELECT nama FROM datapembimbing WHERE nohp = ? LIMIT 1",
            [$nohp62]
        );
        if ($pembimbing) {
            return "👋 Selamat datang, *{$pembimbing['nama']}*!\n\n"
                . "📌 Nomor ini terdaftar sebagai *Pembimbing PKL SMKN Bansari {$this->tahun}*.\n\n"
                . "💡 Melalui chatbot ini Anda dapat:\n"
                . "• Memantau presensi siswa\n"
                . "• Input/koreksi presensi\n"
                . "• Akses rekap kehadiran\n"
                . "• Hubungi admin\n\n"
                . "➡️ Balas `help` untuk panduan lengkap.";
        }

        return "📢 *Sistem Presensi PKL SMKN Bansari*\n\n"
            . "🚫 Nomor Anda *tidak terdaftar*.\n\n"
            . "📌 Jika Anda siswa, segera daftarkan nomor.\n"
            . "💬 Balas `admin` untuk hubungi admin.";
    }

    // ─── Pesan bebas — anti-spam + notif admin ────────────────────────────
    private function handlePesanBebas(
        string $number, string $pushName, string $message, ?string $mediaUrl
    ): ?string {
        $nohp62     = WaSender::normalisasi0ke62($number);
        $isAdminSes = $this->session->isAdminSession($nohp62);

        $siswa = $this->db->queryOne(
            "SELECT nama, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$number, $nohp62]
        );
        $pembimbing = !$siswa ? $this->db->queryOne(
            "SELECT nama FROM datapembimbing WHERE nohp = ? LIMIT 1", [$nohp62]
        ) : null;

        $typeMap = [
            'siswa'      => $siswa      ? "Dari: {$siswa['nama']}\nKelas: {$siswa['kelas']}\n"     : null,
            'pembimbing' => $pembimbing ? "Dari Pembimbing: {$pembimbing['nama']}\n"               : null,
        ];
        $typeLabel = $typeMap['siswa'] ?? $typeMap['pembimbing'] ?? "Dari: Nomor Tidak terdaftar\n";

        // Anti-spam
        $limitDetik = 180;
        $limitPesan = 5;
        $spam = $this->session->getAntispam($nohp62);
        $now  = time();
        $selisih = $spam['last_reply'] ? ($now - strtotime($spam['last_reply'])) : PHP_INT_MAX;

        $this->session->incrementAntispam($nohp62);
        $count = ($spam['count'] ?? 0) + 1;

        $defaultMsgs = [
            "📌 Balas dengan Ketik `info` untuk info layanan.\n📞 Balas dengan ketik `admin` atau `7` untuk hubungi admin.",
            "✅ Untuk daftar layanan, balas dengan ketik `info`.\n👨‍💼 Untuk bantuan admin, balas dengan ketik `admin` atau `7`.",
            "📖 Balas dengan ketik `info` untuk panduan.\n🆘 Butuh bantuan? Balas `admin` atau `7`.",
            "🚫 Pesan tanpa format tidak diproses. Balas dengan ketik `info` atau `admin`.",
        ];

        if ($pembimbing) $defaultMsgs = null; // pembimbing tidak dibalas otomatis

        $sendmsg = null;

        if (!$isAdminSes) {
            if ($defaultMsgs && ($count >= $limitPesan || $selisih >= $limitDetik)) {
                if ($siswa) {
                    $hello = ["👋 Hai {$siswa['nama']}", "👋 Hallo {$siswa['nama']}"];
                    $sendmsg = $hello[array_rand($hello)] . "\n\n" . $defaultMsgs[array_rand($defaultMsgs)];
                } else {
                    $sendmsg = $defaultMsgs[array_rand($defaultMsgs)];
                }
                $this->session->resetAntispam($nohp62);

                $adminMsg = $typeLabel . "☑ Chat ini telah dibalas sistem:\n$nohp62 ~ $pushName:\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n$message\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nSesi Hub.Admin *Tidak Aktif* 🚫";
                $this->sender->send($this->adminNumber, $adminMsg, $mediaUrl);
            } else {
                $adminMsg = $typeLabel . "⏳ Belum terbalas sistem:\n$nohp62 ~ $pushName:\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n$message\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nSesi Hub.Admin *Tidak Aktif* 🚫";
                $this->sender->send($this->adminNumber, $adminMsg, $mediaUrl);
            }
        } else {
            // Dalam sesi admin — sudah dihandle di atas, tapi untuk foto/pesan bebas
            $this->notifAdmin($number, $pushName, $message, $mediaUrl, true);
        }

        return $sendmsg;
    }

    // ─── Live location ────────────────────────────────────────────────────
    private function handleLiveLocation(string $message, string $number): string
    {
        list($lat, $lon) = explode(",", $message);
        $radius = 300;

        $query = "[out:json];\n(\nnode(around:$radius,$lat,$lon)[name];\nway(around:$radius,$lat,$lon)[name];\nrelation(around:$radius,$lat,$lon)[name];\n);\nout center;\n";
        $url = "https://overpass-api.de/api/interpreter?data=" . urlencode($query);

        $opts    = ["http" => ["header" => "User-Agent: PKLBot/1.0\r\n"]];
        $context = stream_context_create($opts);
        $resp    = @file_get_contents($url, false, $context);

        if (!$resp) {
            return "📍 Lokasi diterima: $lat, $lon\n🔗 https://www.google.com/maps?q=$lat,$lon";
        }

        $dataLokasi = json_decode($resp, true);
        $results = [];

        if (!empty($dataLokasi['elements'])) {
            foreach ($dataLokasi['elements'] as $el) {
                if (!empty($el['tags']['name'])) {
                    $poiLat = $el['lat'];
                    $poiLon = $el['lon'];
                    $theta  = $lon - $poiLon;
                    $dist   = sin(deg2rad($lat)) * sin(deg2rad($poiLat))
                            + cos(deg2rad($lat)) * cos(deg2rad($poiLat)) * cos(deg2rad($theta));
                    $dist   = acos(max(-1, min(1, $dist)));
                    $meters = rad2deg($dist) * 60 * 1.1515 * 1609.34;
                    $results[] = ['name' => $el['tags']['name'], 'distance_m' => round($meters)];
                }
            }
            usort($results, fn($a, $b) => $a['distance_m'] <=> $b['distance_m']);
            $nearest = array_slice($results, 0, 3);

            $sendmsg  = "📍 Lokasi Anda: $lat, $lon\n";
            $sendmsg .= "🔗 [Lihat di Google Maps]\nhttps://www.google.com/maps?q=$lat,$lon\n\nLokasi terdekat:\n";
            foreach ($nearest as $i => $place) {
                $sendmsg .= ($i+1) . ". {$place['name']} ({$place['distance_m']} m)\n";
            }
            return $sendmsg;
        }

        return "Tidak ada lokasi terdekat ditemukan di sekitar koordinat $lat, $lon.";
    }

    // ─── # Broadcast ──────────────────────────────────────────────────────
    private function handleBroadcast(): string
    {
        return "📢 *Layanan Presensi Prakerin Telah Diperbarui!*\n\n"
            . "ℹ️ Untuk melihat informasi lengkap tentang layanan presensi PKL, balas dengan mengetik: `info`\n\n"
            . "💬 Jika membutuhkan bantuan atau ingin berbicara dengan admin, balas dengan:\n- `7`\n- `admin`\n\n"
            . "🙏 Terima kasih atas perhatiannya.\n\n"
            . "©️ _Sistem Presensi PKL SMK Negeri Bansari_";
    }

    private function handleKonfirmasiLibur(string $number, array $pending): string
    {
        $nis    = $pending['nis']    ?? '';
        $nama   = $pending['nama']   ?? '';
        $kelas  = $pending['kelas']  ?? '';
        $waktu  = date('Y-m-d', strtotime($pending['waktu'] ?? 'now'));
        $ts     = date('Y-m-d H:i:s', strtotime($pending['waktu'] ?? 'now'));

        $existing = $this->db->queryOne(
            "SELECT 1 FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
            [$nis, $waktu]
        );

        if ($existing) {
            return "✅ Presensi tanggal $waktu sudah tercatat sebelumnya.";
        }

        $this->db->execute(
            "INSERT INTO presensi (nis, namasiswa, kelas, ket, catatan, link, kode, timestamp) VALUES (?, ?, ?, 'libur', '', '', '', ?)",
            [$nis, $nama, $kelas, $ts]
        );

        $tanggal = date('Y-m-d');
        $formattedDate = ($waktu === $tanggal) ? "Hari ini" : "Hari/Tanggal:\n" . $this->formatTanggalIndo($waktu);

        return "✅ Oke, $formattedDate dicatat sebagai *libur* untuk $nama ($kelas).";
    }

    // ─── Pesan sukses presensi ────────────────────────────────────────────
    private function pesanPresensiOK(string $status, string $catatan, string $nama, string $kelas, string $nis): string
    {
        $tanggalIndo = $this->formatTanggalIndo(date('Y-m-d'));
        $jam         = date('H:i:s');
        $config      = require BASE_PATH . '/config/app.php';
        $webUrl      = rtrim($config['url'] ?? 'https://pklbos.smknbansari.sch.id', '/');

        $msg  = "```\n✅ Presensi Berhasil\n\n";
        $msg .= "🗓️ Status   : $status\n";
        $msg .= "📝 Catatan  : $catatan\n";
        $msg .= "👤 Nama     : $nama\n";
        $msg .= "🏫 Kelas    : $kelas\n\n";
        $msg .= "⏰ Waktu    : $tanggalIndo\nPukul $jam";
        $msg .= "```\n\n";

        if ($status === 'sakit') $msg .= "🌼 Semoga cepat sembuh dan bisa kembali beraktivitas seperti biasa.\nTetap jaga kesehatan ya 💪\n\n";
        elseif ($status === 'izin') $msg .= "📌 Semoga urusan atau kegiatanmu hari ini berjalan lancar.\nTetap semangat dan jangan lupa kembali presensi besok ya!\n\n";
        elseif ($status === 'libur') $msg .= "🌴 Selamat menikmati waktu liburmu.\nGunakan waktu istirahat dengan baik agar kembali fresh dan siap beraktivitas.\n\n";

        $msg .= "📊 Lihat rekap presensi kamu, bisa balas dengan ketik `cek` atau klik link ini:\n";
        $msg .= "$webUrl/?akses=detail&nis=$nis\n\n";
        $msg .= "ℹ️ Fitur *Lupa Absen* sudah aktif.\nBalas dengan ketik `2` untuk petunjuk penggunaannya.\n\n";
        $msg .= "ℹ️ Fitur *Batal Absen / Hapus Absen* sudah aktif.\nBalas dengan ketik `batal` untuk petunjuk penggunaannya.\n\n";
        $msg .= "📄 Panduan Laporan PKL dapat dilihat pada menu nomor `8`. Pilih menu balas dengan ketik `8`";

        return $msg;
    }

    // ─── Typo detection ───────────────────────────────────────────────────
    private function detectTypo(string $message): ?string
    {
        $keyboardMap = [
            'q'=>['w','a'],'w'=>['q','e','s','a'],'e'=>['w','r','d','s'],'r'=>['e','t','f','d'],
            't'=>['r','y','g','f'],'y'=>['t','u','h','g'],'u'=>['y','i','j','h'],'i'=>['u','o','k','j'],
            'o'=>['i','p','l','k','u'],'p'=>['o','p'],'a'=>['q','s','z','w'],'s'=>['a','d','w','x','e','z'],
            'd'=>['s','f','e','c','x','r'],'f'=>['d','g','r','v','t'],'g'=>['f','h','t','b','v','y'],
            'h'=>['g','j','y','n','b','u'],'j'=>['h','k','u','m','i'],'k'=>['j','l','i','m','o'],
            'l'=>['k','o','p'],'z'=>['a','x','s'],'x'=>['z','c','d'],'c'=>['x','v','f','d','g'],
            'v'=>['c','b','g','f','h'],'b'=>['v','n','h','g','j'],'n'=>['b','m','h','j','k'],'m'=>['n','j','k','l'],
        ];
        $validWords = ["reg","info","masuk","izin","batal","ya","tidak","help","admin","lupa","balas","cari","jurnal","input","cek","rekap","unreg","set"];

        $clean     = preg_replace('/[^a-zA-Z0-9 ]/', '', $message);
        $firstWord = strtolower(explode(" ", trim($clean))[0]);

        if (in_array($firstWord, $validWords, true)) return null;

        foreach ($validWords as $word) {
            $typos = [];
            for ($i = 0; $i < strlen($word); $i++) {
                $char = $word[$i];
                if (isset($keyboardMap[$char])) {
                    foreach ($keyboardMap[$char] as $nb) {
                        $typos[] = substr($word, 0, $i) . $nb . substr($word, $i+1);
                    }
                }
                $typos[] = substr($word, 0, $i+1) . $word[$i] . substr($word, $i+1); // double
                $typos[] = substr($word, 0, $i) . substr($word, $i+1);               // hilang
            }
            if (in_array($firstWord, $typos)) {
                return "`$message` sepertinya salah tulis.\nMungkin seharusnya: *$word*\n\nCoba ulangi kirim pesan dengan ejaan yang benar.";
            }
        }
        return null;
    }

    // ─── Notif ke admin ───────────────────────────────────────────────────
    private function notifAdmin(
        string $number, string $pushName, string $message,
        ?string $mediaUrl, bool $sesiAktif, string $typoReply = ''
    ): void {
        $nohp62 = WaSender::normalisasi0ke62($number);
        $siswa  = $this->db->queryOne(
            "SELECT nama, kelas FROM datasiswa WHERE nohp = ? OR nohp = ? LIMIT 1",
            [$number, $nohp62]
        );
        $pembimbing = !$siswa ? $this->db->queryOne(
            "SELECT nama FROM datapembimbing WHERE nohp = ? LIMIT 1", [$nohp62]
        ) : null;

        $typeLabel = $siswa
            ? "Dari: {$siswa['nama']}\nKelas: {$siswa['kelas']}\n"
            : ($pembimbing ? "Dari Pembimbing: {$pembimbing['nama']}\n" : "Dari: Nomor Tidak terdaftar\n");

        $statusSesi = $sesiAktif ? "Sesi Hub.Admin *Aktif* ✅" : "Sesi Hub.Admin *Tidak Aktif* 🚫";

        $adminMsg = $typeLabel;
        if ($typoReply) $adminMsg .= "Telah dibalas sistem dengan:\n$typoReply\n";
        $adminMsg .= "$nohp62 ~ $pushName:\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n$message\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n$statusSesi";
        $this->sender->send($this->adminNumber, $adminMsg, $mediaUrl);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────
    private function isValidCoordinate(string $coord): bool
    {
        if (!preg_match('/^-?\d{1,2}\.\d+,-?\d{1,3}\.\d+$/', $coord)) return false;
        list($lat, $lon) = explode(",", $coord);
        return ($lat >= -90 && $lat <= 90) && ($lon >= -180 && $lon <= 180);
    }

    private function generateKode(int $length = 6): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code  = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    private function jalankanProseschat(string $nis, string $kode, string $link): void
    {
        $url = "https://hadir.masbendz.com/app/proseschat.php?nis=$nis&kode=$kode&link=" . urlencode($link);
        $ch  = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 10]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function formatTanggalIndo(string $tgl): string
    {
        $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $d = date('j', strtotime($tgl));
        $m = (int)date('n', strtotime($tgl));
        $y = date('Y', strtotime($tgl));
        $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $h = $hari[(int)date('w', strtotime($tgl))];
        return "$h, $d {$bulan[$m]} $y";
    }

    private function simpanTmp(string $number, string $message): void
    {
        if (empty($number)) return;
        try {
            $this->db->execute("INSERT INTO tmp (number, msg, timestamp) VALUES (?, ?, NOW())", [$number, $message]);
        } catch (\Throwable $e) {}
    }
}