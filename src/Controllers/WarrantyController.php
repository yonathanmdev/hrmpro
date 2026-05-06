<?php
namespace App\Controllers;
use App\Models\WarrantyModel;
use App\Models\EmployeeRegistration;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;

class WarrantyController extends BaseController {
    use FileUploadTrait;

    public function showWarrantyForm() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;


        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new WarrantyModel($this->db);
            $staus = 'pending';
            $employees = $employeeModel->getWarranties($organizationId, $branchId, $staus);
        }

        $data = [
            'title' => 'HRM - የዋስትን መመዝገቢያ',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-warranty', $data);
    }
public function showActiveWarranties() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $user = $_SESSION['user'] ?? [];
        $branchId = $user['branch_id'] ?? null;
        $organizationId = $user['organization_id'] ?? null;


        $employees = [];
        if ($organizationId && $branchId) {
            $employeeModel = new WarrantyModel($this->db);
            $staus = 'active';
            $employees = $employeeModel->getWarranties($organizationId, $branchId, $staus);
        }

        $data = [
            'title' => 'HRM - የዋስትን መመዝገቢያ',
            'user'  => $user,
            'employees' => $employees,
        ];

        $this->render('employee-has-warranty', $data);
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
        $employeeModel = new WarrantyModel($this->db);
        $results = $employeeModel->autoSearch($term, $branchId);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
}

public function storeWarranty() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // ── 1. Input collection ───────────────────────────────────────
    $employee_uuid  = trim($_POST['employee_id']    ?? '');
    $to_whom        = trim($_POST['to_whom']         ?? '');
    $the_person     = trim($_POST['the_person']      ?? '');
    $warranty_type  = trim($_POST['warranty_type']   ?? '');
    $registered_by  = $_SESSION['user']['id']        ?? null;

    // ── 2. Validation ─────────────────────────────────────────────
    if (!$employee_uuid || !$to_whom || !$the_person || !$warranty_type || !$registered_by) {
        \App\Helpers\AuditHelper::log(
            action:     'warranty_store_validation_failed',
            entityType: 'warranty',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'missing_fields'    => array_keys(array_filter([
                    'employee_id'   => !$employee_uuid,
                    'to_whom'       => !$to_whom,
                    'the_person'    => !$the_person,
                    'warranty_type' => !$warranty_type,
                ])),
                'performed_by'      => $registered_by,
                'deletion_source'   => 'INDIVIDUAL_ACTION',
            ]
        );

        $_SESSION['error'] = 'እባክዎ ሁሉንም አስፈላጊ መረጃዎች ያስገቡ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── 3. Prepare payload ────────────────────────────────────────
    $warrantyId   = Uuid::uuid4()->toString();
    $documentId = Uuid::uuid4()->toString();
    $warrantyData = [
        'id'            => $warrantyId,
        'document_id'   => $documentId,
        'emp_id'        => $employee_uuid,
        'to_whom'       => $to_whom,
        'the_person'    => $the_person,
        'warranty_type' => $warranty_type,
        'registered_by' => $registered_by,
    ];

    // ── 4. Persist ────────────────────────────────────────────────
    try {
        $model  = new WarrantyModel($this->db);
        $result = $model->saveWarrantyData($warrantyData);

        if (!$result) {
            throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
        }

        // ── 5. Audit log — success ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'warranty_created',
            entityType: 'warranty',
            entityId:   $warrantyId,
            oldValues:  null,
            newValues:  $warrantyData,
            metadata:   [
                'performed_by'      => $registered_by,
            ]
        );

        $_SESSION['success'] = 'የዋስትና መረጃው በትክክል ተመዝግቧል!';
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-warranty");
        exit();

    } catch (\Exception $e) {

        // ── 6. Audit log — failure ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'warranty_store_failed',
            entityType: 'warranty',
            entityId:   $warrantyId ?? null,
            oldValues:  null,
            newValues:  $warrantyData ?? null,
            metadata:   [
                'error'             => $e->getMessage(),
                'performed_by'      => $registered_by,
            ]
        );

        $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

