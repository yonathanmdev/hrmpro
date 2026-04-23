<?php
namespace App\Models;
use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class AuditLog {
    private $db;
    private $logger;

    public function __construct($db) {
        $this->db = $db;

        // Initialize Monolog logger
        $this->logger = new Logger('audit');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/audit.log', Level::Info));
    }

    /**
     * Log an audit event
     */
    public function log($userId, $action, $entityType, $entityId = null, $oldValues = null, $newValues = null, $metadata = []) {
        try {
            // Prepare data
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Insert into database
            $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $action,
                $entityType,
                $entityId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ipAddress,
                $userAgent
            ]);

            // Also log to file with Monolog
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => $ipAddress,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            if ($oldValues) $logData['old_values'] = $oldValues;
            if ($newValues) $logData['new_values'] = $newValues;
            if ($metadata) $logData['metadata'] = $metadata;

            $this->logger->info($action, $logData);

        } catch (\Exception $e) {
            // Log the error but don't throw it to avoid breaking the main flow
            error_log("Audit log error: " . $e->getMessage());
        }
    }

    /**
     * Get audit logs with optional filters
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['entity_id'])) {
            $where[] = "entity_id = ?";
            $params[] = $filters['entity_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT * FROM audit_logs $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get audit log statistics
     */
    public function getStats($dateFrom = null, $dateTo = null) {
        $params = [];
        $where = "";

        if ($dateFrom && $dateTo) {
            $where = "WHERE created_at BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $where = "WHERE created_at >= ?";
            $params = [$dateFrom];
        } elseif ($dateTo) {
            $where = "WHERE created_at <= ?";
            $params = [$dateTo];
        }

        $sql = "SELECT
                    action,
                    entity_type,
                    COUNT(*) as count
                FROM audit_logs
                $where
                GROUP BY action, entity_type
                ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}