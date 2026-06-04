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

    public function create($data)
    {
        $sql = "
            INSERT INTO evaluation_seasons
            (
                id,
                season_name,
                season_label,
                default_start,
                default_end,
                created_by
            )
            VALUES
            (
                :id,
                :season_name,
                :season_label,
                :default_start,
                :default_end,
                :created_by
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
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