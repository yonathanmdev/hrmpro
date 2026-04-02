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
 
    public function create($id, $director_id, $organization_id, $branch_id, $positionName, $positionCode, 
                                            $seraDereja, 
                                            $seraRken, 
                                            $salary, 
                                            $yeteyashHuneta, 
                                            $nesaHkmna, 
                                            $clothDuration, 
                                            $description, 
                                            $registeredBy){
        $sql = "INSERT INTO job_property (id, director_id, organization_id, branch_id, job_name, job_identifier_no, dereja, scale, salary, wastna, hkmna, cloth_due, description,registered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([ $id,
                                    $director_id, 
                                    $organization_id, 
                                    $branch_id, 
                                    $positionName, 
                                    $positionCode, 
                                    $seraDereja, 
                                    $seraRken, 
                                    $salary, 
                                    $yeteyashHuneta, 
                                    $nesaHkmna, 
                                    $clothDuration, 
                                    $description, 
                                    $registeredBy
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
       /**
     * Fetch all positions for dropdowns
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
            jp.status
        FROM job_property jp
        LEFT JOIN directors d ON jp.director_id = d.id
        WHERE jp.branch_id = ?
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
}
