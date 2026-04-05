<?php
namespace App\Controllers;

use App\Models\EmployeeRegistration;
use Ramsey\Uuid\Uuid;

class EmployeeRegistrationController extends BaseController {
    public function showForm() {
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

    public function handleRegistration() {
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
}
