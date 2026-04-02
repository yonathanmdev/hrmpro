<?php
namespace App\Models;
use PDO;

class Posstion {
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
 
    public function createposstion($position_uuid, $director_uuid , $position_name, $position_level, $position_code, $salary, $registered_by) {
        $sql = "INSERT INTO positions (position_uuid, director_uuid , position_name, position_level, position_code, salary, registered_by) 
                VALUES (?, ?, ?, ?, ?, ?,?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $position_uuid,
                $director_uuid,
                $position_name,
                $position_level,
                $position_code,
                $salary,
                $registered_by
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Fetch all positions for dropdowns
     */
    public function getAllPostions() {
        $sql = "SELECT * FROM positions ORDER BY position_name ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching positions: " . $e->getMessage());
            return [];
        }
    }
}
