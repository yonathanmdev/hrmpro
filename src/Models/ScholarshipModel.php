<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
use PDO;
class ScholarshipModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function onLeaveEmployees(string $organizationId, string $branchId) {
        $sql = "SELECT 
                e.uuid,
                e.employee_id,
                e.first_name,
                e.father_name,
                e.g_father_name,
                e.sex,
                e.birth_date,
                e.phone_number,
                e.yegabcha_huneta,
                e.organization_id,
                e.branch_id,
                e.job_property_id,
                jp.job_name,
                jp.status as job_status,
                e.status,
                e.rdate
            FROM employees_table e
            LEFT JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ?
              AND e.branch_id = ?
              AND  e.status = 'Study Leave'
              AND e.is_deleted != 2
            ORDER BY e.rdate DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
public function autoSearch($term, $branchId) {
        // ተጠቃሚው የጻፈውን ቃል Normalize እናደርጋለን
        $cleanTerm = AmharicNormalizer::normalize($term);

        // organization_id በመጠቀም የአንዱ ተከራይ ዳታ ከሌላው እንዳይቀላቀል እናደርጋለን
        $sql = "SELECT uuid, employee_id, first_name, father_name, g_father_name, employee_id, employee_image 
                FROM employees_table 
                WHERE branch_id = ? AND status = 'Active' AND is_deleted != 2
                AND full_name_normalized LIKE ? 
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        // % በመጠቀም በስሙ ውስጥ የትኛውም ቦታ ላይ ያለን ቃል እንዲያገኝ እናደርጋለን
        $stmt->execute([$branchId, "%$cleanTerm%"]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

  public function saveScholarshipWithDocument($scholarshipData, $documentData) {
    try {
        $this->db->beginTransaction();

        // 1. Insert into employee_scholarships using the manual ID provided
        $sql1 = "INSERT INTO employee_scholarships (
                    id, emp_id, agreement_date, scholarship_type, 
                    scholarship_duration_years, registered_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $scholarshipData['id'],            // Manual Scholarship ID
            $scholarshipData['employee_id'],    // Numeric Employee ID
            $scholarshipData['agreement_date'],
            $scholarshipData['scholarship_type'],
            $scholarshipData['duration'],
            $scholarshipData['registered_by']
        ]);

        // 2. Insert into employee_documents using the manual ID provided
        $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type, file_url) 
                 VALUES (?, ?, 'SCHOLARSHIP', ?, 'የት/ት ውል', ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $documentData['id'],               // Manual Document ID
            $scholarshipData['employee_id'],
            $scholarshipData['id'],     // Numeric Employee ID
            $documentData['file_url']
        ]);
        // 4. Update Employee Status using the UUID from the controller
        // Note: $scholarshipData['uuid'] must be passed from your controller
        $sql4 = "UPDATE employees_table SET status = 'Study Leave Pending' WHERE uuid = ?";
        $stmt4 = $this->db->prepare($sql4);
        $stmt4->execute([$scholarshipData['employee_id']]);

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function countPendingScholarshipEmployees(string $organizationId, string $branchId) {
    $sql = "
        SELECT COUNT(*) as total
        FROM employees_table 
        WHERE organization_id = ?
          AND branch_id = ? 
          AND is_deleted != 2
          AND status = 'Study Leave Pending'
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId]);
    
    // fetchColumn() በቀጥታ ቁጥሩን (total) ይመልስልሃል
    return $stmt->fetchColumn();
}
 public function onLeavePendingEmployees(string $organizationId, string $branchId) {
        $sql = " SELECT 
                e.uuid,
                e.employee_id,
                e.first_name,
                e.father_name,
                e.g_father_name,
                e.sex,
                e.birth_date,
                e.phone_number,
                e.yegabcha_huneta,
                e.organization_id,
                e.branch_id,
                e.job_property_id,
                jp.job_name,
                jp.status as job_status,
                e.status,
                s.created_at as rdate,
                s.id as record_id,
                s.registered_by
            FROM employees_table e
            INNER JOIN job_property jp ON e.job_property_id = jp.id
            INNER JOIN employee_scholarships s ON e.uuid = s.emp_id 
            WHERE e.organization_id = ?
              AND s.status = 'pending'
              AND e.branch_id = ?
              AND s.is_deleted = 0
              AND  e.status = 'Study Leave Pending'
            ORDER BY s.created_at ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getActiveScholarships(string $organizationId, string $branchId, string $status) {
        $sql = "SELECT 
                    e.uuid, 
                    e.employee_id, 
                    e.first_name, 
                    e.father_name, 
                    e.g_father_name,
                    e.birth_date,
                    e.rdate,
                    jp.job_name, 
                    s.id as record_id,
                    s.status as scholarship_status,
                    s.scholarship_type,
                    s.agreement_date,
                    s.scholarship_duration_years
                FROM employees_table e
                INNER JOIN employee_scholarships s ON e.uuid = s.emp_id
                INNER JOIN job_property jp ON e.job_property_id = jp.id
                WHERE e.organization_id = ? 
                  AND e.branch_id = ? 
                  AND s.status = ?
                  AND s.is_deleted = 0
                ORDER BY s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId, $status]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
   public function getScholarshipDetails(string $scholarshipId) {
    // Note: I removed s.agreement_number to prevent the "Column not found" error
    // until you manually add it to your database.
   $sql = "SELECT 
                s.*, 
                d.file_url, 
                d.entity_type
            FROM employee_scholarships s
            INNER JOIN employee_documents d ON s.id = d.owner_id 
            WHERE s.id = ? 
            AND s.status = 'pending' AND s.is_deleted = 0 AND d.owner_type = 'SCHOLARSHIP'
            ORDER BY s.created_at DESC LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$scholarshipId]);
    
    // Use fetchAll if an employee can have multiple scholarship records
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}
   public function approveOnLeaveEmployee(string $uuid, string $scholarship_uuid, string $userID): bool {
    try {
        // Start the transaction
        $this->db->beginTransaction();

        // 1. Update the main employee table
        $sqlEmployee = "UPDATE employees_table SET status = 'Study Leave' WHERE uuid = ?";
        $stmt1 = $this->db->prepare($sqlEmployee);
        $stmt1->execute([$uuid]);

        // 2. Update the scholarship table 
        // Note: Ensure employee_scholarships actually has a 'uuid' column. 
        // If it uses 'emp_id', you'll need to fetch that ID first.
        $sqlScholarship = "UPDATE employee_scholarships 
                           SET status = 'approved', 
                               approved_by = ?, 
                               approval_date = NOW() 
                           WHERE id = ?"; 
        
        $stmt2 = $this->db->prepare($sqlScholarship);
        $stmt2->execute([$userID, $scholarship_uuid]);

        // If both queries succeed, commit the changes
        return $this->db->commit();

    } catch (\Exception $e) {
        // If anything goes wrong, undo everything
        $this->db->rollBack();
        // You can log $e->getMessage() here for debugging on your Ubuntu server
        return false;
    }
}
public function getDocumentByEmpId(string $uuid) {
    // Note: If emp_id is a UUID string, ensure the column is INDEXED 
    // in MariaDB for performance with 780k+ rows.
    $sql = "SELECT 
                d.id,
                d.owner_type,
                d.file_url, 
                d.created_at,
                d.entity_type
            FROM employee_documents d
            WHERE d.emp_id = ? 
            AND is_deleted = 0
            ORDER BY d.created_at DESC";

    $stmt = $this->db->prepare($sql);
    // PDO handles the string escaping for the UUID automatically
    $stmt->execute([$uuid]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
}

public function getScholarshipDetailsById(string $recordId) {
    $sql = "SELECT 
                s.*, 
                s.id AS record_id,
                d.file_url, 
                d.entity_type,
                s.agreement_date
            FROM employee_scholarships s
            INNER JOIN employee_documents d ON s.id = d.owner_id AND d.owner_type = 'SCHOLARSHIP'
            WHERE s.id = ? 
            AND d.entity_type = 'የት/ት ውል'
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$recordId]);
    
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function updateScholarship($scholarshipData) {
    try {
        $this->db->beginTransaction();

        // 1. Update employee_scholarships table
        $sql1 = "UPDATE employee_scholarships 
                 SET scholarship_type = ?, 
                     agreement_date = ?, 
                     scholarship_duration_years = ?
                 WHERE id = ? AND emp_id = ?";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $scholarshipData['scholarship_type'],
            $scholarshipData['agreement_date'],
            $scholarshipData['duration'],
            $scholarshipData['record_id'],
            $scholarshipData['employee_id']
        ]);

        // 2. If there's a new file, update the document
        if (!empty($scholarshipData['file_url'])) {
            // Get existing document_id
            $sqlDoc = "SELECT d.id FROM employee_documents d
                       INNER JOIN employee_scholarships s ON d.owner_id = s.id
                       WHERE s.id = ? AND d.entity_type = 'የት/ት ውል'";
            $stmtDoc = $this->db->prepare($sqlDoc);
            $stmtDoc->execute([$scholarshipData['record_id']]);
            $existingDoc = $stmtDoc->fetch(\PDO::FETCH_ASSOC);

            if ($existingDoc) {
                // Update existing document
                $sql2 = "UPDATE employee_documents SET file_url = ?, updated_at = NOW() WHERE id = ?";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $scholarshipData['file_url'],
                    $existingDoc['id']
                ]);
            } 
        }

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}

