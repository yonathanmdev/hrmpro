<?php
namespace App\Controllers;
use App\Helpers\AuthHelper;
use App\Helpers\EthiopianDateHelper; 
use App\Models\ExperienceModel;
use App\Models\User;
use App\Models\EmployeeRegistration;
use Ramsey\Uuid\Uuid;   
class ExperiencesController extends BaseController {
      public function showExperience($params = []){
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $employee_uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;
        $branch_id = $_SESSION['user']['branch_id'] ?? null;
        if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-director");
            exit();
        }
        $experienceModel = new ExperienceModel($this->db);
        $experiences = $experienceModel->getEmployeeExperiences($employee_uuid);
        $employeeModel = new EmployeeRegistration($this->db);
        $employee = $employeeModel->getEmployeeByUuid($employee_uuid);

     
         $this->render('employee-experience', [
            'title' => 'ልምድ መመዝገቢያ',
            'experiences' => $experiences,
            'employee' => $employee
        ]);
    }  
public function showExperienceLetter($params = []){
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    
    $employee_uuid = $params['uuid'] ?? $_GET['uuid'] ?? null;
    $branch_id = $_SESSION['user']['branch_id'] ?? null;
    
    if (!$branch_id) {
        $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
        header("Location: " . $_ENV['BASE_URL'] . "/register-director");
        exit();
    }
    
    $experienceModel = new ExperienceModel($this->db);
    $experiences = $experienceModel->getEmployeeExperiences($employee_uuid);
    $employeeModel = new EmployeeRegistration($this->db);
    $employee = $employeeModel->getEmployeeByUuid($employee_uuid);

    // 🆕 Using renderPrintable to avoid Header/Footer
    $this->renderPrintable('employee-experience-letter', [
        'title'       => 'የስራ ልምድ ደብዳቤ',
        'experiences' => $experiences,
        'employee'    => $employee,
        'isPrint'     => true // Flag to handle print-specific logic in the view
    ]);
}
    public function employeeSearch() {
    // 1. ማንኛውንም ቀድሞ የወጣ Output (Warning/Notice) ለማጽዳት
    if (ob_get_length()) ob_clean();

    // 2. Role Check - ማሳሰቢያ፡ checkRole ስህተት ሲያገኝ JSON እንዲመልስ ማድረግ አለብህ
    // ካልሆነ ግን ተጠቃሚው ገብቶ ካልሆነ የሚመጣው HTML ለ JS ስህተት ይፈጥራል
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    
    $term = $_GET['query'] ?? '';
    $source = $_GET['source'] ?? '';
    $branchId = $_SESSION['user']['branch_id'] ?? null;

    // 3. Headerን ቀድሞ መላክ (ለደህንነት)
    header('Content-Type: application/json');

    if (!$branchId || empty($term)) {
        echo json_encode([]);
        exit;
    }

    try {
        
        $employeeModel = new EmployeeRegistration($this->db);
        $results = $employeeModel->autoSearch($term, $branchId,  $source);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
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
        $experienceModel = new ExperienceModel($this->db);
        $results = $experienceModel->autoSearch($term);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
}
public function jobSearch() {
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
        $experienceModel = new ExperienceModel($this->db);
        $results = $experienceModel->jobSearch($term);

        // 4. ውጤቱን መላክ
        echo json_encode($results);
    } catch (\Exception $e) {
        // ስህተት ካለ ባዶ array መላክ (HTML Error ገጽ እንዳይመጣ)
        echo json_encode(['error' => 'Search failed']);
    }
    
    exit; // ከዚህ በኋላ ምንም አይነት ዳታ እንዳይወጣ ያረጋግጣል
}

