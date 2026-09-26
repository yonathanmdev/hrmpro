<?php
// src/Models/PasswordResetModel.php
namespace App\Models;

use PDO;

class PasswordResetModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

public function create(string $id, string $userId, string $tokenHash, ?string $requestedBy = null): bool {
    $sql = "INSERT INTO password_resets (id, user_id, requested_by, token_hash, expires_at, used, created_at)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), 0, NOW())";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$id, $userId, $requestedBy, $tokenHash]);
}

    public function findValidByHash(string $tokenHash): ?array {
        $sql = "SELECT * FROM password_resets
                WHERE token_hash = ? AND used = 0 AND expires_at > NOW()
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markUsed(string $id): bool {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Optional but recommended: invalidate old unused tokens when issuing a new one
    public function invalidateForUser(string $userId): bool {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0");
        return $stmt->execute([$userId]);
    }
}