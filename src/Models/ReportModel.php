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
      'education' => $this->getEducationSummary($branchId, $filters), // 👈 ወደ አዲሱ ማጠቃለያ እንዲመራ ተደርጓል
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
                e.level_of_education,
                e.employment_situation,
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

  // ─── 📊 አዲሱ የትምህርት ደረጃ ማጠቃለያ ኩዌሪ (Education Summary) ───────────────────
  private function getEducationSummary(string $branchId, array $filters): array {
    // በትምህርት ደረጃ እየከፈለ ቋሚና ጊዜያዊ ወንድ/ሴት ሠራተኞችን በአንድ ጊዜ መቁጠሪያ
    $sql = "SELECT 
                TRIM(e.level_of_education) AS level_of_education,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
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

    $sql .= " GROUP BY TRIM(e.level_of_education)";
    $results = $this->query($sql, $params);

    // 🔄 ቪው ገጹ ላይ በ Foreach ሉፕ በቁልፍ (Key) በቀላሉ እንዲጠራ ዳታውን ማደራጀት
    $formattedData = [];
    foreach ($results as $row) {
        $formattedData[$row['level_of_education']] = [
            'permanent_male'   => (int)$row['permanent_male'],
            'permanent_female' => (int)$row['permanent_female'],
            'temporary_male'   => (int)$row['temporary_male'],
            'temporary_female' => (int)$row['temporary_female']
        ];
    }

    return $formattedData;
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

  // ─── 🔄 የተሻሻለ የፆታ፣ ቅጥር ሁኔታ እና የብራንች ስም መፈለጊያ ───────────────────
  public function getGenderCounts(string $branchId, array $filters = []): array {
    $sql = "SELECT 
                -- የብራንች ስም ከቅርንጫፍ ሰንጠረዥ ማምጣት
                (SELECT b.name FROM branches b WHERE b.id = e.branch_id LIMIT 1) as branch_name,

                -- ቋሚ የሆኑትን በፆታ መለየት
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                
                -- ጊዜያዊ የሆኑትን በፆታ መለየት
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female,
                
                -- ጠቅላላ ድምር
                COUNT(e.employee_id) as total_count
            FROM employees_table e
            WHERE e.branch_id = ?";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'branch_name'      => 'ያልታወቀ ቅርንጫፍ',
        'permanent_male'   => 0, 
        'permanent_female' => 0, 
        'temporary_male'   => 0, 
        'temporary_female' => 0, 
        'total_count'      => 0
    ];
  }

  // Query helper
  private function query(string $sql, array $params = []): array {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}