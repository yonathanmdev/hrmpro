<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use PDO;

class BranchEvaluationWindow
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * All windows
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                w.*,
                b.name AS branch_name,
                s.season_name,
                s.fiscal_year
                
            FROM branch_evaluation_windows w
            INNER JOIN branches b
                ON b.id = w.branch_id
            INNER JOIN evaluation_seasons s
                ON s.id = w.season_id
            ORDER BY b.name ASC
        ";

        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all branches with their configuration
     * for a selected season
     */
    public function getBranchSettingsBySeason(string $seasonId): array
    {
        $sql = "
            SELECT
                b.id,
                b.name,

                w.id AS window_id,
                w.mode,
                w.custom_start,
                w.custom_end,
                COALESCE(w.is_locked,0) AS is_locked,
                w.notes

            FROM branches b

            LEFT JOIN branch_evaluation_windows w
                ON w.branch_id = b.id
                AND w.season_id = :season_id

            WHERE b.is_deleted = 0
            AND b.status = 'active'

            ORDER BY b.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'season_id' => $seasonId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update branch settings
     */
    public function saveOrUpdate(array $data): bool
    {
        $checkSql = "
            SELECT id
            FROM branch_evaluation_windows
            WHERE branch_id = ?
            AND season_id = ?
            LIMIT 1
        ";

        $check = $this->db->prepare($checkSql);

        $check->execute([
            $data['branch_id'],
            $data['season_id']
        ]);

        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {

            $updateSql = "
                UPDATE branch_evaluation_windows
                SET
                    mode = :mode,
                    custom_start = :custom_start,
                    custom_end = :custom_end,
                    is_locked = :is_locked,
                    override_by = :override_by,
                    notes = :notes
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($updateSql);

            return $stmt->execute([
                'mode'         => $data['mode'],
                'custom_start' => $data['custom_start'],
                'custom_end'   => $data['custom_end'],
                'is_locked'    => $data['is_locked'],
                'override_by'  => $data['override_by'],
                'notes'        => $data['notes'] ?? null,
                'id'           => $existing['id']
            ]);
        }

        $insertSql = "
            INSERT INTO branch_evaluation_windows
            (
                id,
                branch_id,
                season_id,
                mode,
                custom_start,
                custom_end,
                is_locked,
                override_by,
                notes
            )
            VALUES
            (
                :id,
                :branch_id,
                :season_id,
                :mode,
                :custom_start,
                :custom_end,
                :is_locked,
                :override_by,
                :notes
            )
        ";

        $stmt = $this->db->prepare($insertSql);

        return $stmt->execute([
            'id'           => Uuid::uuid4()->toString(),
            'branch_id'    => $data['branch_id'],
            'season_id'    => $data['season_id'],
            'mode'         => $data['mode'],
            'custom_start' => $data['custom_start'],
            'custom_end'   => $data['custom_end'],
            'is_locked'    => $data['is_locked'],
            'override_by'  => $data['override_by'],
            'notes'        => $data['notes'] ?? null
        ]);
    }

    /**
     * Get a specific branch-season setting
     */
    public function findByBranchAndSeason(
        string $branchId,
        string $seasonId
    ): ?array
    {
        $sql = "
            SELECT *
            FROM branch_evaluation_windows
            WHERE branch_id = ?
            AND season_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $branchId,
            $seasonId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

public function isEvaluationOpen(
    string $branchId,
    string $seasonId
): bool
{
    $window = $this->findByBranchAndSeason(
        $branchId,
        $seasonId
    );

    if (!$window) {
        return false;
    }

    // Locked by system admin
    if ((int)$window['is_locked'] === 1) {
        return false;
    }

    // Always open
    if ($window['mode'] === 'always_open') {
        return true;
    }

    $today = date('Y-m-d');

    // Custom branch dates
    if ($window['mode'] === 'custom') {

        if (
            empty($window['custom_start']) ||
            empty($window['custom_end'])
        ) {
            return false;
        }

        return (
            $today >= $window['custom_start'] &&
            $today <= $window['custom_end']
        );
    }

    // Default season dates
    $seasonModel = new EvaluationSeason($this->db);

    $season = $seasonModel->find($seasonId);

    if (!$season) {
        return false;
    }

    $year = date('Y');

    $startDate = $year . '-' . $season['default_start'];
    $endDate   = $year . '-' . $season['default_end'];

    return (
        $today >= $startDate &&
        $today <= $endDate
    );
}

public function getOpenSeasonsForBranch(
    string $branchId
): array
{
    $today = date('Y-m-d');

    $sql = "
        SELECT
            w.*,
            s.season_name,
            s.season_label,
            s.default_start,
            s.default_end,
            s.fiscal_year
        FROM branch_evaluation_windows w
        INNER JOIN evaluation_seasons s
            ON s.id = w.season_id
        WHERE w.branch_id = ? ORDER BY s.fiscal_year ASC, s.season_label ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$branchId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $openSeasons = [];

    foreach ($rows as $row) {

        if ((int)$row['is_locked'] === 1) {
            continue;
        }

        if ($row['mode'] === 'always_open') {
            $openSeasons[] = $row;
            continue;
        }

        if ($row['mode'] === 'custom') {

            if (
                !empty($row['custom_start']) &&
                !empty($row['custom_end']) &&
                $today >= $row['custom_start'] &&
                $today <= $row['custom_end']
            ) {
                $openSeasons[] = $row;
            }

            continue;
        }

        $year = date('Y');

        $start = $year . '-' . $row['default_start'];
        $end   = $year . '-' . $row['default_end'];

        if (
            $today >= $start &&
            $today <= $end
        ) {
            $openSeasons[] = $row;
        }
    }

    return $openSeasons;
}
    }