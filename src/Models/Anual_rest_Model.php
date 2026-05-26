<?php
namespace App\Models;
use App\Helpers\E;  
use PDO;
class Anual_rest_Model {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getEmployeeAnualRest($employee_uuid) {
        $sql = "SELECT `rest_table_id`, `budget_year`, `employee_id`, `registered_date`, `registered_by` FROM `anual_rest` 
                WHERE employee_id = ? AND is_deleted = 0 ORDER BY registered_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employee_uuid]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function updateEmployeeAnualRest($employee_uuid, $data) {
        $sql = "UPDATE `anual_rest` SET `budget_year` = ?, `employee_id` = ?, `registered_date` = ?, `registered_by` = ? 
                WHERE employee_uuid = ? AND is_deleted = 0";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['budget_year'], 
            $data['employee_id'], 
            $data['registered_date'], 
            $data['registered_by'], 
            $employee_uuid
        ]);
    }
    public function deleteEmployeeAnualRest($employee_uuid) {
        $sql = "UPDATE `anual_rest` SET is_deleted = 1 WHERE employee_uuid = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$employee_uuid]);
        }   
        public function crearAnualRest($employee_uuid, $data) {
            $sql = "INSERT INTO `anual_rest` (employee_id, budget_year, employee_id, registered_date, registered_by) 
                    VALUES (?, ?, ?, ?, ?)";
                    
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $employee_uuid,
                $data['budget_year'], 
                $data['employee_id'], 
                $data['registered_date'], 
                $data['registered_by']
            ]);
        }
 
    
}