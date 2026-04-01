<?php
namespace App\Models;

use PDO;

class Organization {
    private $db;

    /**
     * ኮኔክሽኑን ከውጭ ይቀበላል (Dependency Injection)
     * ይህ በየቦታው አዲስ ኮኔክሽን እንዳይከፈት ይረዳል
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * አዲስ ድርጅት መመዝገቢያ
     * @param string $id በኮንትሮለር የተፈጠረ UUID
     * @param string $name የተመዘገበው የድርጅት ስም
     */
   public function create($id, $name, $description, $registeredBy) {
    try {
        $this->db->beginTransaction();

        // 1. ድርጅቱን መመዝገብ (እዚህ ጋር return አታድርግ!)
        $sql = "INSERT INTO organizations (id, name, organization_type) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        // Execute ብቻ አድርግ፣ return አትበል
        $stmt->execute([
            $id,
            $name,
            $description
        ]);

        // 2. የ Branch ሞዴልን መጥራት
        $branchModel = new \App\Models\Branch($this->db);
        $branchModel->insertBranch([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'org_id' => $id,
            'parent_id' => null,
            'name' => $name,
            'level' => 1,
            'registered_by' => $registeredBy
        ]);

        // 3. አሁን ዳታቤዙ ላይ እንዲጸድቅ commit እናደርጋለን
        $this->db->commit();
        
        return true; // ስራው በሙሉ ሲያልቅ ብቻ return አድርግ

    } catch (\Exception $e) {
        // ስህተት ካለ ወደ ኋላ ይመልሰዋል
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        throw $e;
    }
}
    /**
     * ሁሉንም ድርጅቶች ለዝርዝር ለማምጣት (እንደ ተጨማሪ)
     */
    public function getAll() {
        $sql = "SELECT * FROM organizations ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function updateOrganization($id, $name) {
        $sql = "UPDATE organizations SET name = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $name,
                $id
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
/*
    public function delete($id) {
        $sql = "DELETE FROM organizations WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw $e;
        }
    } */
}