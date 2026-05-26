<?php
namespace App\Controllers;
use App\Helpers\AuthHelper;
use App\Helpers\EthiopianDateHelper; 
use App\Models\Anual_rest_Model;
use App\Models\User;
use App\Models\EmployeeRegistration;
use Ramsey\Uuid\Uuid;  
class AnualRestController {
    private $anualRestModel;
    private $userModel;
    private $employeeRegistrationModel;

    public function __construct($db) {
        $this->anualRestModel = new Anual_rest_Model($db);
        $this->userModel = new User($db);
        $this->employeeRegistrationModel = new EmployeeRegistration($db);
    }

    public function index($employee_uuid) {
        $employee = $this->employeeRegistrationModel->getEmployeeByUuid($employee_uuid);
        if (!$employee) {
            http_response_code(404);
            echo "Employee not found.";
            return;
        }
        include __DIR__ . '/../views/employee-leave.php';
    } 
    public function create($employee_uuid) {
        $employee = $this->anualRestModel->getEmployeeAnualRest($employee_uuid);
        if (!$employee) {
            http_response_code(404);
            echo "Employee not found.";
            return;
        }
        include __DIR__ . '/../views/add-annual-leave.php';
    }   
    public function update($employee_uuid, $data) {
        $employee = $this->employeeRegistrationModel->getEmployeeByUuid($employee_uuid);
        if (!$employee) {
            http_response_code(404);
            echo "Employee not found.";
            return;
        }
        $data['registered_by'] = AuthHelper::getCurrentUserId();
        $data['registered_date'] = EthiopianDateHelper::getCurrentEthiopianDate();
        $success = $this->anualRestModel->updateEmployeeAnualRest($employee_uuid, $data);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Annual rest updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update annual rest.']);
        }
    }
    public function delete($employee_uuid) {
        $employee = $this->employeeRegistrationModel->getEmployeeByUuid($employee_uuid);
        if (!$employee) {
            http_response_code(404);
            echo "Employee not found.";
            return;
        }
        $success = $this->anualRestModel->deleteEmployeeAnualRest($employee_uuid);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Annual rest deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete annual rest.']);
        }
    }       
    public function updateEmployeeAnualRest($employee_uuid, $data) {
        $employee = $this->employeeRegistrationModel->getEmployeeByUuid($employee_uuid);
        if (!$employee) {
            http_response_code(404);
            echo "Employee not found.";
            return;
        }
        $data['registered_by'] = AuthHelper::getCurrentUserId();
        $data['registered_date'] = EthiopianDateHelper::getCurrentEthiopianDate();
        $success = $this->anualRestModel->updateEmployeeAnualRest($employee_uuid, $data);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Annual rest updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update annual rest.']);
        }
    }
}