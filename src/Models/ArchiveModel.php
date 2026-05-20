<?php
namespace App\Models;
use PDO;

class ArchiveModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($archiveData) {
        // 7 columns = 7 placeholders
        $sql = "INSERT INTO employee_documents 
                    (id, emp_id, owner_type, owner_id, entity_type, file_url, registered_by) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                $archiveData['id'],
                $archiveData['employee_id'],
                $archiveData['owner_type'],
                $archiveData['owner_id'],
                $archiveData['cert_type'],
                $archiveData['certificate_file'],
                $archiveData['registered_by']
            ]);

        } catch (\PDOException $e) {
            throw $e;
        }
    }
public function update($editData) {
    try {
        // Build query dynamically — only update file_url if a new file was uploaded
        if (!empty($editData['file_url'])) {
            // Fetch old file url before overwriting (for cleanup in controller)
            $stmt = $this->db->prepare("SELECT file_url FROM employee_documents WHERE id = ?");
            $stmt->execute([$editData['id']]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            $editData['old_file_url'] = $existing['file_url'] ?? null;

            $sql = "UPDATE employee_documents 
                    SET entity_type = ?, file_url = ?, updated_by = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $editData['cert_type'],
                $editData['file_url'],
                $editData['updated_by'],
                $editData['id'],
            ]);
        } else {
            // No new file — only update the type
            $sql = "UPDATE employee_documents 
                    SET entity_type = ?, updated_by = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $editData['cert_type'],
                $editData['updated_by'],
                $editData['id'],
            ]);
        }
    } catch (\PDOException $e) {
        throw $e;
    }
}
}