<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
use PDO;
class WarrantyModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
public function getWarranties(string $organizationId, string $branchId, $status) {
    $sql = "SELECT 
                e.uuid, 
                e.employee_id, 
                e.first_name, 
                e.father_name, 
                e.g_father_name,
                jp.job_name, 
                jp.registered_by,
                w.id as record_id,
                w.to_whom,
                w.the_person,
                w.warranty_type,
                w.created_at as warranty_created_at,
                w.status as warranty_status
            FROM employees_table e
            INNER JOIN warranty w ON e.uuid = w.emp_id 
            INNER JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ? 
              AND e.branch_id = ? 
              AND w.status = ?
              AND w.is_deleted = 0
            ORDER BY w.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId, $status]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            AND (e.status = 'Active' OR e.status = 'Study Leave' OR e.status = 'Study Leave Pending')
            AND e.full_name_normalized LIKE ?
            AND NOT EXISTS (
                SELECT 1 
                FROM debt_suspension d
                WHERE d.emp_id = e.uuid 
                AND d.status != 'cleared'
                AND d.is_deleted = 0
            )
            LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId, "%$cleanTerm%"]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function saveWarrantyData($warrantyData) {
    try {
        $this->db->beginTransaction();

        // 1. Insert into employee_scholarships using the manual ID provided
        $sql1 = "INSERT INTO warranty(id, emp_id, to_whom, the_person, warranty_type, registered_by)
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $warrantyData['id'],            // Manual Scholarship ID
            $warrantyData['emp_id'],    // Numeric Employee ID
            $warrantyData['to_whom'],
            $warrantyData['the_person'],
            $warrantyData['warranty_type'],
            $warrantyData['registered_by']
        ]);

        // 2. Insert into employee_documents using the manual ID provided
        $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type) 
                 VALUES (?, ?, ?, ?, 'የዋስትና ደብዳቤ')";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $warrantyData['document_id'],               // Manual Document ID
            $warrantyData['emp_id'],    // Numeric Employee ID
            'WARRANTY',
            $warrantyData['id']
        ]);

        $this->db->commit();
        return true;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}

