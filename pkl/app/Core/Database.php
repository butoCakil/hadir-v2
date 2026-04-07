<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['name'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Jangan expose detail error ke user
            error_log('[DB ERROR] ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }

    // Ambil instance (singleton)
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Ambil objek PDO langsung
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Jalankan query SELECT, return semua baris
     * Contoh: DB::query("SELECT * FROM datasiswa WHERE kelas = ?", [$kelas])
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Jalankan query SELECT, return satu baris saja
     * Contoh: DB::queryOne("SELECT * FROM datasiswa WHERE nis = ?", [$nis])
     */
    public function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Jalankan query INSERT / UPDATE / DELETE
     * Return jumlah baris yang terpengaruh
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Ambil ID terakhir setelah INSERT
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Jalankan beberapa query dalam satu transaksi
     * Otomatis rollback jika salah satu gagal
     */
    public function transaction(callable $callback): mixed
    {
        try {
            $this->pdo->beginTransaction();
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('[DB TRANSACTION ERROR] ' . $e->getMessage());
            throw $e;
        }
    }

    // Cegah clone dan unserialize singleton
    private function __clone() {}
    public function __wakeup() {}
}