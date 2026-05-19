<?php
namespace App\Controllers;
use App\Models\Director;
use App\Models\Position;
use App\Helpers\AuthHelper; 
class ReportController extends BaseController {
      public function showReport() {
         AuthHelper::checkRole(['hr_director', 'hr_officer']);
         $branch_id = $_SESSION['user']['branch_id'] ?? null;
         if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: " . $_ENV['BASE_URL'] . "/register-director");
            exit();
        }
        //$directorModel = new Director($this->db);
        //$directors = $directorModel->getAllDirectors($branch_id);

         $this->render('report', [
            'title' => 'Report Generation',
        ]);
    }  
}