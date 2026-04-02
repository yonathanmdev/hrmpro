<?php
namespace App\Controllers;

use App\Models\Director;
use App\Models\Posstion;
use Ramsey\Uuid\Uuid;
use App\Models\Developer;

class TeddybackendContoller extends BaseController {

  
    public function handlePositionRegistration() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $director_uuid = $_POST['director_name'] ?? '';
            $position_name = trim($_POST['position_name'] ?? '');
            $position_level = trim($_POST['grade'] ?? '');
            $salary = $_POST['salary'] ?? 0;
            $registeredBy = $_SESSION['user']['id'] ?? null;
            $position_code= trim($_POST['position_code'] ?? '');
            if (empty($position_name) || empty($director_uuid)) {
                $_SESSION['error'] = "እባክዎ አስፈላጊ መረጃዎችን በትክክል ያስገቡ!";
                header("Location: /HRM/register-posstion");
                exit();
            }

            $position_uuid = Uuid::uuid4()->toString();
            $positionModel = new Posstion($this->db);
            $positionModel->createposstion($position_uuid, $director_uuid, $position_name, $position_level,$position_code, $salary, $registeredBy);
            if($positionModel) {
                $_SESSION['success'] = "መደብ በትክክል ተመዝግቧል!";
            } else {
                $_SESSION['error'] = "መደብ መመዝገቢያ ላይ ችግር አጋጥሟል!";
            }
            header("Location: /HRM/register-posstion");
            exit();
        }
    }

public function handleDeveloperRegistration() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Collect and sanitize input
        $full_name   = trim($_POST['full_name'] ?? '');
        $position    = trim($_POST['position'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $active_time = trim($_POST['active_time'] ?? '');
        $payment     = $_POST['payment'] ?? 0;
        $registeredBy = $_SESSION['user']['id'] ?? null;

        // Validate required fields
        if (empty($full_name) || empty($position)) {
            $_SESSION['error'] = "እባክዎ አስፈላጊ መረጃዎችን በትክክል ያስገቡ!";
            header("Location: /HRM/register-developer");
            exit();
        }

        // Create developer
        $developer_uuid = Uuid::uuid4()->toString();
        $developerModel = new Developer($this->db);
        $result = $developerModel->createdeveloper(
            $developer_uuid,
            $full_name,
            $position,
            $email,
            $phone,
            $active_time,
            $payment
        );

        // Check result and set session message
        if ($result) {
            $_SESSION['success'] = "ባለሙያው በትክክል ተመዝግቧል!";
        } else {
            $_SESSION['error'] = "መመዝገብ አልተሳካም!";
        }

        // Redirect to prevent form resubmission (PRG pattern)
        header("Location: /HRM/register-developer");
        exit();
    }
}

}
?>