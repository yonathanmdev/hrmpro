<?php

namespace App\Models;

class EvaluationSeason
{
    private $db;
    private $table = 'evaluation_seasons';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT *
            FROM {$this->table}
            ORDER BY season_label
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
{
    $sql = "
        INSERT INTO evaluation_seasons
            (id, fiscal_year, season_name, season_label,
             default_start, default_end, created_by)
        VALUES
            (:id, :fiscal_year, :season_name, :season_label,
             :default_start, :default_end, :created_by)
    ";

    return $this->db->prepare($sql)->execute($data);
}

public function update(string $id, array $data): bool
{
    $sql = "
        UPDATE evaluation_seasons
        SET
            fiscal_year   = :fiscal_year,
            season_name   = :season_name,
            season_label  = :season_label,
            default_start = :default_start,
            default_end   = :default_end,
            updated_by    = :updated_by,
            updated_at    = NOW()
        WHERE id = :id
    ";

    return $this->db->prepare($sql)->execute(array_merge($data, ['id' => $id]));
}
    public function find($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM evaluation_seasons
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

 
}