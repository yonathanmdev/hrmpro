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
      'payroll'   => $this->getPayroll($branchId, $filters),
      default     => [],
    };
  }

  // ─── Employees Query ─────────────────────────────────────────
  private function getEmployees(string $branchId, array $filters): array {
    $sql = "SELECT 
                e.employee_id AS id, 
                CONCAT(e.first_name, ' ', e.father_name) AS full_name,
                e.sex,
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

  // ─── Payroll Query ───────────────────────────────────────────
  private function getPayroll(string $branchId, array $filters): array {
    $sql = "SELECT 
                e.employee_id AS id, 
                CONCAT(e.first_name, ' ', e.father_name) AS full_name,
                e.sex,
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

  // ─── 🛠️ የተስተካከለ የፆታ ቆጠራ ፈንክሽን (ባዶ ቦታዎችን እና አጻጻፍን የሚያስተካክል) ───
  public function getGenderCounts(string $branchId, array $filters = []): array {
    // LOWER() እና TRIM() በዳታቤዝ ውስጥ ያሉትን የካፒታል/ትንሽ ፊደላት እና የባዶ ቦታ ክፍተቶችን ያጠፋሉ
    $sql = "SELECT 
                SUM(CASE WHEN TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as female_count,
                COUNT(e.employee_id) as total_count
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

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['male_count' => 0, 'female_count' => 0, 'total_count' => 0];
  }

  // ─── Query helper ────────────────────────────────────────────
  private function query(string $sql, array $params = []): array {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}