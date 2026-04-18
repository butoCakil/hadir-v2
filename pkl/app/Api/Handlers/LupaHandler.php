<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;
use App\Api\Helpers\PeriodeHelper;
use App\Models\WabotSessionModel;

class LupaHandler
{
    private Database $db;
    private WaSender $sender;
    private WabotSessionModel $session;
    private int $maxLupaPerHari = 2;

    // Format tanggal yang didukung
    private array $formatTanggal = [
        '/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2,4})$/',
        '/^(\d{1,2})[-\/](januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)[-\/](\d{2,4})$/i',
    ];

    private array $namaBulan = [
        'januari'=>'01','februari'=>'02','maret'=>'03','april'=>'04',
        'mei'=>'05','juni'=>'06','juli'=>'07','agustus'=>'08',
        'september'=>'09','oktober'=>'10','november'=>'11','desember'=>'12',
    ];

    public function __construct(Database $db, WaSender $sender, WabotSessionModel $session)
    {
        $this->db      = $db;
        $this->sender  = $sender;
        $this->session = $session;
    }

    /**
     * Handle pesan LUPA dengan foto
     * Format: "LUPA <ket> <tanggal> <catatan>" + foto
     */
    public function handle(string $number, string $pushName, string $message, ?string $mediaUrl): ?string
    {
        // Cek siswa terdaftar
        $nohp0        = WaSender::normalisasi62ke0($number);
        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? (int)$periodeAktif['id'] : 0;
        $siswa        = $this->db->queryOne(
            "SELECT nis, nama, kelas FROM datasiswa WHERE (nohp = ? OR nohp = ? OR encryp = ?) AND periode_id = ? LIMIT 1",
            [$nohp0, $number, $number, $periodeId]
        );

        if (!$siswa) {
            return "🚫 Nomor belum terdaftar. Daftar dengan format `REG<spasi>NIS`.";
        }

        $nis   = $siswa['nis'];
        $nama  = $siswa['nama'];
        $kelas = $siswa['kelas'];

        // Cek jika ada foto
        if (!$mediaUrl) {
            return "🚫 *Presensi Lupa Absen gagal!*\n\n"
                . "Presensi lupa absen harus disertai *foto selfie*.\n\n"
                . "Format:\n`LUPA<spasi>KETERANGAN<spasi>TANGGAL<spasi>CATATAN`\n"
                . "dengan mengirim *foto sebagai caption*.\n\n"
                . "Contoh caption:\n`LUPA Masuk 22-07-2025 Input data alat lab`";
        }

        // Parse pesan: LUPA <ket> <tanggal> <catatan>
        $parts = explode(' ', trim($message), 4);
        // parts[0] = LUPA, parts[1] = ket, parts[2] = tanggal, parts[3] = catatan
        if (count($parts) < 3) {
            return $this->pesanFormatSalah();
        }

        $ketRaw  = strtolower($parts[1]);
        $tglRaw  = $parts[2];
        $catatan = $parts[3] ?? '';

        $ketValid = ['masuk', 'izin', 'sakit', 'libur'];
        if (!in_array($ketRaw, $ketValid)) {
            return $this->pesanKeteranganTidakValid($nama);
        }

        // Parse tanggal
        $tanggal = $this->parseTanggal($tglRaw);
        if (!$tanggal) {
            return $this->pesanTanggalTidakValid($nama);
        }

        // Tidak boleh lupa presensi untuk hari ini
        if ($tanggal === date('Y-m-d')) {
            return "⚠️ Fitur *Lupa Absen* hanya untuk *hari sebelumnya*, bukan hari ini.";
        }
        
        // Tidak boleh untuk masa depan
        if ($tanggal > date('Y-m-d')) {
            return "⚠️ Tanggal tidak valid. Tidak bisa presensi untuk tanggal yang akan datang.";
        }
        
        // Cek periode + toleransi
        $cekPeriode = PeriodeHelper::cekTanggalValid($tanggal);
        if (!$cekPeriode['valid']) {
            return "🚫 *Lupa Absen gagal.*\n\n" . $cekPeriode['pesan'];
        }

        // Cek sudah presensi di tanggal itu
        $sudah = $this->db->queryOne(
            "SELECT id FROM presensi WHERE nis = ? AND DATE(timestamp) = ? LIMIT 1",
            [$nis, $tanggal]
        );
        if ($sudah) {
            return "⚠️ Presensi tanggal *" . $this->formatTanggalIndo($tanggal) . "* sudah tercatat.";
        }

        // Cek rate limit hari ini
        $jumlahHariIni = $this->session->getLupaHariIni($nohp0);
        if ($jumlahHariIni >= $this->maxLupaPerHari) {
            return "🚫 Batas penggunaan fitur *Lupa Absen* sudah tercapai.\n\n"
                . "Maksimal *{$this->maxLupaPerHari} kali per hari*.";
        }

        // Generate kode
        $kode      = 'L' . strtoupper(substr(md5($nis . $tanggal . time()), 0, 5));
        $timestamp = $tanggal . ' ' . date('H:i:s');

        // Ambil periode aktif
        $periodeAktif = $this->db->queryOne("SELECT id FROM periode_pkl WHERE aktif = 1 LIMIT 1");
        $periodeId    = $periodeAktif ? $periodeAktif['id'] : null;
        
        // Simpan presensi
        $this->db->execute(
            "INSERT INTO presensi (periode_id, nis, namasiswa, kelas, ket, catatan, link, statuslink, kode, timestamp)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$periodeId, $nis, $nama, $kelas, ucfirst($ketRaw), $catatan ?: '', $mediaUrl ?: '', 'OK', $kode, $timestamp]
        );

        // Increment rate limit
        $jumlahBaru = $this->session->incrementLupa($nohp0);

        $tglFormatted = $this->formatTanggalIndo($tanggal);

        return "```\n"
            . "✅ Lupa Absen Berhasil Dicatat\n\n"
            . "📅 Tanggal    : $tglFormatted\n"
            . "📝 Keterangan : " . ucfirst($ketRaw) . "\n"
            . "🙍 Nama       : $nama\n"
            . "🏫 Kelas      : $kelas\n"
            . "🗒️ Catatan    : " . ($catatan ?: '-') . "\n"
            . "🔑 Kode       : $kode\n"
            . "📊 Pemakaian  : $jumlahBaru dari {$this->maxLupaPerHari} kali\n"
            . "```";
    }

