<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

class LogsController
{
    private Database $db;

    // Root directories to scan
    private array $scanDirs = [
        '/home/dvttaulx/pkl/storage',
        '/home/dvttaulx/public_html/dev',
    ];

    // Extensions/names to include
    private array $allowedNames  = ['error_log'];
    private array $allowedExts   = ['log', 'json'];

    // Files to exclude
    private array $excludedNames = ['.htaccess', 'index.php'];
    private array $excludedExts  = ['php', 'html', 'css', 'js'];

    public function __construct()
    {
        Auth::required();
        $this->db = Database::getInstance();
    }

    // ==========================================
    // GET /logs
    // ==========================================
    public function index(): void
    {
        $logFiles = $this->scanLogFiles();
        $tab      = $_GET['tab']   ?? 'file';
        $file     = $_GET['file']  ?? '';
        $limit    = (int)($_GET['limit'] ?? 50);
        $limit    = in_array($limit, [15, 30, 50, 100, 500]) ? $limit : 50;

        $selectedFile    = null;
        $fileContent     = null;
        $fileContentRows = [];

        // Load file content if selected
        if ($tab === 'file' && $file) {
            $fullPath = $this->resolveFilePath($file, $logFiles);
            if ($fullPath) {
                $selectedFile = $file;
                $lines        = file($fullPath, FILE_IGNORE_NEW_LINES) ?: [];
                $lines        = array_reverse($lines); // terbaru di atas
                $fileContentRows = array_slice($lines, 0, $limit);
                $fileContent  = implode("\n", $fileContentRows);
            }
        }

        // DB logs (tabel tmp)
        $dbLogs = [];
        if ($tab === 'db') {
            $dbLogs = $this->db->query(
                "SELECT number, msg, timestamp FROM tmp ORDER BY timestamp DESC LIMIT ?",
                [$limit]
            );
        }

        Response::view('logs/index', [
            'title'        => 'System Logs',
            'user'         => Auth::user(),
            'logFiles'     => $logFiles,
            'tab'          => $tab,
            'selectedFile' => $selectedFile,
            'fileContent'  => $fileContent,
            'limit'        => $limit,
            'dbLogs'       => $dbLogs,
        ]);
    }

    // ==========================================
    // POST /logs/clear
    // ==========================================
    public function clear(): void
    {
        $file     = trim($_POST['file'] ?? '');
        $logFiles = $this->scanLogFiles();
        $fullPath = $this->resolveFilePath($file, $logFiles);

        if (!$fullPath) {
            Response::error('File tidak ditemukan atau tidak diizinkan.', 404);
            return;
        }

        file_put_contents($fullPath, '');
        Response::success([], 'Log berhasil dikosongkan.');
    }

    // ==========================================
    // GET /logs/raw — raw content untuk AJAX refresh
    // ==========================================
    public function raw(): void
    {
        Auth::required();

        $file     = $_GET['file']  ?? '';
        $limit    = (int)($_GET['limit'] ?? 50);
        $limit    = in_array($limit, [15, 30, 50, 100, 500]) ? $limit : 50;
        $logFiles = $this->scanLogFiles();
        $fullPath = $this->resolveFilePath($file, $logFiles);

        if (!$fullPath) {
            Response::json(['status'=>'error','message'=>'File tidak ditemukan.']);
            return;
        }

        $lines = file($fullPath, FILE_IGNORE_NEW_LINES) ?: [];
        $lines = array_reverse($lines);
        $lines = array_slice($lines, 0, $limit);

        Response::json([
            'status'  => 'success',
            'content' => implode("\n", $lines),
            'size'    => round(filesize($fullPath) / 1024, 1),
        ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function scanLogFiles(): array
    {
        $files = [];

        foreach ($this->scanDirs as $dir) {
            if (!is_dir($dir)) continue;

            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iter as $file) {
                $name = $file->getFilename();
                $ext  = strtolower($file->getExtension());
                $path = $file->getPathname();

                if (in_array($name, $this->excludedNames)) continue;
                if (in_array($ext, $this->excludedExts)) continue;

                $allowed = in_array($name, $this->allowedNames) || in_array($ext, $this->allowedExts);
                if (!$allowed) continue;

                // Relative key untuk URL
                $key = str_replace(
                    ['/home/dvttaulx/pkl/', '/home/dvttaulx/public_html/dev/'],
                    ['pkl/', 'dev/'],
                    $path
                );

                $files[$key] = [
                    'key'      => $key,
                    'name'     => $name,
                    'path'     => $path,
                    'size'     => round($file->getSize() / 1024, 1),
                    'modified' => date('d M Y H:i', $file->getMTime()),
                    'ext'      => $ext ?: 'log',
                ];
            }
        }

        // Sort by modified time DESC
        uasort($files, fn($a, $b) => filemtime($b['path']) <=> filemtime($a['path']));

        return $files;
    }

    private function resolveFilePath(string $key, array $logFiles): ?string
    {
        return $logFiles[$key]['path'] ?? null;
    }
}
