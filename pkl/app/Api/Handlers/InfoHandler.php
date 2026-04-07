<?php

namespace App\Api\Handlers;

use App\Core\Database;
use App\Api\WaSender;

class InfoHandler
{
    private string $tahun;
    private string $fileLaporan;
    private string $fileDokumentasi;
    private string $fotoContohPresensi;
    private string $fotoContohLupa;
    private WaSender $sender;
    private Database $db;

    public function __construct(WaSender $sender)
    {
        $this->tahun              = date('Y');
        $this->fileLaporan        = 'https://hadir.masbendz.com/data/Format_Laporan_PKL.pdf';
        $this->fileDokumentasi    = 'https://hadir.masbendz.com/data/Panduan Presensi PKL melalui WA.pdf';
        $this->fotoContohPresensi = 'https://hadir.masbendz.com/app/contohpresensi.jpg';
        $this->fotoContohLupa     = 'https://hadir.masbendz.com/app/contohlupaabsen.jpg';
        $this->sender             = $sender;
        $this->db                 = Database::getInstance();
    }

    public function handle(string $number, string $message): ?string
    {
        $msg = strtolower(trim($message));

        return match(true) {
            in_array($msg, ['info','menu','halo','hai','hi','hello'])
                => $this->menuUtama(),
            $msg === '1'                           => $this->panduanPresensi($number),
            $msg === '2' || $msg === 'lupa absen' => $this->panduanLupa($number),
            $msg === '3'                           => $this->menu3($number),
            $msg === '4' || str_starts_with($msg, 'reg status') => $this->menu4($number),
            $msg === '5'                           => $this->menu5(),
            $msg === '6' || $msg === 'rekap'       => null, // ditangani CekHandler (rekapPribadi)
            $msg === '8'                           => $this->menu8($number),
            $msg === 'tes'                         => $this->menuTes(),
            $msg === 'p'                           => $this->menuP(),
            $msg === 'help'                        => $this->menuHelp($number),
            default                                => null,
        };
    }

    private function menuUtama(): string
    {
        $tahun = $this->tahun;
        $menus = [
            "📋 *Layanan Presensi PKL SMK Negeri Bansari - Tahun $tahun*\n\nBerikut pilihan menu yang tersedia:\n\n1⃣ Panduan Presensi  \n2⃣ Lupa Presensi *(SUDAH AKTIF)*  \n3⃣ Daftar / Ganti Nomor WhatsApp  \n4⃣ Cek Status Nomor  \n5⃣ Hapus / Batal / Ganti Nomor  \n6⃣ Rekap Presensi  \n7⃣ Hubungi Admin  \n8⃣ *Panduan Laporan PKL*  \n\n🔁 *Balas dengan ketik angka sesuai menu yang dipilih.*  \nContoh: ketik `3` untuk daftar atau ganti nomor.",
            "Hai! 👋  \nIni dia layanan Presensi PKL SMKN Bansari $tahun:\n\n1. 📖 Cara Presensi  \n2. 🕒 Lupa Presensi  \n3. 📱 Daftar/Ganti Nomor  \n4. 🔍 Cek Status  \n5. ❌ Hapus Nomor  \n6. 📊 Rekap Presensi  \n7. 📞 Admin  \n8⃣ *Panduan Laporan PKL*  \n\n👉 *Ketik angka yang kamu pilih, misalnya `1` untuk cara presensi.*",
            "🗂 *Sistem Layanan Presensi PKL SMKN Bansari - $tahun*\n\nSilakan pilih layanan berikut:\n\n1⃣ Langkah-langkah Presensi  \n2⃣ Fitur Lupa Presensi  \n3⃣ Pendaftaran / Perubahan Nomor WA  \n4⃣ Pemeriksaan Status Nomor  \n5⃣ Penghapusan / Penggantian Nomor  \n6⃣ Lihat Rekap Presensi  \n7⃣ Kontak Admin  \n8⃣ *Panduan Laporan PKL*  \n\n📝 Balas pesan ini dengan angka dari 1 sampai 8.",
            "📋 *Menu Layanan Presensi PKL*  \n📚 Untuk Siswa SMKN Bansari Tahun $tahun\n\nYuk pilih menu yang kamu butuhkan:\n\n1⃣ *Panduan Presensi*  \n    📝 Cara presensi harian yang benar.\n\n2⃣ *Lupa Presensi* *(SUDAH AKTIF)*  \n    🕒 Kalau kamu lupa presensi kemarin, bisa pakai fitur ini (maks 2x sehari).\n\n3⃣ *Daftar / Ganti Nomor WA*  \n    📱 Ganti atau daftarkan nomor WA supaya bisa akses presensi.\n\n4⃣ *Cek Status Nomor*  \n    🔍 Cek apakah nomor WA kamu sudah terdaftar atau belum.\n\n5⃣ *Hapus / Ganti Nomor*  \n    🧹 Ganti atau hapus nomor WA yang sebelumnya terdaftar.\n\n6⃣ *Rekap Presensi*  \n    📊 Lihat rangkuman presensimu selama PKL.\n\n7⃣ *Hubungi Admin*  \n    📩 Ada kendala? Mau tanya? Bisa langsung ke Admin.\n\n8⃣ *Panduan Laporan PKL*  \n    🗂 Info lengkap soal laporan PKL.\n\n🔁 *Ketik angka sesuai menu pilihanmu.*  \nContoh: ketik `2` untuk pakai fitur Lupa Presensi.",
        ];
        return $menus[array_rand($menus)];
    }

