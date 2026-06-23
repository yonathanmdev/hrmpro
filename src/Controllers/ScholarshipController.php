<?php
namespace App\Controllers;
use App\Models\ScholarshipModel;
use App\Models\EmployeeRegistration;
use App\Models\User;
use App\Models\EmployeeGuarantor;
use App\Helpers\AuthHelper;
use App\Helpers\EthiopianDateHelper;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;
use DateTime;

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
        $status = 'Approved';

        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new ScholarshipModel($this->db);
            $employees = $employeeModel->getActiveScholarships($organizationId, $branchId, $status);
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
        // 1. Get and sanitize input
        $employee_uuid = $_POST['employee_id'] ?? null;
        $scholarship_type = $_POST['scholarship_type'] ?? null;
        $agreement_date = $_POST['agreement_date'] ?? null;
        $duration = $_POST['scholarship_duration_years'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $is_historical = isset($_POST['is_historical']) ? true : false;
        
        // 2. Perform Validation
        $errors = [];

        if (empty($employee_uuid)) {
            $errors[] = "ሰራተኛ መመረጥ አለበት።";
        }

        if (empty($scholarship_type)) {
            $errors[] = "የስኮላርሺፕ አይነት መመረጥ አለበት።";
        }

        // Validate Agreement Date Format (YYYY-MM-DD) - Always required
        if (empty($agreement_date)) {
            $errors[] = "የውል ቀን መምረጥ አለበት።";
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $agreement_date);
            if (!$d || $d->format('Y-m-d') !== $agreement_date) {
                $errors[] = "ትክክለኛ የውል ቀን ይምረጡ።";
            }
        }

        // Validate Duration
        if (!is_numeric($duration) || (int)$duration < 1) {
            $errors[] = "የቆይታ ጊዜ ትክክለኛ ቁጥር መሆን አለበት (ቢያንስ 1 ዓመት)።";
        }

        // Conditional Validation for End Date based on is_historical
        if ($is_historical) {
            // Historical scholarship: End date is required
            if (empty($end_date)) {
                $errors[] = "የማጠናቀቂያ ቀን መምረጥ አለበት (ያለፈ የትምህርት እድል ነው)።";
            } else {
                $end = DateTime::createFromFormat('Y-m-d', $end_date);
                if (!$end || $end->format('Y-m-d') !== $end_date) {
                    $errors[] = "ትክክለኛ የማጠናቀቂያ ቀን ይምረጡ።";
                } else {
                    // Check if end date is after agreement date
                    if (isset($d) && $d instanceof DateTime && $end <= $d) {
                        $errors[] = "የማጠናቀቂያ ቀን ከውል ቀን በኋላ መሆን አለበት።";
                    }
                }
            }
        } else {
            // Active scholarship: End date should NOT be provided
            if (!empty($end_date)) {
                $errors[] = "ያልተጠናቀቀ የትምህርት እድል ላይ የማጠናቀቂያ ቀን ማስገባት አይቻልም።";
            }
        }

        // File validation - Conditional based on is_historical
        if ($is_historical) {
            // Historical: File is optional, but validate if provided
            if (!empty($_FILES['scholarship_file']['name']) && $_FILES['scholarship_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                $file_type = $_FILES['scholarship_file']['type'];
                if (!in_array($file_type, $allowed_types)) {
                    $errors[] = "ፋይሉ PDF ወይም ምስል (JPG, PNG) ብቻ መሆን አለበት።";
                }
            }
        } else {
            // Active: File is required
            if (empty($_FILES['scholarship_file']['name']) || $_FILES['scholarship_file']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "እባክዎ የስኮላርሺፕ ፋይል ይምረጡ።";
            }
        }

        // 3. Handle Errors
        if (!empty($errors)) {
            $_SESSION['error'] = $errors;
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $registered_by = $_SESSION['user']['id'] ?? null;

        // 4. Upload File (only if file is provided)
        $scholarshipFileName = null;
        if (!empty($_FILES['scholarship_file']['name']) && $_FILES['scholarship_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $scholarshipFileName = $this->uploadFile('scholarship_file', 'documents');
            
            if (!$scholarshipFileName) {
                $fileError = $_FILES['scholarship_file']['error'] ?? UPLOAD_ERR_NO_FILE;
                $_SESSION['error'] = 'የስኮላርሺፕ ፋይሉን መጫን አልተቻለም። ስህተት፡ ' . $this->getUploadErrorMessage($fileError);
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        }

        // 5. Prepare data for database
        $scholarshipData = [
            'id' => Uuid::uuid4()->toString(),
            'employee_id' => $employee_uuid,
            'agreement_date' => $agreement_date,
            'scholarship_type' => $scholarship_type,
            'duration' => $duration,
            'registered_by' => $registered_by,
            'is_historical' => $is_historical ? 1 : 0
        ];

        // Add end_date only if it exists (for historical records)
        if ($is_historical && !empty($end_date)) {
            $scholarshipData['end_date'] = $end_date;
        }

        // Prepare document data only if file was uploaded
        $documentData = null;
        if ($scholarshipFileName !== null) {
            $documentData = [
                'id' => Uuid::uuid4()->toString(),
                'doc_id' => Uuid::uuid4()->toString(),
                'file_url' => $scholarshipFileName,
                'document_type' => 'Scholarship Agreement'
            ];
        }

        // 6. Insert into database
        try {
            $model = new ScholarshipModel($this->db);
            $result = $model->saveScholarshipWithDocument($scholarshipData, $documentData);

            if ($result) {
                $_SESSION['success'] = 'የስኮላርሺፕ መረጃው በትክክል ተመዝግቧል!';
                header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-onleave");
                exit();
            } else {
                throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
            }

        } catch (\Exception $e) {
            // Cleanup: Delete uploaded file if database insertion fails
            if ($scholarshipFileName !== null) {
                $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $scholarshipFileName;
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

public function showScholarshipReturnees() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    $user = $_SESSION['user'] ?? [];
    $branchId = $user['branch_id'] ?? null;
    $organizationId = $user['organization_id'] ?? null;
    $employees = [];
    
    if ($organizationId && $branchId) {
        $scholarshipModel = new ScholarshipModel($this->db);
        $employees = $scholarshipModel->getCompletedScholarships($organizationId, $branchId);
    }

    $data = [
        'title' => 'HRM - የተጠናቀቁ የትምህርት እድሎች',
        'user'  => $user,
        'employees' => $employees,
    ];

    $this->render('employee-scholarship-returnee', $data);
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

public function showScholarshipJson(): void
{
    header('Content-Type: application/json');
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    // Get record_id from request
    $recordId = $_GET['record_id'] ?? null;

    // Validate record_id
    if (!$recordId) {
        echo json_encode(['status' => 'error', 'message' => 'መለያ ቁጥር አልተገኘም።']);
        return;
    }

    // Get scholarship details by record_id
    $scholarshipModel = new ScholarshipModel($this->db);
    $scholarship = $scholarshipModel->getScholarshipDetailsById($recordId);

    if (!$scholarship) {
        echo json_encode(['status' => 'error', 'message' => 'መረጃ አልተገኘም።']);
        return;
    }

    // Initialize employee data with defaults
    $employeeData = [
        'employee_id' => '',
        'emp_id' => $scholarship['emp_id'] ?? '',
        'first_name' => '',
        'father_name' => '',
        'g_father_name' => '',
        'job_name' => 'N/A',
        'sex' => ''
    ];

    // Get employee information ONLY if employee_uuid exists and is not null
    if (!empty($scholarship['emp_id'])) {
        try {
            $employeeModel = new EmployeeRegistration($this->db);
            $employee = $employeeModel->getEmployeeByUuid($scholarship['emp_id']);
            
            if ($employee && is_array($employee)) {
                $employeeData['employee_id'] = $employee['employee_id'] ?? '';
                $employeeData['first_name'] = $employee['first_name'] ?? '';
                $employeeData['father_name'] = $employee['father_name'] ?? '';
                $employeeData['g_father_name'] = $employee['g_father_name'] ?? '';
                $employeeData['job_name'] = $employee['job_name'] ?? 'N/A';
                $employeeData['sex'] = $employee['sex'] ?? '';
            }
        } catch (\Exception $e) {
            // Log error but continue with default data
            error_log('Error fetching employee: ' . $e->getMessage());
        }
    }

    // Format Ethiopian dates
    $ethStartDate = '';
    if (!empty($scholarship['agreement_date'])) {
        $parts = explode('-', $scholarship['agreement_date']);
        if (count($parts) == 3) {
            $ethDate = EthiopianDateHelper::toEthCalendar($parts[2], $parts[1], $parts[0]);
            $ethStartDate = EthiopianDateHelper::getMonthName($ethDate['month']) . ' ' . 
                           $ethDate['day'] . ' ' . $ethDate['year'];
        }
    }
    
    $ethEndDate = '';
    if (!empty($scholarship['end_date'])) {
        $parts = explode('-', $scholarship['end_date']);
        if (count($parts) == 3) {
            $ethDate = EthiopianDateHelper::toEthCalendar($parts[2], $parts[1], $parts[0]);
            $ethEndDate = EthiopianDateHelper::getMonthName($ethDate['month']) . ' ' . 
                         $ethDate['day'] . ' ' . $ethDate['year'];
        }
    } else {
        $ethEndDate = ($scholarship['is_historical'] ?? 0) == 1 ? 'ያለፈ ታሪካዊ' : 'በሂደት ላይ';
    }

    // Build response data
    $responseData = [
        'record_id' => $scholarship['record_id'],
        'uuid' => $scholarship['emp_id'] ?? '',
        'employee_id' => $employeeData['employee_id'],
        'full_name' => trim(($employeeData['first_name'] ?? '') . ' ' . 
                           ($employeeData['father_name'] ?? '') . ' ' . 
                           ($employeeData['g_father_name'] ?? '')),
        'job_name' => $employeeData['job_name'],
        'sex' => $employeeData['sex'],
        'agreement_date' => $scholarship['agreement_date'] ?? '',
        'end_date' => $scholarship['end_date'] ?? '',
        'scholarship_type' => $scholarship['scholarship_type'] ?? '',
        'scholarship_duration_years' => $scholarship['scholarship_duration_years'] ?? '',
        'is_historical' => $scholarship['is_historical'] ?? 0,
        'file_url' => $scholarship['file_url'] ?? '',
        'eth_start_date' => $ethStartDate,
        'eth_end_date' => $ethEndDate
    ];

    echo json_encode(['status' => 'success', 'data' => $responseData]);
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

     $guarantorModel = new EmployeeGuarantor($this->db);
    // Directly passing the UUID string
    $employeeGuarantor = $guarantorModel->getByEmployeeId($uuid);

    $data = [
        'title' => 'HRM - የሰራተኛ ማህደር',
        'documentData' => $documentData,
        'employee' => $employee,
        'employeeGuarantor' => $employeeGuarantor,
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
public function storeReturnScholarship() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit();
    }

    $file_url = null;

    try {
        $employee_uuid  = $_POST['employee_uuid'] ?? null;
        $record_id      = $_POST['record_id'] ?? null;
        $return_date    = $_POST['return_date'] ?? null;
        $agreement_date = $_POST['agreement_date'] ?? null;
        $registered_by  = $_SESSION['user']['id'] ?? null;

        // Validation: Ensure required fields are present
        if (!$employee_uuid || !$record_id || !$return_date || !$agreement_date || !isset($_FILES['tempo_file'])) {
            throw new \Exception("ሁሉም አስፈላጊ መረጃዎች አልተሟሉም።");
        }

        // --- SERVER-SIDE DATE VALIDATION ---
        $today = date('Y-m-d');

        if ($return_date > $today) {
            throw new \Exception("የተመለሰበት ቀን ከዛሬ ቀን በኋላ መሆን አይችልም።");
        }

        if ($return_date < $agreement_date) {
            throw new \Exception("የተመለሰበት ቀን ከውል ቀን በፊት መሆን አይችልም።");
        }

      

        $model   = new ScholarshipModel($this->db);
        $oldData = $model->getScholarshipDetailsById($record_id);

        if (!$oldData) {
            throw new \Exception("የስኮላርሺፕ መረጃ አልተገኘም።");
        }

        // Handle File Upload
        if ($_FILES['tempo_file']['error'] === UPLOAD_ERR_OK) {
            $file_url = $this->uploadFile('tempo_file', 'documents');
        }

        if (!$file_url) {
            throw new \Exception("የፋይል ማያያዝ ስህተት ተፈጥሯል።");
        }

        // Prepare data for update — no new 'id' here, record_id already
        // identifies the row being updated.
        $updateData = [
            'id'             => uuid::uuid4()->toString(),
            'record_id'      => $record_id,
            'emp_id'         => $employee_uuid,
            'agreement_date' => $agreement_date,
            'return_date'    => $return_date,
            'file_url'       => $file_url,
            'registered_by'  => $registered_by,
        ];

        $result = $model->updateScholarshipReturn($updateData);

        if ($result) {
            \App\Helpers\AuditHelper::log(
                action: 'employee_scholarship_returned',
                entityType: 'employee',
                entityId: $employee_uuid,
                oldValues: $oldData,
                newValues: $updateData,
                metadata: ['change_type' => 'scholarship_returned']
            );

            echo json_encode(['success' => true, 'message' => 'የትምህርት ተመላሽ መረጃው በትክክል ተመዝግቧል!']);
            exit();
        }

        throw new \Exception("ዳታቤዝ ላይ መረጃውን ማስቀመጥ አልተቻለም።");

    } catch (\Exception $e) {
        // Clean up the uploaded file via the trait, not a hand-rolled path,
        // so this stays correct if the upload directory structure changes.
        if ($file_url) {
            $this->cleanupUploadedFile($file_url, 'documents');
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
public function updateScholarshipReturnee() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (!empty($_POST['ajax']) && $_POST['ajax'] == '1');

    $respondError = function(string $message) use ($isAjax) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }
        $_SESSION['error'] = $message;
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    };

    $respondSuccess = function(string $message) use ($isAjax) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            exit();
        }
        $_SESSION['success'] = $message;
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship-returnee");
        exit();
    };

    $employee_uuid = $_POST['employee_id'] ?? null;
    $record_id = $_POST['record_id'] ?? null;
    $scholarship_type = $_POST['scholarship_type'] ?? null;
    $agreement_date = $_POST['agreement_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $duration = $_POST['scholarship_duration_years'] ?? null;
    $registered_by = $_SESSION['user']['id'] ?? null;
    $is_historical = $_POST['is_historical'] ?? 0;
    $update_file_only = $_POST['update_file_only'] ?? '0';

    $model = new ScholarshipModel($this->db);
    $oldData = $model->getScholarshipDetailsById($record_id);

    if (!$oldData) {
        $respondError('መረጃ አልተገኘም።');
    }

    if (empty($employee_uuid)) {
        $employee_uuid = $oldData['employee_id'] ?? null;
    }

    // ── VALIDATION ───────────────────────────────────────────────────────

    if ($is_historical == 1) {
        // Historical returnee records: all fields editable
        if (empty($scholarship_type)) {
            $respondError('የትምህርት እድል ዓይነት ያስፈልጋል።');
        }

        if (empty($agreement_date) || !strtotime($agreement_date)) {
            $respondError('የት/ት የሄዱበት ቀን ትክክለኛ አይደለም።');
        }

        if (!empty($end_date) && !strtotime($end_date)) {
            $respondError('የተመለሱበት ቀን ትክክለኛ አይደለም።');
        }

        if (!empty($end_date) && strtotime($end_date) < strtotime($agreement_date)) {
            $respondError('የተመለሱበት ቀን ከት/ት ከሄዱበት ቀን በኋላ መሆን አለበት።');
        }

    } else {
        // Non-historical returnee records: only end_date and file are editable
        $scholarship_type = $oldData['scholarship_type'] ?? $scholarship_type;
        $agreement_date = $oldData['agreement_date'] ?? $agreement_date;

        if (!empty($end_date)) {
            if (!strtotime($end_date)) {
                $respondError('የተመለሱበት ቀን ትክክለኛ አይደለም።');
            }

            if (strtotime($end_date) < strtotime($oldData['agreement_date'])) {
                $respondError('የተመለሱበት ቀን ከት/ት ከሄዱበት ቀን በኋላ መሆን አለበት።');
            }

        } else {
            $end_date = $oldData['end_date'] ?? null;
        }
    }

    // ── FILE UPLOAD (always allowed) ────────────────────────────────────
    
    // Upload the file - this returns a string (filename) or null
    $file_url = $this->uploadFile('scholarship_file', 'documents');
    
    // Determine if a new file was actually uploaded
    $newFileUploaded = !empty($file_url);

    // Check if file upload failed when a file was selected
    if (empty($_FILES['scholarship_file']['name']) || $_FILES['scholarship_file']['error'] === UPLOAD_ERR_NO_FILE) {
        // No file uploaded - this is fine, keep existing file
        $file_url = null;
        $newFileUploaded = false;
    } elseif (!$file_url) {
        // File was selected but upload failed
        $messages = [];
        if (!$file_url) {
            $messages[] = 'File: ' . $this->getUploadErrorMessage($_FILES['scholarship_file']['error'] ?? UPLOAD_ERR_NO_FILE);
        }
        $respondError('ፋይል ሊያያዝ አልቻለም። ' . implode(' | ', $messages));
        exit();
    }

    // ── PREPARE DATA ─────────────────────────────────────────────────────

    $scholarshipData = [
        'record_id' => $record_id,
        'employee_id' => $employee_uuid,
        'scholarship_type' => $scholarship_type,
        'agreement_date' => $agreement_date,
        'end_date' => $end_date,
        'duration' => $duration ?? $oldData['scholarship_duration_years'] ?? null,
        'registered_by' => $registered_by,
        'file_url' => $file_url,
        'is_historical' => $is_historical,
        'update_file_only' => $update_file_only,
        'old_agreement_date' => $oldData['agreement_date'] ?? null,
        'old_end_date' => $oldData['end_date'] ?? null,
    ];

    try {
        $result = $model->updateScholarshipReturnee($scholarshipData);

        // Check if the result is an array (success/error response from model)
        if (is_array($result)) {
            if ($result['success'] === false) {
                // Clean up uploaded file if there was an error
                if ($newFileUploaded && $file_url) {
                    $this->cleanupUploadedFile($newFileUploaded, $file_url);
                }
                $respondError($result['message']);
                return;
            }
        }

        if ($result['success'] ?? false) {
            $newData = [
                'scholarship_type' => $scholarship_type,
                'agreement_date' => $agreement_date,
                'end_date' => $end_date,
                'duration' => $scholarshipData['duration'],
                'file_url' => $file_url ?? $oldData['file_url'] ?? null,
            ];
            \App\Helpers\AuditHelper::log('employee_scholarship_updated', 'employee', $employee_uuid, $oldData, $newData, ['change_type' => 'scholarship_returnee_updated']);

            $respondSuccess('የት/ት መረጃው በትክክል ተስተካክሏል!');
            return;
        }

        // If we get here, something went wrong
        if ($newFileUploaded && $file_url) {
            $this->cleanupUploadedFile($newFileUploaded, $file_url);
        }
        throw new \Exception("ዳታቤዝ ላይ መስተካከል አልተቻለም።");

    } catch (\Exception $e) {
        // Clean up uploaded file if there was an error
        if ($newFileUploaded && $file_url) {
            $this->cleanupUploadedFile($newFileUploaded, $file_url);
        }
        $respondError('ስህተት ተፈጥሯል፡ ' . $e->getMessage());
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