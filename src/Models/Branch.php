<?php
namespace App\Models;

use PDO;

class Branch {
    private $db;
    public function __construct($db) { 
        $this->db = $db;
         }

    public function insertBranch($data) {
        $sql = "INSERT INTO branches (id, organization_id, parent_id, name, level, registered_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['id'], 
            $data['org_id'], 
            $data['parent_id'], 
            $data['name'], 
            $data['level'], 
            $data['registered_by']
        ]);
    }
/**
 * የራሴን ቅርንጫፍ ቀጥተኛ ንዑስ ቅርንጫፎች (Immediate Sub-branches) ብቻ ያመጣል
 */
public function getImmediateSubBranches($myBranchId) {
    // parent_id የእኔን ID የመሰሉትን ብቻ ይፈልጋል
    $sql = "SELECT * FROM branches 
            WHERE parent_id = ? 
            ORDER BY name ASC";
            
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$myBranchId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Get immediate sub-branches error: " . $e->getMessage());
        return [];
    }
}
public function getMainOfficeId($orgId) {
    $sql = "SELECT id FROM branches WHERE organization_id = ? AND level = 1 LIMIT 1";
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        $result = $stmt->fetch();
        
        // ID-ውን ብቻ ወይም ካልተገኘ false ይመልሳል
        return $result ? $result['id'] : false;
    } catch (\PDOException $e) {
        error_log("Get Main Office ID Error: " . $e->getMessage());
        return false;
    }
}
public function getBranchById($branchId) {
    $sql = "SELECT name, level FROM branches WHERE id = ? LIMIT 1";
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Get Branch by ID Error: " . $e->getMessage());
        return false;
    }
}

}