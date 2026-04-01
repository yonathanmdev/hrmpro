<?php
namespace App\Controllers;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\Director;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;

// 1. BaseControllerን እንዲወርስ እናደርጋለን
class UserController extends BaseController {
    
    public function showRegisterForm() {
         AuthHelper::checkRole(['system_admin', 'org_admin']);
    $myBranchId = $_SESSION['user']['branch_id'];
        // BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        // 'register-user' ማለት views/register-user.php ማለት ነው
        $users = (new User($this->db));
        $branchModel =  new Branch($this->db);
        $branchName = $branchModel->getBranchById($_SESSION['user']['branch_id']);
        $organizations = [];
         if ($_SESSION['user']['role'] === 'system_admin') {
            $users = $users->getAllOrgAdmins(); // ያንተ የድሮ ፋንክሽን
         $organizations = (new Organization($this->db))->getAll();

        } else {
            // ሌላ ከሆነ ግን የሱንና የንዑስ ቅርንጫፎቹን ብቻ
            $users = $users->getUsersForMyBranchHierarchy($myBranchId);
            $organizations = (new Branch($this->db))->getImmediateSubBranches($myBranchId);
          
        }
        $this->render('register-user', [
            'title' => 'ተጠቃሚ መመዝገቢያ',
            'organizations' => $organizations,
            'users' => $users,
            'branchName' => $branchName
        ]);
    }

    public function handleRegistration() {
    // 1. Check roles
    AuthHelper::checkRole(['system_admin', 'org_admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. ዳታውን መቀበል እና trim() ማድረግ
            $firstName    = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
            $fatherName   = isset($_POST['fathername']) ? trim($_POST['fathername']) : '';
            $gFatherName  = isset($_POST['grandfathername']) ? trim($_POST['grandfathername']) : '';
            $email        = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password     = $_POST['password'] ?? '';
            $phone           = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $organization_id = isset($_POST['organization_id']) ? trim($_POST['organization_id']) : '';
            $branchModel = new Branch($this->db);
            $mainBranchId = $branchModel->getMainOfficeId($organization_id);
       
            if($_SESSION['user']['role'] === 'system_admin') {
                $role = 'org_admin'; // ስለ ሲስተም አድሚን ብቻ ይመዘገባል
                 // SQL በ Controller ውስጥ ከመጻፍ ይልቅ ሞዴሉን እንጠይቃለን
        $mainBranchId = $branchModel->getMainOfficeId($organization_id);

        if (!$mainBranchId) {
            throw new \Exception("የዚህ ድርጅት ዋና መሥሪያ ቤት አልተገኘም!");
        }
            } else {
                $role         = $_POST['role'] ?? '';
                if($role === 'org_admin'){
                    $organization_id = $_SESSION['user']['organization_id'];
                    $mainBranchId = isset($_POST['organization_id']) ? trim($_POST['organization_id']) : '';
                  
                }
                else{
                     $organization_id = $_SESSION['user']['organization_id'];
                    $mainBranchId = $_SESSION['user']['branch_id'];
                }
            }
          
            $registeredBy = $_SESSION['user']['id'] ?? null;


            // 2. Validation (መሰረታዊ ማረጋገጫ)
            if (empty($firstName) || empty($email) || empty($password) || empty($role) || empty($fatherName) 
                || empty($gFatherName) || empty($phone) ) {
                $_SESSION['error'] = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!";
                header("Location: /HRM/register-user");
                exit();
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "እባክዎ ትክክለኛ ኢሜይል ያስገቡ!";
                header("Location: /HRM/register-user");
                exit();
            }
     
            // 3. UUID እና Password Hash (በኮንትሮለር ደረጃ)
            $uuid = Uuid::uuid4()->toString();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // 4. የ User ሞዴልን መጥራት (ከ BaseController የመጣውን $this->db በመስጠት)
            $userModel = new User($this->db);

            try {
                // 5. ዳታቤዝ ውስጥ እንዲመዘግብ ለሞዴሉ ንጹህ ዳታ መላክ
                $result = $userModel->create(
                    $uuid,
                    $organization_id,
                    $mainBranchId,
                    $firstName,
                    $fatherName,
                    $gFatherName,
                    $email,
                    $hashedPassword,
                    $role,
                    $registeredBy
                );

                if ($result) {
                    $_SESSION['success'] = "ተጠቃሚው በተሳካ ሁኔታ ተመዝግቧል!";
                    header("Location: /HRM/register-user");
                    exit();
                } else {
                    $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
                    header("Location: /HRM/register-user");
                    exit();
                }

            } catch (\PDOException $e) {
                // Duplicate entry (ኢሜይል ከተደገመ)
                if ($e->getCode() == 23000) {
                    $_SESSION['error'] = "ይህ ኢሜይል ቀደም ብሎ ተመዝግቧል!";
                } else {
                    error_log("Registration Error: " . $e->getMessage());
                    $_SESSION['error'] = "የቴክኒክ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
                }
                header("Location: /HRM/register-user");
                exit();
            }
        }
    }
}