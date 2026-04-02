<?php
namespace App\Models;
use PDO;

class Developer {
    private $db;

    /**
     * Dependency Injection for Database Connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
     public function createdeveloper($developer_uuid, $full_name, $position, $email, $phone, $active_time, $payment) {
        try {
            $stmt = $this->db->prepare("INSERT INTO developers (uuid, full_name, position, email, phone, active_time, payment) VALUES (:uuid, :full_name, :position, :email, :phone, :active_time, :payment)");
            return $stmt->execute([
                ':uuid' => $developer_uuid,
                ':full_name' => $full_name,
                ':position' => $position,
                ':email' => $email,
                ':phone' => $phone,
                ':active_time' => $active_time,
                ':payment' => $payment 
            ]);
        } catch (\PDOException $e) { // እዚህ ጋር \ ምልክቷን ጨምርባት
            if ($e->getCode() == 23000) {
                // ኢሜይሉ ቀድሞ ካለ ይህ ስህተት ይመዘገባል
                error_log("Duplicate Entry: " . $e->getMessage());
            }
            return false;
        }
    }
    public function getAllDevelopers() {
        $stmt = $this->db->query("SELECT * FROM developers ORDER BY created_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
