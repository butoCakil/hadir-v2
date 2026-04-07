<?php

namespace App\Api;

class WaSender
{
    private string $deviceId;
    private string $apiUrl;

    public function __construct()
    {
        $config        = require BASE_PATH . '/config/app.php';
        $this->deviceId = $config['wa']['device_id'];
        $this->apiUrl   = $config['wa']['api_url'];
    }

    public function send(string $number, string $message, ?string $file = null): string|false
    {
        $number = $this->normalisasi0ke62($number);

        $data = [
            'device_id' => $this->deviceId,
            'number'    => $number,
            'message'   => $message,
        ];

        if ($file) {
            $data['file'] = $file;
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST          => true,
            CURLOPT_POSTFIELDS    => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT       => 15,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    // ==========================================
    // Format nomor
    // ==========================================

    public static function normalisasi0ke62(string $no): string
    {
        $no = preg_replace('/[^0-9]/', '', $no);
        if (str_starts_with($no, '0')) {
            $no = '62' . substr($no, 1);
        }
        return $no;
    }

    public static function normalisasi62ke0(string $no): string
    {
        $no = preg_replace('/[^0-9]/', '', $no);
        if (str_starts_with($no, '62')) {
            $no = '0' . substr($no, 2);
        }
        return $no;
    }
}
