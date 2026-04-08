<?php
namespace App\Controllers;
use DateTime;
use App\Models\ScholarshipModel;
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
            'file_url' => $scholarshipFileName,
            'document_type' => 'Scholarship Agreement'
        ];

        // 4. ወደ ዳታቤዝ ማስገባት (Transaction)
        try {
            $model = new ScholarshipModel($this->db);
            $result = $model->saveScholarshipWithDocument($scholarshipData, $documentData);

            if ($result) {
                $_SESSION['success'] = 'የስኮላርሺፕ መረጃው በትክክል ተመዝግቧል!';
                header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-scholarship"); // ወይም የፈለግከው ቦታ
                exit();
            } else {
                throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
            }

        } catch (\Exception $e) {
            // 5. ዳታቤዝ ላይ ካልተመዘገበ የተጫነውን ፋይል ሰርቨር ላይ ማጥፋት (Cleanup)
            $fullPath = dirname(__DIR__, 2) . '/storage/uploads/scholarships/' . $scholarshipFileName;
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
}