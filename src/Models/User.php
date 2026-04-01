<?php
namespace App\Models;
use PDO;

class User {
    private $db;

    // ኮኔክሽኑን ከውጭ መቀበል (Dependency Injection) ለፈጣን አሰራር ይረዳል
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * አዲስ ተጠቃሚ መመዝገቢያ
     * ዳታው አስቀድሞ በ Controller ተዘጋጅቶ መምጣት አለበት
     */
    public function create($id, $organization_id, $branch_id, $firstName, $fatherName, $grandFatherName, $email, $password, $role, $registeredBy) {
        
        $sql = "INSERT INTO users (
                    id, organization_id, branch_id, first_name, father_name, grand_father_name, 
                    email, password, role, registered_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                $id,
                $organization_id,
                $branch_id,
                $firstName,
                $fatherName,
                $grandFatherName,
                $email,
                $password, // አስቀድሞ Hash የተደረገ
                $role,
                $registeredBy
            ]);
        } catch (\PDOException $e) {
            // ስህተቱን ለ Controller እንዲያሳውቅ ደግመን እንወረውራለን (Throw)
            throw $e;
        }
    }

    /**
     * በኢሜይል አድራሻ ተጠቃሚን መፈለጊያ
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Email lookup error: " . $e->getMessage());
            return false;
        }
    }
    public function getAllOrgAdmins() {
    $sql = "SELECT u.*, o.name as organization_name, b.name as branch_name 
            FROM users u
            JOIN branches b ON u.branch_id = b.id
            JOIN organizations o ON u.organization_id = o.id
            WHERE b.level = 1 
              AND u.role = 'org_admin'
            ORDER BY o.name ASC";
    
    try {
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Get all org admins error: " . $e->getMessage());
        return [];
    }
    }
   public function getUsersForMyBranchHierarchy($myBranchId) {
    $sql = "SELECT u.*, o.name as organization_name, b.name as branch_name 
            FROM users u
            JOIN branches b ON u.branch_id = b.id
            JOIN organizations o ON u.organization_id = o.id
            WHERE u.branch_id = :my_branch1 
               OR b.parent_id = :my_branch2"; // ስሙን ለየው (1 እና 2)
    
    try {
        $stmt = $this->db->prepare($sql);
        // እዚህ ጋር ለሁለቱም ቦታ ዳታውን እንሰጣለን
        $stmt->execute([
            ':my_branch1' => $myBranchId,
            ':my_branch2' => $myBranchId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Get hierarchy users error: " . $e->getMessage());
        return [];
    }
}
}