    private function panduanPresensi(string $number): string
    {
        $this->sender->send($number, 'Contoh Presensi', $this->fotoContohPresensi);
        return "📸 *Panduan Presensi PKL SMK Negeri Bansari*\n\n✅ *Pastikan nomor WhatsApp Anda telah terdaftar!*\nJika belum, balas dengan ketik `3` untuk panduan pendaftaran.\n\n🔹 *Langkah Presensi:*\n1️⃣ Ambil foto selfie saat berada di lokasi PKL.\n2️⃣ Tambahkan *keterangan* pada foto dengan format:\n`KETERANGAN<spasi>CATATAN`\n\n🔸 *Pilihan KETERANGAN:*\n    - masuk\n    - izin\n    - sakit\n    - libur\n\n🔸 *Contoh Penggunaan:*\n    - `Masuk Memasang instalasi listrik`\n    - `Sakit Demam dan batuk`\n    - `Izin Ada acara keluarga`\n    - `Libur Tidak ada kegiatan hari ini`\n\n3️⃣ Kirim foto tersebut.\n🕐 Tunggu respon konfirmasi berhasil dari sistem.\nJika belum ada balasan, *silakan kirim ulang atau hubungi admin*.";
    }

    private function panduanLupa(string $number): string
    {
        $this->sender->send($number, 'Contoh Lupa Absen', $this->fotoContohLupa);
        return "🕒 *Panduan Lupa Absen*\n\nJika kamu *lupa presensi kemarin*, kamu masih bisa mengisi hari ini.\nNamun, *maksimal hanya 2 kali lupa absen dalam 1 hari!*\n\n🔹 *Langkah Lupa Absen:*\n\n1️⃣ Ambil *foto selfie* seperti biasa saat presensi.\n\n2️⃣ Tambahkan caption dengan format:\n`LUPA<spasi>KETERANGAN<spasi>TANGGAL<spasi>CATATAN`\n\n🔸 *Pilihan KETERANGAN:*\n- Masuk\n- Izin\n- Sakit\n- Libur\n\n🔸 *Format TANGGAL:*\n- `22-07-2025` atau `22/07/2025`\n\n🔸 *Contoh:*\n- `LUPA Masuk 22-07-2025 Input data alat lab`\n- `LUPA Sakit 21/07/2025 Demam dan pusing`\n\n3️⃣ Kirim seperti biasa.\n🕐 Jika belum ada balasan dari sistem, *kirim ulang atau teruskan atau tanya admin.*\n\n📌 *Catatan:*\nJangan gunakan fitur ini untuk mengakali presensi. Sistem mencatat semua aktivitas.";
    }

