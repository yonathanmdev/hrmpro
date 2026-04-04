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
                header("Location: " . $_ENV['BASE_URL'] . "/register-user");
                exit();
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "እባክዎ ትክክለኛ ኢሜይል ያስገቡ!";
                header("Location: " . $_ENV['BASE_URL'] . "/register-user");
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
                    $phone,
                    $email,
                    $hashedPassword,
                    $role,
                    $registeredBy
                );

                if ($result) {
                    $_SESSION['success'] = "ተጠቃሚው በተሳካ ሁኔታ ተመዝግቧል!";
                    header("Location: " . $_ENV['BASE_URL'] . "/register-user");
                    exit();
                } else {
                    $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
                    header("Location: " . $_ENV['BASE_URL'] . "/register-user");
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
                header("Location: " . $_ENV['BASE_URL'] . "/register-user");
                exit();
            }
        }
    }

public function getUserById()
{
     AuthHelper::checkRole(['system_admin', 'org_admin']);
    header('Content-Type: application/json');

    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid ID'
        ]);
        return;
    }

    $userModel = new User($this->db);
    $user = $userModel->findById($id);

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'data' => $user
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found'
        ]);
    }
}
public function handleUpdateUser()
{
    AuthHelper::checkRole(['system_admin', 'org_admin']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . $_ENV['BASE_URL'] . "/register-user");
        exit();
    }

    try {
        // 1. መረጃዎችን መቀበል
        $id = $_POST['id'] ?? null; // በ Form ውስጥ <input type="hidden" name="id"> መኖሩን አረጋግጥ
        
        $data = [
            'first_name'        => trim($_POST['edit_firstname']),
            'father_name'       => trim($_POST['edit_fathername']),
            'grand_father_name' => trim($_POST['edit_grandfathername']),
            'phone'             => trim($_POST['edit_phone']),
            'email'             => trim($_POST['edit_email'])
        ];

        // 2. Validation (መሰረታዊ ማረጋገጫ)
        if (empty($id) || in_array("", $data)) {
            $_SESSION['error'] = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-user");
            exit();
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "ትክክለኛ ኢሜይል ያስገቡ!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-user");
            exit();
        }

        // 3. Update ለማድረግ መሞከር
        $userModel = new User($this->db);
        $isUpdated = $userModel->updateUser($id, $data);

        if ($isUpdated) {
            $_SESSION['success'] = "መረጃው በተሳካ ሁኔታ ተቀይሯል!";
        } else {
            // እዚህ ጋር ዳታቤዙ ላይ ምንም ለውጥ ካልተደረገ (ለምሳሌ መረጃው ያው ከሆነ)
            $_SESSION['info'] = "ምንም የተቀየረ አዲስ መረጃ የለም።";
        }

    } catch (\Exception $e) {
        error_log("Update Error: " . $e->getMessage());
        $_SESSION['error'] = "የቴክኒክ ስህተት ተፈጥሯል።";
    }

    header("Location: " . $_ENV['BASE_URL'] . "/register-user");
    exit();
}
}