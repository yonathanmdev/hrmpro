<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Models\BranchEvaluationWindow;
use App\Models\BscPlanFile;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;
use  App\Models\EfficiencyFileModel;

class HrBscPlanController extends BaseController
{
     use FileUploadTrait;
    /*
    |--------------------------------------------------------------------------
    | Shared season resolution logic
    |--------------------------------------------------------------------------
    */
    private function resolveSelectedSeason(
        array $openSeasons,
        ?string $seasonId
    ): ?array
    {
        if (!$seasonId && count($openSeasons) === 1) {
            return $openSeasons[0];
        }

        if ($seasonId) {
            foreach ($openSeasons as $season) {
                if ($season['season_id'] === $seasonId) {
                    return $season;
                }
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | BSC Plan Attachment Page
    | Shows employees WITHOUT a plan
    | Statistics show how many out of total already have a plan
    |--------------------------------------------------------------------------
    */
    public function index(array $params = [])
    {
        AuthHelper::checkRole([
            'hr_officer',
            'hr_director'
        ]);

        $branchId = $_SESSION['user']['branch_id'];

        $windowModel = new BranchEvaluationWindow($this->db);

        $openSeasons = $windowModel->getOpenSeasonsForBranch($branchId);

        $seasonId = $params['uuid'] ?? null;

        $selectedSeason = $this->resolveSelectedSeason(
            $openSeasons,
            $seasonId
        );

        $employees        = [];
        $withPlan = [];
        $totalEmployees   = 0;
        $withPlanCount    = 0;
        $withoutPlanCount = 0;

        if ($selectedSeason) {

            $planModel = new BscPlanFile($this->db);

            // Employees without a plan — shown in the table
            $employees = $planModel->getEmployeesWithoutPlan(
                $branchId,
                $selectedSeason['season_id']
            );

            // All employees with plan — to compute statistics
            $withPlan = $planModel->getEmployeesWithPlanStatus(
                $branchId,
                $selectedSeason['season_id']
            );

            $withPlanCount    = count($withPlan);
            $withoutPlanCount = count($employees);
            $totalEmployees   = $withPlanCount + $withoutPlanCount;
        }

        $this->render(
            'bsc-plan-management',
            [
                'openSeasons'      => $openSeasons,
                'selectedSeason'   => $selectedSeason,
                'employees'        => $employees,
                'employeesWithPlan'   => $withPlan,   // add this
                'totalEmployees'   => $totalEmployees,
                'withPlanCount'    => $withPlanCount,
                'withoutPlanCount' => $withoutPlanCount
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Efficiency Filling Page
    | Shows employees WITH a plan
    |--------------------------------------------------------------------------
    */
   
public function markConfirmed(): void
{
    AuthHelper::checkRole([
        'hr_officer',
        'hr_director'
    ]);

    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $employeeUuid = trim($body['employee_uuid'] ?? '');
    $seasonId     = trim($body['season_id'] ?? '');

    if (empty($employeeUuid) || empty($seasonId)) {
        $_SESSION['error'] = 'Invalid request data.';
        http_response_code(400);
        echo json_encode(['status' => 'error']);
        return;
    }

    $branchId   = $_SESSION['user']['branch_id'];
    $uploadedBy = $_SESSION['user']['id'];

    $planModel = new BscPlanFile($this->db);

    $result = $planModel->markAsConfirmed(
        Uuid::uuid4()->toString(),
        $employeeUuid,
        $branchId,
        $seasonId,
        $uploadedBy
    );

    if ($result) {
        \App\Helpers\AuditHelper::log(
    action: 'bsc_plan_confirmed',
    entityType: 'bsc_plan',
    entityId: $employeeUuid,
    oldValues: null,
    newValues: [
        'employee_id' => $employeeUuid,
        'season_id'   => $seasonId,
        'file_name'   => 'CONFIRMATION_MARK',
    ],
    metadata: [
        'uploaded_by' => $uploadedBy,
        'branch_id'   => $branchId
    ]
);
        $_SESSION['success'] = 'BSC እቅድ በተሳካ ሁኔታ ተያይዟል!';
        echo json_encode(['status' => 'success']);
    } else {
        $_SESSION['error'] = 'አልተያያዘም።';
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
}

public function upload(array $params = []): void
{
    AuthHelper::checkRole([
        'hr_officer',
        'hr_director'
    ]);

    $employeeUuid = $params['uuid']      ?? '';
$seasonId     = $params['record_id'] ?? '';

    $redirectBack = rtrim($_ENV['BASE_URL'], '/') . '/bsc-plan-management/' . $seasonId;

    // Validate params
    if (empty($employeeUuid) || empty($seasonId)) {
        $_SESSION['error'] = 'Invalid request.';
        header("Location: " . $redirectBack);
        exit();
    }

    // Check file
    if (
        empty($_FILES['bsc_plan']['name']) ||
        $_FILES['bsc_plan']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        $_SESSION['error'] = 'እባክዎ የBSC እቅድ ፋይል ይምረጡ።';
        header("Location: " . $redirectBack);
        exit();
    }

    // Upload file
    $fileName = $this->uploadFile('bsc_plan', 'documents');

    if (!$fileName) {
        $fileError = $_FILES['bsc_plan']['error'] ?? UPLOAD_ERR_NO_FILE;
        $_SESSION['error'] = 'ፋይሉን መጫን አልተቻለም። ስህተት፡ ' . $this->getUploadErrorMessage($fileError);
        header("Location: " . $redirectBack);
        exit();
    }

    $filePath = $fileName;
    $branchId   = $_SESSION['user']['branch_id'];
    $uploadedBy = $_SESSION['user']['id'];

    $planModel = new BscPlanFile($this->db);

    $result = $planModel->uploadPlan(
        Uuid::uuid4()->toString(),
        $employeeUuid,
        $branchId,
        $seasonId,
        $fileName,
        $filePath,
        $uploadedBy
    );

    if ($result) {
        \App\Helpers\AuditHelper::log(
    action: 'bsc_plan_uploaded',
    entityType: 'bsc_plan',
    entityId: $employeeUuid,
    oldValues: null,
    newValues: [
        'employee_id' => $employeeUuid,
        'season_id'   => $seasonId,
        'file_name'   => $fileName
    ],
    metadata: [
        'uploaded_by' => $uploadedBy,
        'branch_id'   => $branchId
    ]
);
        $_SESSION['success'] = 'BSC እቅድ በተሳካ ሁኔታ ተያይዟል!';
    } else {
        $_SESSION['error'] = 'BSC እቅድ ማያያዝ አልተቻለም።';
    }

    header("Location: " . $redirectBack);
    exit();
}
public function indexEfficency(array $params = [])
{
    AuthHelper::checkRole([
        'hr_officer',
        'hr_director'
    ]);

    $branchId = $_SESSION['user']['branch_id'];

    $windowModel = new BranchEvaluationWindow($this->db);
    $openSeasons = $windowModel->getOpenSeasonsForBranch($branchId);

    $seasonId       = $params['uuid'] ?? null;
    $selectedSeason = $this->resolveSelectedSeason($openSeasons, $seasonId);

    $employees        = [];
    $withEfficiency = [];
    $totalEmployees   = 0;
    $withFileCount    = 0;
    $withoutFileCount = 0;

    

    if ($selectedSeason) {

        $planModel       = new BscPlanFile($this->db);
        $efficiencyModel = new EfficiencyFileModel($this->db);

        $sid = $selectedSeason['season_id'];

        // Total eligible = all employees with a BSC file this season
        $bscEmployees   = $planModel->getEmployeesWithPlanStatus($branchId, $sid);
        $totalEmployees = count($bscEmployees);

        // Already have efficiency attached — for stats
        $withEfficiency = $efficiencyModel->getEmployeesWithEfficiency($branchId, $sid);
        $withFileCount  = count($withEfficiency);

        // Only employees whose BSC file has NO efficiency yet — shown in table
        $employees        = $efficiencyModel->getEmployeesWithoutEfficiency($branchId, $sid);
        $withoutFileCount = count($employees);
    }

    $this->render(
        'efficiency-management',
        [
            'openSeasons'      => $openSeasons,
            'selectedSeason'   => $selectedSeason,
            'employees'        => $employees,
             'withEfficiency'   => $withEfficiency,   // add this
            'totalEmployees'   => $totalEmployees,
            'withPlanCount'    => $withFileCount,
            'withoutPlanCount' => $withoutFileCount,
          
        ]
    );
}
public function efficiencyRegistration(array $params = []): void
{
    AuthHelper::checkRole([
        'hr_officer',
        'hr_director'
    ]);

    $employeeUuid = $params['uuid']      ?? '';
    $seasonId     = $params['record_id'] ?? '';

    $redirectBack = rtrim($_ENV['BASE_URL'], '/') . '/efficiency-management/' . $seasonId;

    // ── 1. Param check ────────────────────────────────────────────────────────
    if (empty($employeeUuid) || empty($seasonId)) {
        $_SESSION['error'] = 'ያልተሟላ መረጃ ተልኳል።';
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 2. BSC file ID ────────────────────────────────────────────────────────
    $bscFileId = trim($_POST['bsc_file_id'] ?? '');

    if (empty($bscFileId)) {
        $_SESSION['error'] = 'የBSC ፋይል መለያ አልተገኘም።';
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 3. Mark validation ────────────────────────────────────────────────────
    $mark = trim($_POST['efficiency_mark'] ?? '');

    if ($mark === '' || !is_numeric($mark) || (float)$mark < 0 || (float)$mark > 100) {
        $_SESSION['error'] = 'እባክዎ ትክክለኛ ነጥብ (0–100) ያስገቡ።';
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 4. File check ─────────────────────────────────────────────────────────
    if (
        empty($_FILES['efficiency_file']['name']) ||
        $_FILES['efficiency_file']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        $_SESSION['error'] = 'እባክዎ የብቃት ምዘና ፋይል ይምረጡ።';
        header('Location: ' . $redirectBack);
        exit();
    }

    $efficiencyModel = new EfficiencyFileModel($this->db);

    // ── 5. Duplicate check — per BSC file ID ─────────────────────────────────
    if ($efficiencyModel->efficiencyExistsForBscFile($bscFileId)) {
        $_SESSION['error'] = 'ለዚህ BSC ፋይል አስቀድሞ የብቃት ምዘና ፋይል ተያይዟል።';
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 6. Upload file ────────────────────────────────────────────────────────
    $fileName = $this->uploadFile('efficiency_file', 'documents');

    if (!$fileName) {
        $fileError = $_FILES['efficiency_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $_SESSION['error'] = 'ፋይሉን መጫን አልተቻለም። ስህተት፡ ' . $this->getUploadErrorMessage($fileError);
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 7. Insert record ──────────────────────────────────────────────────────
    $branchId   = $_SESSION['user']['branch_id'];
    $uploadedBy = $_SESSION['user']['id'];
    $fileSize   = $_FILES['efficiency_file']['size'] ?? 0;

    $result = $efficiencyModel->uploadEfficiency(
        Uuid::uuid4()->toString(),
        $employeeUuid,
        $branchId,
        $seasonId,
        $bscFileId,
        (float) $mark,
        $fileName,
        $fileName,
        (int) $fileSize,
        $uploadedBy
    );

    if ($result) {
        \App\Helpers\AuditHelper::log(
    action: 'efficiency_uploaded',
    entityType: 'efficiency_file',
    entityId: $employeeUuid,
    oldValues: null,
    newValues: [
        'employee_id'      => $employeeUuid,
        'season_id'        => $seasonId,
        'bsc_file_id'      => $bscFileId,
        'efficiency_mark'  => (float)$mark,
        'file_name'        => $fileName,
        'file_size'        => $fileSize
    ],
    metadata: [
        'uploaded_by' => $uploadedBy,
        'branch_id'   => $branchId
    ]
);
        $_SESSION['success'] = 'የብቃት ምዘና ፋይል በተሳካ ሁኔታ ተያይዟል!';
    } else {
        $_SESSION['error'] = 'የብቃት ምዘና ፋይል ማያያዝ አልተቻለም።';
    }

    header('Location: ' . $redirectBack);
    exit();
}
public function efficiencyUpdate(array $params = []): void
{
    AuthHelper::checkRole([
        'hr_officer',
        'hr_director'
    ]);

    $redirectBack = $_SERVER['HTTP_REFERER']
        ?? rtrim($_ENV['BASE_URL'], '/');

    // ── 1. Validate Efficiency ID ───────────────────────────────
    $efficiencyId = trim($_POST['efficiency_file_id'] ?? '');

    if (empty($efficiencyId)) {
        $_SESSION['error'] = 'የብቃት ምዘና ፋይል መለያ አልተገኘም።';
        header('Location: ' . $redirectBack);
        exit();
    }

    // ── 2. Validate Mark ───────────────────────────────────────
    $mark = trim($_POST['efficiency_mark'] ?? '');

    if (
        $mark === '' ||
        !is_numeric($mark) ||
        (float)$mark < 0 ||
        (float)$mark > 100
    ) {
        $_SESSION['error'] = 'እባክዎ ትክክለኛ ነጥብ (0-100) ያስገቡ።';
        header('Location: ' . $redirectBack);
        exit();
    }

    $efficiencyModel = new EfficiencyFileModel($this->db);

    // ── 3. Get Existing Record ─────────────────────────────────
    $record = $efficiencyModel->getEfficiencyById($efficiencyId);

    if (!$record) {
        $_SESSION['error'] = 'የብቃት ምዘና ፋይሉ አልተገኘም።';
        header('Location: ' . $redirectBack);
        exit();
    }

    $updatedBy = $_SESSION['user']['id'];

    // ── 4. Update With New File ────────────────────────────────
    if (
        isset($_FILES['efficiency_file']) &&
        $_FILES['efficiency_file']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $newFileName = $this->uploadFile(
            'efficiency_file',
            'documents'
        );

        if (!$newFileName) {
            $_SESSION['error'] = 'ፋይሉን መጫን አልተቻለም።';
            header('Location: ' . $redirectBack);
            exit();
        }


        $result = $efficiencyModel->updateEfficiency(
            $efficiencyId,
            (float)$mark,
            $newFileName,
            $newFileName,
            (int)$_FILES['efficiency_file']['size'],
            $updatedBy
        );

    } else {

        // ── 5. Update Mark Only ────────────────────────────────
        $result = $efficiencyModel->updateEfficiencyMark(
            $efficiencyId,
            (float)$mark,
            $updatedBy
        );
    }

    // ── 6. Result Message ──────────────────────────────────────
    if ($result) {
         \App\Helpers\AuditHelper::log(
        action: 'efficiency_updated',
        entityType: 'efficiency_file',
        entityId: $efficiencyId,
        oldValues: $record,
        newValues: [
            'efficiency_mark' => (float)$mark,
            'file_name'       => $newFileName ?? $record['file_name'],
            'file_path'       => $newFileName ?? $record['file_path'],
            'file_size'       => isset($_FILES['efficiency_file']) &&
                                $_FILES['efficiency_file']['error'] !== UPLOAD_ERR_NO_FILE
                                    ? (int)$_FILES['efficiency_file']['size']
                                    : $record['file_size']
        ],
        metadata: [
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    );
        $_SESSION['success'] =
            'የብቃት ምዘና ፋይል በተሳካ ሁኔታ ተሻሽሏል።';
    } else {
        $_SESSION['error'] =
            'የብቃት ምዘና ፋይል ማሻሻል አልተቻለም።';
    }

    header('Location: ' . $redirectBack);
    exit();
}

public function delete(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    $data    = json_decode(file_get_contents('php://input'), true);
    $id      = trim((string) ($data['id'] ?? ''));
    $employeeId = trim((string) ($data['uuid'] ?? ''));
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
        $model  = new BscPlanFile($this->db);
        $result = $model->deleteRecord($id, $employeeId, $adminId, $reason, $source);

        if ($result['status'] === 'success') {
            \App\Helpers\AuditHelper::log(
                action:     'document_deleted',
                entityType: 'document',
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
        error_log('ArchiveController::delete - ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።']);
    }
}
}