public function storeExperience()
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // ── 1. Input collection ───────────────────────────────────────
    $employee_uuid   = trim($_POST['employee_uuid']    ?? '');
    $company_name    = trim($_POST['company_name']     ?? '');
    $job_title       = trim($_POST['job_title']        ?? '');
    $employment_type = trim($_POST['employment_type']  ?? '');
    $start_date      = trim($_POST['start_date']       ?? '');
    $end_date        = trim($_POST['end_date']         ?? '');
    $is_current      = 0;
    $registered_by   = $_SESSION['user']['id']         ?? null;

    // ── 2. Required Field Validation ─────────────────────────────
    $missingFields = array_keys(array_filter([
        'employee_uuid'   => !$employee_uuid,
        'company_name'    => !$company_name,
        'job_title'       => !$job_title,
        'employment_type' => !$employment_type,
        'start_date'      => !$start_date,
        'end_date'        => !$end_date,  // ← was missing from your check
    ]));

    if (!empty($missingFields)) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_store_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'missing_required_fields',
                'missing_fields'=> $missingFields,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'እባክዎ ሁሉንም አስፈላጊ መረጃዎች ያስገቡ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── 3. Date Validation ────────────────────────────────────────
    $today     = strtotime(date('Y-m-d'));
    $startTime = strtotime($start_date);
    $endTime   = strtotime($end_date);

    // Start date cannot be in the future
    if ($startTime > $today) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_store_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'start_date_in_future',
                'start_date'    => $start_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ የጀመሩበት ቀን ከዛሬ በላይ መሆን የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // End date cannot be in the future
    if ($endTime > $today) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_store_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'end_date_in_future',
                'end_date'      => $end_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ ስራ የጨረሱበት ቀን ከዛሬ በላይ መሆን የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // End date must be after start date
    if ($endTime <= $startTime) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_store_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'end_date_before_or_equal_start_date',
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ ስራ የጨረሱበት ቀን ከጀመሩበት ቀን ማነስ የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── 4. Prepare Payload ────────────────────────────────────────
    $experienceId   = Uuid::uuid4()->toString();
    $experienceData = [
        'id'             => $experienceId,
        'employee_uuid'  => $employee_uuid,
        'company_name'   => $company_name,
        'job_title'      => $job_title,
        'employment_type'=> $employment_type,
        'start_date'     => $start_date,
        'end_date'       => $end_date,
        'is_current'     => $is_current,
        'registered_by'  => $registered_by,
    ];

    // ── 5. Persist ────────────────────────────────────────────────
    try {
        $model = new ExperienceModel($this->db);

        // Check employment type overlap
        $isTypeOverlapping = $model->checkEmploymentTypeOverlap(
            $employee_uuid,
            $start_date,
            $end_date,
            $employment_type
        );

        if ($isTypeOverlapping) {
            \App\Helpers\AuditHelper::log(
                action:     'experience_store_validation_failed',
                entityType: 'experience',
                entityId:   null,
                oldValues:  null,
                newValues:  $experienceData,
                metadata:   [
                    'reason'          => 'employment_type_overlap',
                    'employment_type' => $employment_type,
                    'start_date'      => $start_date,
                    'end_date'        => $end_date,
                    'performed_by'    => $registered_by,
                    'employee_uuid'   => $employee_uuid,
                ]
            );

            $_SESSION['error'] = 'በተመረጠው የጊዜ ገደብ ውስጥ ተመሳሳይ የቅጥር አይነት (' . $employment_type . ') ቀድሞ ተመዝግቧል።';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $result = $model->saveExperienceData($experienceData);

        if (!$result) {
            throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
        }

        // ── 6. Audit log — success ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'experience_created',
            entityType: 'experience',
            entityId:   $experienceId,
            oldValues:  null,
            newValues:  $experienceData,
            metadata:   [
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['success'] = 'የስራ ልምድ በትክክል ተመዝግቧል!';
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-experience?uuid=" . urlencode($employee_uuid));
        exit();

    } catch (\Exception $e) {

        // ── 7. Audit log — failure ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'experience_store_failed',
            entityType: 'experience',
            entityId:   $experienceId ?? null,
            oldValues:  null,
            newValues:  $experienceData ?? null,
            metadata:   [
                'reason'        => 'database_exception',
                'error'         => $e->getMessage(),
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

public function getExperienceById()
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    $id = $_GET['uuid'] ?? null;

    if (!$id) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid ID'
        ]);
        return;
    }

    $experienceModel = new ExperienceModel($this->db);
    $experience      = $experienceModel->getExperienceById($id);

    if (!$experience) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Experience not found'
        ]);
        return;
    }

    // Convert start_date to Ethiopian
    $startParts      = explode('-', $experience['start_date']);
    $ethStart        = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);
    $experience['eth_start_date'] = EthiopianDateHelper::getMonthName($ethStart['month']) . ' ' . $ethStart['day'] . ' ' . $ethStart['year'];

    // Convert end_date to Ethiopian
    $endParts        = explode('-', $experience['end_date']);
    $ethEnd          = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
    $experience['eth_end_date'] = EthiopianDateHelper::getMonthName($ethEnd['month']) . ' ' . $ethEnd['day'] . ' ' . $ethEnd['year'];

    echo json_encode([
        'status' => 'success',
        'data'   => $experience
    ]);
}

