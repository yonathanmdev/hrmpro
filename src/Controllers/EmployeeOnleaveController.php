<?php
namespace App\Controllers;
use App\Models\EmployeeRegistration;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid; 
use App\Models\Anual_rest_Model;
class EmployeeOnleaveController extends BaseController {
    
 public function showOnLeavePage($params = null) {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    $branch_id = $_SESSION['user']['branch_id'] ?? null;

    $uuid = null;
    if (is_array($params)) {
        $uuid = $params['uuid'] ?? ($params[0] ?? null);
    } elseif (is_string($params)) {
        $uuid = $params;
    }

    if (empty($uuid)) {
        $uriSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $uuid = end($uriSegments);
    }

    $employeeModel = new EmployeeRegistration($this->db);
    $employee = $employeeModel->getEmployeeByUuid($uuid);

    // Check first before using $employee data
    if (!$employee) {
        header("Location: /HRM/dashboard?error=employee_not_found");
        exit;
    }

    $anualRestModel = new Anual_rest_Model($this->db);
    $anualRestData = $anualRestModel->fetchById($uuid); // ← correct

    $this->render('employee-leave', [
        'title'         => 'HRM - የሰራተኞች እረፍት',
        'employee'      => $employee,
        'anualRestData' => $anualRestData
    ]);
}
    public function anualRestRegstration() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         $budget_year = $_POST['budget_year'] ?? null;
         $leave_days = $_POST['leave_days'] ?? null;
         $employee_UUID = $_POST['employee_uuid'] ?? null;
         $registeredBy    = $_SESSION['user']['id']              ?? null;
        // ✅ Fix — null when cloth disabled or empty
        
       // $organization_id = $_SESSION['user']['organization_id'] ?? null;
       // $branch_id       = $_SESSION['user']['branch_id']       ?? null;
        
    }

    if (empty($budget_year) || empty($leave_days) || empty($employee_UUID)) {
        $_SESSION['error'] = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!"."employee id:".$employee_UUID."budget year:".$budget_year."leave days:".$leave_days;
        header("Location: " . $_ENV['BASE_URL'] . "/employee-leave");
        exit();
    }

    $id            = Uuid::uuid4()->toString();
    $annualRestModel = new Anual_rest_Model($this->db);

    try {
        $result = $annualRestModel->insert(
            $id,
            $budget_year,
            $leave_days,
            $employee_UUID,
            $registeredBy
        );

        if ($result) {
            \App\Helpers\AuditHelper::log('anual_rest_registration', 'anual_rest', $id, null, [
                'budget_year'     => $budget_year,
                'leave_days'      => $leave_days,
                'employee_id'     => $employee_UUID,
                'registered_by'   => $registeredBy,
            ]);

                          

            $_SESSION['success'] = " የአመት እረፈቱ በተሳካ ሁኔታ ተመዝግቧል!";
        } else {
            $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
        }

    } catch (\PDOException $e) {
        $_SESSION["error"] = $e->getMessage();
    }

    header("Location: " . $_ENV['BASE_URL'] . "/employee-leave");
    exit();
} 
 

}
      
       
 