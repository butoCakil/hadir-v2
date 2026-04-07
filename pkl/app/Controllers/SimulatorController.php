<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class SimulatorController
{
    public function index(): void
    {
        Auth::required();

        $config  = require BASE_PATH . '/config/app.php';
        $webhook = rtrim($config['url'], '/') . '/api/webhook.php';

        Response::view('simulator/index', [
            'title'      => 'WA Bot Simulator',
            'user'       => Auth::user(),
            'webhookUrl' => $webhook,
        ]);
    }

    /**
     * POST /simulator/send
     * Terima payload dari UI, proses via WabotHandler simulator mode
     */
    public function send(): void
    {
        Auth::required();

        $payload = Request::json();

        if (empty($payload)) {
            Response::json(['error' => 'Payload kosong'], 400);
        }

        try {
            // Tandai sebagai simulator agar tidak kirim notif WA ke admin
            $payload['_simulator'] = true;

            // Jalankan handler dalam mode simulator — tidak kirim WA, kembalikan reply
            $handler = new \App\Api\WabotHandler(true);
            $reply   = $handler->handle($payload, true);

            Response::json([
                'status'  => 200,
                'reply'   => $reply ?? '(tidak ada balasan)',
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            error_log('[SIMULATOR ERROR] ' . $e->getMessage());
            Response::json([
                'status' => 500,
                'reply'  => '❌ Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}