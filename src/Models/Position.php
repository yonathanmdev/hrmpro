<?php
namespace App\Models;
use PDO;

class Position {
    private $db;

    /**
     * Dependency Injection for Database Connection
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new position record
     * 
     * @param string $id UUID
     * @param string $directorId The ID of the directorate
     * @param string $name Position title
     * @param string $grade Position grade
     * @param float $salary Position salary
     * @param string $registeredBy User ID of the creator
     */
 
    public function create(
    string $id, string $director_id, string $organization_id, string $branch_id,
    string $positionName, string $positionCode, string $seraDereja, string $seraRken,
    float $salary, string $yeteyashHuneta, string $nesaHkmna, $clothDuration,
    string $description, string $registeredBy,
    int $allowMultiple = 0,   // ← new
    $vacancyCount  = null // ← new
): bool {
    $sql = "INSERT INTO job_property (
                id, director_id, organization_id, branch_id,
                job_name, job_identifier_no, dereja, scale,
                salary, wastna, hkmna,  allow_multiple, vacancy_count, cloth_due,
                description, registered_by
               
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?,
                ?, ?
            )";

    try {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id, $director_id, $organization_id, $branch_id,
            $positionName, $positionCode, $seraDereja, $seraRken,
            $salary, $yeteyashHuneta, $nesaHkmna,$allowMultiple, $vacancyCount, 
            $clothDuration,   // ← null when disabled ✅
            $description, $registeredBy
        ]);
    } catch (\PDOException $e) {
        throw $e;
    }
}
    /* Fetch all positions for dropdowns
     */
  public function getAllPositions($branch_id) {
        $sql = "
            SELECT 
                jp.id,
                jp.director_id,
                d.director_name,
                jp.job_name,
                jp.job_identifier_no,
                jp.dereja,
                jp.scale,
                jp.salary,
                jp.wastna,
                jp.hkmna,
                jp.status,
                jp.current_filled
            FROM job_property jp
            LEFT JOIN directors d ON jp.director_id = d.id
            WHERE jp.branch_id = ?
             AND jp.deletion_source IS NULL
            ORDER BY jp.job_name ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$branch_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching positions: " . $e->getMessage());
            return [];
        }
    }

 public function getActiveJobsByBranch($branch_id, ?string $currentJobId = null): array
{
    $sql = "
        SELECT 
            jp.id,
            jp.job_name,
            jp.allow_multiple,
            jp.vacancy_count,
            jp.current_filled as filled_count
        FROM job_property jp
        WHERE 
            jp.branch_id = ?
            AND jp.status = 'active'
            AND jp.is_deleted = 0

            AND (
                -- Case A: single position
                (jp.allow_multiple = 0 AND jp.current_filled = 0)

                OR

                -- Case B1: unlimited
                (jp.allow_multiple = 1 AND jp.vacancy_count IS NULL)

                OR

                -- Case B2: limited
                (jp.allow_multiple = 1 
                 AND jp.vacancy_count IS NOT NULL 
                 AND jp.current_filled < jp.vacancy_count)
            )

            OR jp.id = ?

        ORDER BY jp.job_name ASC
    ";

    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $branch_id,
            $currentJobId
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    } catch (\PDOException $e) {
        error_log("Error fetching active jobs: " . $e->getMessage());
        return [];
    }
}


    /**
     * Get position by ID
     * 
     * @param string $id Position ID
     * @return array|false Position data or false if not found
     */
    public function getPositionById($id) {
        $sql = "
            SELECT 
                jp.*,
                d.director_name
            FROM job_property jp
            LEFT JOIN directors d ON jp.director_id = d.id
            WHERE jp.id = ?
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching position by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a position record
     * 
     * @param string $id Position ID
     * @param array $data Position data to update
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        $sql = "UPDATE job_property SET 
                    director_id = :director_id,
                    job_name = :job_name,
                    job_identifier_no = :job_identifier_no,
                    dereja = :dereja,
                    scale = :scale,
                    salary = :salary,
                    wastna = :wastna,
                    hkmna = :hkmna,
                    allow_multiple = :allow_multiple,
                    vacancy_count = :vacancy_count,
                    cloth_due = :cloth_due,
                    description = :description
                WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':director_id' => $data['director_id'],
                ':job_name' => $data['job_name'],
                ':job_identifier_no' => $data['job_identifier_no'],
                ':dereja' => $data['dereja'],
                ':scale' => $data['scale'],
                ':salary' => $data['salary'],
                ':wastna' => $data['wastna'],
                ':hkmna' => $data['hkmna'],
                ':allow_multiple' => $data['allow_multiple'],
                ':vacancy_count' => $data['vacancy_count'],
                ':cloth_due' => $data['cloth_due'],
                ':description' => $data['description'],
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating position: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete a position
     * 
     * @param string $id Position ID
     * @param string $adminId Admin user ID
     * @return array Result with status and message
     */
   public function softDelete(string $id, string $adminId, string $reason, string $deletionSource): array
{
    try {
        $this->db->beginTransaction();

        // 1. Verify position exists and is active
        $stmt = $this->db->prepare(
            "SELECT id, job_name 
             FROM job_property
             WHERE id     = ?
             AND   status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $position = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$position) {
            $this->db->rollBack();
            return [
                'status'  => 'error',
                'message' => 'የስራ መደቡ አልተገኘም ወይም አስቀድሞ ተሰርዟል።'
            ];
        }

        // 2. Count active employees on this position (for info only)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM employees_table
             WHERE job_property_id = ?
             AND   is_deleted      = 0 AND status = 'active'"
        );
        $stmt->execute([$id]);
        $activeEmployeeCount = (int) $stmt->fetchColumn();

        // 3. Soft delete position ONLY — employees are untouched
        $stmt = $this->db->prepare(
            "UPDATE job_property SET
                status          = 'inactive',
                is_deleted      = 1,
                deleted_at      = NOW(),
                deleted_by      = ?,
                deletion_reason = ?,
                deletion_source = ?
             WHERE id     = ?
             AND   status = 'active' AND is_deleted = 0 AND current_filled = 0"
        );
        $stmt->execute([$adminId, $reason, $deletionSource, $id]);

        if ($stmt->rowCount() === 0) {
            $this->db->rollBack();
            return [
                'status'  => 'error',
                'message' => 'መሰረዝ አልተሳካም። እባክዎ በድጋሚ ይሞክሩ።'
            ];
        }

        $this->db->commit();

        return [
            'status'              => 'success',
            'message'             => 'የስራ መደቡ ተሰርዟል።',
            'position_name'       => $position['job_name'],
            'active_employee_count' => $activeEmployeeCount,
            // Warn if employees still assigned
            'warning'             => $activeEmployeeCount > 0
                ? $activeEmployeeCount . ' ሰራተኞች አሁንም በዚህ መደብ ላይ ናቸው። ወደ ሌላ መደብ ያዛውሯቸው።'
                : null,
        ];

    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('JobPropertyModel::softDelete - ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።'];
    }
}
public function findAllDeleted(): array
{
    $stmt = $this->db->prepare("
        SELECT
            jp.id,
            jp.job_name,
            jp.job_identifier_no,
            jp.dereja,
            jp.scale,
            jp.salary,
            jp.status,
            jp.deletion_source,
            jp.deleted_at,

            -- Director info
            d.director_name,

            -- Who deleted the position, pulled directly from job_property
            CONCAT(u.first_name, ' ', u.father_name) AS deleted_by_name,

            -- Count cascade-deleted employees under this position
            (
                SELECT COUNT(*)
                FROM employees_table e
                WHERE e.job_property_id = jp.id
                  AND e.is_deleted != 2
            ) AS affected_employees,

            -- Whether this was cascade-deleted by a director deletion
            (jp.deletion_source = 'DIRECTOR_CASCADE') AS is_director_cascade,

            -- Can purge only if no active employees remain
            (
                SELECT COUNT(*)
                FROM employees_table e
                WHERE e.job_property_id = jp.id
                  AND e.is_deleted != 2
            ) = 0 AS can_purge

        FROM job_property jp

        -- Director info
        LEFT JOIN directors d ON d.id = jp.director_id

        -- Who performed the deletion
        INNER JOIN users u ON u.id = jp.deleted_by

        WHERE jp.status     = 'inactive'
          AND jp.deleted_at IS NOT NULL

        ORDER BY jp.deleted_at DESC
    ");

    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
public function restore(string $id, string $userId): array
{
    try {
        $this->db->beginTransaction();

        // 1. Verify the position exists and is actually soft-deleted individually
        $stmt = $this->db->prepare("
            SELECT id FROM job_property
            WHERE id = ?
              AND status = 'inactive'
              AND is_deleted = 1
              AND deletion_source = 'INDIVIDUAL'
        ");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'መደቡ አልተገኘም ወይም በዳይሬክተር Cascade ተሰርዟል'];
        }
// 2. Count active employees on this position (for info only)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM employees_table
             WHERE job_property_id = ?
             AND   is_deleted      = 0 AND status = 'active'"
        );
        $stmt->execute([$id]);
        $activeEmployeeCount = (int) $stmt->fetchColumn();
        // 2. Restore the position itself
        $stmt = $this->db->prepare("
            UPDATE job_property
            SET status          = 'active',
                deleted_at      = NULL,
                deleted_by      = NULL,
                deletion_source = NULL,
                is_deleted      = 0
            WHERE id            = ?
              AND status        = 'inactive'
              AND is_deleted    = 1
              AND deletion_source = 'INDIVIDUAL'
        ");
        $stmt->execute([$id]);

        $this->db->commit();

        return [
            'status'        => 'success',
            'message'       => 'በትክክል ተመልሷል',
            'employeeCount' => $activeEmployeeCount
        ];

    } catch (\Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('JobPropertyModel::restore - ' . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
}