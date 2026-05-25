<?php
namespace App\Models;

use PDO;

class ReportModel {

  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  // ─── 🔄 የሪፖርት አይነቶችን ወደየፈንክሽናቸው መምሪያ ───────────────────
  public function getReport(string $type, string $branchId, array $filters = []): array {
    return match($type) {
      'employees'   => $this->getEmployees($branchId, $filters),
      'education'   => $this->getEducationSummary($branchId, $filters),
      'age'         => $this->getAgeSummary($branchId, $filters),
      'level'       => $this->getLevelSummary($branchId, $filters),
      'payroll'     => $this->getPayroll($branchId, $filters),
      'discipline'  => $this->getDisciplineSummary($branchId, $filters),
      'performance' => $this->getPerformanceSummary($branchId, $filters), 
      default       => [],
    };
  }

  // ─── Employees Query ─────────────────────────────────────────
  private function getEmployees(string $branchId, array $filters): array {
    $sql = "SELECT e.employee_id AS id, CONCAT(e.first_name, ' ', e.father_name) AS full_name, e.sex, e.level_of_education, e.employment_situation, e.date_of_employed
            FROM employees_table e WHERE e.branch_id = ? AND e.status = 'Active'";
    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    $sql .= " ORDER BY e.first_name ASC";
    return $this->query($sql, $params); 
  }

  // ─── 📊 የትምህርት ደረጃ ማጠቃለያ ኩዌሪ ───────────────────
  private function getEducationSummary(string $branchId, array $filters): array {
    $sql = "SELECT TRIM(e.level_of_education) AS level_of_education,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
            FROM employees_table e WHERE e.branch_id = ? AND e.status = 'Active'";

    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    $sql .= " GROUP BY TRIM(e.level_of_education)";
    
    $results = $this->query($sql, $params);
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

  // ─── 📊 የእድሜ ክልል ማጠቃለያ ኩዌሪ ───────────────────
  private function getAgeSummary(string $branchId, array $filters): array {
    $sql = "SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 18 AND 22 THEN '18_22'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 23 AND 27 THEN '23_27'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 28 AND 29 THEN '28_29'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 30 AND 32 THEN '30_32'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 33 AND 37 THEN '33_37'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 38 AND 42 THEN '38_42'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) BETWEEN 43 AND 47 THEN '43_47'
                    WHEN TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) >= 48 THEN '48_above'
                    ELSE 'other'
                END AS age_range,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
            FROM employees_table e WHERE e.branch_id = ? AND e.status = 'Active'";

    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    $sql .= " GROUP BY age_range";

    $results = $this->query($sql, $params);
    $formattedData = [];
    foreach ($results as $row) {
        $formattedData[$row['age_range']] = [
            'permanent_male'   => (int)$row['permanent_male'],
            'permanent_female' => (int)$row['permanent_female'],
            'temporary_male'   => (int)$row['temporary_male'],
            'temporary_female' => (int)$row['temporary_female']
        ];
    }
    return $formattedData;
  }

  // ─── 📊 የስራ ደረጃ ማጠቃለያ ኩዌሪ ───────────────────
  private function getLevelSummary(string $branchId, array $filters): array {
    $sql = "SELECT TRIM(j.dereja) AS job_level,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
            FROM employees_table e
            INNER JOIN job_property j ON e.job_property_id = j.id
            WHERE e.branch_id = ? AND e.status = 'Active'";

    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    $sql .= " GROUP BY TRIM(j.dereja)";
    
    $results = $this->query($sql, $params);
    $formattedData = [];
    foreach ($results as $row) {
        $formattedData[$row['job_level']] = [
            'permanent_male'   => (int)$row['permanent_male'],
            'permanent_female' => (int)$row['permanent_female'],
            'temporary_male'   => (int)$row['temporary_male'],
            'temporary_female' => (int)$row['temporary_female']
        ];
    }
    return $formattedData;
  }

  // ─── 📊 የዲሲፕሊን ሁኔታ ማጠቃለያ ኩዌሪ ───────────────────
  private function getDisciplineSummary(string $branchId, array $filters): array {
    // 🛠️ የተስተካከለ፦ የ CASE ቁልፎች ከቪው ማፒንግ ቁልፎች ጋር 100% እንዲገጣጠሙ ተደርገዋል
    $sql = "SELECT 
                CASE 
                    WHEN TRIM(LOWER(e.displin_situation)) IN ('ንፁህ', 'የለም', 'ምንም', 'no punishment', 'clean', '') OR e.displin_situation IS NULL THEN 'no_discipline'
                    WHEN TRIM(LOWER(e.displin_situation)) LIKE '%15%' OR TRIM(LOWER(e.displin_situation)) LIKE '%ደመወዝ%' THEN 'salary_cut_15'
                    WHEN TRIM(LOWER(e.displin_situation)) LIKE '%ማስጠንቀቂያ%' OR TRIM(LOWER(e.displin_situation)) LIKE '%warning%' THEN 'warning'
                    WHEN TRIM(LOWER(e.displin_situation)) LIKE '%ዕገዳ%' OR TRIM(LOWER(e.displin_situation)) LIKE '%suspension%' THEN 'suspension'
                    WHEN TRIM(LOWER(e.displin_situation)) LIKE '%ስንብት%' OR TRIM(LOWER(e.displin_situation)) LIKE '%dismiss%' THEN 'dismissal'
                    ELSE 'no_discipline'
                END AS discipline_status,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
            FROM employees_table e 
            WHERE e.branch_id = ? AND e.status = 'Active'";

    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    
    $sql .= " GROUP BY discipline_status";

    $results = $this->query($sql, $params);
    
    $formattedData = [
        'no_discipline' => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'salary_cut_15' => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'warning'       => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'suspension'    => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'dismissal'     => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0]
    ];

