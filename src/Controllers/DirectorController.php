<?php
namespace App\Controllers;
use App\Models\Director;
use App\Models\Position;
use App\Models\User;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;   
class DirectorController extends BaseController {
      public function showDirector() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
         $branch_id = $_SESSION['user']['branch_id'] ?? null;
         if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-director");
            exit();
        }
        $directorModel = new Director($this->db);
        $directors = $directorModel->getAllDirectors($branch_id);

         $this->render('register-director', [
            'title' => 'ዳይሬክተር መመዝገቢያ',
            'directors' => $directors
        ]);
    }  
    public function handleDirector() {
   // 1. Check roles
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
        // የዳይሬክተሩ መመዝገቢያ ሂደት እዚህ ይገባል
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 2. ዳታውን መቀበል እና trim() ማድረግ
            $directorName = isset($_POST['director_name']) ? trim($_POST['director_name']) : '';
            $organization_id = $_SESSION['user']['organization_id'] ?? null;
            $branch_id = $_SESSION['user']['branch_id'] ?? null;
            $registeredBy = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        }
            // 3. Validation: ስሙ ባዶ አለመሆኑን ማረጋገጥ
            if (empty($directorName)) {
                $_SESSION['error'] = "እባክዎ የዳይሬክተሩን ስም በትክክል ያስገቡ!";
                header("Location: " . $_ENV['BASE_URL'] . "/register-director");
                exit();
            }

            // 4. UUID በ Controller ደረጃ ማመንጨት
            $id = Uuid::uuid4()->toString();
            // 5. የ Director ሞዴልን መጥራት (ከ BaseController የመጣውን $this->db በመስጠት)
            $directorModel = new Director($this->db);

            try {
                // 6. ዳታቤዝ ውስጥ እንዲመዘግብ ለሞዴሉ ID እና Name መላክ
                $result = $directorModel->create($id, $organization_id, $branch_id, $directorName, $registeredBy);

                if ($result) {
                    // Log director creation
                    \App\Helpers\AuditHelper::log('director_created', 'director', $id, null, [
                        'organization_id' => $organization_id,
                        'branch_id' => $branch_id,
                        'name' => $directorName,
                        'registered_by' => $registeredBy
                    ]);

                    $_SESSION['success'] = "ዳይሬክቱሩ በተሳካ ሁኔታ ተመዝግቧል!";
                    header("Location: " . $_ENV['BASE_URL'] . "/register-director");
                    exit();
                } else {
                    $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
                    header("Location: " . $_ENV['BASE_URL'] . "/register-director");
                    exit();
                }
            } catch (\PDOException $e) {
                // ስሙ ደግሞ ከተመዘገበ (Unique constraint ካለህ)
                if ($e->getCode() == 23000) {
                    $_SESSION['error'] = "ይህ ድርጅት ቀደም ብሎ ተመዝግቧል!";
                } else {
                    error_log("Org Registration Error: " . $e->getMessage());
                    $_SESSION['error'] = "የዳታቤዝ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
                }
                header("Location: " . $_ENV['BASE_URL'] . "/register-director");
                exit();
            }

    }
    public function showPosition() {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $branch_id = $_SESSION['user']['branch_id'] ?? null;
        if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-position");
            exit();
        }
        $directorModel = new Director($this->db);
        $directors = $directorModel->getAllDirectors($branch_id);

       
        $positionModel = new Position($this->db);
        $positions = $positionModel->getAllPositions($branch_id);

         $this->render('register-position', [
            'title' => 'መደብ መመዝገቢያ',
            'positions' => $positions,
            'directors' => $directors
        ]);
    }  
    public function handlePositionRegistration() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $director_id    = trim($_POST['director_name']    ?? '');
        $positionName   = trim($_POST['position_name']    ?? '');
        $positionCode   = trim($_POST['position_code']    ?? '');
        $seraDereja     = trim($_POST['sera_dereja']      ?? '');
        $seraRken       = trim($_POST['sera_rken']        ?? '');
        $salary         = trim($_POST['salary']           ?? '');
        $yeteyashHuneta = trim($_POST['yeteyash_huneta']  ?? '');
        $nesaHkmna      = trim($_POST['nesa_hkmna']       ?? 'no');
        $description    = trim($_POST['description']      ?? '');
        $allowMultiple  = 0; //(int)($_POST['allow_multiple']  ?? 0);
        $vacancyCount   = null;// !empty($_POST['vacancy_count']) ? (int)$_POST['vacancy_count'] : null;

        // ✅ Fix — null when cloth disabled or empty
        $clothEnabled  = trim($_POST['cloth_enabled']   ?? 'no');
        $clothDuration = $clothEnabled === 'yes' && !empty(trim($_POST['cloth_duration'] ?? ''))
                         ? trim($_POST['cloth_duration'])
                         : null;  // ← null not empty string

        $organization_id = $_SESSION['user']['organization_id'] ?? null;
        $branch_id       = $_SESSION['user']['branch_id']       ?? null;
        $registeredBy    = $_SESSION['user']['id']              ?? null;
    }

    if (empty($positionName) || empty($director_id) || empty($positionCode) 
        || empty($seraDereja) || empty($seraRken) || empty($salary)) {
        $_SESSION['error'] = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!";
        header("Location: " . $_ENV['BASE_URL'] . "/register-position");
        exit();
    }

    $id            = Uuid::uuid4()->toString();
    $positionModel = new Position($this->db);

    try {
        $result = $positionModel->create(
            $id,
            $director_id,
            $organization_id,
            $branch_id,
            $positionName,
            $positionCode,
            $seraDereja,
            $seraRken,
            $salary,
            $yeteyashHuneta,
            $nesaHkmna,
            $clothDuration,   // ← now null when disabled
            $description,
            $registeredBy,
            $allowMultiple,
            $vacancyCount
        );

        if ($result) {
            \App\Helpers\AuditHelper::log('position_created', 'position', $id, null, [
                'director_id'     => $director_id,
                'organization_id' => $organization_id,
                'branch_id'       => $branch_id,
                'name'            => $positionName,
                'code'            => $positionCode,
                'sera_dereja'     => $seraDereja,
                'sera_rken'       => $seraRken,
                'salary'          => $salary,
                'yeteyash_huneta' => $yeteyashHuneta,
                'nesa_hkmna'      => $nesaHkmna,
                'cloth_duration'  => $clothDuration,
                'allow_multiple'  => $allowMultiple,
                'vacancy_count'   => $vacancyCount,
                'description'     => $description,
                'registered_by'   => $registeredBy,
            ]);

            $_SESSION['success'] = "መደቡ በተሳካ ሁኔታ ተመዝግቧል!";
        } else {
            $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
        }

    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "ይህ መደብ ቀደም ብሎ ተመዝግቧል!";
        } else {
            error_log("Position Registration Error: " . $e->getMessage());
            $_SESSION['error'] = "የዳታቤዝ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
        }
    }

    header("Location: " . $_ENV['BASE_URL'] . "/register-position");
    exit();
}
public function handleEditDirector() {
    // ለጃቫ ስክሪፕት ምላሽ ለመስጠት header ማስተካከል
     AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {        
        $directorId = isset($_POST['id']) ? trim($_POST['id']) : '';
        $directorName = isset($_POST['director_name']) ? trim($_POST['director_name']) : '';
               if (empty($directorId) || empty($directorName) ) {
            echo json_encode(['status' => 'error', 'message' => 'እባክዎ የተቋሙን መለያ እና ስም በትክክል ያስገቡ!']);
            exit();
        }

        $orgModel = new Director($this->db);

        try {
            // Get old data for logging
            $oldData = $orgModel->getDirectorById($directorId);
            
            $result = $orgModel->update($directorId, $directorName);

            if ($result) {
                // Log branch update
                \App\Helpers\AuditHelper::log('director_updated', 'director', $directorId, $oldData, ['director_name' => $directorName], ['updated_by' => $_SESSION['user']['id'] ?? null]);

                echo json_encode(['status' => 'success', 'message' => 'ዳይሬክተር በተሳካ ሁኔታ ተሻሽሏል!']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ማስተካከያው አልተሳካም፤ ምንም የተቀየረ መረጃ የለም።']);
                exit();
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ይህ ዳይሬክተር ቀደም ብሎ ተመዝግቧል!']);
            } else {
                error_log("Director Update Error: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'የዳታቤዝ ስህተት አጋጥሟል!']);
            }
            exit();
        }
    }
}

public function handleEditPosition() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $positionId = isset($_POST['id']) ? trim($_POST['id']) : '';
        $directorId = isset($_POST['director_name']) ? trim($_POST['director_name']) : '';
        $positionName = isset($_POST['position_name']) ? trim($_POST['position_name']) : '';
        $positionCode = isset($_POST['position_code']) ? trim($_POST['position_code']) : '';
        $seraDereja = isset($_POST['sera_dereja']) ? trim($_POST['sera_dereja']) : '';
        $seraRken = isset($_POST['sera_rken']) ? trim($_POST['sera_rken']) : '';
        $salary = isset($_POST['salary']) ? trim($_POST['salary']) : '';
        $yeteyashHuneta = isset($_POST['yeteyash_huneta']) ? trim($_POST['yeteyash_huneta']) : '';
        $allowMultiple  = (int)($_POST['allow_multiple']  ?? 0);
        $vacancyCount   = !empty($_POST['vacancy_count']) 
                          ? (int)$_POST['vacancy_count'] 
                          : null;
        $nesaHkmna = isset($_POST['nesa_hkmna']) ? trim($_POST['nesa_hkmna']) : '';
        $clothDuration = isset($_POST['cloth_duration']) ? trim($_POST['cloth_duration']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        if (empty($positionId) || empty($positionName) || empty($directorId)) {
            echo json_encode(['status' => 'error', 'message' => 'እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!']);
            exit();
        }

        $positionModel = new Position($this->db);

        try {
            // Get old data for logging
            $oldData = $positionModel->getPositionById($positionId);

            $data = [
                'director_id' => $directorId,
                'job_name' => $positionName,
                'job_identifier_no' => $positionCode,
                'dereja' => $seraDereja,
                'scale' => $seraRken,
                'salary' => $salary,
                'wastna' => $yeteyashHuneta,
                'allow_multiple'  => $allowMultiple,
                'vacancy_count'   => $vacancyCount,
                'hkmna' => $nesaHkmna,
                'cloth_due' => $clothDuration,
                'description' => $description
            ];

            $result = $positionModel->update($positionId, $data);

            if ($result) {
                // Log position update
                \App\Helpers\AuditHelper::log('position_updated', 'job_property', $positionId, $oldData, $data, ['updated_by' => $_SESSION['user']['id'] ?? null]);

                echo json_encode(['status' => 'success', 'message' => 'መደቡ በተሳካ ሁኔታ ተሻሽሏል!']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ማስተካከያው አልተሳካም፤ ምንም የተቀየረ መረጃ የለም።']);
                exit();
            }
        } catch (\PDOException $e) {
            error_log("Position Update Error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'የዳታቤዝ ስህተት አጋጥሟል!']);
            exit();
        }
    }
}

public function getPositionById() {
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    $id = isset($_GET['id']) ? trim($_GET['id']) : '';

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'መለያ አልተገኘም']);
        exit();
    }

    try {
        $positionModel = new Position($this->db);
        $position = $positionModel->getPositionById($id);

        if ($position) {
            echo json_encode(['status' => 'success', 'position' => $position]);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'መደቡ አልተገኘም']);
            exit();
        }
    } catch (\PDOException $e) {
        error_log("Get Position Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'የዳታቤዝ ስህተት አጋጥሟል!']);
        exit();
    }
}

