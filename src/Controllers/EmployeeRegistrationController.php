<?php
namespace App\Controllers;
use DateTime;
use App\Models\EmployeeRegistration;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;

class EmployeeRegistrationController extends BaseController {
    public function showForm() {
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
            $employeeModel = new EmployeeRegistration($this->db);
            $employees = $employeeModel->getEmployeesByBranch($organizationId, $branchId);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ መመዝገቢያ',
            'user'  => $user,
            'jobs'  => $jobs,
            'employees' => $employees,
        ];

        $this->render('employee-registration', $data);
    }


    public function showEditForm() {
           AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $uuid = $_GET['uuid'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;

        if (!$organizationId || !$branchId) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($uuid);

        if (!$employee) {
            $_SESSION['error'] = 'ሰራተኛ አልተገኘም።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        // Get available jobs (active + current job)
        $availableJobs = $employeeModel->getAvailableJobsByBranch($branchId, $employee['job_property_id']);

        $data = [
            'title' => 'HRM - የሰራተኛ ማስተካከያ',
            'user'  => $user,
            'employee' => $employee,
            'availableJobs' => $availableJobs,
        ];

        $this->render('employee-edit', $data);
    }

    public function handleEdit() {
           AuthHelper::checkRole(['hr_director', 'hr_officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $uuid = $_POST['uuid'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId || empty($user['id'])) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        // Server-side validation
        $validationErrors = $this->validateEmployeeData($_POST);
        if (!empty($validationErrors)) {
            $_SESSION['error'] = implode('<br>', $validationErrors);
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        // Get current employee to check for job change
        $employeeModel = new EmployeeRegistration($this->db);
        $currentEmployee = $employeeModel->getEmployeeByUuid($uuid);

        if (!$currentEmployee) {
            $_SESSION['error'] = 'ሰራተኛ አልተገኘም።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $oldJobId = $currentEmployee['job_property_id'];
        $newJobId = trim($_POST['job_property_id'] ?? '');

        // Handle file uploads (optional for editing)
        $imageName = $this->uploadFile('employee_image', 'images');
        $file201Name = $this->uploadFile('employee_file201', 'documents');

        // Use existing files if no new files uploaded
        if (!$imageName) {
            $imageName = $currentEmployee['employee_image'];
        }
        if (!$file201Name) {
            $file201Name = $currentEmployee['employee_file201'];
        }

        $data = [
            'employee_id' => trim($_POST['employee_id'] ?? ''),
            'pension_number' => trim($_POST['pension_number'] ?? null) ?: null,
            'first_name' => trim($_POST['first_name'] ?? ''),
            'father_name' => trim($_POST['father_name'] ?? ''),
            'g_father_name' => trim($_POST['g_father_name'] ?? ''),
            'mother_name' => trim($_POST['mother_name'] ?? ''),
            'sex' => $_POST['sex'] ?? 'Male',
            'birth_date' => trim($_POST['birth_date'] ?? null) ?: null,
            'phone_number' => trim($_POST['phone_number'] ?? null) ?: null,
            'yegabcha_huneta' => trim($_POST['yegabcha_huneta'] ?? ''),
            'job_property_id' => $newJobId,
            'date_of_employed' => trim($_POST['date_of_employed'] ?? null) ?: null,
            'level_of_education' => trim($_POST['level_of_education'] ?? ''),
            'department' => trim($_POST['department'] ?? null) ?: null,
            'employment_situation' => trim($_POST['employment_situation'] ?? ''),
            'immidate_boss' => trim($_POST['immidate_boss'] ?? null) ?: null,
            'experience' => trim($_POST['experience'] ?? null) ?: null,
            'annual_rest' => isset($_POST['annual_rest']) ? (int) $_POST['annual_rest'] : 0,
            'displin_situation' => trim($_POST['displin_situation'] ?? ''),
            'competency_situation' => trim($_POST['competency_situation'] ?? null) ?: null,
            'effeciency' => $this->normalizeDecimal($_POST['effeciency'] ?? null),
            'level_of_effeciency' => trim($_POST['level_of_effeciency'] ?? null) ?: null,
            'no_of_files_in_folder' => isset($_POST['no_of_files_in_folder']) ? (int) $_POST['no_of_files_in_folder'] : 0,
            'employee_image' => $imageName,
            'employee_file201' => $file201Name,
            'remark' => trim($_POST['remark'] ?? null) ?: null,
        ];

        if ($employeeModel->updateEmployee($uuid, $data)) {
            // Log audit if job was changed
            if ($oldJobId != $newJobId) {
                \App\Helpers\AuditHelper::logEmployeeJobChange($uuid, [
                    'old_job_id' => $oldJobId,
                    'new_job_id' => $newJobId,
                    'employee_id' => $data['employee_id'],
                    'changed_by' => $user['id']
                ]);
            }

            $_SESSION['success'] = 'ሰራተኛው መረጃ በትክክል ተስተካከለ።';
        } else {
            $_SESSION['error'] = 'የሰራተኛ ማስተካከያ ሂደት አልተሳካም።';
        }

        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
        exit();
    }

    public function handleRegistration() {
           AuthHelper::checkRole(['hr_director', 'hr_officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId || empty($user['id'])) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        // Server-side validation
        $validationErrors = $this->validateEmployeeData($_POST);
        if (!empty($validationErrors)) {
            $_SESSION['error'] = implode('<br>', $validationErrors);
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $imageName = $this->uploadFile('employee_image', 'images');
        $file201Name = $this->uploadFile('employee_file201', 'documents');

        if (!$imageName || !$file201Name) {
            $imageError = $_FILES['employee_image']['error'] ?? UPLOAD_ERR_NO_FILE;
            $file201Error = $_FILES['employee_file201']['error'] ?? UPLOAD_ERR_NO_FILE;

            $imageMessage = $imageName ? null : $this->getUploadErrorMessage($imageError);
            $file201Message = $file201Name ? null : $this->getUploadErrorMessage($file201Error);

            $messages = array_filter([
                $imageMessage ? "Photo: $imageMessage" : null,
                $file201Message ? "File201: $file201Message" : null,
            ]);

            $_SESSION['error'] = 'ፋይል እንዲወርድ አልቻለም። ' . implode(' | ', $messages);
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
            exit();
        }

        $data = [
            'uuid' => Uuid::uuid4()->toString(),
            'employee_id' => trim($_POST['employee_id'] ?? ''),
            'pension_number' => trim($_POST['pension_number'] ?? null) ?: null,
            'first_name' => trim($_POST['first_name'] ?? ''),
            'father_name' => trim($_POST['father_name'] ?? ''),
            'g_father_name' => trim($_POST['g_father_name'] ?? ''),
            'mother_name' => trim($_POST['mother_name'] ?? ''),
            'sex' => $_POST['sex'] ?? 'Male',
            'birth_date' => trim($_POST['birth_date'] ?? null) ?: null,
            'phone_number' => trim($_POST['phone_number'] ?? null) ?: null,
            'yegabcha_huneta' => trim($_POST['yegabcha_huneta'] ?? ''),
            'organization_id' => $organizationId,
            'branch_id' => $branchId,
            'job_property_id' => trim($_POST['job_property_id'] ?? ''),
            'date_of_employed' => trim($_POST['date_of_employed'] ?? null) ?: null,
            'level_of_education' => trim($_POST['level_of_education'] ?? ''),
            'department' => trim($_POST['department'] ?? null) ?: null,
            'employment_situation' => trim($_POST['employment_situation'] ?? ''),
            'immidate_boss' => trim($_POST['immidate_boss'] ?? null) ?: null,
            'experience' => trim($_POST['experience'] ?? null) ?: null,
            'annual_rest' => isset($_POST['annual_rest']) ? (int) $_POST['annual_rest'] : 0,
            'displin_situation' => trim($_POST['displin_situation'] ?? ''),
            'competency_situation' => trim($_POST['competency_situation'] ?? null) ?: null,
            'effeciency' => $this->normalizeDecimal($_POST['effeciency'] ?? null),
            'level_of_effeciency' => trim($_POST['level_of_effeciency'] ?? null) ?: null,
            'no_of_files_in_folder' => isset($_POST['no_of_files_in_folder']) ? (int) $_POST['no_of_files_in_folder'] : 0,
            'employee_image' => $imageName,
            'employee_file201' => $file201Name,
            'remark' => trim($_POST['remark'] ?? null) ?: null,
            'reg_by' => $user['id'],
        ];

        $employeeModel = new EmployeeRegistration($this->db);
        if ($employeeModel->createEmployee($data)) {
            \App\Helpers\AuditHelper::logEmployeeRegistration($data['uuid'], [
                'employee_id' => $data['employee_id'],
                'first_name' => $data['first_name'],
                'father_name' => $data['father_name'],
                'g_father_name' => $data['g_father_name'],
                'job_property_id' => $data['job_property_id'],
                'organization_id' => $data['organization_id'],
                'branch_id' => $data['branch_id'],
            ]);

            $_SESSION['success'] = 'ሰራተኛው መረጃ በትክክል ተመዝግቧል።';
        } else {
            // Clean up uploaded files on failure
            if ($imageName) {
                $imagePath = dirname(__DIR__, 2) . '/storage/uploads/images/' . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            if ($file201Name) {
                $file201Path = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $file201Name;
                if (file_exists($file201Path)) {
                    unlink($file201Path);
                }
            }

            $_SESSION['error'] = 'የሰራተኛ መመዝገቢያ ሂደት አልተሳካም።';
        }

        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-registration");
        exit();
    }

    private function uploadFile(string $inputName, string $path): ?string {
        if (!isset($_FILES[$inputName])) {
            return null;
        }

        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error ({$inputName}): " . $_FILES[$inputName]['error']);
            return null;
        }

        $fileName = time() . '_' . basename($_FILES[$inputName]['name']);
        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads/';
        $fullPath = rtrim($storageRoot . trim($path, '/'), '/') . '/';

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $fullPath . $fileName)) {
            error_log("Failed to move uploaded file for {$inputName} to {$fullPath}{$fileName}");
            return null;
        }

        return $fileName;
    }

    private function normalizeDecimal($value): ?string {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = str_replace([',', ' '], ['.', ''], trim($value));
        return is_numeric($normalized) ? $normalized : null;
    }

    private function getUploadErrorMessage(int $errorCode): string {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return 'Upload successful.';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'ፋይሉ ከፍተኛ ነው። እባክዎ ከእጅጉ ዕድል ውስጥ ያስገቡ።';
            case UPLOAD_ERR_PARTIAL:
                return 'ፋይሉ ክፍት ነው። እንገና ይሞክሩ።';
            case UPLOAD_ERR_NO_FILE:
                return 'ፋይል አልተመረጠም። እባክዎ ፎቶንና የ201 ፋይልን ይምረጡ።';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'የጊጥ ግዴታ በሆነ አውታረ ማዕከል የተጎዳ።';
            case UPLOAD_ERR_CANT_WRITE:
                return 'ፋይሉን ወደ አውታረ ማከማቻ ማድረግ አልተቻለም።';
            case UPLOAD_ERR_EXTENSION:
                return 'የፋይል እቅድ በሚገድድ ስር ተዘግቷል።';
            default:
                return 'የፋይል ስህተት ተከስቷል።';
        }
    }

    private function validateEmployeeData(array $data): array {
        $errors = [];

        // Required field validations
        $requiredFields = [
            'employee_id' => 'የሰራተኛ መለያ ቁጥር',
            'first_name' => 'ስም',
            'father_name' => 'የአባት ስም',
            'g_father_name' => 'የአያት ስም',
            'mother_name' => 'የእናት ሙሉ ስም',
            'sex' => 'ጾታ',
            'birth_date' => 'የትውልድ ቀን',
            'phone_number' => 'ስልክ ቁጥር',
            'yegabcha_huneta' => 'የጋብቻ ሁኔታ',
            'job_property_id' => 'የስራ መደብ',
            'level_of_education' => 'የትምህርት ደረጃ',
            'employment_situation' => 'Employment Situation',
            'immidate_boss' => 'የቅርብ ተጠሪ',
            'displin_situation' => 'የዲሲፕሊን ሁኔታ'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = "$label አስፈላጊ ነው።";
            }
        }

        // Length validations
        $lengthValidations = [
            'employee_id' => ['min' => 2, 'max' => 50, 'label' => 'የሰራተኛ መለያ ቁጥር'],
            'first_name' => ['min' => 2, 'max' => 50, 'label' => 'ስም'],
            'father_name' => ['min' => 2, 'max' => 50, 'label' => 'የአባት ስም'],
            'g_father_name' => ['min' => 2, 'max' => 50, 'label' => 'የአያት ስም'],
            'mother_name' => ['min' => 2, 'max' => 100, 'label' => 'የእናት ሙሉ ስም'],
            'yegabcha_huneta' => ['min' => 2, 'max' => 50, 'label' => 'የጋብቻ ሁኔታ'],
            'level_of_education' => ['min' => 2, 'max' => 100, 'label' => 'የትምህርት ደረጃ'],
            'employment_situation' => ['min' => 2, 'max' => 100, 'label' => 'Employment Situation'],
            'displin_situation' => ['min' => 2, 'max' => 100, 'label' => 'የዲሲፕሊን ሁኔታ']
        ];

        foreach ($lengthValidations as $field => $config) {
            $value = trim($data[$field] ?? '');
            if (!empty($value)) {
                $length = strlen($value);
                if ($length < $config['min']) {
                    $errors[] = "{$config['label']} ቢያንስ {$config['min']}  ፊደል መሆን አለበት።";
                }
                if ($length > $config['max']) {
                    $errors[] = "{$config['label']} {$config['max']}  ፊደል ከመብለጫ ቀር መሆን አለበት።";
                }
            }
        }

        // Date validations
        if (!empty($data['birth_date'])) {
            if (!strtotime($data['birth_date'])) {
                $errors[] = "የትውልድ ቀን ትክክለኛ ቀን መሆን አለበት።";
            } else {
                $birthDate = new DateTime($data['birth_date']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                if ($age < 18 || $age > 65) {
                    $errors[] = "የሰራተኛ እድሜ ከ18 እስከ 65 አመት መሆን አለበት።";
                }
            }
        }

        if (!empty($data['date_of_employed'])) {
            if (!strtotime($data['date_of_employed'])) {
                $errors[] = "የቅጥር ቀን ትክክለኛ ቀን መሆን አለበት።";
            }
        }

        // Phone number validation
        if (!empty($data['phone_number'])) {
            if (!preg_match('/^[0-9]{10}$/', $data['phone_number'])) {
                $errors[] = "ስልክ ቁጥር ትክክለኛ 10 አሃዝ መሆን አለበት።";
            }
        }

        // Numeric validations
        if (isset($data['annual_rest']) && $data['annual_rest'] !== '') {
            $annualRest = (int)$data['annual_rest'];
            if ($annualRest < 0 || $annualRest > 365) {
                $errors[] = "የዓመት እረፍት ከ0 እስከ 365 መሆን አለበት።";
            }
        }

        if (isset($data['effeciency']) && $data['effeciency'] !== '') {
            $efficiency = (float)str_replace([',', ' '], ['.', ''], $data['effeciency']);
            if ($efficiency < 0 || $efficiency > 100) {
                $errors[] = "Efficiency ከ0 እስከ 100 መሆን አለበት።";
            }
        }

        if (isset($data['no_of_files_in_folder']) && $data['no_of_files_in_folder'] !== '') {
            $filesCount = (int)$data['no_of_files_in_folder'];
            if ($filesCount < 0) {
                $errors[] = "የማህደር የፋይል ብዛት 0 ወይም ከዚህ በላይ መሆን አለበት።";
            }
        }

        // Sex validation
        if (!empty($data['sex']) && !in_array($data['sex'], ['Male', 'Female'])) {
            $errors[] = "ጾታ ትክክለኛ መሆን አለበት።";
        }

        // Remark length validation
        if (!empty($data['remark']) && strlen(trim($data['remark'])) > 500) {
            $errors[] = "Remark 500  ፊደል ከመብለጫ ቀር መሆን አለበት።";
        }

        return $errors;
    }
 public function onboardingEmployees() {
    $user = $_SESSION['user'] ?? [];
    $organizationId = $user['organization_id'] ?? null;
    $branchId = $user['branch_id'] ?? null;
    AuthHelper::checkRole(['hr_director']);
    $employeeModel = new EmployeeRegistration($this->db);
    $count = $employeeModel->countOnboardingEmployees($organizationId, $branchId);

    header('Content-Type: application/json'); // <-- must be here
    echo json_encode(['count' => $count]);
    exit(); // <-- add this to stop any extra output
}
 public function listofOnboardingEmployees() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;

        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new EmployeeRegistration($this->db);
            $employees = $employeeModel->getOnboardingEmployees($organizationId, $branchId);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ መመዝገቢያ',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-onboarding', $data);
    }
public function showOnBoardingForm() {
           AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $uuid = $_GET['uuid'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/login");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;

        if (!$organizationId || !$branchId) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/login");
            exit();
        }

        $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($uuid);

        if (!$employee) {
            $_SESSION['error'] = 'ሰራተኛ አልተገኘም።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onbording");
            exit();
        }

        $data = [
            'title' => 'HRM - የሰራተኛ ማስተካከያ',
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-onboarding-views', $data);
    }
    
     public function handleOnboardingApproval() {
           AuthHelper::checkRole(['hr_director', 'hr_officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
            exit();
        }

        $uuid = $_POST['uuid'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId || empty($user['id'])) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
            exit();
        }


        // Get current employee to check for job change
        $employeeModel = new EmployeeRegistration($this->db);
        $currentEmployee = $employeeModel->getEmployeeByUuid($uuid);

        if (!$currentEmployee) {
            $_SESSION['error'] = 'ሰራተኛ አልተገኘም።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
            exit();
        }
        if ($employeeModel->approveOnBoardingEmployee($uuid)) {
            
               \App\Helpers\AuditHelper::logOnBoardingEmployeeApproval($uuid, [
            ]);
          

            $_SESSION['success'] = 'ሰራተኛው መረጃ በትክክል ተስተካከለ።';
        } else {
            $_SESSION['error'] = 'የሰራተኛ ማስተካከያ ሂደት አልተሳካም።';
        }

        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
        exit();
    }

}