public function findById(string $id) {
    $sql = "SELECT * FROM employee_scholarships WHERE id = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function deleteRecord(string $id, string $userId, string $reason, string $deletionSource): array
{
    try {
        $this->db->beginTransaction();

        $oldRecord = $this->findById($id);
        if (!$oldRecord) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'አልተገኘም።'];
        }

        // ✅ Get employee UUID from oldRecord
        $empId = $oldRecord['emp_id'] ?? null; // ← adjust key to match your column name

        if (!$empId) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'የሰራተኛ መለያ አልተገኘም።'];
        }

        // Delete scholarship
        $stmt = $this->db->prepare(
            "UPDATE employee_scholarships SET
                is_deleted      = 1,
                deletion_source = ?,
                deletion_reason = ?,
                deleted_by      = ?,
                deleted_at      = NOW()
             WHERE id         = ?
             AND   is_deleted = 0"
        );
        $stmt->execute([$deletionSource, $reason, $userId, $id]);

        if ($stmt->rowCount() === 0) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'መዝገቡ አልተሰረዘም። እባክዎ በድጋሚ ይሞክሩ።'];
        }

        // Delete attached documents
        $stmt = $this->db->prepare(
            "UPDATE employee_documents SET
                is_deleted      = 1
             WHERE owner_id   = ?
             AND   is_deleted = 0"
        );
        $stmt->execute([$id]);
        $deletedDocumentCount = $stmt->rowCount();

        // ✅ Update employee status back to Active
        $stmt = $this->db->prepare(
            "UPDATE employees_table SET
                status      = 'Active',
                updated_by  = ?
             WHERE uuid        = ?"
        );
        $stmt->execute([$userId, $empId]);

        $this->db->commit();

        return [
            'status'               => 'success',
            'deleted_type'         => 'soft',
            'message'              => 'የት/ት እድል ሙሉ በሙሉ ተሰርዟል።',
            'oldRecord'            => $oldRecord,
            'deletedDocumentCount' => $deletedDocumentCount,
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log('ScholarshipModel::deleteRecord - ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።'];
    }
}
public function getStudyLeavesByEmployeeId(string $empId): array
{
    $sql = "SELECT 
                agreement_date  AS start_date,
                end_date
            FROM employee_scholarships
            WHERE emp_id    = :emp_id
              AND is_deleted = 0";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':emp_id' => $empId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
