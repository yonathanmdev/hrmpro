<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
class DebtSuspension {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

public function autoSearch($term, $branchId) {
    $cleanTerm = AmharicNormalizer::normalize($term);

    $sql = "SELECT 
                e.uuid, 
                e.first_name, 
                e.father_name, 
                e.g_father_name, 
                e.employee_id, 
                e.employee_image 
            FROM employees_table e
            WHERE e.branch_id = ? 
            AND (e.status = 'Active' OR e.status = 'On Leave')
            AND e.full_name_normalized LIKE ? 
            -- Exclude if there is any record that isn't 'cleared'
            AND NOT EXISTS (
                SELECT 1 
                FROM debt_suspension ds 
                WHERE ds.emp_id = e.uuid 
                AND ds.status != 'cleared'
            )
            LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId, "%$cleanTerm%"]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
public function saveDebtSuspensionWithDocument($debtSuspensionData, $documentData) {
    try {
        $this->db->beginTransaction();

        // 1. Insert into employee_scholarships using the manual ID provided
        $sql1 = "INSERT INTO debt_suspension(id, emp_id, start_date, registered_by)
        VALUES (?, ?, ?, ?)";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $debtSuspensionData['id'],            // Manual Scholarship ID
            $debtSuspensionData['employee_id'],    // Numeric Employee ID
            $debtSuspensionData['start_date'],
            $debtSuspensionData['registered_by']
        ]);

        // 2. Insert into employee_documents using the manual ID provided
        $sql2 = "INSERT INTO employee_documents (id, emp_id, file_url, registered_by) 
                 VALUES (?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $documentData['id'],               // Manual Document ID
            $debtSuspensionData['employee_id'],    // Numeric Employee ID
            $documentData['file_url'],
            $debtSuspensionData['registered_by']
        ]);

        // 3. Create the assignment using your manual bridge IDs
        $sql3 = "INSERT INTO document_assignments (id, document_id, entity_id, entity_type) 
                 VALUES (?, ?, ?, ?)";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([
            $documentData['doc_id'],           // Manual Assignment ID
            $documentData['id'],               // Reference to Document ID above
            $debtSuspensionData['id'],            // Reference to Scholarship ID above
            $debtSuspensionData['debt_suspension_type'],
        ]);
        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
}