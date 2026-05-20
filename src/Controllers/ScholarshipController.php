<?php
namespace App\Controllers;
use App\Models\ScholarshipModel;
use App\Models\EmployeeRegistration;
use App\Models\User;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;

class ScholarshipController extends BaseController {
    use FileUploadTrait;

    public function showScholarshipForm() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;

        $jobs = [];
        if ($branchId) {
            $positionModel = new \App\Models\Position($this->db);
            $jobs = $positionModel->getActiveJobsByBranch($branchId);
        }

        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new ScholarshipModel($this->db);
            $employees = $employeeModel->onLeaveEmployees($organizationId, $branchId);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ መመዝገቢያ',
            'user'  => $user,
            'jobs'  => $jobs,
            'employees' => $employees,
        ];

        $this->render('employee-scholarship', $data);
    }
    public function liveSearch() {
    // 1. ማንኛውንም ቀድሞ የወጣ Output (Warning/Notice) ለማጽዳት
    if (ob_get_length()) ob_clean();

    // 2. Role Check - ማሳሰቢያ፡ checkRole ስህተት ሲያገኝ JSON እንዲመልስ ማድረግ አለብህ
    // ካልሆነ ግን ተጠቃሚው ገብቶ ካልሆነ የሚመጣው HTML ለ JS ስህተት ይፈጥራል
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    
    $term = $_GET['query'] ?? '';
    $branchId = $_SESSION['user']['branch_id'] ?? null;

    // 3. Headerን ቀድሞ መላክ (ለደህንነት)
    header('Content-Type: application/json');

    if (!$branchId || empty($term)) {
        echo json_encode([]);
        exit;
    }

    try {
        $employeeModel = new ScholarshipModel($this->db);
        $results = $employeeModel->autoSearch($term, $branchId);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
}
public function storeScholarship() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1. መረጃዎችን መቀበል
        $employee_uuid = $_POST['employee_id'] ?? null;
        $scholarship_type = $_POST['scholarship_type'] ?? null;
        $agreement_date = $_POST['agreement_date'] ?? null;
        $duration = $_POST['scholarship_duration_years'] ?? null;
        $registered_by = $_SESSION['user']['id'] ?? null;
// ADD THIS
if (empty($_FILES['scholarship_file']['name']) || $_FILES['scholarship_file']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['error'] = 'እባክዎ የስኮላርሺፕ ፋይል ይምረጡ።';
    header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
    exit();
}
        // 2. ፋይሉን መጫን (በከፈትከው uploadFile ፈንክሽን በመጠቀም)
        // ማሳሰቢያ፡ በ HTML ፎርምህ ላይ የፋይሉ ስም 'scholarship_file' መሆኑን አረጋግጥ
        $scholarshipFileName = $this->uploadFile('scholarship_file', 'documents');

        if (!$scholarshipFileName) {
            $fileError = $_FILES['scholarship_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $_SESSION['error'] = 'የስኮላርሺፕ ፋይሉን መጫን አልተቻለም። ስህተት፡ ' . $this->getUploadErrorMessage($fileError);
            header("Location: " . $_SERVER['HTTP_REFERER']); // ወደ መጣህበት ይመልሰሃል
            exit();
        }

        // በዳታቤዝ ውስጥ የሚቀመጠው የፋይሉ ሙሉ ፓዝ (ለ document_url)

        // 3. ዳታዎችን ማዘጋጀት
        $scholarshipData = [
            'id' => Uuid::uuid4()->toString(),
            'employee_id' => $employee_uuid,
            'agreement_date' => $agreement_date,
            'scholarship_type' => $scholarship_type,
            'duration' => $duration,
            'registered_by' => $registered_by
        ];

        $documentData = [
            'id' => Uuid::uuid4()->toString(), // ['id'],
            'doc_id' => Uuid::uuid4()->toString(), // ['id'],
            'file_url' => $scholarshipFileName,
            'document_type' => 'Scholarship Agreement'
        ];

        // 4. ወደ ዳታቤዝ ማስገባት (Transaction)
        try {
            $model = new ScholarshipModel($this->db);
            $result = $model->saveScholarshipWithDocument($scholarshipData, $documentData);

            if ($result) {
                $_SESSION['success'] = 'የስኮላርሺፕ መረጃው በትክክል ተመዝግቧል!';
                header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave"); // ወይም የፈለግከው ቦታ
                exit();
            } else {
                throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
            }

        } catch (\Exception $e) {
            // 5. ዳታቤዝ ላይ ካልተመዘገበ የተጫነውን ፋይል ሰርቨር ላይ ማጥፋት (Cleanup)
            $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $scholarshipFileName;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $e->getMessage();
            die($e->getMessage());
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
}
public function onLeaveScholarshipEmployees() {
    $user = $_SESSION['user'] ?? [];
    $organizationId = $user['organization_id'] ?? null;
    $branchId = $user['branch_id'] ?? null;
    AuthHelper::checkRole(['hr_director']);
    $employeeModel = new ScholarshipModel($this->db);
    $count = $employeeModel->countPendingScholarshipEmployees($organizationId, $branchId);

    header('Content-Type: application/json'); // <-- must be here
    echo json_encode(['count' => $count]);
    exit(); // <-- add this to stop any extra output
}
 public function showScholarshiponLeavePending() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;
        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new ScholarshipModel($this->db);
            $employees = $employeeModel->onLeavePendingEmployees($organizationId, $branchId);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ የትምህርት እድል',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-scholarship-onleave', $data);
    }
