<?php
// Bootstrap
define('BASE_PATH', dirname(__DIR__, 2) . '/../pkl');

$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        if (!isset($_ENV[trim($key)])) $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

require_once BASE_PATH . '/app/Core/Database.php';
use App\Core\Database;

$nis  = $_GET['nis']  ?? null;
$kode = $_GET['kode'] ?? null;
$link = $_GET['link'] ?? null;

if (!$nis || !$kode || !$link) {
    echo json_encode(['status'=>'gagal','message'=>'Parameter tidak lengkap.']);
    exit;
}

// Apps Script URL
$appsScriptUrl = $_ENV['APPS_SCRIPT_URL'] ?? '';

if (!$appsScriptUrl) {
    echo json_encode(['status'=>'gagal','message'=>'Apps Script URL tidak dikonfigurasi.']);
    exit;
}

// Kirim ke Apps Script dengan retry
function sendGetRequest(string $url, array $params, int $maxRetries = 3): mixed
{
    $finalUrl = $url . '?' . http_build_query($params);
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $finalUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
        ]);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) { curl_close($ch); return $response; }
        curl_close($ch);
        sleep(pow(2, $attempt));
    }
    return false;
}

$response = sendGetRequest($appsScriptUrl, ['nis'=>$nis,'kode'=>$kode,'link'=>$link]);
$json     = json_decode($response, true);

if (!$json || ($json['status'] ?? '') !== 'berhasil') {
    echo json_encode(['status'=>'gagal','message'=>'Apps Script gagal: ' . ($json['message'] ?? 'Unknown')]);
    exit;
}

$linkTersimpan = $json['link_tersimpan'];

// Update DB via PDO
$db = Database::getInstance();
$db->query(
    "UPDATE presensi SET link = ?, statuslink = 'OK' WHERE nis = ? AND kode = ?",
    [$linkTersimpan, $nis, $kode]
);

echo json_encode([
    'status'  => 'berhasil',
    'message' => 'Data berhasil diperbarui.',
    'nis'     => $nis,
    'kode'    => $kode,
    'link_baru' => $linkTersimpan,
]);