public function getPendingWarrantyDetails($recordId) {
     $sql = "SELECT id, emp_id, to_whom, the_person, warranty_type, registered_by, created_at
            FROM warranty 
            WHERE id = ? LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$recordId]);
    
    // Use fetchAll if an employee can have multiple scholarship records
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function attachWarrantyFile($uuid, $recordId, $userId, $fileurl): array {
    // ── Snapshot before update (for audit log) ────────────────
    $snapshot = $this->db->prepare(
        "SELECT w.*, ed.file_url AS current_file_url
         FROM warranty w
         LEFT JOIN employee_documents ed 
            ON ed.owner_id = w.id 
           AND ed.owner_type = 'WARRANTY' 
           AND ed.emp_id = w.emp_id
         WHERE w.id = ? AND w.emp_id = ? AND w.status = 'pending'
         LIMIT 1"
    );
    $snapshot->execute([$recordId, $uuid]);
    $oldRecord = $snapshot->fetch(\PDO::FETCH_ASSOC);

    if (!$oldRecord) {
        return [
            'status'  => 'error',
            'message' => 'Record not found or already approved.',
        ];
    }

    // ── Transaction ───────────────────────────────────────────
    try {
        $this->db->beginTransaction();

        // 1. Approve warranty
        $approveStmt = $this->db->prepare(
            "UPDATE warranty 
             SET status = 'active', updated_by = ?
             WHERE id = ? AND emp_id = ? AND status = 'pending'"
        );
        $approveStmt->execute([$userId, $recordId, $uuid]);

        if ($approveStmt->rowCount() === 0) {
            throw new \Exception("Warranty update affected 0 rows — may already be approved.");
        }

        // 2. Update document file URL
        $docStmt = $this->db->prepare(
            "UPDATE employee_documents 
             SET file_url = ?
             WHERE owner_type = 'WARRANTY' AND owner_id = ? AND emp_id = ?"
        );
        $docStmt->execute([$fileurl, $recordId, $uuid]);

        $this->db->commit();

        return [
            'status'    => 'success',
            'oldRecord' => $oldRecord,
            'newRecord' => [
                'status'     => 'active',
                'updated_by' => $userId,
                'file_url'   => $fileurl,
            ],
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();

        return [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ];
    }
}

public function getWarrantyDetails($recordId): array|false {
    $sql = "SELECT 
                -- ── Warranty columns ──────────────────────────
                w.id,
                w.emp_id,
                w.to_whom,
                w.the_person,
                w.warranty_type,
                w.status,
                w.registered_by,
                w.created_at,
                w.cleared_by,
                w.cleared_at,
                w.updated_by,
                w.updated_at,
                w.deleted_by,
                w.deleted_at,
                w.deletion_source,
                w.is_deleted,

                -- ── Document columns ──────────────────────────
                ed.id           AS document_id,
                ed.emp_id       AS document_emp_id,
                ed.owner_type,
                ed.owner_id,
                ed.entity_type,
                ed.file_url

            FROM warranty w
            LEFT JOIN employee_documents ed
                ON  ed.owner_id   = w.id
                AND ed.owner_type = 'WARRANTY'
                AND ed.emp_id     = w.emp_id
            WHERE w.id        = ?
              AND w.is_deleted = 0
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$recordId]);

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function releasingWarranty($docId, $uuid, $recordId, $userId, $fileurl): array {
    // ── Snapshot before update (for audit log) ────────────────
    $snapshot = $this->db->prepare(
        "SELECT w.id, w.emp_id, w.to_whom, w.the_person, w.warranty_type,
                w.status, w.registered_by, w.created_at,
                ed.file_url AS current_file_url
         FROM warranty w
         LEFT JOIN employee_documents ed 
            ON  ed.owner_id   = w.id 
            AND ed.owner_type = 'WARRANTY' 
            AND ed.emp_id     = w.emp_id
         WHERE w.id = ? AND w.emp_id = ? AND w.status = 'active'
         LIMIT 1"
    );
    $snapshot->execute([$recordId, $uuid]);
    $oldRecord = $snapshot->fetch(\PDO::FETCH_ASSOC);

    if (!$oldRecord) {
        return [
            'status'  => 'error',
            'message' => 'Record not found or not in active status.',
        ];
    }

    try {
        $this->db->beginTransaction();

        // ── 1. Clear warranty status ──────────────────────────
        $clearStmt = $this->db->prepare(
            "UPDATE warranty 
             SET status     = 'cleared',
                 cleared_by = ?,
                 cleared_at = NOW(),
                 updated_by = ?,
                 updated_at = NOW()
             WHERE id = ? AND emp_id = ? AND status = 'active'"
        );
        $clearStmt->execute([$userId, $userId, $recordId, $uuid]);

        if ($clearStmt->rowCount() === 0) {
            throw new \Exception("Warranty update affected 0 rows — may already be cleared.");
        }

        // ── 2. INSERT removal document (not update) ───────────
        $docStmt = $this->db->prepare(
            "INSERT INTO employee_documents 
                (id, emp_id, owner_type, owner_id, entity_type, file_url)
             VALUES 
                (?, ?, 'WARRANTY', ?, 'ዋስትና የተነሳበት', ?)"
        );
        $docStmt->execute([$docId, $uuid, $recordId, $fileurl]);

        $this->db->commit();

        return [
            'status'    => 'success',
            'oldRecord' => $oldRecord,
            'newRecord' => [
                'status'     => 'cleared',
                'cleared_by' => $userId,
                'cleared_at' => date('Y-m-d H:i:s'),
                'file_url'   => $fileurl,
                'entity_type' => 'REMOVAL',
            ],
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();

        return [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ];
    }
}
}