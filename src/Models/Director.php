<?php
namespace App\Models;
use PDO;
class Director {
    private $db;

    /**
     * ኮኔክሽኑን ከውጭ ይቀበላል (Dependency Injection)
     * ይህ በየቦታው አዲስ ኮኔክሽን እንዳይከፈት ይረዳል
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * አዲስ ዳይሬክተር መመዝገቢያ
     * @param string $id በኮንትሮለር የተፈጠረ UUID
     * @param string $name የተመዘገበው የዳይሬክተሩ ስም
     * @param string $registeredBy የተመዘገበው የመ_regsitered_by ተጠቃሚ
     */
   public function create($id, $organization_id, $branch_id, $directorName, $registeredBy) {
        $sql = "INSERT INTO directors (id, organization_id, branch_id, director_name, registered_by) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);

            // በቀጥታ የተላኩትን ቫሪያብሎች በመጠቀም ማስገባት
            return $stmt->execute([
                $id,
                $organization_id,
                $branch_id,
                $directorName,
                $registeredBy

            ]);
        } catch (\PDOException $e) {
            // ስህተት ካለ ለኮንትሮለሩ እንዲያውቀው ደግመን እንወረውራለን (Throw)
            throw $e;
        }
    }
    public function getAllDirectors($myBranchId) {
        $sql = "SELECT * FROM directors WHERE branch_id = ? ORDER BY director_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$myBranchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}