    private function parseTanggal(string $raw): ?string
    {
        // Format DD-MM-YYYY atau DD/MM/YYYY atau DD.MM.YYYY
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2,4})$/', $raw, $m)) {
            $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $y = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            $tgl = "$y-$mo-$d";
            return checkdate((int)$mo, (int)$d, (int)$y) ? $tgl : null;
        }

        // Format DD/NamaBulan/YYYY
        if (preg_match('/^(\d{1,2})[-\/]([a-zA-Z]+)[-\/](\d{2,4})$/i', $raw, $m)) {
            $bulan = strtolower($m[2]);
            if (!isset($this->namaBulan[$bulan])) return null;
            $d  = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = $this->namaBulan[$bulan];
            $y  = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            $tgl = "$y-$mo-$d";
            return checkdate((int)$mo, (int)$d, (int)$y) ? $tgl : null;
        }

        return null;
    }

    private function formatTanggalIndo(string $tanggal): string
    {
        $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                  '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                  '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        [$y, $m, $d] = explode('-', $tanggal);
        return "$d " . ($bulan[$m] ?? $m) . " $y";
    }

    private function pesanFormatSalah(): string
    {
        return "❌ *Format Lupa Absen tidak sesuai.*\n\n"
            . "Format yang benar:\n"
            . "`LUPA<spasi>KETERANGAN<spasi>TANGGAL<spasi>CATATAN`\n\n"
            . "Contoh:\n`LUPA Masuk 22-07-2025 Input data alat lab`\n\n"
            . "Keterangan yang valid: masuk, izin, sakit, libur";
    }

    private function pesanKeteranganTidakValid(string $nama): string
    {
        return "🚫 *Keterangan tidak dikenali.*\n\n"
            . "Gunakan salah satu:\n- Masuk\n- Izin\n- Sakit\n- Libur";
    }

    private function pesanTanggalTidakValid(string $nama): string
    {
        return "❌ *Format tanggal tidak valid.*\n\n"
            . "Format yang didukung:\n"
            . "- `22-07-2025`\n- `22/07/2025`\n- `22.07.2025`\n- `22/Juli/2025`";
    }
}