<?php
namespace App\Models;

use PDO;

class ReportModel {

  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  // ─── Route to correct query by report type ───────────────────
  public function getReport(string $type, string $branchId, array $filters = []): array {
    return match($type) {
      'employees' => $this->getEmployees($branchId, $filters),
      'payroll' => $this->getPayroll($branchId, $filters),
      default     => [],
    };
  }

  // ─── Employees Query ─────────────────────────────────────────
 private function getEmployees(string $branchId, array $filters): array {
    // 🆕 የሌሉትን የ JOIN ሰንጠረዦች በሙሉ አውጥተነዋል
    $sql = "SELECT 
                e.employee_id AS id, 
                CONCAT(e.first_name, ' ', e.father_name) AS full_name,
                e.date_of_employed
            FROM employees_table e 
            WHERE e.branch_id = ?";
            
    $params = [$branchId];

    if (!empty($filters['from'])) {
      $sql .= " AND e.date_of_employed >= ?";
      $params[] = $filters['from'];
    }
    if (!empty($filters['to'])) {
      $sql .= " AND e.date_of_employed <= ?";
      $params[] = $filters['to'];
    }

    $sql .= " ORDER BY e.first_name ASC";
    return $this->query($sql, $params); 
}
   private function getPayroll(string $branchId, array $filters): array {
    // 🆕 የሌሉትን የ JOIN ሰንጠረዦች በሙሉ አውጥተነዋል
    $sql = "SELECT 
                e.employee_id AS id, 
                CONCAT(e.first_name, ' ', e.father_name) AS full_name,
                e.date_of_employed
            FROM employees_table e 
            WHERE e.branch_id = ?";
            
    $params = [$branchId];

    if (!empty($filters['from'])) {
      $sql .= " AND e.date_of_employed >= ?";
      $params[] = $filters['from'];
    }
    if (!empty($filters['to'])) {
      $sql .= " AND e.date_of_employed <= ?";
      $params[] = $filters['to'];
    }

    $sql .= " ORDER BY e.first_name ASC";
    return $this->query($sql, $params); 
}
// ─── Query helper ────────────────────────────────────────────
  private function query(string $sql, array $params = []): array {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}