    foreach ($results as $row) {
        if (isset($formattedData[$row['discipline_status']])) {
            $formattedData[$row['discipline_status']] = [
                'permanent_male'   => (int)$row['permanent_male'],
                'permanent_female' => (int)$row['permanent_female'],
                'temporary_male'   => (int)$row['temporary_male'],
                'temporary_female' => (int)$row['temporary_female']
            ];
        }
    }
    return $formattedData;
  }

  // ─── 📊 የBSC የአፈጻጸም ማጠቃለያ ኩዌሪ (የተስተካከለ) ───────────────────
private function getPerformanceSummary(string $branchId, array $filters): array {
    // 📊 3ቱን ደረጃዎች ብቻ ታሳቢ ያደረገው እና GROUP BY ላይ አስተማማኝ የሆነው ኪውሪ
    $sql = "SELECT 
                CASE 
                    -- 1. ከፍተኛ (High)
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%እጅግ ከፍተኛ%' 
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%outstanding%'
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%ከፍተኛ%' 
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%very good%' THEN 'high'
                    
                    -- 2. መካከለኛ (Medium)
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%መካከለኛ%' 
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%good%' THEN 'medium'
                    
                    -- 3. ዝቅተኛ (Low)
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%ዝቅተኛ%' 
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%satisfactory%'
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%poor%'
                      OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%እጅግ ዝቅተኛ%' THEN 'low'
                    
                    ELSE 'low'
                END AS perf_status,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female
            FROM employees_table e 
            WHERE e.branch_id = ? AND e.status = 'Active'";

    $params = [$branchId];
    if (!empty($filters['from'])) { $sql .= " AND e.date_of_employed >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to'])) { $sql .= " AND e.date_of_employed <= ?"; $params[] = $filters['to']; }
    
    // 🔥 እዚህ ጋር ሙሉውን የ CASE መዋቅር በ GROUP BY ውስጥ መደገም አለበት (ለደህንነት)
    $sql .= " GROUP BY 
                CASE 
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%እጅግ ከፍተኛ%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%outstanding%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%ከፍተኛ%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%very good%' THEN 'high'
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%መካከለኛ%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%good%' THEN 'medium'
                    WHEN TRIM(LOWER(e.level_of_effeciency)) LIKE '%ዝቅተኛ%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%satisfactory%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%poor%' OR TRIM(LOWER(e.level_of_effeciency)) LIKE '%እጅግ ዝቅተኛ%' THEN 'low'
                    ELSE 'low'
                END";

    $results = $this->query($sql, $params);
    
    // 🔄 ከቪው (`report_performance.php`) ጋር 100% የሚገጥመው አዲሱ አደረጃጀት
    $formattedData = [
        'high'   => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'medium' => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0],
        'low'    => ['permanent_male' => 0, 'permanent_female' => 0, 'temporary_male' => 0, 'temporary_female' => 0]
    ];

    foreach ($results as $row) {
        $status = $row['perf_status'];
        if (isset($formattedData[$status])) {
            $formattedData[$status] = [
                'permanent_male'   => (int)$row['permanent_male'],
                'permanent_female' => (int)$row['permanent_female'],
                'temporary_male'   => (int)$row['temporary_male'],
                'temporary_female' => (int)$row['temporary_female']
            ];
        }
    }
    
    return $formattedData;
}

  // ─── Payroll Query ───────────────────────────────────────────
  private function getPayroll(string $branchId, array $filters): array {
    $sql = "SELECT e.employee_id AS id, CONCAT(e.first_name, ' ', e.father_name) AS full_name, e.sex, e.date_of_employed
            FROM employees_table e WHERE e.branch_id = ? AND e.status = 'Active' ORDER BY e.first_name ASC";
    return $this->query($sql, [$branchId]); 
  }

  // ─── 🔄 የፆታ፣ ቅጥር ሁኔታ እና የብራንች ስም መፈለጊያ ───────────────────
  public function getGenderCounts(string $branchId, array $filters = []): array {
    $sql = "SELECT (SELECT b.name FROM branches b WHERE b.id = e.branch_id LIMIT 1) as branch_name,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as permanent_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('permanent', 'ቋሚ') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as permanent_female,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('male', 'm', 'ወንድ') THEN 1 ELSE 0 END) as temporary_male,
                SUM(CASE WHEN TRIM(LOWER(e.employment_situation)) IN ('temporary', 'contract', 'ጊዜያዊ', 'ኮንትራት') AND TRIM(LOWER(e.sex)) IN ('female', 'f', 'ሴት') THEN 1 ELSE 0 END) as temporary_female,
                COUNT(e.employee_id) as total_count
            FROM employees_table e WHERE e.branch_id = ? AND e.status = 'Active'";
            
    $stmt = $this->db->prepare($sql); $stmt->execute([$branchId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['branch_name'=>'ያልታወቀ ቅርንጫፍ','permanent_male'=>0,'permanent_female'=>0,'temporary_male'=>0,'temporary_female'=>0,'total_count'=>0];
  }

  private function query(string $sql, array $params = []): array {
    $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}