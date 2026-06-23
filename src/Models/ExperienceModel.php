<?php
namespace App\Models;
use App\Helpers\E;
use PDO;
class ExperienceModel {
    private $db;

    /**
     * ኮኔክሽኑን ከውጭ ይቀበላል (Dependency Injection)
     * ይህ በየቦታው አዲስ ኮኔክሽን እንዳይከፈት ይረዳል
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * አዲስ ዳይሬክተር መመዝገቢያ
     * @param string $id በኮንትሮለር የተፈጠረ UUID
     * @param string $name የተመዘገበው የዳይሬክተሩ ስም
     * @param string $registeredBy የተመዘገበው የመ_regsitered_by ተጠቃሚ
     */
public function getEmployeeExperiences($id) {
    // 1. ኮማዎቹ ተስተካክለዋል (double commas and trailing commas removed)
    $sql = "SELECT id, employee_uuid, company_name, job_title, employment_type, start_date, end_date 
            FROM employee_experiences 
            WHERE employee_uuid = ? AND is_deleted = 0 ORDER BY start_date DESC";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    // 2. fetch() ፋንታ fetchAll() ተጠቀም፤ ምክንያቱም አንድ ሰራተኛ ብዙ የስራ ልምድ ሊኖረው ስለሚችል
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
    public function autoSearch(string $term) {
    $cleanTerm = $term; //AmharicNormalizer::normalize($term);

    $sql = "SELECT 
               company_name
            FROM employee_experiences
            WHERE company_name LIKE ? 
           LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["%$cleanTerm%"]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
public function jobSearch(string $term) {
    $cleanTerm = $term; //AmharicNormalizer::normalize($term);

    $sql = "SELECT 
               job_title
            FROM employee_experiences
            WHERE job_title LIKE ? 
           LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["%$cleanTerm%"]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
/**
 * Checks if a date range overlaps with existing records for an employee
 */
/**
 * Checks if a specific employment type overlaps with existing records
 */
private function overlapsWithScholarship(
    string $employee_uuid,
    string $start_date,
    string $comparison_end
): bool {
    $sql = "SELECT agreement_date, end_date FROM employee_scholarships
            WHERE emp_id     = :uuid
              AND is_deleted = 0
              AND status    != 'rejected'";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':uuid' => $employee_uuid]);
    $scholarships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($scholarships as $scholarship) {
        $schStart = $scholarship['agreement_date'];
        $schEnd   = (!empty($scholarship['end_date']))
                    ? $scholarship['end_date']
                    : date('Y-m-d');

        if ($schStart < $comparison_end && $schEnd >= $start_date) {
            return true;
        }
    }

    return false;
}

public function checkEmploymentTypeOverlap(
    string $employee_uuid,
    string $start_date,
    ?string $end_date,
    string $employment_type,
    ?string $excludeUuid = null
): bool {
    // ── Normalize: accept timestamp integer or Y-m-d string ──────────────────
    $start_date     = date('Y-m-d', is_numeric($start_date) ? (int)$start_date : strtotime($start_date));
    $end_date       = $end_date
                      ? date('Y-m-d', is_numeric($end_date) ? (int)$end_date : strtotime($end_date))
                      : null;
    $comparison_end = $end_date ?: '9999-12-31';

    // ── Case 1: Delegate ──────────────────────────────────────────────────────
    // Blocked by: another Delegate in same interval + scholarship overlap
    // Allowed   : Full-time/Contract in employee_experiences + main employment
    if ($employment_type === 'Delegate') {

        // 1a. Check for another Delegate in same interval
        $sql = "SELECT COUNT(*) FROM employee_experiences 
                WHERE employee_uuid   = :uuid 
                  AND employment_type = 'Delegate'
                  AND is_deleted      = 0
                  AND start_date      < :new_end 
                  AND (end_date IS NULL OR end_date > :new_start)";

        if ($excludeUuid) {
            $sql .= " AND id != :exclude_uuid";
        }

        $params = [
            ':uuid'      => $employee_uuid,
            ':new_start' => $start_date,
            ':new_end'   => $comparison_end,
        ];

        if ($excludeUuid) {
            $params[':exclude_uuid'] = $excludeUuid;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            return true;
        }

        // 1b. Check scholarship overlap
        return $this->overlapsWithScholarship($employee_uuid, $start_date, $comparison_end);
    }

    // ── Case 2a: Non-Delegate → blocked by other non-Delegate experiences ─────
    $sql = "SELECT COUNT(*) FROM employee_experiences 
            WHERE employee_uuid    = :uuid 
              AND employment_type != 'Delegate'
              AND is_deleted       = 0
              AND start_date       < :new_end 
              AND (end_date IS NULL OR end_date > :new_start)";

    if ($excludeUuid) {
        $sql .= " AND id != :exclude_uuid";
    }

    $params = [
        ':uuid'      => $employee_uuid,
        ':new_start' => $start_date,
        ':new_end'   => $comparison_end,
    ];

    if ($excludeUuid) {
        $params[':exclude_uuid'] = $excludeUuid;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    if ($stmt->fetchColumn() > 0) {
        return true;
    }

    // ── Case 2b: Non-Delegate → blocked by main employment period ─────────────
    // Join through employee_experiences to safely resolve the UUID
    $sql2 = "SELECT e.date_of_employed 
             FROM employees_table e
             JOIN employee_experiences ex ON ex.employee_uuid = e.uuid
             WHERE ex.employee_uuid = :uuid
               AND e.is_deleted     != 1
               AND e.date_of_employed IS NOT NULL
             LIMIT 1";

    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([':uuid' => $employee_uuid]);
    $row = $stmt2->fetch(\PDO::FETCH_ASSOC);

    if ($row) {
        $hireDate = $row['date_of_employed'];
        $today    = date('Y-m-d');

        // [hireDate, today] overlaps [start_date, comparison_end]
        // ⟺ hireDate < comparison_end AND today >= start_date
        if ($hireDate < $comparison_end && $today >= $start_date) {
            return true;
        }
    }

    // ── Case 2c: Non-Delegate → blocked by scholarship overlap ────────────────
    return $this->overlapsWithScholarship($employee_uuid, $start_date, $comparison_end);
}
public function saveExperienceData($experienceData) {
    try {
        $this->db->beginTransaction();
        // 1. Insert into employee_experiences using the manual ID provided
        $sql1 = "INSERT INTO employee_experiences(id, employee_uuid, company_name, job_title, employment_type, start_date, end_date, registered_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $experienceData['id'],            // Manual Experience ID
            $experienceData['employee_uuid'],    // Numeric Employee ID
            $experienceData['company_name'],
            $experienceData['job_title'],
            $experienceData['employment_type'],
            $experienceData['start_date'],
            $experienceData['end_date'],
            $experienceData['registered_by']
        ]);

        
        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function getExperienceById(string $id) {
    $sql = "SELECT *
            FROM employee_experiences 
            WHERE id = ? AND is_deleted = 0";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function updateExperienceData($experienceData) {
    try {
        $this->db->beginTransaction();
        // 1. Insert into employee_experiences using the manual ID provided
        $sql1 = "UPDATE employee_experiences SET  company_name = ?, job_title = ?, employment_type = ?, start_date = ?, end_date = ?, updated_by = ? WHERE id = ?";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([$experienceData['company_name'],
            $experienceData['job_title'],
            $experienceData['employment_type'],
            $experienceData['start_date'],
            $experienceData['end_date'],
            $experienceData['registered_by'],
            $experienceData['id']
        ]);

        
        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function softDelete(string $id, string $userId, string $deletionReason, string $deletionSource): array
{
    try {
        $this->db->beginTransaction();

        // 1. Fetch only if not already deleted
        $oldRecord = $this->getExperienceById($id); 
        if (!$oldRecord || (isset($oldRecord['is_deleted']) && $oldRecord['is_deleted'] == 1)) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'መረጃው አልተገኘም ወይም ቀድሞ ተሰርዟል።'];
        }

        // 2. Perform the soft delete
        $stmt = $this->db->prepare("
            UPDATE employee_experiences 
            SET deleted_by = ?, 
                deleted_at = NOW(), 
                is_deleted = 1, 
                deletion_source = ?, 
                deletion_reason = ? 
            WHERE id = ? AND is_deleted = 0
        ");
        
        $executed = $stmt->execute([$userId, $deletionSource, $deletionReason, $id]);

        if (!$executed || $stmt->rowCount() === 0) {
            throw new \Exception("Update failed or record missing.");
        }

        // 3. INTERNAL AUDIT LOG (Highly Recommended)
        // $this->logAudit($userId, $id, 'SOFT_DELETE', $oldRecord);

        $this->db->commit();

        return [
            'status'       => 'success',
            'deleted_type' => 'soft',
            'message'      => 'ይህ የስራ ልምድ በትክክል ተሰርዟል።',
            'oldRecord'    => $oldRecord, 
        ];

    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('ExperienceModel::softDelete - ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።'];
    }
}
}