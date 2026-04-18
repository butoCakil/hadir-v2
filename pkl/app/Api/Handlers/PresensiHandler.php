<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;
use App\Api\Helpers\PeriodeHelper;
use App\Models\WabotSessionModel;

class PresensiHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private string $adminNumber;
    private array $ketValid = ['masuk', 'izin', 'sakit', 'libur'];

    public function __construct(Database $db, WaSender $sender, string $adminNumber)
    {
        $this->db          = $db;
        $this->sender      = $sender;
        $this->adminNumber = $adminNumber;
        $this->session     = new WabotSessionModel();
    }

    public function handle(string $number, string $pushName, string $message, ?string $mediaUrl): ?string
    {
        $parts   = explode(' ', trim($message), 2);
        $status  = strtolower(preg_replace('/[^a-z]/i', '', $parts[0]));
        $catatan = isset($parts[1]) ? htmlspecialchars(trim($parts[1]), ENT_QUOTES, 'UTF-8') : '';

        if (!in_array($status, $this->ketValid)) {
            return "🚫 *Keterangan presensi* `$status` *tidak valid!*\n\n"
                . "📌 Pastikan ejaan *benar, sesuai, dan persis* dengan salah satu kata berikut:\n"
                . "- `masuk`\n- `izin`\n- `sakit`\n- `libur`\n\n"
                . "⚠️ Harus *diawali* dengan salah satu kata di atas, kemudian diikuti *spasi* dan keterangan kegiatan.\n"
                . "*Tidak boleh* ada tanda baca di sekitar kata pertama.\n\n"
                . "📝 *Contoh yang benar:*\n"
                . "- `Masuk Memasang instalasi listrik`\n"
                . "- `Izin Pergi ke acara keluarga`";
        }

        // Cek siswa terdaftar
        $siswa = $this->getSiswaByNohp($number);
        if (!$siswa) {
            return "🚫 *Nomor WhatsApp Anda belum terdaftar untuk presensi!*\n\n"
                . "Nomer: $number\n\n"
                . "📲 Silakan *daftarkan nomor WA* terlebih dahulu dengan format berikut:\n"
                . "`REG<spasi>NIS`\n\n"
                . "🔹 *Contoh:*\n`REG 1234`\n\n"
                . "Setelah berhasil terdaftar, Anda bisa langsung melakukan presensi.";
        }

        $nis     = $siswa['nis'];
        $nama    = $siswa['nama'];
        $kelas   = $siswa['kelas'];
        $tanggal = date('Y-m-d');

        // Cek sudah presensi hari ini
        $existing = $this->db->queryOne(
            "SELECT ket, timestamp FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
            [$nis, $tanggal]
        );

        if ($existing) {
            $jam           = date('H:i:s', strtotime($existing['timestamp']));
            $ket           = $existing['ket'];
            $formattedDate = "Hari ini";
            return "✅ $nama ($nis - $kelas),\n\n"
                . "Presensimu untuk $formattedDate pada pukul $jam *sudah tercatat* sebelumnya.\n"
                . "Ket: $ket\n"
                . "Jadi, tidak perlu presensi ulang ya. Terima kasih! 🙌";
        }

        // ── Cek periode ──
        $cekPeriode = PeriodeHelper::cekTanggalValid($tanggal);
        if (!$cekPeriode['valid']) {
            return "🚫 *Presensi gagal.*\n\n" . $cekPeriode['pesan'];
        }
        
        // ── masuk tanpa foto → minta foto dulu (pending presensi) ──
        if ($status === 'masuk' && empty($mediaUrl)) {
            $pending = [
                'type'      => 'pending_presensi',
                'nis'       => $nis,
                'namasiswa' => $nama,
                'kelas'     => $kelas,
                'status'    => $status,
                'catatan'   => $catatan,
                'foto'      => '',
                'tanggal'   => $tanggal,
                'timestamp' => time(),
            ];
            $this->session->setPendingPresensi($number, $pending);

            return "⏳ Presensi *$status* Anda hampir selesai!\n"
                . "📸 Namun, sistem belum menerima foto presensi.\n\n"
                . "Untuk melengkapi dan menyimpan presensi hari ini:\n"
                . "➡️ Balas pesan ini dengan *mengirim foto saja* (tanpa teks tambahan).\n\n"
                . "Terima kasih 🙂 _cheers_ 🥂";
        }

        // ── izin/sakit/libur → langsung simpan (foto opsional) ──
        // ── masuk dengan foto → langsung simpan ──
        $kode = $this->generateKode();
        $link = $mediaUrl ?: '';
        
        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? $periodeAktif['id'] : null;
        
        $this->db->execute(
            "INSERT INTO presensi (periode_id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$periodeId, $nis, $nama, $kelas, ucfirst($status), $catatan, $link, $link ? 'OK' : 'NOFOTO', $kode]
        );

        // Jalankan proseschat untuk upload foto ke Google Drive
        if ($mediaUrl) {
            $this->jalankanProseschat($nis, $kode, $mediaUrl);
        }

        return $this->pesanOK($status, $catatan, $nama, $kelas, $nis, (bool)$mediaUrl);
    }

    private function getSiswaByNohp(string $number): array|false
    {
        $nohp0        = WaSender::normalisasi62ke0($number);
        $nohp62       = WaSender::normalisasi0ke62($number);
        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;
    
        return $this->db->queryOne(
            "SELECT nis, nama, kelas FROM datasiswa
             WHERE (nohp = ? OR nohp = ? OR encryp = ?) AND periode_id = ? LIMIT 1",
            [$nohp0, $nohp62, $number, $periodeId]
        ) ?: false;
    }

    private function jalankanProseschat(string $nis, string $kode, string $link): void
    {
        $url = "https://hadir.masbendz.com/app/proseschat.php?nis=$nis&kode=$kode&link=" . urlencode($link);
        $ch  = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 15]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function pesanOK(string $status, string $catatan, string $nama, string $kelas, string $nis, bool $adaFoto): string
    {
        $tglIndo = $this->formatTanggalIndo(date('Y-m-d'));
        $jam     = date('H:i:s');
        $config  = require BASE_PATH . '/config/app.php';
        $webUrl  = rtrim($config['url'] ?? 'https://pklbos.smknbansari.sch.id', '/');

        $msg  = "```\n✅ Presensi Berhasil\n\n";
        $msg .= "🗓️ Status   : $status\n";
        $msg .= "📝 Catatan  : $catatan\n";
        $msg .= "👤 Nama     : $nama\n";
        $msg .= "🏫 Kelas    : $kelas\n\n";
        $msg .= "⏰ Waktu    : $tglIndo\nPukul $jam";
        $msg .= "```\n\n";

        if ($status === 'sakit')  $msg .= "🌼 Semoga cepat sembuh dan bisa kembali beraktivitas seperti biasa.\nTetap jaga kesehatan ya 💪\n\n";
        elseif ($status === 'izin')  $msg .= "📌 Semoga urusan atau kegiatanmu hari ini berjalan lancar.\nTetap semangat dan jangan lupa kembali presensi besok ya!\n\n";
        elseif ($status === 'libur') $msg .= "🌴 Selamat menikmati waktu liburmu.\nGunakan waktu istirahat dengan baik agar kembali fresh dan siap beraktivitas.\n\n";

        $msg .= "📊 Lihat rekap presensi kamu, bisa balas dengan ketik `cek` atau klik link ini:\n";
        $msg .= "$webUrl/?akses=detail&nis=$nis\n\n";
        $msg .= "ℹ️ Fitur *Lupa Absen* sudah aktif.\nBalas dengan ketik `2` untuk petunjuk penggunaannya.\n\n";
        $msg .= "ℹ️ Fitur *Batal Absen / Hapus Absen* sudah aktif.\nBalas dengan ketik `batal` untuk petunjuk penggunaannya.\n\n";
        $msg .= "📄 Panduan Laporan PKL Dapat di lihat pada menu nomor `8`. pilih menu balas dengan ketik `8`";

        return $msg;
    }

    private function generateKode(int $len = 6): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code  = '';
        for ($i = 0; $i < $len; $i++) $code .= $chars[random_int(0, strlen($chars)-1)];
        return $code;
    }

    private function formatTanggalIndo(string $tgl): string
    {
        $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                  '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                  '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $d = date('j', strtotime($tgl));
        $m = date('m', strtotime($tgl));
        $y = date('Y', strtotime($tgl));
        $h = $hari[(int)date('w', strtotime($tgl))];
        return "$h, $d {$bulan[$m]} $y";
    }
}