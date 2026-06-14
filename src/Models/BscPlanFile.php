<?php

namespace App\Models;

use PDO;

class BscPlanFile
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


   public function getEmployeesWithPlanStatus(
    string $branchId,
    string $seasonId
): array
{
    $sql = "
        SELECT
            e.uuid,
            e.first_name,
            e.father_name,
            e.g_father_name,

            f.id AS file_id,
            f.file_name,
            f.file_path,
            f.uploaded_at,
            f.attachment_status

        FROM employees_table e

        INNER JOIN bsc_plan_files f
            ON f.employee_id = e.uuid
            AND f.season_id = :season_id
            AND f.is_deleted = 0

        WHERE e.branch_id = :branch_id

        ORDER BY
            e.first_name,
            e.father_name
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        'branch_id' => $branchId,
        'season_id' => $seasonId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
   public function getEmployeesWithoutPlan(
    string $branchId,
    string $seasonId
): array
{
    $sql = "
        SELECT
            e.uuid,
            e.first_name,
            e.father_name,
            e.g_father_name

        FROM employees_table e

        LEFT JOIN bsc_plan_files f
            ON f.employee_id = e.uuid
            AND f.season_id = :season_id
            AND f.is_deleted = 0

        WHERE e.branch_id = :branch_id
        AND f.id IS NULL

        ORDER BY
            e.first_name,
            e.father_name
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        'branch_id' => $branchId,
        'season_id' => $seasonId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



/**
     * Insert a manually confirmed BSC plan record
     * file_name, file_path, file_size are null
     */
    public function markAsConfirmed(string $id,
        string $employeeId,
        string $branchId,
        string $seasonId,
        string $uploadedBy
    ): bool
    {
        // Check if a record already exists
        $checkSql = "
            SELECT id
            FROM bsc_plan_files
            WHERE employee_id = :employee_id
            AND season_id     = :season_id
            AND is_deleted    = 0
            LIMIT 1
        ";

        $check = $this->db->prepare($checkSql);

        $check->execute([
            'employee_id' => $employeeId,
            'season_id'   => $seasonId
        ]);

        if ($check->fetch()) {
            // Already has a record — just update status
            $updateSql = "
                UPDATE bsc_plan_files
                SET attachment_status = 'manually_confirmed'
                WHERE employee_id = :employee_id
                AND season_id     = :season_id
                AND is_deleted    = 0
            ";

            $stmt = $this->db->prepare($updateSql);

            return $stmt->execute([
                'employee_id' => $employeeId,
                'season_id'   => $seasonId
            ]);
        }

        $insertSql = "
            INSERT INTO bsc_plan_files
            (
                id,
                employee_id,
                branch_id,
                season_id,
                file_name,
                file_path,
                file_size,
                attachment_status,
                uploaded_by,
                uploaded_at,
                is_deleted,
                deleted_at
            )
            VALUES
            (
                :id,
                :employee_id,
                :branch_id,
                :season_id,
                NULL,
                NULL,
                NULL,
                'manually_confirmed',
                :uploaded_by,
                NOW(),
                0,
                NULL
            )
        ";

        $stmt = $this->db->prepare($insertSql);

        return $stmt->execute([
            'id'          =>$id,
            'employee_id' => $employeeId,
            'branch_id'   => $branchId,
            'season_id'   => $seasonId,
            'uploaded_by' => $uploadedBy
        ]);
    }

public function uploadPlan(
    string $id,
    string $employeeId,
    string $branchId,
    string $seasonId,
    string $fileName,
    string $filePath,
    string $uploadedBy
): bool
{
    // Check if a record already exists
    $checkSql = "
        SELECT id
        FROM bsc_plan_files
        WHERE employee_id = :employee_id
        AND season_id     = :season_id
        AND is_deleted    = 0
        LIMIT 1
    ";

    $check = $this->db->prepare($checkSql);
    $check->execute([
        'employee_id' => $employeeId,
        'season_id'   => $seasonId
    ]);

    if ($check->fetch()) {
        $updateSql = "
            UPDATE bsc_plan_files
            SET
                file_name         = :file_name,
                file_path         = :file_path,
                attachment_status = 'uploaded',
                uploaded_by       = :uploaded_by,
                uploaded_at       = NOW()
            WHERE employee_id = :employee_id
            AND season_id     = :season_id
            AND is_deleted    = 0
        ";

        $stmt = $this->db->prepare($updateSql);

        return $stmt->execute([
            'file_name'   => $fileName,
            'file_path'   => $filePath,
            'uploaded_by' => $uploadedBy,
            'employee_id' => $employeeId,
            'season_id'   => $seasonId
        ]);
    }

    $insertSql = "
        INSERT INTO bsc_plan_files
        (
            id,
            employee_id,
            branch_id,
            season_id,
            file_name,
            file_path,
            attachment_status,
            uploaded_by,
            uploaded_at,
            is_deleted,
            deleted_at
        )
        VALUES
        (
            :id,
            :employee_id,
            :branch_id,
            :season_id,
            :file_name,
            :file_path,
            'uploaded',
            :uploaded_by,
            NOW(),
            0,
            NULL
        )
    ";

    $stmt = $this->db->prepare($insertSql);

    return $stmt->execute([
        'id'          => $id,
        'employee_id' => $employeeId,
        'branch_id'   => $branchId,
        'season_id'   => $seasonId,
        'file_name'   => $fileName,
        'file_path'   => $filePath,
        'uploaded_by' => $uploadedBy
    ]);
}

public function findById(string $id) {
    $sql = "SELECT * FROM bsc_plan_files WHERE id = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
public function deleteRecord(string $id, string $employeeId, string $userId, string $reason, string $deletionSource): array
{
    try {
        $this->db->beginTransaction();

        $oldRecord = $this->findById($id);
        if (!$oldRecord) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'አልተገኘም።'];
        }

        // ✅ Get employee UUID from oldRecord
        $empId = $oldRecord['employee_id'] ?? null; // ← adjust key to match your column name

        if (!$empId) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'የሰራተኛ መለያ አልተገኘም።'];
        }


        // Delete attached documents
        $stmt = $this->db->prepare(
            "UPDATE bsc_plan_files SET
                is_deleted      = 1
             WHERE id   = ?
             AND   is_deleted = 0"
        );
        $stmt->execute([$id]);
        $deletedDocumentCount = $stmt->rowCount();

        $this->db->commit();

        return [
            'status'               => 'success',
            'deleted_type'         => 'soft',
            'message'              => 'በትክክል ተሰርዟል።',
            'oldRecord'            => $oldRecord,
            'deletedDocumentCount' => $deletedDocumentCount,
        ];

    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log('ScholarshipModel::deleteRecord - ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።'];
    }
}
}