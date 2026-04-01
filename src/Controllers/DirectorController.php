<?php
namespace App\Controllers;
use App\Models\Director;
use App\Models\Position;
use App\Helpers\AuthHelper;
use Ramsey\Uuid\Uuid;   
class DirectorController extends BaseController {
      public function showDirector() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
         $branch_id = $_SESSION['user']['branch_id'] ?? null;
         if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: /HRM/register-director");
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
                header("Location: /HRM/register-director");
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
                    $_SESSION['success'] = "ዳይሬክቱሩ በተሳካ ሁኔታ ተመዝግቧል!";
                    header("Location: /HRM/register-director");
                    exit();
                } else {
                    $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
                    header("Location: /HRM/register-director");
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
                header("Location: /HRM/register-director");
                exit();
            }

    }
    public function showPosition() {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $branch_id = $_SESSION['user']['branch_id'] ?? null;
        if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: /HRM/register-position");
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
            $director_id = isset($_POST['director_name']) ? trim($_POST['director_name']) : '';
            $positionName = isset($_POST['position_name']) ? trim($_POST['position_name']) : '';
            $positionCode = isset($_POST['position_code']) ? trim($_POST['position_code']) : '';
            $seraDereja = isset($_POST['sera_dereja']) ? trim($_POST['sera_dereja']) : '';
            $seraRken = isset($_POST['sera_rken']) ? trim($_POST['sera_rken']) : '';
            $salary = isset($_POST['salary']) ? trim($_POST['salary']) : '';
            $yeteyashHuneta = isset($_POST['yeteyash_huneta']) ? trim($_POST['yeteyash_huneta']) : '';
            $nesaHkmna = isset($_POST['nesa_hkmna']) ? trim($_POST['nesa_hkmna']) : '';
            $clothDuration = isset($_POST['cloth_duration']) ? trim($_POST['cloth_duration']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';

            $organization_id = $_SESSION['user']['organization_id'] ?? null;
            $branch_id = $_SESSION['user']['branch_id'] ?? null;
            $registeredBy = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        }
        if (empty($positionName) || empty($director_id) || empty($positionCode) || empty($seraDereja) || empty($seraRken) || empty($salary)) {
            $_SESSION['error'] = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች በትክክል ያስገቡ!";
            header("Location: /HRM/register-position");
            exit();
        }

        $id = Uuid::uuid4()->toString();
        $positionModel = new Position($this->db);

        try {
            $result = $positionModel->create($id, 
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
                                            $clothDuration, 
                                            $description, 
                                            $registeredBy);

            if ($result) {
                $_SESSION['success'] = "መደቡ በተሳካ ሁኔታ ተመዝግቧል!";
                header("Location: /HRM/register-position");
                exit();
            } else {
                $_SESSION['error'] = "ምዝገባው አልተሳካም፤ እባክዎ እንደገና ይሞክሩ።";
                header("Location: /HRM/register-position");
                exit();
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = "ይህ መደብ ቀደም ብሎ ተመዝግቧል!";
            } else {
                error_log("Position Registration Error: " . $e->getMessage());
                $_SESSION['error'] = "የዳታቤዝ ስህተት አጋጥሟል፤ እባክዎ ቆይተው ይሞክሩ።";
            }
            header("Location: /HRM/register-position");
            exit(); 
}
   
}
}