<?php
namespace App\Models;
use App\Helpers;  

use PDO;
class Anual_rest_Model {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
       public function insert($id, $budget_year, $leave_days, $employee_UUID, $registeredBy) { 
    try {
        // 1. Debugging check (Temporary): If you want to catch exactly which variable is an array
        // if (is_array($employee_UUID)) { error_log("Employee UUID is an array: " . print_r($employee_UUID, true)); }

        $sql = "INSERT INTO anual_rest (rest_table_id, budget_year, restaamout, employee_id, registered_by) 
                VALUES (:rest_table_id, :budget_year, :restaamout, :employee_id, :registered_by)";
        
        $stmt = $this->db->prepare($sql);
        
        // Using explicit data types for binding provides better security and optimization
        $stmt->bindParam(':rest_table_id', $id, \PDO::PARAM_STR);
        $stmt->bindParam(':budget_year', $budget_year, \PDO::PARAM_INT);
        $stmt->bindParam(':restaamout', $leave_days, \PDO::PARAM_INT);
        
        // If $employee_UUID is an array, we cast it or grab the first element to prevent the crash
        $clean_uuid = is_array($employee_UUID) ? reset($employee_UUID) : $employee_UUID;
        $stmt->bindParam(':employee_id', $clean_uuid, \PDO::PARAM_STR);
        
        $stmt->bindParam(':registered_by', $registeredBy, \PDO::PARAM_STR);
        
        return $stmt->execute();

    } catch (\PDOException $e) {
        // SECURITY NOTE: Never echo raw $e->getMessage() to the end user. 
        // It can expose your database table structure and details. Log it secretly instead.
        error_log("Database Insertion Error in Anual_rest_Model: " . $e->getMessage());
        return false;
    }

}
public function featchbyid($id) {
    try {
        $sql = "SELECT * FROM anual_rest WHERE employee_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Database Fetch Error in Anual_rest_Model: " . $e->getMessage());
        return false;
    }
    
}
}