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

        // 1. ወደ employee_scholarships ቴብል ማስገባት
        $sql1 = "INSERT INTO employee_scholarships (
                    id, emp_id, agreement_date, scholarship_type, 
                    scholarship_duration_years, registered_by
                ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $scholarshipData['id'],
            $scholarshipData['employee_id'],
            $scholarshipData['agreement_date'],
            $scholarshipData['scholarship_type'],
            $scholarshipData['duration'],
            $scholarshipData['registered_by']
        ]);

        // 2. ወደ employee_documents ቴብል ማስገባት (ከ scholarship_id ጋር በማያያዝ)
        $sql2 = "INSERT INTO employee_documents (id, scholarship_id, emp_id, document_type, file_url, registered_by) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $documentData['id'],
            $scholarshipData['id'], // ግንኙነቱን ለመፍጠር
            $scholarshipData['employee_id'],
            'Scholarship Agreement',
            $documentData['file_url'],
            $scholarshipData['registered_by']
        ]);

        // 3. የሰራተኛውን ስታተስ ወደ 'On Leave' ማዘመን (Update Employee Status)
        // ማሳሰቢያ፡ የቴብሉ ስም 'employees_table' እና መለያው 'uuid' መሆኑን አረጋግጥ
        $sql3 = "UPDATE employees_table SET status = 'On Leave Pending' WHERE uuid = ?";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([$scholarshipData['employee_id']]);

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        // ለ Controller ግልጽ የሆነ የስህተት መልዕክት ይልካል
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
}