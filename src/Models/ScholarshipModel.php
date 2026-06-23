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

    /**
     * Check if scholarship overlaps with existing scholarships or work experiences
     */
   public function checkOverlaps($employeeId, $agreementDate, $endDate = null, $excludeId = null) {
    // Two ranges [startA, endA] and [startB, endB] overlap iff:
    //   startA <= (endB or +infinity) AND startB <= (endA or +infinity)
    // NULL end_date is treated as "ongoing" (open-ended), so it never
    // blocks on the upper bound. This single condition replaces the
    // three separate OR-branches that used to exist, since they were
    // all special cases of the same rule.

    // 1. Check for overlapping scholarships
    $scholarshipSql = "
        SELECT 'scholarship' as type, agreement_date as start_date, end_date
        FROM employee_scholarships
        WHERE emp_id = :employee_id
        AND is_deleted = 0
        AND agreement_date <= COALESCE(:end_date, '9999-12-31')
        AND COALESCE(end_date, '9999-12-31') >= :agreement_date
    ";

    $params = [
        ':employee_id'    => $employeeId,
        ':agreement_date' => $agreementDate,
        ':end_date'       => $endDate,
    ];

    if ($excludeId !== null) {
        $scholarshipSql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($scholarshipSql);
    $stmt->execute($params);
    $scholarshipOverlaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($scholarshipOverlaps)) {
        return ['has_overlap' => true, 'type' => 'scholarship', 'overlaps' => $scholarshipOverlaps];
    }

    // 2. Check for overlapping work experiences
    $experienceSql = "
        SELECT 'experience' as type, start_date, end_date
        FROM employee_experiences
        WHERE employee_uuid = :employee_id
        AND is_deleted = 0
        AND start_date <= COALESCE(:end_date, '9999-12-31')
        AND COALESCE(end_date, '9999-12-31') >= :agreement_date
    ";

    $expParams = [
        ':employee_id'    => $employeeId,
        ':agreement_date' => $agreementDate,
        ':end_date'       => $endDate,
    ];

    if ($excludeId !== null) {
        $experienceSql .= " AND id != :exclude_id";
        $expParams[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($experienceSql);
    $stmt->execute($expParams);
    $experienceOverlaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($experienceOverlaps)) {
        return ['has_overlap' => true, 'type' => 'experience', 'overlaps' => $experienceOverlaps];
    }

    return ['has_overlap' => false];
}
    public function saveScholarshipWithDocument($scholarshipData, $documentData = null) {
        try {
            $this->db->beginTransaction();

            // Check for overlaps before saving
            $employeeId = $scholarshipData['employee_id'];
            $agreementDate = $scholarshipData['agreement_date'];
            $endDate = isset($scholarshipData['end_date']) ? $scholarshipData['end_date'] : null;
            $recordId = isset($scholarshipData['record_id']) ? $scholarshipData['record_id'] : null;
            
            $overlapCheck = $this->checkOverlaps($employeeId, $agreementDate, $endDate);
            
            if ($overlapCheck['has_overlap']) {
                $message = $overlapCheck['type'] === 'scholarship' 
                    ? "ሰራተኛው በዚህ ጊዜ ውስጥ ሌላ የትምህርት እድል አለው።" 
                    : "ሰራተኛው በዚህ ጊዜ ውስጥ የስራ ልምድ አለው። በትምህርት እድል እና በስራ ልምድ ጊዜ መደራረብ አይቻልም።";
                throw new \Exception($message);
            }

 // 1. Insert into employee_scholarships
$isHistorical = isset($scholarshipData['is_historical']) ? $scholarshipData['is_historical'] : 0;
$status = ($isHistorical == 0) ? 'pending' : 'completed';

$sql1 = "INSERT INTO employee_scholarships (
            id, emp_id, agreement_date, scholarship_type, 
            scholarship_duration_years, registered_by, status";

$optionalFields = [];
$optionalValues = [];

if (isset($scholarshipData['end_date']) && !empty($scholarshipData['end_date'])) {
    $optionalFields[] = 'end_date';
    $optionalValues[] = $scholarshipData['end_date'];
}

if (isset($scholarshipData['is_historical'])) {
    $optionalFields[] = 'is_historical';
    $optionalValues[] = $scholarshipData['is_historical'];
}

if (!empty($optionalFields)) {
    $sql1 .= ", " . implode(', ', $optionalFields);
}
$sql1 .= ") VALUES (?, ?, ?, ?, ?, ?, ?";

if (!empty($optionalValues)) {
    $sql1 .= ", " . rtrim(str_repeat('?, ', count($optionalValues)), ', ');
}
$sql1 .= ")";

$values = [
    $scholarshipData['id'],
    $scholarshipData['employee_id'],
    $scholarshipData['agreement_date'],
    $scholarshipData['scholarship_type'],
    $scholarshipData['duration'],
    $scholarshipData['registered_by'],
    $status
];

$values = array_merge($values, $optionalValues);

            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute($values);

            // 2. Insert into employee_documents ONLY if document data is provided
            if ($documentData !== null && !empty($documentData)) {
                $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type, file_url) 
                         VALUES (?, ?, 'SCHOLARSHIP', ?, 'የት/ት ውል', ?)";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $documentData['id'],
                    $scholarshipData['employee_id'],
                    $scholarshipData['id'],
                    $documentData['file_url']
                ]);
            }
            
            // 3. Update Employee Status ONLY for active scholarships (NOT historical)
            $isHistorical = isset($scholarshipData['is_historical']) ? $scholarshipData['is_historical'] : 0;
            
            if ($isHistorical == 0) {
                $sql4 = "UPDATE employees_table SET status = 'Study Leave Pending' WHERE uuid = ?";
                $stmt4 = $this->db->prepare($sql4);
                $stmt4->execute([$scholarshipData['employee_id']]);
            }

            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Error: " . $e->getMessage()); 
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
                  AND e.is_deleted != 1
                  AND e.status = 'Study Leave'
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
                s.id AS record_id,
                s.emp_id,
                s.is_historical,
                s.agreement_date,
                s.end_date,
                s.scholarship_type,
                s.scholarship_duration_years, 
                d.file_url, 
                d.entity_type
            FROM employee_scholarships s
            LEFT JOIN employee_documents d ON s.id = d.owner_id AND d.owner_type = 'SCHOLARSHIP' AND d.is_deleted = 0
            WHERE s.id = ? 
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

