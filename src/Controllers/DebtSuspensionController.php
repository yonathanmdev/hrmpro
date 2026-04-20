<?php
namespace App\Controllers;
use App\Models\DebtSuspension;
use App\Models\EmployeeRegistration;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;

class DebtSuspensionController extends BaseController {
    use FileUploadTrait;

    public function showdebtSuspensionForm() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;


        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new DebtSuspension($this->db);
            $staus = 'active';
            $employees = $employeeModel->getActiveSuspensions($organizationId, $branchId, $staus);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ መመዝገቢያ',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-debt-suspension', $data);
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
        $employeeModel = new DebtSuspension($this->db);
        $results = $employeeModel->autoSearch($term, $branchId);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
}
public function storeDebtSuspension() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1. መረጃዎችን መቀበል
        $employee_uuid = $_POST['employee_id'] ?? null;
        $debt_type = $_POST['debt_suspension_type'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $registered_by = $_SESSION['user']['id'] ?? null;

        // 2. ፋይሉን መጫን (በከፈትከው uploadFile ፈንክሽን በመጠቀም)
        // ማሳሰቢያ፡ በ HTML ፎርምህ ላይ የፋይሉ ስም 'scholarship_file' መሆኑን አረጋግጥ
        $debtSuspensionFileName = $this->uploadFile('debt_suspension_file', 'documents');

        if (!$debtSuspensionFileName) {
            $fileError = $_FILES['debt_suspension_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $_SESSION['error'] = 'የእዳ/እገዳ ፋይሉን መጫን አልተቻለም። ስህተት፡ ' . $this->getUploadErrorMessage($fileError);
            header("Location: " . $_SERVER['HTTP_REFERER']); // ወደ መጣህበት ይመልሰሃል
            exit();
        }

        // በዳታቤዝ ውስጥ የሚቀመጠው የፋይሉ ሙሉ ፓዝ (ለ document_url)

        // 3. ዳታዎችን ማዘጋጀት
        $debtSuspensionData = [
            'id' => Uuid::uuid4()->toString(),
            'employee_id' => $employee_uuid,
            'start_date' => $start_date,
            'debt_suspension_type' => $debt_type,
            'reason' => $_POST['reason'] ?? '',
            'registered_by' => $registered_by
        ];

        $documentData = [
            'id' => Uuid::uuid4()->toString(), // ['id'],
            'doc_id' => Uuid::uuid4()->toString(), // ['id'],
            'file_url' => $debtSuspensionFileName,
        ];


        // 4. ወደ ዳታቤዝ ማስገባት (Transaction)
        try {
            $model = new DebtSuspension($this->db);
            $result = $model->saveDebtSuspensionWithDocument($debtSuspensionData, $documentData);

            if ($result) {
                $_SESSION['success'] = 'የእዳ/እገዳ መረጃው በትክክል ተመዝግቧል!';
                header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-debt-suspension"); // ወይም የፈለግከው ቦታ
                exit();
            } else {
                throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
            }

        } catch (\Exception $e) {
            // 5. ዳታቤዝ ላይ ካልተመዘገበ የተጫነውን ፋይል ሰርቨር ላይ ማጥፋት (Cleanup)
            $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $debtSuspensionFileName;
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
function countPending() {
     $user = $_SESSION['user'] ?? [];
    $organizationId = $user['organization_id'] ?? null;
    $branchId = $user['branch_id'] ?? null;
    AuthHelper::checkRole(['hr_director']);
    $employeeModel = new DebtSuspension($this->db);
    $count = $employeeModel->countPending($organizationId, $branchId);

    header('Content-Type: application/json'); // <-- must be here
    echo json_encode(['count' => $count]);
    exit(); // <-- add this to stop any extra output
}
    public function showDebtSuspensionPending() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;

        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new DebtSuspension($this->db);
            $staus = 'pending';
            $employees = $employeeModel->getActiveSuspensions($organizationId, $branchId, $staus);
        }

        $data = [
            'title' => 'HRM - የሰራተኛ መመዝገቢያ',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-debt-suspension-pending', $data);
    }

    public function getDebtSuspensionDetails() {
     AuthHelper::checkRole(['hr_director', 'hr_officer']);
     $employee_uuid = $_GET['uuid'] ?? null;
     $recordId = $_GET['record_id'] ?? null;

    if (!$recordId) {
        die("Debt Suspension ID is missing.");
    }
    $model = new DebtSuspension($this->db);
    $scholarship = $model->getDebtSuspensionDetails($recordId);
     $user = $_SESSION['user'] ?? [];
       $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($employee_uuid);
   
    $data = [
            'title' => 'HRM - እዳ/እገዳ',
            'scholarship' => $scholarship,
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-debt-suspension-approval-view', $data);
}
     public function handleDebtSuspensionApproval() {
           AuthHelper::checkRole(['hr_director']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-debt-suspension-pending");
            exit();
        }

        $uuid = $_POST['uuid'] ?? null;
         $recordId = $_POST['record_id'] ?? null;
        if (!$uuid) {
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-debt-suspension-pending");
            exit();
        }

        $user = $_SESSION['user'] ?? [];
        $userID = $user['id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;
        $branchId = $user['branch_id'] ?? null;

        if (!$organizationId || !$branchId || empty($user['id'])) {
            $_SESSION['error'] = 'የሰራተኛውን የድርጅት እና የቅርንጫፍ መረጃ ከስር ያስገቡ።';
            header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-onboarding");
            exit();
        }


        // Get current employee to check for job change
        $employeeModel = new DebtSuspension($this->db);
        if ($employeeModel->approveDebtSuspension($uuid, $recordId, $userID)) {
            
               \App\Helpers\AuditHelper::logDebtSuspensionApproval($uuid, [
            ]);
          

            $_SESSION['success'] = 'ሰራተኛው መረጃ በትክክል ተስተካከለ።';
        } else {
            $_SESSION['error'] = 'የሰራተኛ እዳ/እገዳ ማጽደቅ አልተሳካም።';
        }

        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-debt-suspension-pending");
        exit();
    }
}