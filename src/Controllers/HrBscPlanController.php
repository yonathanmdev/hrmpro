<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Models\BranchEvaluationWindow;
use App\Models\BscPlanFile;
use Ramsey\Uuid\Uuid;
use \App\Traits\FileUploadTrait;
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
    public function indexEfficency(array $params = [])
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

            $employees = $planModel->getEmployeesWithPlanStatus(
                $branchId,
                $selectedSeason['season_id']
            );

            $withPlanCount    = count($employees);
            $totalEmployees   = $withPlanCount;
        }

        $this->render(
            'bsc-efficiency',
            [
                'openSeasons'    => $openSeasons,
                'selectedSeason' => $selectedSeason,
                'employees'      => $employees,
                'totalEmployees' => $totalEmployees,
                'withPlanCount'  => $withPlanCount
            ]
        );
    }

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
}