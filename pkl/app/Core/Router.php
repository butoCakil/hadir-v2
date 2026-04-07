<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    // ==========================================
    // Registrasi Route
    // ==========================================

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    // ==========================================
    // Jalankan Router
    // ==========================================

    public function dispatch(): void
    {
        try {
            $this->run();
        } catch (\Throwable $e) {
            error_log('[500] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->serverError($e);
        }
    }

    private function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Normalisasi: hapus trailing slash kecuali root "/"
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            $pattern = $this->buildPattern($route['path']);

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Ambil parameter dinamis dari URL
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                $this->call($route['handler'], $params);
                return;
            }
        }

        // Tidak ada route yang cocok
        $this->notFound();
    }

    // ==========================================
    // Pattern Matching untuk URL dinamis
    // Contoh: /siswa/{nis} → /siswa/12345
    // ==========================================

    private function buildPattern(string $path): string
    {
        // Escape karakter regex
        $pattern = preg_quote($path, '#');

        // Ganti \{param\} menjadi named capture group
        $pattern = preg_replace('/\\\{(\w+)\\\}/', '(?P<$1>[^/]+)', $pattern);

        return '#^' . $pattern . '$#';
    }

    // ==========================================
    // Panggil Handler
    // ==========================================

    private function call(callable|array $handler, array $params = []): void
    {
        if (is_callable($handler)) {
            // Closure: $router->get('/', function() { ... })
            call_user_func($handler, $params);
            return;
        }

        if (is_array($handler)) {
            // Array: [ClassName::class, 'methodName']
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                error_log("[ROUTER] Controller tidak ditemukan: $class");
                $this->notFound();
                return;
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                error_log("[ROUTER] Method tidak ditemukan: $class::$method");
                $this->notFound();
                return;
            }

            call_user_func([$controller, $method], $params);
        }
    }

    // ==========================================
    // 404 Handler
    // ==========================================

    private function notFound(): void
    {
        http_response_code(404);

        $view = BASE_PATH . '/app/Views/errors/404.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '<h1>404 — Halaman tidak ditemukan.</h1>';
        }
    }

    // ==========================================
    // 500 Handler
    // ==========================================

    private function serverError(\Throwable $e): void
    {
        if (headers_sent()) return;

        http_response_code(500);

        $view = BASE_PATH . '/app/Views/errors/500.php';
        if (file_exists($view)) {
            $isDev   = ($_ENV['APP_ENV'] ?? 'production') === 'development';
            $message = $isDev
                ? $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine()
                : '';
            require $view;
        } else {
            echo '<h1>500 — Terjadi kesalahan server.</h1>';
        }
    }
}