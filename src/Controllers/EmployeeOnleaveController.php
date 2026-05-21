<?php
namespace App\Controllers;
use App\Models\EmployeeRegistration;
use App\Helpers\AuthHelper;

class EmployeeOnleaveController extends BaseController {
    
    public function showOnLeavePage() {
 AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $branch_id = $_SESSION['user']['branch_id'] ?? null;
        
            $employeeModel        = new EmployeeRegistration($this->db);
        // Pass everything to the view package template
        $data = [
            'title'                => 'HRM - የሰራተኞች እረፍት',
        ];

        $this->render('employee-leave', $data);
    }
}