<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Models\BranchEvaluationWindow;
use App\Models\BscPlanFile;
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
    $fileName = $this->uploadFile('efficiency_file', 'efficiency');

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
        $_SESSION['success'] = 'የብቃት ምዘና ፋይል በተሳካ ሁኔታ ተያይዟል!';
    } else {
        $_SESSION['error'] = 'የብቃት ምዘና ፋይል ማያያዝ አልተቻለም።';
    }

    header('Location: ' . $redirectBack);
    exit();
}
}