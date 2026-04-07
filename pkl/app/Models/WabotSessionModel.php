<?php

namespace App\Models;

use App\Core\Database;

class WabotSessionModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==========================================
    // ADMIN SESSION (ganti hubadmin.json)
    // ==========================================

    public function isAdminSession(string $nohp): bool
    {
        $row = $this->db->queryOne(
            "SELECT id FROM wabot_admin_session WHERE nohp = ? LIMIT 1",
            [$nohp]
        );
        return $row !== false;
    }

    public function startAdminSession(string $nohp, string $pushName): void
    {
        $this->db->execute(
            "INSERT INTO wabot_admin_session (nohp, push_name) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE push_name = VALUES(push_name), created_at = NOW()",
            [$nohp, $pushName]
        );
    }

    public function endAdminSession(string $nohp): void
    {
        $this->db->execute(
            "DELETE FROM wabot_admin_session WHERE nohp = ?",
            [$nohp]
        );
    }

    // ==========================================
    // PENDING / TANGGUHAN (ganti tangguhan.json)
    // ==========================================

    public function getPending(string $nohp): array|null
    {
        $row = $this->db->queryOne(
            "SELECT payload FROM wabot_pending WHERE nohp = ? LIMIT 1",
            [$nohp]
        );
        if (!$row) return null;
        return json_decode($row['payload'], true);
    }

    public function setPending(string $nohp, array $payload): void
    {
        $this->db->execute(
            "INSERT INTO wabot_pending (nohp, payload) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE payload = VALUES(payload), created_at = NOW()",
            [$nohp, json_encode($payload)]
        );
    }

    public function clearPending(string $nohp): void
    {
        $this->db->execute(
            "DELETE FROM wabot_pending WHERE nohp = ?",
            [$nohp]
        );
    }

    // ==========================================
    // LUPA PRESENSI rate limit (ganti lupa.json)
    // ==========================================

    public function getLupaHariIni(string $nohp): int
    {
        $today = date('Y-m-d');
        $row   = $this->db->queryOne(
            "SELECT jumlah FROM wabot_lupa WHERE nohp = ? AND tanggal = ? LIMIT 1",
            [$nohp, $today]
        );
        return $row ? (int)$row['jumlah'] : 0;
    }

    public function incrementLupa(string $nohp): int
    {
        $today = date('Y-m-d');
        $this->db->execute(
            "INSERT INTO wabot_lupa (nohp, tanggal, jumlah) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE jumlah = jumlah + 1",
            [$nohp, $today]
        );
        return $this->getLupaHariIni($nohp);
    }

    // ==========================================
    // ANTI-SPAM bebas chat (ganti bebas.json)
    // ==========================================

    public function getAntispam(string $nohp): array
    {
        $row = $this->db->queryOne(
            "SELECT count, last_reply FROM wabot_antispam WHERE nohp = ? LIMIT 1",
            [$nohp]
        );
        return $row ?: ['count' => 0, 'last_reply' => null];
    }

    public function incrementAntispam(string $nohp): int
    {
        $this->db->execute(
            "INSERT INTO wabot_antispam (nohp, count, last_reply) VALUES (?, 1, NULL)
             ON DUPLICATE KEY UPDATE count = count + 1",
            [$nohp]
        );
        return (int)($this->getAntispam($nohp)['count']);
    }

    public function resetAntispam(string $nohp): void
    {
        $this->db->execute(
            "INSERT INTO wabot_antispam (nohp, count, last_reply) VALUES (?, 0, NOW())
             ON DUPLICATE KEY UPDATE count = 0, last_reply = NOW()",
            [$nohp]
        );
    }

    public function setAntispam(string $nohp, array $data): void
    {
        $count     = $data['count'] ?? 0;
        $lastReply = $data['last_reply'] ?? null;
        $this->db->execute(
            "INSERT INTO wabot_antispam (nohp, count, last_reply) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE count = VALUES(count), last_reply = VALUES(last_reply)",
            [$nohp, $count, $lastReply]
        );
    }

    // ==========================================
    // PENDING PRESENSI (foto dulu → ket setelahnya)
    // Simpan di wabot_pending dengan prefix key 'presensi_'
    // ==========================================

    public function getPendingPresensi(string $nohp): ?array
    {
        $row = $this->db->queryOne(
            "SELECT payload FROM wabot_pending WHERE nohp = ? AND payload LIKE '%pending_presensi%' LIMIT 1",
            ['presensi_' . $nohp]
        );
        if (!$row) {
            // Coba tanpa prefix (fallback)
            $row = $this->db->queryOne(
                "SELECT payload FROM wabot_pending WHERE nohp = ? AND payload LIKE '%\"type\":\"pending_presensi\"%' LIMIT 1",
                [$nohp]
            );
        }
        if (!$row) return null;
        $data = json_decode($row['payload'], true);
        if (!isset($data['type']) || $data['type'] !== 'pending_presensi') return null;

        // Cek timeout 1 jam
        if (isset($data['timestamp']) && (time() - $data['timestamp']) > 3600) {
            $this->clearPendingPresensi($nohp);
            return null;
        }
        return $data;
    }

    public function setPendingPresensi(string $nohp, array $payload): void
    {
        $this->db->execute(
            "INSERT INTO wabot_pending (nohp, payload) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE payload = VALUES(payload), created_at = NOW()",
            [$nohp, json_encode($payload)]
        );
    }

    public function clearPendingPresensi(string $nohp): void
    {
        $this->db->execute(
            "DELETE FROM wabot_pending WHERE nohp = ? AND payload LIKE '%\"type\":\"pending_presensi\"%'",
            [$nohp]
        );
    }

    // ==========================================
    // REKAP STEP (multi-step cek rekap)
    // ==========================================

    public function getRekapStep(string $nohp): ?array
    {
        $row = $this->db->queryOne(
            "SELECT payload FROM wabot_pending WHERE nohp = ? AND payload LIKE '%\"menu\":\"rekap\"%' LIMIT 1",
            ['rekap_' . $nohp]
        );
        if (!$row) return null;
        return json_decode($row['payload'], true);
    }

    public function setRekapStep(string $nohp, array $data): void
    {
        $this->db->execute(
            "INSERT INTO wabot_pending (nohp, payload) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE payload = VALUES(payload), created_at = NOW()",
            ['rekap_' . $nohp, json_encode($data)]
        );
    }

    public function clearRekapStep(string $nohp): void
    {
        $this->db->execute(
            "DELETE FROM wabot_pending WHERE nohp = ?",
            ['rekap_' . $nohp]
        );
    }
}