public function updateScholarshipReturn($updateData) {
    try {
        // --- OVERLAP CHECK ---
        // Pass the record_id to exclude the current record from the check
        $overlapCheck = $this->checkOverlaps(
            $updateData['emp_id'],
            $updateData['agreement_date'], // Assuming this is needed for your overlap logic
            $updateData['return_date'],    // Using this as the end date
            $updateData['record_id']
        );

        if ($overlapCheck['has_overlap']) {
            $errorMessage = $overlapCheck['type'] === 'scholarship'
                ? 'የትምህርት ውል ቀን ከሌላ የትምህርት ውል ጋር ተደራርቧል'
                : 'የትምህርት ውል ቀን ከሥራ ልምድ ጋር ተደራርቧል';
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }

        // --- TRANSACTION START ---
        $this->db->beginTransaction();

        // 1. Update the scholarship record
        $sql1 = "UPDATE employee_scholarships 
                 SET end_date = ?, 
                     status = 'completed'
                 WHERE id = ? AND emp_id = ?";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([
            $updateData['return_date'],
            $updateData['record_id'],
            $updateData['emp_id']
        ]);

        // 2. Insert the new document
        $sql2 = "INSERT INTO employee_documents (id, emp_id, owner_type, owner_id, entity_type, file_url, registered_by) 
                 VALUES (?, ?, 'SCHOLARSHIP', ?, 'የት/ት ማስረጃ', ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            $updateData['id'], 
            $updateData['emp_id'], 
            $updateData['record_id'], 
            $updateData['file_url'], 
            $updateData['registered_by']
        ]);

        // 3. Update Employee Status
        $sql3 = "UPDATE employees_table SET status = 'Active' WHERE uuid = ?";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([$updateData['emp_id']]);

        $this->db->commit();
        
        return ['success' => true];
        
    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        throw new \Exception("Model Error: " . $e->getMessage()); 
    }
}
public function updateScholarshipReturnee($scholarshipData) {
    try {
        $this->db->beginTransaction();

        // Get current record data
        $currentRecord = $this->getScholarshipDetailsById($scholarshipData['record_id']);

        if (!$currentRecord) {
            throw new \Exception("Record not found");
        }

        $isHistorical = $currentRecord['is_historical'] ?? 0;
        $updateFileOnly = !empty($scholarshipData['update_file_only']) && $scholarshipData['update_file_only'] == '1';

        // Debug logging
        if (!$updateFileOnly) {
            // Prepare data based on record type
            $updateData = [
                'id' => $scholarshipData['record_id'],
                // Use emp_id from the current record, not from the form
                'emp_id' => $currentRecord['emp_id'],
                'end_date' => $scholarshipData['end_date'],
            ];

            if ($isHistorical == 1) {
                // Historical: can update everything
                $updateData['scholarship_type'] = $scholarshipData['scholarship_type'] ?? $currentRecord['scholarship_type'];
                $updateData['agreement_date'] = $scholarshipData['agreement_date'] ?? $currentRecord['agreement_date'];
                
                // Get duration from either key
                $duration = $scholarshipData['duration'] ?? $scholarshipData['scholarship_duration_years'] ?? $currentRecord['scholarship_duration_years'] ?? null;
                $updateData['scholarship_duration_years'] = $duration;
                
                if (empty($updateData['end_date'])) {
                    $updateData['end_date'] = $currentRecord['end_date'];
                }
                
              } else {
                // Non-historical: only end_date can change
                $updateData['scholarship_type'] = $currentRecord['scholarship_type'];
                $updateData['agreement_date'] = $currentRecord['agreement_date'];
                $updateData['scholarship_duration_years'] = $currentRecord['scholarship_duration_years'] ?? null;

                if (empty($updateData['end_date'])) {
                    $updateData['end_date'] = $currentRecord['end_date'];
                }
                
                error_log("Non-historical update data: " . print_r($updateData, true));
            }

            // Check for overlaps - PASS THE CURRENT RECORD ID TO EXCLUDE IT
            $overlapCheck = $this->checkOverlaps(
                $scholarshipData['employee_id'],
                $updateData['agreement_date'],
                $updateData['end_date'],
                $scholarshipData['record_id']
            );

            if ($overlapCheck['has_overlap']) {
                $type = $overlapCheck['type'];
                $errorMessage = $type === 'scholarship'
                    ? 'የትምህርት ውል ቀን ከሌላ የትምህርት ውል ጋር ተደራርቧል'
                    : 'የትምህርት ውል ቀን ከሥራ ልምድ ጋር ተደራርቧል';

                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }

            // 1. Update employee_scholarships table - ONLY USING ID (not emp_id)
            $sql1 = "UPDATE employee_scholarships
                     SET scholarship_type = ?,
                         agreement_date = ?,
                         end_date = ?,
                         scholarship_duration_years = ?
                     WHERE id = ?";
            $stmt1 = $this->db->prepare($sql1);
            
            error_log("SQL: " . $sql1);
            error_log("Params: " . print_r([
                'scholarship_type' => $updateData['scholarship_type'],
                'agreement_date' => $updateData['agreement_date'],
                'end_date' => $updateData['end_date'],
                'scholarship_duration_years' => $updateData['scholarship_duration_years'],
                'id' => $updateData['id']
            ], true));
            
            $stmt1->execute([
                $updateData['scholarship_type'],
                $updateData['agreement_date'],
                $updateData['end_date'],
                $updateData['scholarship_duration_years'],
                $updateData['id']
            ]);
            
            $affectedRows = $stmt1->rowCount();
            error_log("Update executed. Affected rows: " . $affectedRows);
        }

        // 2. If there's a new file, update the existing document row
        if (!empty($scholarshipData['file_url'])) {
            $checkSql = "SELECT id FROM employee_documents
                         WHERE owner_type = 'SCHOLARSHIP' AND owner_id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$scholarshipData['record_id']]);
            $existingDoc = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingDoc) {
                $sql2 = "UPDATE employee_documents
                         SET file_url = ?
                         WHERE owner_type = 'SCHOLARSHIP' AND owner_id = ?";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    $scholarshipData['file_url'],
                    $scholarshipData['record_id'],
                ]);
                error_log("Document updated with file: " . $scholarshipData['file_url']);
            } else {
                error_log("No existing document found for scholarship: " . $scholarshipData['record_id']);
            }
        }

        $this->db->commit();
        return [
            'success' => true,
            'message' => 'Scholarship updated successfully'
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log("Model Error in updateScholarshipReturnee: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
public function findById(string $id) {
    $sql = "SELECT * FROM employee_scholarships WHERE id = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
/**
 * Get completed scholarships (returnees) - both historical and completed active scholarships
 */
public function getCompletedScholarships(string $organizationId, string $branchId) {
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
                e.status as employee_status,
                jp.job_name,
                jp.status as job_status,
                s.id as record_id,
                s.scholarship_type,
                s.agreement_date,
                s.end_date,
                s.is_historical,
                s.status as scholarship_status,
                s.registered_by,
                s.created_at as scholarship_created_date,
                s.approval_date,
                s.approved_by,
                d.file_url
            FROM employees_table e
            INNER JOIN job_property jp ON e.job_property_id = jp.id
            INNER JOIN employee_scholarships s ON e.uuid = s.emp_id 
            LEFT JOIN employee_documents d ON s.id = d.owner_id AND d.owner_type = 'SCHOLARSHIP' AND d.is_deleted = 0
            WHERE e.organization_id = ?
              AND e.branch_id = ?
              AND s.is_deleted = 0
              AND (
                  -- Completed scholarships (with end date)
                  s.end_date IS NOT NULL 
                  OR 
                  -- Historical scholarships
                  s.is_historical = 1
              )
            ORDER BY s.end_date DESC, s.agreement_date DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