public function getPendingWarranty($params = []) {
    
     AuthHelper::checkRole(['hr_director', 'hr_officer']);
     $employee_uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;
     $recordId = $params['record_id'] ?? $_GET['record_id'] ?? null;

    if (!$recordId) {
        die("Warranty ID is missing.");
    }
    $model = new WarrantyModel($this->db);
    $warrantyData = $model->getPendingWarrantyDetails($recordId);
     $user = $_SESSION['user'] ?? [];
       $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($employee_uuid);
   
    $data = [
            'title' => 'HRM - የዋስትና ፋይል ማያያዝ',
            'warranty' => $warrantyData,
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-warranty-pending', $data);
}

public function handleFileAttachment() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $employee_uuid = $_POST['employee_id'] ?? null;
    $record_id     = $_POST['record_id']   ?? null;
    $userId        = $_SESSION['user']['id'] ?? null;

    // ── Validation ────────────────────────────────────────────
    if (!$employee_uuid || !$record_id || !$userId) {
        $_SESSION['error'] = 'እባክዎ አስፈላጊ መረጃዎችን ያስገቡ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── File upload ───────────────────────────────────────────
    $file_url        = null;
    $newFileUploaded = false;

    if (isset($_FILES['warranty_file']) && $_FILES['warranty_file']['error'] === UPLOAD_ERR_OK) {
        $warrantyFileName = $this->uploadFile('warranty_file', 'documents');
        if ($warrantyFileName) {
            $file_url        = $warrantyFileName;
            $newFileUploaded = true;
        }
    }

    if (!$file_url) {
        $_SESSION['error'] = 'እባክዎ ትክክለኛ ፋይል ያያይዙ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── Call model (mirrors approveWarranty pattern) ──────────
    $model  = new WarrantyModel($this->db);
    $result = $model->attachWarrantyFile(
        uuid:     $employee_uuid,
        recordId: $record_id,
        userId:   $userId,
        fileurl:  $file_url
    );

    // ── Handle result ─────────────────────────────────────────
    if ($result['status'] === 'success') {
        \App\Helpers\AuditHelper::log(
            action:     'warranty_file_attached',
            entityType: 'warranty',
            entityId:   $record_id,
            oldValues:  $result['oldRecord'],
            newValues:  $result['newRecord'],
            metadata:   [
                'file_url'        => $file_url,
                'performed_by'    => $userId,
            ]
        );

        unset($result['oldRecord'], $result['newRecord']);
        $_SESSION['success'] = 'የዋስትና ፋይሉ በትክክል ተያይዟል!';
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-warranty");
        exit();

    } else {
        // ── Cleanup uploaded file on failure ──────────────────
        if ($newFileUploaded && $file_url) {
            $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $file_url;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        \App\Helpers\AuditHelper::log(
            action:     'warranty_file_attach_failed',
            entityType: 'warranty',
            entityId:   $record_id,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'error'           => $result['message'],
                'file_url'        => $file_url,
                'performed_by'    => $userId,
            ]
        );

        $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $result['message'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

public function getWarrantyDetails($params = []) {
    $employee_uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;
    $recordId = $params['record_id'] ?? $_GET['record_id'] ?? null;

    if (!$recordId) {
        die("Warranty ID is missing.");
    }
    $model = new WarrantyModel($this->db);
    $warrantyData = $model->getWarrantyDetails($recordId);
     $user = $_SESSION['user'] ?? [];
       $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($employee_uuid);
   
    $data = [
            'title' => 'HRM - ዋስትን ለማውረድ',
            'warranty' => $warrantyData,
            'user'  => $user,
            'employee' => $employee,
        ];

        $this->render('employee-warranty-views', $data);
}

public function handleWarrantyRelease() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $employee_uuid = $_POST['employee_id'] ?? null;
    $record_id     = $_POST['record_id']   ?? null;
    $userId        = $_SESSION['user']['id'] ?? null;

    // ── Validation ────────────────────────────────────────────
    if (!$employee_uuid || !$record_id || !$userId) {
        $_SESSION['error'] = 'እባክዎ አስፈላጊ መረጃዎችን ያስገቡ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── File upload ───────────────────────────────────────────
    $file_url        = null;
    $newFileUploaded = false;

    if (isset($_FILES['warranty_file']) && $_FILES['warranty_file']['error'] === UPLOAD_ERR_OK) {
        $warrantyFileName = $this->uploadFile('warranty_file', 'documents');
        if ($warrantyFileName) {
            $file_url        = $warrantyFileName;
            $newFileUploaded = true;
        }
    }

    if (!$file_url) {
        $_SESSION['error'] = 'እባክዎ ትክክለኛ ፋይል ያያይዙ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── Call model (mirrors approveWarranty pattern) ──────────
    $doc_id   = Uuid::uuid4()->toString();
    $model  = new WarrantyModel($this->db);
    $result = $model->releasingWarranty(
        docId:   $doc_id,
        uuid:     $employee_uuid,
        recordId: $record_id,
        userId:   $userId,
        fileurl:  $file_url
    );

    // ── Handle result ─────────────────────────────────────────
    if ($result['status'] === 'success') {
        \App\Helpers\AuditHelper::log(
            action:    'warranty_released',
            entityType: 'warranty',
            entityId:   $record_id,
            oldValues:  $result['oldRecord'],
            newValues:  $result['newRecord'],
            metadata:   [
                'file_url'        => $file_url,
                'performed_by'    => $userId,
            ]
        );

        unset($result['oldRecord'], $result['newRecord']);
        $_SESSION['success'] = 'ዋስትናው በትክክል ተነስቷል!';
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-has-warranty");
        exit();

    } else {
        // ── Cleanup uploaded file on failure ──────────────────
        if ($newFileUploaded && $file_url) {
            $fullPath = dirname(__DIR__, 2) . '/storage/uploads/documents/' . $file_url;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        \App\Helpers\AuditHelper::log(
            action:     'warranty_release_failed',
            entityType: 'warranty',
            entityId:   $record_id,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'error'           => $result['message'],
                'file_url'        => $file_url,
                'performed_by'    => $userId,
            ]
        );

        $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $result['message'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

}