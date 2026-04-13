<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
class ScholarshipModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function onLeaveEmployees($organizationId, $branchId) {
        $sql = "
            SELECT 
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
              AND  e.status = 'On Leave'
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
        $sql = "SELECT uuid, first_name, father_name, g_father_name, employee_id, employee_image 
                FROM employees_table 
                WHERE branch_id = ? AND status = 'Active'
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
        $sql2 = "INSERT INTO employee_documents (id, emp_id, file_url, registered_by) 
                 VALUES (?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $documentData['id'],               // Manual Document ID
            $scholarshipData['employee_id'],    // Numeric Employee ID
            $documentData['file_url'],
            $scholarshipData['registered_by']
        ]);

        // 3. Create the assignment using your manual bridge IDs
        $sql3 = "INSERT INTO document_assignments (id, document_id, entity_id, entity_type) 
                 VALUES (?, ?, ?, ?)";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([
            $documentData['doc_id'],           // Manual Assignment ID
            $documentData['id'],               // Reference to Document ID above
            $scholarshipData['id'],            // Reference to Scholarship ID above
            'የት/ት ውል'
        ]);

        // 4. Update Employee Status using the UUID from the controller
        // Note: $scholarshipData['uuid'] must be passed from your controller
        $sql4 = "UPDATE employees_table SET status = 'On Leave Pending' WHERE uuid = ?";
        $stmt4 = $this->db->prepare($sql4);
        $stmt4->execute([$scholarshipData['employee_id']]);

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function countPendingScholarshipEmployees($organizationId, $branchId) {
    $sql = "
        SELECT COUNT(*) as total
        FROM employees_table 
        WHERE organization_id = ?
          AND branch_id = ? 
          AND status = 'On Leave Pending'
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId]);
    
    // fetchColumn() በቀጥታ ቁጥሩን (total) ይመልስልሃል
    return $stmt->fetchColumn();
}
 public function onLeavePendingEmployees($organizationId, $branchId) {
        $sql = "
            SELECT 
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
              AND  e.status = 'On Leave Pending'
            ORDER BY e.rdate DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
   public function getScholarshipDetails($empId) {
    // Note: I removed s.agreement_number to prevent the "Column not found" error
    // until you manually add it to your database.
   $sql = "SELECT 
                s.*, 
                d.file_url, 
                da.entity_type
            FROM employee_scholarships s
            INNER JOIN document_assignments da ON s.id = da.entity_id
            INNER JOIN employee_documents d ON da.document_id = d.id
            WHERE s.emp_id = ? 
            AND s.status = 'pending' AND da.entity_type = 'የት/ት ውል'
            ORDER BY s.created_at DESC LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$empId]);
    
    // Use fetchAll if an employee can have multiple scholarship records
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}
   public function approveOnLeaveEmployee($uuid, $scholarship_uuid, $userID): bool {
    try {
        // Start the transaction
        $this->db->beginTransaction();

        // 1. Update the main employee table
        $sqlEmployee = "UPDATE employees_table SET status = 'On Leave' WHERE uuid = ?";
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
public function getDocumentByEmpId($uuid) {
    // Note: If emp_id is a UUID string, ensure the column is INDEXED 
    // in MariaDB for performance with 780k+ rows.
    $sql = "SELECT 
                d.file_url, 
                d.created_at,
                s.scholarship_type,
                da.entity_type
            FROM employee_documents d
            LEFT JOIN document_assignments da ON d.id = da.document_id
            LEFT JOIN employee_scholarships s ON da.entity_id = s.id 
            WHERE d.emp_id = ? 
            ORDER BY d.created_at DESC";

    $stmt = $this->db->prepare($sql);
    // PDO handles the string escaping for the UUID automatically
    $stmt->execute([$uuid]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
}
}