public function updateExperience()
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // ── 1. Input collection ───────────────────────────────────────
    $experienceId   = $_POST['experience_id'] ?? null;
    $employee_uuid   = trim($_POST['employee_uuid']    ?? '');
    $company_name    = trim($_POST['company_name']     ?? '');
    $job_title       = trim($_POST['job_title']        ?? '');
    $employment_type = trim($_POST['employment_type']  ?? '');
    $start_date      = trim($_POST['start_date']       ?? '');
    $end_date        = trim($_POST['end_date']         ?? '');
    $is_current      = 0;
    $registered_by   = $_SESSION['user']['id']         ?? null;

    // ── 2. Required Field Validation ─────────────────────────────
    $missingFields = array_keys(array_filter([
        'experience_id'   => !$experienceId,
        'employee_uuid'   => !$employee_uuid,
        'company_name'    => !$company_name,
        'job_title'       => !$job_title,
        'employment_type' => !$employment_type,
        'start_date'      => !$start_date,
        'end_date'        => !$end_date,  // ← was missing from your check
    ]));

    if (!empty($missingFields)) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_update_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'missing_required_fields',
                'missing_fields'=> $missingFields,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
                'experience_id' => $experienceId,
            ]
        );

        $_SESSION['error'] = 'እባክዎ ሁሉንም አስፈላጊ መረጃዎች ያስገቡ።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── 3. Date Validation ────────────────────────────────────────
    $today     = strtotime(date('Y-m-d'));
    $startTime = strtotime($start_date);
    $endTime   = strtotime($end_date);

    // Start date cannot be in the future
    if ($startTime > $today) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_update_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'start_date_in_future',
                'start_date'    => $start_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
                'experience_id' => $experienceId,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ የጀመሩበት ቀን ከዛሬ በላይ መሆን የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // End date cannot be in the future
    if ($endTime > $today) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_update_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'end_date_in_future',
                'end_date'      => $end_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
                'experience_id' => $experienceId,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ ስራ የጨረሱበት ቀን ከዛሬ በላይ መሆን የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // End date must be after start date
    if ($endTime <= $startTime) {
        \App\Helpers\AuditHelper::log(
            action:     'experience_update_validation_failed',
            entityType: 'experience',
            entityId:   null,
            oldValues:  null,
            newValues:  null,
            metadata:   [
                'reason'        => 'end_date_before_or_equal_start_date',
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
                'experience_id' => $experienceId,
            ]
        );

        $_SESSION['error'] = 'ስህተት፡ ስራ የጨረሱበት ቀን ከጀመሩበት ቀን ማነስ የለበትም።';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ── 4. Prepare Payload ────────────────────────────────────────
    $experienceData = [
        'id'             => $experienceId,
        'employee_uuid'  => $employee_uuid,
        'company_name'   => $company_name,
        'job_title'      => $job_title,
        'employment_type'=> $employment_type,
        'start_date'     => $start_date,
        'end_date'       => $end_date,
        'is_current'     => $is_current,
        'registered_by'  => $registered_by,
    ];

    // ── 5. Persist ────────────────────────────────────────────────
    try {
        $model = new ExperienceModel($this->db);

        // Check employment type overlap
        $isTypeOverlapping = $model->checkEmploymentTypeOverlap(
        $employee_uuid,
            $start_date,
            $end_date,
            $employment_type,
            $experienceId
        );

        if ($isTypeOverlapping) {
            \App\Helpers\AuditHelper::log(
                action:     'experience_update_validation_failed',
                entityType: 'experience',
                entityId:   null,
                oldValues:  null,
                newValues:  $experienceData,
                metadata:   [
                    'reason'          => 'employment_type_overlap',
                    'employment_type' => $employment_type,
                    'start_date'      => $start_date,
                    'end_date'        => $end_date,
                    'performed_by'    => $registered_by,
                    'employee_uuid'   => $employee_uuid,
                    'experience_id'   => $experienceId,
                ]
            );

            $_SESSION['error'] = 'በተመረጠው የጊዜ ገደብ ውስጥ ተመሳሳይ የቅጥር አይነት (' . $employment_type . ') ቀድሞ ተመዝግቧል።';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $result = $model->updateExperienceData($experienceData);

        if (!$result) {
            throw new \Exception("ዳታቤዝ ላይ መመዝገብ አልተቻለም።");
        }

        // ── 6. Audit log — success ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'experience_updated',
            entityType: 'experience',
            entityId:   $experienceId,
            oldValues:  null,
            newValues:  $experienceData,
            metadata:   [
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['success'] = 'የስራ ልምድ በትክክል ተስተካክሏል!';
        header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/employee-experience?uuid=" . urlencode($employee_uuid));
        exit();

    } catch (\Exception $e) {

        // ── 7. Audit log — failure ────────────────────────────────
        \App\Helpers\AuditHelper::log(
            action:     'experience_update_failed',
            entityType: 'experience',
            entityId:   $experienceId ?? null,
            oldValues:  null,
            newValues:  $experienceData ?? null,
            metadata:   [
                'reason'        => 'database_exception',
                'error'         => $e->getMessage(),
                'performed_by'  => $registered_by,
                'employee_uuid' => $employee_uuid,
            ]
        );

        $_SESSION['error'] = 'ስህተት ተፈጥሯል፡ ' . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
public function delete($params = []): void
{
    AuthHelper::checkRole(['hr_officer', 'hr_director', 'system_admin']);
    header('Content-Type: application/json');

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (string) ($data['id']   ?? '');
    $type= 'employee_experience';
    $reason = trim($data['reason']  ?? '');
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
             $model = new ExperienceModel($this->db);
            $action = 'experience_deleted';
            
        $result = $model->softDelete($id, $adminId, $reason, $source);

        if ($result['status'] === 'success') {
  

    $metadata = [
        'deletion_source' => $source,
        'reason'          => $reason
    ];

    \App\Helpers\AuditHelper::log(
        action:     $action,
        entityType: $type,
        entityId:   $id,
        oldValues:  $result['oldRecord'],
        newValues:  ['is_deleted' => 1],
        metadata:   $metadata
    );

   
}

        echo json_encode($result);

    } catch (\Exception $e) {
        error_log("Delete Error ({$type}): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።']);
    }
}
}