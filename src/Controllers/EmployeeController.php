<?php
namespace App\Controllers;
use App\Models\Employee;
use App\Models\Branch;  
use App\Models\JobProperty;
// 1. BaseControllerን እንዲወርስ (Extends) እናደርጋለን
class EmployeeController extends BaseController {   
    /**
     * የዳሽቦርድ ዋና ገጽ
     */
  
    public function showemployee() {
        // 2. ለዳሽቦርዱ የሚያስፈልጉ ዳታዎችን እዚህ ማዘጋጀት ትችላለን
        // ለምሳሌ፡ የተመዘገቡ ተጠቃሚዎችን ብዛት ከሞዴል መጥራት ትችላለህ
        $employeeModel = new Employee($this->db);
        $employees = $employeeModel->getAllEmployees();

        $branchModel = new Branch($this->db);
        $branches = $branchModel->getAllBranches();

        $jobPropertyModel = new JobProperty($this->db);
        $jobs = $jobPropertyModel->getAllJobProperties();

        $data = [
            'title' => 'HRM - ሰራተኛ መመዝገቢያ',
            'user'  => $_SESSION['user'] ?? null,
            'employees' => $employees,
            'branches' => $branches,
            'jobs' => $jobs
            // 'total_users' => $userModel->countAll(), // ወደፊት የምንጨምረው
        ];

        // 3. BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        // 'dashboard' ማለት views/dashboard.php ማለት ነው
        $this->render('register-employee', $data);
    }
}
?>