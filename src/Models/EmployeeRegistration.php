<?php
namespace App\Models;
use App\Helpers\AmharicNormalizer;
class EmployeeRegistration {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createEmployee(array $data): bool {
        try {
            $fullNameRaw = $data['first_name'] . ' ' . $data['father_name'] . ' ' . $data['g_father_name'];
            $normalizedFullName = AmharicNormalizer::normalize($fullNameRaw);
            // Start transaction
            $this->db->beginTransaction();

            // Insert employee
            $sql = "INSERT INTO employees_table (
                uuid, employee_id, first_name, father_name, g_father_name, mother_name,
                sex, birth_date, phone_number, yegabcha_huneta, organization_id,
                branch_id, job_property_id, date_of_employed, level_of_education,
                department, employment_situation, immidate_boss, experience, pension_number, annual_rest,
                displin_situation, competency_situation, effeciency, level_of_effeciency,
                no_of_files_in_folder, employee_image, employee_file201, remark, reg_by, full_name_normalized
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

            $stmt = $this->db->prepare($sql);
            $result1 = $stmt->execute([
                $data['uuid'],
                $data['employee_id'],
                $data['first_name'],
                $data['father_name'],
                $data['g_father_name'],
                $data['mother_name'],
                $data['sex'],
                $data['birth_date'],
                $data['phone_number'],
                $data['yegabcha_huneta'],
                $data['organization_id'],
                $data['branch_id'],
                $data['job_property_id'],
                $data['date_of_employed'],
                $data['level_of_education'],
                $data['department'],
                $data['employment_situation'],
                $data['immidate_boss'],
                $data['experience'],
                $data['pension_number'],
                $data['annual_rest'],
                $data['displin_situation'],
                $data['competency_situation'],
                $data['effeciency'],
                $data['level_of_effeciency'],
                $data['no_of_files_in_folder'],
                $data['employee_image'],
                $data['employee_file201'],
                $data['remark'],
                $data['reg_by'],
                $normalizedFullName,
            ]);

            if (!$result1) {
                throw new \Exception("Failed to insert employee");
            }

            // Update job_property status to 'reserved'
            $updateSql = "UPDATE job_property SET status = 'reserved' WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $result2 = $updateStmt->execute([$data['job_property_id']]);

            if (!$result2) {
                throw new \Exception("Failed to update job status");
            }

            // Commit transaction
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Employee registration transaction failed: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeesByBranch($organizationId, $branchId) {
        $sql = "
            SELECT 
                e.uuid,
                e.employee_id,
                e.first_name,
                e.father_name,
                e.g_father_name,
                e.sex,
                e.birth_date,
                e.phone_number,
                e.yegabcha_huneta,
                e.organization_id,
                e.branch_id,
                e.job_property_id,
                jp.job_name,
                jp.status as job_status,
                e.status,
                e.rdate
            FROM employees_table e
            LEFT JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ?
              AND e.branch_id = ?
            ORDER BY e.rdate DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEmployeeByUuid($uuid) {
        $sql = "
            SELECT 
                e.*,
                jp.job_name,
                jp.status as job_status
            FROM employees_table e
            LEFT JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.uuid = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uuid]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateEmployee($uuid, array $data): bool {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Get current employee data for comparison
            $currentEmployee = $this->getEmployeeByUuid($uuid);
            if (!$currentEmployee) {
                throw new \Exception("Employee not found");
            }

            $oldJobId = $currentEmployee['job_property_id'];
            $newJobId = $data['job_property_id'];

            // Update employee
            $sql = "UPDATE employees_table SET
                employee_id = ?, pension_number = ?, first_name = ?, father_name = ?, g_father_name = ?, mother_name = ?,
                sex = ?, birth_date = ?, phone_number = ?, yegabcha_huneta = ?, job_property_id = ?,
                date_of_employed = ?, level_of_education = ?, department = ?, employment_situation = ?,
                immidate_boss = ?, experience = ?, annual_rest = ?, displin_situation = ?,
                competency_situation = ?, effeciency = ?, level_of_effeciency = ?,
                no_of_files_in_folder = ?, employee_image = ?, employee_file201 = ?, remark = ?
                WHERE uuid = ?";

            $stmt = $this->db->prepare($sql);
            $result1 = $stmt->execute([
                $data['employee_id'],
                $data['pension_number'],
                $data['first_name'],
                $data['father_name'],
                $data['g_father_name'],
                $data['mother_name'],
                $data['sex'],
                $data['birth_date'],
                $data['phone_number'],
                $data['yegabcha_huneta'],
                $data['job_property_id'],
                $data['date_of_employed'],
                $data['level_of_education'],
                $data['department'],
                $data['employment_situation'],
                $data['immidate_boss'],
                $data['experience'],
                $data['annual_rest'],
                $data['displin_situation'],
                $data['competency_situation'],
                $data['effeciency'],
                $data['level_of_effeciency'],
                $data['no_of_files_in_folder'],
                $data['employee_image'],
                $data['employee_file201'],
                $data['remark'],
                $uuid
            ]);

            if (!$result1) {
                throw new \Exception("Failed to update employee");
            }

            // Handle job change if job was changed
            if ($oldJobId != $newJobId) {
                // Set old job back to active
                $updateOldJobSql = "UPDATE job_property SET status = 'Active' WHERE id = ?";
                $updateOldJobStmt = $this->db->prepare($updateOldJobSql);
                $result2 = $updateOldJobStmt->execute([$oldJobId]);

                if (!$result2) {
                    throw new \Exception("Failed to update old job status");
                }

                // Set new job to reserved
                $updateNewJobSql = "UPDATE job_property SET status = 'reserved' WHERE id = ?";
                $updateNewJobStmt = $this->db->prepare($updateNewJobSql);
                $result3 = $updateNewJobStmt->execute([$newJobId]);

                if (!$result3) {
                    throw new \Exception("Failed to update new job status");
                }
            }

            // Commit transaction
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Employee update transaction failed: " . $e->getMessage());
            return false;
        }
    }

    public function getAvailableJobsByBranch($branchId, $excludeJobId = null) {
        $sql = "
            SELECT id, job_name, status
            FROM job_property
            WHERE branch_id = ?
              AND (status = 'Active' OR id = ?)
            ORDER BY job_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $excludeJobId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


   public function countOnboardingEmployees($organizationId, $branchId) {
    $sql = "
        SELECT COUNT(*) as total
        FROM employees_table 
        WHERE organization_id = ?
          AND branch_id = ? 
          AND status = 'Onboarding'
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$organizationId, $branchId]);
    return $stmt->fetchColumn();
}

public function getOnboardingEmployees($organizationId, $branchId) {
        $sql = "
            SELECT 
                e.uuid,
                e.employee_id,
                e.first_name,
                e.father_name,
                e.g_father_name,
                e.sex,
                e.birth_date,
                e.phone_number,
                e.yegabcha_huneta,
                e.organization_id,
                e.branch_id,
                e.job_property_id,
                jp.job_name,
                jp.status as job_status,
                e.status,
                e.rdate
            FROM employees_table e
            LEFT JOIN job_property jp ON e.job_property_id = jp.id
            WHERE e.organization_id = ?
              AND e.branch_id = ?
              AND  e.status = 'Onboarding'
            ORDER BY e.rdate DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizationId, $branchId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

   public function approveOnBoardingEmployee($uuid, $userID): bool {
    $sql = "UPDATE employees_table SET reg_approve_by = ?,   reg_approve_date = NOW() , status = 'Active' WHERE uuid = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$userID, $uuid]);
}
}

