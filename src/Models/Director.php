<?php
namespace App\Models;
use PDO;
class Director {
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
   public function create($id, $organization_id, $branch_id, $directorName, $registeredBy) {
        $sql = "INSERT INTO directors (id, organization_id, branch_id, director_name, registered_by) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);

            // በቀጥታ የተላኩትን ቫሪያብሎች በመጠቀም ማስገባት
            return $stmt->execute([
                $id,
                $organization_id,
                $branch_id,
                $directorName,
                $registeredBy

            ]);
        } catch (\PDOException $e) {
            // ስህተት ካለ ለኮንትሮለሩ እንዲያውቀው ደግመን እንወረውራለን (Throw)
            throw $e;
        }
    }
    public function getAllDirectors($myBranchId) {
        $sql = "SELECT * FROM directors WHERE branch_id = ? AND status = 'active' ORDER BY director_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$myBranchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
public function getDirectorById($id) {
        $sql = "SELECT * FROM directors WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function update($id, $directorName) {
        $sql = "UPDATE directors SET director_name = ? WHERE id = ? AND status = 'active'";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $directorName,
                $id
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
public function softDelete(string $id, string $userId, string $reason, string $source): array
{
    try {
        $this->db->beginTransaction();

        // 1. Retrieve all job_property_ids for this director
        $stmt = $this->db->prepare("SELECT id FROM job_property WHERE director_id = ?");
        $stmt->execute([$id]);
        $propertyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $jobPropertyCount = count($propertyIds);

        $totalEmployees = 0;

        // 2. Count and Soft-Delete Employees if properties exist
        if ($jobPropertyCount > 0) {
            // Count for the return message
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM employees_table 
                WHERE is_deleted != 2  AND job_property_id IN ($placeholders)
            ");
            $stmt->execute($propertyIds);
            $totalEmployees = $stmt->fetchColumn();

        }

        // ✅ 3. Inactivate the Director
        $stmt = $this->db->prepare("
            UPDATE directors 
            SET status = 'inactive',
            is_deleted = 1,
                deleted_at = NOW(),
                deleted_by = ?,
                deletion_source = ?,
                deletion_reason = ?
            WHERE id = ? AND status = 'active' and is_deleted = 0
        ");
        $stmt->execute([$userId, $source, $reason, $id]);

        $this->db->commit();

        return [
            'status'           => 'success',
            'message'          => 'በትክክል ተሰርዟል',
            'jobPropertyCount' => $jobPropertyCount,
            'employeeCount'    => $totalEmployees
        ];

    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('DirectorModel::softDelete - ' . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

public function findAllDeleted(?string $branchId): array
{
    $whereClause = $branchId
        ? "AND d.branch_id = :branchId"
        : "";

    $stmt = $this->db->prepare("
        SELECT
            d.id,
            d.director_name,
            d.deleted_at,
            d.status,
            d.deletion_source,

            -- Who deleted the director, pulled directly from the directors table
            CONCAT(u.first_name, ' ', u.father_name) AS deleted_by_name,

            -- Count all cascade-deleted properties under this director
            (
                SELECT COUNT(*)
                FROM job_property jp
                WHERE jp.director_id = d.id
            ) AS affected_job_properties,

            -- Count all cascade-deleted employees under this director
            (
                SELECT COUNT(*)
                FROM employees_table e
                INNER JOIN job_property jp ON e.job_property_id = jp.id
                WHERE jp.director_id = d.id

            ) AS affected_employees,

            -- Can purge only if no active properties remain
            (
                SELECT COUNT(*)
                FROM job_property jp
                WHERE jp.director_id = d.id
                  AND jp.status = 'active'
            ) = 0 AS can_purge

        FROM directors d

        -- Join directly to users via deleted_by on the directors table
        INNER JOIN users u ON u.id = d.deleted_by

        WHERE d.status     = 'inactive'
          AND d.is_deleted = 1
          $whereClause

        ORDER BY d.deleted_at DESC
    ");

    $params = $branchId ? ['branchId' => $branchId] : [];
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
 public function restore(string $id, string $userId): array
{
    try {
        $this->db->beginTransaction();

        // 1. Verify the director exists and is actually soft-deleted
        $stmt = $this->db->prepare("
            SELECT id FROM directors 
            WHERE id = ? AND status = 'inactive' AND is_deleted = 1
        ");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'Director not found or is already active'];
        }

        // 2. Retrieve all cascade-deleted job_property IDs for this director
        $stmt = $this->db->prepare("
            SELECT id FROM job_property 
            WHERE director_id = ? 
        ");
        $stmt->execute([$id]);
        $propertyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $jobPropertyCount = count($propertyIds);

        $totalEmployees = 0;

        // 3. Restore cascade-deleted employees if properties exist
        if ($jobPropertyCount > 0) {
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));

            // Count employees to be restored
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM employees_table 
                WHERE job_property_id IN ($placeholders) 
            ");
            $stmt->execute($propertyIds);
            $totalEmployees = $stmt->fetchColumn();
        }

        // 5. Restore the director
        $stmt = $this->db->prepare("
            UPDATE directors 
            SET status          = 'active',
                deleted_at      = NULL,
                deleted_by      = NULL,
                deletion_source = NULL
            WHERE id = ?
              AND deletion_source = 'INDIVIDUAL'
              AND is_deleted = 1
        ");
        $stmt->execute([$id]);

        $this->db->commit();

        return [
            'status'           => 'success',
            'message'          => 'በትክክል ተመልሷል',
            'jobPropertyCount' => $jobPropertyCount,
            'employeeCount'    => $totalEmployees
        ];

    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('DirectorModel::restore - ' . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

}