public function getScholarshipDetails($params = []) {
     AuthHelper::checkRole(['hr_director', 'hr_officer']);
     $uuId = $params['uuid'] ?? $_GET['uuid'] ?? null;
     $scholarshipId = $params['record_id'] ?? $_GET['record_id'] ?? null;

    if (!$scholarshipId) {
        die("Scholarship ID is missing.");
    }
    $model = new ScholarshipModel($this->db);
    $scholarship = $model->getScholarshipDetails($scholarshipId);
     $user = $_SESSION['user'] ?? [];
       $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($uuId);
   
    $data = [
            'title' => 'HRM - የሰራተኛ የትምህርት እድል',
            'scholarship' => $scholarship,
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-scholarship-onleave-views', $data);
}
     public function handleOnLeaveApproval() {
           AuthHelper::checkRole(['hr_director']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
            exit();
        }

        $uuid = $_POST['uuid'] ?? null;
         $scholarship_uuid = $_POST['scholarship_uuid'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $userID = $user['id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId || empty($user['id'])) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
            exit();
        }


        // Get current employee to check for job change
        $employeeModel = new ScholarshipModel($this->db);
        if ($employeeModel->approveOnLeaveEmployee($uuid, $scholarship_uuid, $userID)) {
            
               \App\Helpers\AuditHelper::log('employee_scholarship_approved', 'employee', $uuid, null, [], ['change_type' => 'Scholarship_approved']);
          

            $_SESSION['success'] = 'ሰራተኛው መረጃ በትክክል ተስተካከለ።';
        } else {
            $_SESSION['error'] = 'የሰራተኛ ማስተካከያ ሂደት አልተሳካም።';
        }

        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
        exit();
    }
   public function getDocument($params = []) {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    $uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;

    if (!$uuid) {
        die("Missing identifier.");
    }

    $employeeModel = new EmployeeRegistration($this->db);
    $employee = $employeeModel->getEmployeeByUuid($uuid);

    $model = new ScholarshipModel($this->db);
    // Directly passing the UUID string
    $documentData = $model->getDocumentByEmpId($uuid);

    $data = [
        'title' => 'HRM - የሰራተኛ ማህደር',
        'documentData' => $documentData,
        'employee' => $employee
    ];

    $this->render('employee-archive', $data);
}

    public function showScholarshipEdit($params = []) {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);
        
        $employee_uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;
        $recordId = $params['record_id'] ?? $_GET['record_id'] ?? null;

        if (!$recordId) {
            die("Scholarship ID is missing.");
        }

        $model = new ScholarshipModel($this->db);
        $scholarship = $model->getScholarshipDetailsById($recordId);
        
        $user = $_SESSION['user'] ?? [];
        $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($employee_uuid);

        $data = [
            'title' => 'HRM - የት/ት ማስተካከያ',
            'scholarship' => $scholarship,
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-scholarship-edit', $data);
    }

    public function updateScholarship() {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employee_uuid = $_POST['employee_id'] ?? null;
            $record_id = $_POST['record_id'] ?? null;
            $scholarship_type = $_POST['scholarship_type'] ?? null;
            $agreement_date = $_POST['agreement_date'] ?? null;
            $duration = $_POST['scholarship_duration_years'] ?? null;
            $registered_by = $_SESSION['user']['id'] ?? null;

            $file_url = null;
            $newFileUploaded = false;

            // Get old data for audit log before updating
            $model = new ScholarshipModel($this->db);
            $oldData = $model->getScholarshipDetailsById($record_id);

            // Check if a new file is being uploaded
            if (isset($_FILES['scholarship_file']) && $_FILES['scholarship_file']['error'] === UPLOAD_ERR_OK) {
                $scholarshipFileName = $this->uploadFile('scholarship_file', 'documents');
                if ($scholarshipFileName) {
                    $file_url = $scholarshipFileName;
                    $newFileUploaded = true;
                }
            }

            $scholarshipData = [
                'record_id' => $record_id,
                'employee_id' => $employee_uuid,
                'scholarship_type' => $scholarship_type,
                'agreement_date' => $agreement_date,
                'duration' => $duration,
                'registered_by' => $registered_by,
                'file_url' => $file_url,
                'doc_id' => Uuid::uuid4()->toString(),
                'assignment_id' => Uuid::uuid4()->toString(),
            ];

            try {
                $result = $model->updateScholarship($scholarshipData);

                if ($result) {
                    // Log audit for the update
                    $newData = [
                        'scholarship_type' => $scholarship_type,
                        'agreement_date' => $agreement_date,
                        'duration' => $duration,
                        'file_url' => $file_url ?? $oldData['file_url'] ?? null
                    ];
                    \App\Helpers\AuditHelper::log('employee_scholarship_updated', 'employee', $employee_uuid, $oldData, $newData, ['change_type' => 'scholarship_updated']);

                    $_SESSION['success'] = 'የት/ት መረጃው በትክክል ተስተካክሏል!';
                    header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
                    exit();
                } else {
                    throw new \Exception("ዳታቤዝ ላይ መስተካከል አልተቻለም።");
                }

            } catch (\Exception $e) {
                // Cleanup if new file was uploaded
                if ($newFileUploaded && $file_url) {
                    $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $file_url;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }

                $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $e->getMessage();
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        }
    }

    public function delete(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    $data    = json_decode(file_get_contents('php://input'), true);
    $id      = trim((string) ($data['id'] ?? ''));
    $reason = trim($data['reason']      ?? '');
    $password = $data['confirm_password'] ?? '';
    $source = 'INDIVIDUAL';
    $adminId = (string) ($_SESSION['user']['id'] ?? '');

     // Validate input
        if (!$id || !$reason || !$password || !$source) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'ሁሉም መስኮች አስፈላጊ ናቸው።'
            ]);
            return;
        }

        $user           = $_SESSION['user'] ?? [];
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'ያልተፈቀደ ድርጊት።'
            ]);
            return;
        }

        // Verify password
        $userModel = new User($this->db);
        if (!$userModel->verifyPassword($user['id'], $password)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'ፓስዋርዱ ትክክል አይደለም።'
            ]);
            return;
        }

    try {
        $model  = new ScholarshipModel($this->db);
        $result = $model->deleteRecord($id, $adminId, $reason, $source);

        if ($result['status'] === 'success') {
            \App\Helpers\AuditHelper::log(
                action:     'scholarship_deleted',
                entityType: 'scholarship',
                entityId:   $id,
                oldValues:  $result['oldRecord'],   // snapshot of deleted record
                newValues:  null,                   // nothing after delete
                metadata:   [
                    'deleted_type'          => 'soft',
                    'deleted_documents'     => $result['deletedDocumentCount'] ?? 0,
                    'deletion_source'       => 'INDIVIDUAL_ACTION',
                    'reason'          => $reason,
                    'performed_by'          => $adminId,
                ]
            );

            // strip internal fields before sending to client
            unset($result['oldRecord'], $result['deletedDocumentCount']);
        }

        echo json_encode($result);

    } catch (\Exception $e) {
        error_log('ScholarshipController::delete - ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።']);
    }
}
}