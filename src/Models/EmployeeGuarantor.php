<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;
use PDO;

class EmployeeGuarantor {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Called from within an already-open transaction (owned by EmployeeRegistration model).
     * Does NOT call beginTransaction / commit / rollBack — the caller owns those.
     */
    public function insertGurantor(array $data): bool {
        $sql = "INSERT INTO employees_guarantors (
                    id,
                    employee_id,
                    guarantor_name,
                    guarantor_phone,
                    guarantor_letter
                ) VALUES (
                    :id,
                    :employee_id,
                    :guarantor_name,
                    :guarantor_phone,
                    :guarantor_letter
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'               => $data['id'],
            ':employee_id'      => $data['employee_id'],
            ':guarantor_name'   => $data['guarantor_name'],
            ':guarantor_phone'  => $data['guarantor_phone'],
            ':guarantor_letter' => $data['guarantor_letter'],
        ]);
    }

    public function getByEmployeeId(string $employeeId): ?array {
    $stmt = $this->db->prepare("
        SELECT * FROM employees_guarantors 
        WHERE employee_id = :employee_id 
        AND is_deleted = 0
    ");
    $stmt->execute([':employee_id' => $employeeId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $result ?: null;
}
// Insert or update guarantor record
public function upsertWithinTransaction(array $data): bool {
    $sql = "INSERT INTO employees_guarantors 
                (id, employee_id, guarantor_name, guarantor_phone, guarantor_letter)
            VALUES 
                (:id, :employee_id, :guarantor_name, :guarantor_phone, :guarantor_letter)
            ON DUPLICATE KEY UPDATE
                guarantor_name   = VALUES(guarantor_name),
                guarantor_phone  = VALUES(guarantor_phone),
                guarantor_letter = VALUES(guarantor_letter),
                is_deleted       = 0";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        ':id'               => $data['id'] ?? Uuid::uuid4()->toString(),
        ':employee_id'      => $data['employee_id'],
        ':guarantor_name'   => $data['guarantor_name'],
        ':guarantor_phone'  => $data['guarantor_phone'],
        ':guarantor_letter' => $data['guarantor_letter'],
    ]);
}

// Soft delete when job changes to non-guarantor
public function softDeleteByEmployeeId(string $employeeId): bool {
    $stmt = $this->db->prepare("
        UPDATE employees_guarantors 
        SET is_deleted = 1 
        WHERE employee_id = :employee_id
    ");
    return $stmt->execute([':employee_id' => $employeeId]);
}
}