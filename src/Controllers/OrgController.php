<?php
namespace App\Controllers;
use App\Models\Organization;
use App\Models\Branch;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;

// 1. BaseControllerን እንዲወርስ እናደርጋለን
class OrgController extends BaseController {
    
   public function showRegisterForm() {
     AuthHelper::checkRole(['system_admin', 'org_admin']);
    $organizations =[];
    if ($_SESSION['user']['role'] === 'system_admin') {
        $organizations = (new Organization($this->db))->getAll();
        $this->render('register-organization', [
        'title' => 'ድርጅት መመዝገቢያ',
        'organizations' => $organizations
    ]);
    }
    else {
      $myBranchId = $_SESSION['user']['branch_id'];
    // 2. ሞዴሉን መጥራት
    $branchModel = new Branch($this->db);
    $organizations = $branchModel->getImmediateSubBranches($myBranchId);

    $this->render('register-branch', [
        'title' => 'ቅርንጫፍ መመዝገቢያ',
        'organizations' => $organizations
    ]);
    }
   }

    public function handleRegistration() {
        AuthHelper::checkRole(['system_admin', 'org_admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. ዳታውን መቀበል
        $orgName = isset($_POST['org_name']) ? trim($_POST['org_name']) : '';
        $orgDescription = isset($_POST['org_description']) ? trim($_POST['org_description']) : '';
        
        // Session ውስጥ ተጠቃሚው መኖሩን ማረጋገጥ (ደህንነት)
        $registeredBy = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

        // 2. Validation
        if (empty($orgName) || empty($orgDescription)) {
            $_SESSION['error'] = "እባክዎ ሁሉንም መስኮች በትክክል ይሙሉ!";
            header("Location: /HRM/register-organization");
            exit();
        }

        if (!$registeredBy) {
            $_SESSION['error'] = "ለዚህ ተግባር መጀመሪያ መግባት (Login) አለብዎት!";
            header("Location: /login");
            exit();
        }
        // 3. UUID ማመንጨት (ለ Organization)
        $id = Uuid::uuid4()->toString();

        $orgModel = new Organization($this->db);

        try {
            // 4. ሞዴሉን መጥራት (ይህ ድርጅቱን እና Main Officeን በአንድ ላይ ይመዘግባል)
            $result = $orgModel->create($id, $orgName, $orgDescription, $registeredBy);

            if ($result) {
                $_SESSION['success'] = "ድርጅቱ እና ዋና መሥሪያ ቤቱ በተሳካ ሁኔታ ተመዝግቧል!";
                header("Location: /HRM/register-organization");
                exit();
            }
        } catch (\Exception $e) {
            // 5. ስህተቶችን መያዝ
            if ($e instanceof \PDOException && $e->getCode() == 23000) {
                $_SESSION['error'] = "ይህ ድርጅት ቀደም ብሎ ተመዝግቧል!";
            } else {
                error_log("Registration Error: " . $e->getMessage());
                $_SESSION['error'] = "የቴክኒክ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
            }
            header("Location: /HRM/register-organization");
            exit();
        }
    }
}
    
    
public function handleEditOrganization() {
    // ለጃቫ ስክሪፕት ምላሽ ለመስጠት header ማስተካከል
     AuthHelper::checkRole(['system_admin', 'org_admin']);
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {        
        $orgId = isset($_POST['id']) ? trim($_POST['id']) : '';
        $orgName = isset($_POST['org_name']) ? trim($_POST['org_name']) : '';

        if (empty($orgId) || empty($orgName)) {
            echo json_encode(['status' => 'error', 'message' => 'እባክዎ የተቋሙን መለያ እና ስም በትክክል ያስገቡ!']);
            exit();
        }

        $orgModel = new Organization($this->db);

        try {
            $result = $orgModel->updateOrganization($orgId, $orgName);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'ድርጅቱ በተሳካ ሁኔታ ተሻሽሏል!']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ማስተካከያው አልተሳካም፤ ምንም የተቀየረ መረጃ የለም።']);
                exit();
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ይህ ድርጅት ቀደም ብሎ ተመዝግቧል!']);
            } else {
                error_log("Org Update Error: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'የዳታቤዝ ስህተት አጋጥሟል!']);
            }
            exit();
        }
    }
}
public function handleBranchRegistration() {
     AuthHelper::checkRole(['system_admin', 'org_admin']);
    // የቅርንጫፍ መመዝገቢያ ሂደት እዚህ ይገባል
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1. ዳታውን መቀበል እና trim() ማድረግ   
        $branchName = isset($_POST['branch_name']) ? trim($_POST['branch_name']) : '';
        $registeredBy = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;  
        $branchModel =  new Branch($this->db);
        $branchLevel = $branchModel->getBranchById($_SESSION['user']['branch_id']);
        if (!$branchLevel || !isset($branchLevel['level'])) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: /HRM/register-branch");
            exit();
        }
        $level = $branchLevel['level'] + 1; // የተጠቃሚው ቅርንጫፍ የሚገኘው በእርሱ በላይ ነው
        $parentId =$_SESSION['user']['branch_id'] ?? null; // የተጠቃሚው ቅርንጫፍ ID
        $orgId = $_SESSION['user']['organization_id'] ?? null;
        // 2. Validation
        if (empty($branchName)) {
            $_SESSION['error'] = "እባክዎ የቅርንጫፉን ስም በትክክል ያስገቡ!";
            header("Location: /HRM/register-branch");
            exit();        
 }
        // 3. UUID ማመንጨት (ለ Branch)
        $id = Uuid::uuid4()->toString();

        try {
            // 4. ሞዴሉን መጥራት
            $result = $branchModel->insertBranch([
                'id' => $id,
                'org_id' => $orgId,
                'parent_id' => $parentId,
                'name' => $branchName,
                'level' => $level,
                'registered_by' => $registeredBy
            ]);

            if ($result) {
                $_SESSION['success'] = "ቅርንጫፉ በተሳካ ሁኔታ ተመዝግቧል!";
                header("Location: /HRM/register-branch");
                exit();
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = "ይህ ቅርንጫፍ ቀደም ብሎ ተመዝግቧል!";
            } else {
                error_log("Branch Registration Error: " . $e->getMessage());
                $_SESSION['error'] = "የዳታቤዝ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
            }
            header("Location: /HRM/register-branch");
            exit();
        }
    }
}
}
