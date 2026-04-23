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
            -- AND NOT EXISTS ( SELECT 1 FROM debt_suspension ds  WHERE ds.emp_id = e.uuid AND ds.status != 'cleared')
            LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId, "%$cleanTerm%"]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function saveDebtSuspensionWithDocument($debtSuspensionData, $documentData) {
    try {
        $this->db->beginTransaction();

        // 1. Insert into employee_scholarships using the manual ID provided
        $sql1 = "INSERT INTO debt_suspension(id, emp_id, debt_suspension_type, reason, start_date, registered_by)
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $debtSuspensionData['id'],            // Manual Scholarship ID
            $debtSuspensionData['employee_id'],    // Numeric Employee ID
            $debtSuspensionData['debt_suspension_type'],
            $debtSuspensionData['reason'],
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
public function countPending($organizationId, $branchId) {
    $sql = "SELECT COUNT(*) 
            FROM employees_table e
            INNER JOIN debt_suspension ds ON e.uuid = ds.emp_id
            WHERE e.branch_id = ? 
              AND e.organization_id = ? 
              AND ds.status = 'pending'";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId, $organizationId]);

    // fetchColumn(0) grabs the first column of the first row
    return (int) $stmt->fetchColumn(); 
}
public function getActiveSuspensions($organizationId, $branchId, $status) {
    $sql = "SELECT 
                e.uuid, 
                e.employee_id, 
                e.first_name, 
                e.father_name, 
                e.g_father_name,
                e.birth_date,
                e.rdate,
                jp.job_name, 
                ds.id as record_id,
                ds.status as debt_status
            FROM employees_table e
            INNER JOIN debt_suspension ds ON e.uuid = ds.emp_id
            INNER JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ? 
              AND e.branch_id = ? 
              AND ds.status = ?
            ORDER BY ds.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId, $status]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
public function getDebtSuspensionDetails($recordId) {
     $sql = "SELECT 
                ds.*, 
                ds.id AS record_id,
                d.file_url, 
                da.entity_type,
                ds.start_date
            FROM debt_suspension ds
            INNER JOIN document_assignments da ON ds.id = da.entity_id
            INNER JOIN employee_documents d ON da.document_id = d.id
            WHERE ds.id = ? LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$recordId]);
    
    // Use fetchAll if an employee can have multiple scholarship records
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function updateDebtSuspension($debtSuspensionData) {
    try {
        $this->db->beginTransaction();

        // 1. Update debt_suspension table
        $sql1 = "UPDATE debt_suspension 
                 SET debt_suspension_type = ?, 
                     reason = ?, 
                     start_date = ?,
                     updated_at = NOW()
                 WHERE id = ? AND emp_id = ?";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $debtSuspensionData['debt_suspension_type'],
            $debtSuspensionData['reason'],
            $debtSuspensionData['start_date'],
            $debtSuspensionData['record_id'],
            $debtSuspensionData['employee_id']
        ]);

        // 2. If there's a new file, update the document
        if (!empty($debtSuspensionData['file_url'])) {
            // Get existing document_id
            $sqlDoc = "SELECT d.id FROM employee_documents d
                       INNER JOIN document_assignments da ON d.id = da.document_id
                       WHERE da.entity_id = ? AND da.entity_type = ?";
            $stmtDoc = $this->db->prepare($sqlDoc);
            $stmtDoc->execute([$debtSuspensionData['record_id'], $debtSuspensionData['debt_suspension_type']]);
            $existingDoc = $stmtDoc->fetch(\PDO::FETCH_ASSOC);

            if ($existingDoc) {
                // Update existing document
                $sql2 = "UPDATE employee_documents SET file_url = ? WHERE id = ?";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $debtSuspensionData['file_url'],
                    $existingDoc['id']
                ]);
            } else {
                // Insert new document if none exists
                $sql2 = "INSERT INTO employee_documents (id, emp_id, file_url, registered_by, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $debtSuspensionData['doc_id'],
                    $debtSuspensionData['employee_id'],
                    $debtSuspensionData['file_url'],
                    $debtSuspensionData['registered_by']
                ]);

                // Create new assignment
                $sql3 = "INSERT INTO document_assignments (id, document_id, entity_id, entity_type) 
                         VALUES (?, ?, ?, ?)";
                $stmt3 = $this->db->prepare($sql3);
                $stmt3->execute([
                    $debtSuspensionData['assignment_id'],
                    $debtSuspensionData['doc_id'],
                    $debtSuspensionData['record_id'],
                    $debtSuspensionData['debt_suspension_type']
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
 public function approveDebtSuspension($uuid, $recordId, $userId): bool {
    $sql = "UPDATE debt_suspension SET status = 'active', approved_by = ?, approval_date = NOW() WHERE id = ? AND emp_id = ? AND status = 'pending'";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$userId, $recordId, $uuid]);
}
public function saveDebtSuspensionClearingData($clearingDebtSuspensionData) {
    try {
        $this->db->beginTransaction();

        // 1. Insert into employee_scholarships using the manual ID provided
        $sql1 = "Update debt_suspension SET status = 'cleared', cleared_by = ?, cleared_at = ? WHERE id = ? AND emp_id = ? AND status = 'active'";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $clearingDebtSuspensionData['cleared_by'],
            $clearingDebtSuspensionData['cleared_at'],
            $clearingDebtSuspensionData['record_id'],
            $clearingDebtSuspensionData['employee_id']
        ]);

        // 2. Insert into employee_documents using the manual ID provided
        $sql2 = "INSERT INTO employee_documents (id, emp_id, file_url, registered_by) 
                 VALUES (?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $clearingDebtSuspensionData['id'],               // Manual Document ID
            $clearingDebtSuspensionData['employee_id'],    // Numeric Employee ID
            $clearingDebtSuspensionData['file_url'],
            $clearingDebtSuspensionData['registered_by']
        ]);

        // 3. Create the assignment using your manual bridge IDs
        $sql3 = "INSERT INTO document_assignments (id, document_id, entity_id, entity_type) 
                 VALUES (?, ?, ?, ?)";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([
            $clearingDebtSuspensionData['doc_id'],           // Manual Assignment ID
            $clearingDebtSuspensionData['id'],               // Reference to Document ID above
            $clearingDebtSuspensionData['record_id'],            // Reference to Scholarship ID above
            'የተነሳ እዳ/እገዳ መረጃ', // Entity type for clearing
        ]);
        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
}