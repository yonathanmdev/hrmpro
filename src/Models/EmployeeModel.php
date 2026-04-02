<?php
namespace App\Models;

class Employee {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    public function createEmployee($data) {
        $sql = "INSERT INTO employees (
            uuid, employee_id, first_name, father_name, g_father_name, mother_name, 
            sex, birth_date, phone_number, yegabcha_huneta, organization_id, 
            branch_id, job_property_id, date_of_employed, level_of_education, 
            department, employment_situation, immidate_boss, experience, 
            annual_rest, displin_situation, competency_situation, effeciency, 
            level_of_effeciency, no_of_files_in_folder, pention_withdrawal, 
            employee_image, employee_file201, remark, status, reg_by
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $this->db->prepare($sql);
        
        // 31 parameters ('s' for strings/mixed, 'i' for integers)
        $stmt->bind_param("ssssssssssssssssssssssssissssss", 
            $data['uuid'], $data['employee_id'], $data['first_name'], $data['father_name'], 
            $data['g_father_name'], $data['mother_name'], $data['sex'], $data['birth_date'], 
            $data['phone_number'], $data['yegabcha_huneta'], $data['organization_id'], 
            $data['branch_id'], $data['job_property_id'], $data['date_of_employed'], 
            $data['level_of_education'], $data['department'], $data['employment_situation'], 
            $data['immidate_boss'], $data['experience'], $data['annual_rest'], 
            $data['displin_situation'], $data['competency_situation'], $data['effeciency'], 
            $data['level_of_effeciency'], $data['no_of_files_in_folder'], $data['pention_withdrawal'], 
            $data['employee_image'], $data['employee_file201'], $data['remark'], 
            $data['status'], $data['reg_by']
        );

        return $stmt->execute();
    }
}