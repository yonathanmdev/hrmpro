<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
use PDO;
use stdClass;

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
            AND (e.status = 'Active' OR e.status = 'On Leave' OR e.status = 'On Leave Pending')
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
        $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type, file_url) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $documentData['id'],               // Manual Document ID
            $debtSuspensionData['employee_id'],    // Numeric Employee ID
            'DEBT_SUSPENSION',
            $debtSuspensionData['id'],
            $debtSuspensionData['debt_suspension_type'],
            $documentData['file_url']
        ]);

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function countPending(string $organizationId, string $branchId) {
    $sql = "SELECT COUNT(*) 
            FROM employees_table e
            INNER JOIN debt_suspension ds ON e.uuid = ds.emp_id
            WHERE e.branch_id = ? 
              AND e.organization_id = ?
              AND ds.is_deleted = 0 
              AND ds.status = 'pending'";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId, $organizationId]);

    // fetchColumn(0) grabs the first column of the first row
    return (int) $stmt->fetchColumn(); 
}
public function getActiveSuspensions(string $organizationId, string$branchId, string $status) {
    $sql = "SELECT 
                e.uuid, 
                e.employee_id, 
                e.first_name, 
                e.father_name, 
                e.g_father_name,
                e.birth_date,
                e.rdate,
                jp.job_name, 
                jp.registered_by,
                ds.id as record_id,
                ds.status as debt_status
            FROM employees_table e
            INNER JOIN debt_suspension ds ON e.uuid = ds.emp_id
            INNER JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ? 
              AND e.branch_id = ? 
              AND ds.status = ?
              AND ds.is_deleted = 0
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
                d.entity_type,
                ds.start_date
            FROM debt_suspension ds
            INNER JOIN employee_documents d ON ds.id = d.owner_id
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
                     updated_at = NOW(),
                     updated_by = ?
                 WHERE id = ? AND emp_id = ?";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $debtSuspensionData['debt_suspension_type'],
            $debtSuspensionData['reason'],
            $debtSuspensionData['start_date'],
            $debtSuspensionData['registered_by'],
            $debtSuspensionData['record_id'],
            $debtSuspensionData['employee_id']
        ]);

        // 2. If there's a new file, update the document
        if (!empty($debtSuspensionData['file_url'])) {
            // Get existing document_id
            $sqlDoc = "SELECT d.id FROM employee_documents d
                       INNER JOIN debt_suspension ds ON d.owner_id = ds.id
                       WHERE ds.id = ?";
            $stmtDoc = $this->db->prepare($sqlDoc);
            $stmtDoc->execute([$debtSuspensionData['record_id']]);
            $existingDoc = $stmtDoc->fetch(\PDO::FETCH_ASSOC);

            if ($existingDoc) {
                // Update existing document
                $sql2 = "UPDATE employee_documents SET file_url = ?,entity_type = ? WHERE id = ?";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $debtSuspensionData['file_url'],
                    $debtSuspensionData['debt_suspension_type'],
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
            $clearingDebtSuspensionData['registered_by'],
            $clearingDebtSuspensionData['cleared_date'],
            $clearingDebtSuspensionData['record_id'],
            $clearingDebtSuspensionData['employee_id']
        ]);

        // 2. Insert into employee_documents using the manual ID provided
        $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type, file_url) 
                 VALUES (?, ?, 'DEBT_SUSPENSION', ?, 'እዳ/እገዳ የተነሳበት', ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $clearingDebtSuspensionData['id'],               // Manual Document ID
            $clearingDebtSuspensionData['employee_id'],    // Numeric Employee ID
            $clearingDebtSuspensionData['record_id'],
            $clearingDebtSuspensionData['file_url']
        ]);

        
        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}

public function findById($id) {
    $sql = "SELECT * FROM debt_suspension WHERE id = ?";
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
            return ['status' => 'error', 'message' => 'ተጠቃሚው አልተገኘም።'];
        }


        // update main record
        $stmt = $this->db->prepare("UPDATE debt_suspension SET deletion_source = ?, deleted_by = ?, deletion_reason = ?, is_deleted = 1, deleted_at = NOW() WHERE id = ? AND is_deleted = 0");
             $stmt->execute([$deletionSource, $userId, $reason,$id]);

        if ($stmt->rowCount() === 0) {
            // Record was already deleted or status changed between the check and delete
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'መዝገቡ አልተሰረዘም። እባክዎ በድጋሚ ይሞክሩ።'];
        }

        // Delete attached documents and capture count
        $stmt = $this->db->prepare("UPDATE employee_documents SET is_deleted = 1 WHERE owner_id = ? AND is_deleted = 0");
        $stmt->execute([$id]);
        $deletedDocumentCount = $stmt->rowCount();

        $this->db->commit();

        return [
            'status'               => 'success',
            'deleted_type'         => 'soft',
            'message'              => 'እዳ/እገዳው ሙሉ በሙሉ ተሰርዟል።',
            'oldRecord'            => $oldRecord,
            'deletedDocumentCount' => $deletedDocumentCount,
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log('DebtSuspensionModel::deletePendingRecord - ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።'];
    }
}


}