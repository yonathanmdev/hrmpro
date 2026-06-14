<?php

namespace App\Models;

use PDO;

class EfficiencyFileModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get employees who have a BSC file but no efficiency file for that specific BSC file
     */
    public function getEmployeesWithoutEfficiency(
    string $branchId,
    string $seasonId
): array {
    $sql = "
        SELECT
            e.uuid,
            e.first_name,
            e.father_name,
            e.g_father_name,
            b.id AS file_id

        FROM employees_table e

        INNER JOIN bsc_plan_files b
            ON b.employee_id = e.uuid
            AND b.season_id  = :season_id
            AND b.is_deleted = 0

        LEFT JOIN efficiency_files ef
            ON ef.bsc_file_id = b.id
            AND ef.is_deleted = 0

        WHERE e.branch_id = :branch_id
        AND ef.id IS NULL

        ORDER BY
            e.first_name,
            e.father_name
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        'branch_id' => $branchId,
        'season_id' => $seasonId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    /**
     * Get employees who already have an efficiency file — indexed by bsc_file_id
     */
    public function getEmployeesWithEfficiency(
        string $branchId,
        string $seasonId
    ): array {
        $sql = "
            SELECT
                e.uuid,
                e.first_name,
                e.father_name,
                e.g_father_name,
                ef.id              AS efficiency_file_id,
                ef.bsc_file_id,
                ef.efficiency_mark,
                ef.file_name,
                ef.file_path,
                ef.created_at

            FROM employees_table e

            INNER JOIN bsc_plan_files b
                ON b.employee_id = e.uuid
                AND b.season_id  = :season_id
                AND b.is_deleted = 0

            INNER JOIN efficiency_files ef
                ON ef.bsc_file_id = b.id
                AND ef.is_deleted = 0

            WHERE e.branch_id = :branch_id

            ORDER BY
                e.first_name,
                e.father_name
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'branch_id' => $branchId,
            'season_id' => $seasonId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check efficiency already exists for this specific BSC file ID
     */
    public function efficiencyExistsForBscFile(
        string $bscFileId
    ): bool {
        $sql = "
            SELECT id
            FROM efficiency_files
            WHERE bsc_file_id = :bsc_file_id
            AND is_deleted    = 0
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'bsc_file_id' => $bscFileId,
        ]);

        return (bool) $stmt->fetch();
    }

    /**
     * Insert a new efficiency file record
     */
    public function uploadEfficiency(
        string $id,
        string $employeeId,
        string $branchId,
        string $seasonId,
        string $bscFileId,
        float  $efficiencyMark,
        string $fileName,
        string $filePath,
        int    $fileSize,
        string $uploadedBy
    ): bool {
        $sql = "
            INSERT INTO efficiency_files
            (
                id,
                employee_id,
                branch_id,
                season_id,
                bsc_file_id,
                efficiency_mark,
                file_name,
                file_path,
                file_size,
                uploaded_by,
                deleted_at
            )
            VALUES
            (
                :id,
                :employee_id,
                :branch_id,
                :season_id,
                :bsc_file_id,
                :efficiency_mark,
                :file_name,
                :file_path,
                :file_size,
                :uploaded_by,
                NULL
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'              => $id,
            'employee_id'     => $employeeId,
            'branch_id'       => $branchId,
            'season_id'       => $seasonId,
            'bsc_file_id'     => $bscFileId,
            'efficiency_mark' => $efficiencyMark,
            'file_name'       => $fileName,
            'file_path'       => $filePath,
            'file_size'       => $fileSize,
            'uploaded_by'     => $uploadedBy,
        ]);
    }
  public function updateEfficiency(
    string $id,
    float $mark,
    string $fileName,
    string $filePath,
    int $fileSize,
    string $updatedBy
): bool {

    $stmt = $this->db->prepare("
        UPDATE efficiency_files
        SET
            efficiency_mark = :efficiency_mark,
            file_name       = :file_name,
            file_path       = :file_path,
            file_size       = :file_size,
            updated_by      = :updated_by
        WHERE id = :id
          AND is_deleted = 0
    ");

    return $stmt->execute([
        'efficiency_mark' => $mark,
        'file_name'       => $fileName,
        'file_path'       => $filePath,
        'file_size'       => $fileSize,
        'updated_by'      => $updatedBy,
        'id'              => $id
    ]);
}
public function updateEfficiencyMark(
    string $id,
    float $mark,
    string $updatedBy
): bool {

    $stmt = $this->db->prepare("
        UPDATE efficiency_files
        SET
            efficiency_mark = :efficiency_mark,
            updated_by      = :updated_by
        WHERE id = :id
          AND is_deleted = 0
    ");

    return $stmt->execute([
        'efficiency_mark' => $mark,
        'updated_by'      => $updatedBy,
        'id'              => $id
    ]);
}
public function getEfficiencyById(string $id): ?array
{
    $stmt = $this->db->prepare("
        SELECT *
        FROM efficiency_files
        WHERE id = :id
          AND is_deleted = 0
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
}