public function deletePosition(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        return;
    }

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = trim((string) ($data['id']   ?? ''));
    $userId = trim((string) ($_SESSION['user']['id'] ?? ''));
    $reason = trim($data['reason']      ?? '');
        $source = 'INDIVIDUAL';
        $password = $data['confirm_password'] ?? '';

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

       

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    if (empty($userId)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
        $model  = new Position($this->db);
        $result = $model->softDelete($id, $userId, $reason, $source);

        if ($result['status'] === 'success') {
            \App\Helpers\AuditHelper::log(
                action:     'position_deleted',
                entityType: 'job_property',
                entityId:   $id,
                newValues:  ['status' => 'inactive'],
                metadata:   [
                    'deleted_position'  => 1,
                    'deletion_type'     => 'INDIVIDUAL',
                    'cascade_employees' => $result['active_employee_count'],
                    'reason'            => $reason
                ]
            );
        }

        echo json_encode($result);

    } catch (\Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ስህተት፡ ' . $e->getMessage()]);
    }
}

 // Handle delete — returns JSON
public function delete(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (string) ($data['id']   ?? '');
    $adminId = $_SESSION['user']['id'] ?? '';
    $type = 'director'; // ለ Audit Log
    $reason = trim($data['reason']      ?? '');
        $source = 'INDIVIDUAL';
        $password = $data['confirm_password'] ?? '';

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

       

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    if (empty($adminId)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
            $model = new Director($this->db);
            $action = 'director_deleted';
            $metaKey = 'affected_records'; // ለድርጅት ቅርንጫፎች ይባላሉ
        $result = $model->softDelete($id, $adminId, $reason, $source);

        if ($result['status'] === 'success') {
    // መጀመሪያ መረጃዎቹን ከሪሰልት እናውጣ
    $jobPropertyCount = $result['jobPropertyCount'] ?? 0;
        $employeeCount = $result['employeeCount'] ?? 0;

    // ንዑስ መረጃዎች (Users ወይም Branches) አብረው ከጠፉ 'CASCADED' ይሁን፣ ካልሆነ 'INDIVIDUAL'
    $source = ($jobPropertyCount > 0 || $employeeCount > 0) ? 'CASCADED' : 'INDIVIDUAL_ACTION';

    $metadata = [
        $metaKey => $result['jobPropertyCount'] ?? 0 + $result['employeeCount'] ?? 0,
        'affected_job_properties' => $result['jobPropertyCount'] ?? 0,
        'affected_employees' => $result['employeeCount'] ?? 0,
        'deletion_source' => $source
    ];

    \App\Helpers\AuditHelper::log(
        action:     $action,
        entityType: $type,
        entityId:   $id,
        newValues:  ['status' => 'inactive'],
        metadata:   $metadata
    );

    unset($result['jobPropertyCount'], $result['employeeCount']);
}

        echo json_encode($result);

    } catch (\Exception $e) {
        error_log("Delete Error ({$type}): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'ስህተት ተፈጥሯል፤ እባክዎ በድጋሚ ይሞክሩ።']);
    }
}
 
public function showDeletedLists() {
     AuthHelper::checkRole(['hr_director', 'hr_officer']);

        $myBranchId = $_SESSION['user']['branch_id'] ?? null;
        $userModel = (new Director($this->db));
            $users = $userModel->findAllDeleted($myBranchId);
        $this->render('deleted-directors', [
        'title' => 'የተሰረዙ ዳይሬክተሮች',
        'users' => $users
    ]);
   
}
public function showDeletedPositions() {
     AuthHelper::checkRole(['hr_director', 'hr_officer']);

        $myBranchId = $_SESSION['user']['branch_id'] ?? null;
        $userModel = (new Position($this->db));
            $deletedPositions = $userModel->findAllDeleted($myBranchId);
        $this->render('deleted-positions', [
        'title' => 'የተሰረዙ የስራ መደቦች',
        'deletedPositions' => $deletedPositions
    ]);
   
}

 public function restore(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        return;
    }

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = trim((string) ($data['id'] ?? ''));
    $userId = trim((string) ($_SESSION['user']['id'] ?? ''));

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    if (empty($userId)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        return;
    }

    try {
        $model  = new Director($this->db);
        $result = $model->restore($id, $userId);

        if ($result['status'] === 'success') {
            \App\Helpers\AuditHelper::log(
                action:     'director_restored',
                entityType: 'director',
                entityId:   $id,
                newValues:  ['status' => 'active'],
                metadata:   [
                    'restored_director'    => 1,
                    'restore_type'         => 'cascaded_restore',
                    'restored_properties'  => $result['jobPropertyCount'],
                    'restored_employees'   => $result['employeeCount'],
                ]
            );
        }

        echo json_encode($result);

    } catch (\Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ስህተት፡ ' . $e->getMessage()]);
    }
}

public function restorePosition(): void
{
    AuthHelper::checkRole(['hr_director', 'hr_officer']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        return;
    }

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = trim((string) ($data['id']   ?? ''));
    $userId = trim((string) ($_SESSION['user']['id'] ?? ''));

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    if (empty($userId)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        return;
    }

    try {
        $model  = new Position($this->db);
        $result = $model->restore($id, $userId);

        if ($result['status'] === 'success') {
            \App\Helpers\AuditHelper::log(
                action:     'position_restored',
                entityType: 'job_property',
                entityId:   $id,
                newValues:  ['status' => 'active'],
                metadata:   [
                    'restored_position'   => 1,
                    'restore_type'        => 'cascaded_restore',
                    'restored_employees'  => $result['employeeCount'],
                ]
            );
        }

        echo json_encode($result);

    } catch (\Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'ስህተት፡ ' . $e->getMessage()]);
    }
}
}