    private function menu3(string $number): string
    {
        $nohp0  = WaSender::normalisasi62ke0($number);
        $siswa  = $this->db->queryOne(
            "SELECT nama, kelas FROM datasiswa WHERE nohp LIKE ? LIMIT 1",
            ["%$nohp0%"]
        );

        if ($siswa) {
            return "✅ *Nomor $nohp0 sudah terdaftar.*\n\n"
                . "🧑 Nama  : {$siswa['nama']}\n"
                . "🏫 Kelas : {$siswa['kelas']}\n\n"
                . "Nomor ini *sudah bisa digunakan* untuk melakukan presensi PKL.\n\n"
                . "📸 Silakan lakukan presensi sesuai panduan.";
        }

        return "📝 *Pendaftaran Nomor WhatsApp*\n\n"
            . "Nomor ini *belum terdaftar* di sistem.\n\n"
            . "Untuk mendaftarkan, balas dengan format berikut:\n\n"
            . "🔡 *reg<spasi>NIS*\n"
            . "Contoh:\nJika NIS kamu adalah 1234\nKetik: `reg 1234`\nLalu kirim.\n\n"
            . "*) Huruf besar / kecil tidak berpengaruh.";
    }

    private function menu4(string $number): string
    {
        $nohp0 = WaSender::normalisasi62ke0($number);
        $siswa = $this->db->queryOne(
            "SELECT nama, kelas FROM datasiswa WHERE nohp LIKE ? LIMIT 1",
            ["%$nohp0%"]
        );

        if ($siswa) {
            return "✅ Nomor *$nohp0* sudah terdaftar di sistem.\n\n"
                . "🧑 Nama  : *{$siswa['nama']}*\n"
                . "🏫 Kelas : *{$siswa['kelas']}*\n\n"
                . "Nomor ini sudah bisa digunakan untuk melakukan presensi PKL.";
        }

        return "❗Nomor WA ini belum terdaftar di sistem.\n\n"
            . "Untuk mendaftarkan nomor:\n"
            . "Ketik: `reg <spasi> NIS`\n\n"
            . "📝 Contoh:\n`reg 1234`\n\n"
            . "Jika mengalami kendala, silakan hubungi Admin.";
    }

    private function menu5(): string
    {
        return "Ingin membatalkan pendaftaran nomor ini?\n\n🔁 *Balas dengan ketik:* `unreg`\n\n📌 Setelah dibatalkan, nomor ini *tidak bisa digunakan* untuk presensi sampai *didaftarkan ulang*.\n\nJika kamu tidak yakin, silakan konsultasikan ke admin terlebih dahulu ya 😊";
    }

    private function menu8(string $number): string
    {
        $this->sender->send($number, '📄 Panduan Laporan PKL', $this->fileLaporan);
        return "📄 Panduan Laporan PKL telah dikirim.";
    }

    private function menuTes(): string
    {
        $v = [
            "✅ testing OK","✅ Tes berhasil. Sistem aktif!","📶 Koneksi aman, presensi siap digunakan.",
            "🆗 Sistem merespon. Lanjutkan penggunaan.","✅ Tes OK. Silakan kirim presensi seperti biasa.",
            "🚀 Sistem online! Tes berhasil.","👍 Respon diterima. Sistem siap melayani.",
            "✅ Sistem berjalan normal.","🟢 Tes sukses. Tidak ada gangguan.",
            "👌 Terhubung ke sistem. Lanjutkan kegiatanmu.","✅ Tes diterima. Presensi bisa dilakukan sekarang.",
        ];
        return $v[array_rand($v)];
    }

    private function menuP(): string
    {
        $v = [
            "Balas dengan ketik `info`.\nUntuk mendapatkan informasi Layanan Presensi.",
            "📌 Untuk melihat panduan layanan presensi, balas dengan ketik `info`.\n\n🧾 Informasi lengkap tersedia dalam menu tersebut.",
            "Ketik `info` untuk melihat petunjuk layanan presensi.\n\n⏳ Sistem hanya merespon perintah yang tersedia.",
            "Hai! 😊 Butuh panduan layanan?\nBalas dengan ketik `info` untuk mulai.",
        ];
        return $v[array_rand($v)];
    }

    private function menuHelp(string $number): string
    {
        $tahun = $this->tahun;
        $this->sender->send($number, "Panduan Penggunaan ChatBot Presensi PKL - $tahun SMKN Bansari", $this->fileDokumentasi);
        return "Panduan Penggunaan ChatBot Presensi PKL - $tahun SMKN Bansari";
    }
}