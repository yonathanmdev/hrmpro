<?php
namespace App\Controllers;

use App\Models\ReportModel;
use App\Helpers\AuthHelper; 

class ReportController extends BaseController {
    
    private array $reportColumns = [
        'employees'   => ['#', 'ሙሉ ስም', 'ፆታ', 'የቅጥር ሁኔታ', 'የተቀጠሩበት ቀን'],
        'payroll'     => ['#', 'ሙሉ ስም', 'ፆታ', 'ዲፓርትመንት', 'የተቀጠሩበት ቀን'],
        'education'   => ['#', 'ሙሉ ስም', 'ፆታ', 'የቅጥር ሁኔታ', 'የተቀጠሩበት ቀን'], 
        'age'         => ['ተ.ቁ', 'ዕድሜ ክልል', 'ቋሚ ወንድ', 'ቋሚ ሴት', 'ቋሚ ድምር', 'ጊዜያዊ ወንድ', 'ጊዜያዊ ሴት', 'ጊዜያዊ ድምር', 'ጠቅላላ ወንድ', 'ጠቅላላ ሴት', 'ጠቅላላ ድምር'], 
        'level'       => ['ተ.ቁ', 'የስራ ደረጃ', 'ቋሚ ወንድ', 'ቋሚ ሴት', 'ቋሚ ድምር', 'ጊዜያዊ ወንድ', 'ጊዜያዊ ሴት', 'ጊዜያዊ ድምር', 'ጠቅላላ ወንድ', 'ጠቅላላ ሴት', 'ጠቅላላ ድምር'],
        'discipline'  => ['ተ.ቁ', 'የዲሲፕሊን የቅጣት ሁኔታዎች', 'ቋሚ ወንድ', 'ቋሚ ሴት', 'ቋሚ ድምር', 'ጊዜያዊ ወንድ', 'ጊዜያዊ ሴት', 'ጊዜያዊ ድምር', 'ጠቅላላ ወንድ', 'ጠቅላላ ሴት', 'ጠቅላላ ድምር'],
        'performance' => ['ተ.ቁ', 'የአፈጻጸም ደረጃ', 'ቋሚ ወንድ', 'ቋሚ ሴት', 'ቋሚ ድምር', 'ጊዜያዊ ወንድ', 'ጊዜያዊ ሴት', 'ጊዜያዊ ድምር', 'ጠቅላላ ወንድ', 'ጠቅላላ ሴት', 'ጠቅላላ ድምር'],
    ];

    private array $reportTitles = [
        'employees'   => 'የሰራተኞች ብዛት ማጠቃለያ ሪፖርት (በፆታ እና ቅጥር ሁኔታ)',
        'payroll'     => 'የደመወዝ ሪፖርት ማጠቃለያ',
        'education'   => 'የሰራተኞች የትምህርት ደረጃ ማጠቃለያ ሪፖርት', 
        'age'         => 'የሰራተኞች ብዛት ማጠቃለያ ሪፖርት በዕድሜ ክልል', 
        'level'       => 'የሠራተኞች ብዛት በስራ ደረጃ እና በፆታ ማጠቃለያ ሪፖርት',
        'discipline'  => 'የቢሮው ሠራተኞች የዲሲፕሊን ሁኔታ ማጠቃለያ ሪፖርት',
        'performance' => 'የሰራተኞች የBSC አፈጻጸም ምዘና ማጠቃለያ ሪፖርት',
    ];

    public function handleReport(array $params = []) {
        if (empty($params['uuid'])) {
            $this->showReportCards();
        } else {
            $this->generateReportView($params);
        }
    }

    private function showReportCards() {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);
        $branch_id = $_SESSION['user']['branch_id'] ?? null;
        if (!$branch_id) {
            $_SESSION['error'] = "የቅርንጫፍ መረጃ አልተገኘም!";
            header("Location: " . $_ENV['BASE_URL'] . "/report");
            exit();
        }
        $this->render('report', [
            'title'    => 'Report Generation',
            'branchId' => $branch_id
        ]);
    }  

    private function generateReportView(array $params) {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);

        // 🔄 የመጣውን የሪፖርት አይነት በጥንቃቄ መቀበል
        $reportType = $params['uuid']      ?? ''; 
        $branchId   = $params['record_id'] ?? ''; 

        // 💡 ዋናው ማስተካከያ፦ ገና መረጃው እንደመጣ ወዲያውኑ ወደ ትክክለኛው 'discipline' እንቀይረዋለን!
        if ($reportType == 'discipline') {
            $reportType = 'discipline';
        }

        // 🛡️ የደህንነት ማረጋገጫ ማሻሻያ
        if (empty($branchId)) {
            die('ስህተት፦ የቅርንጫፍ መለያ (Branch ID) ባዶ ነው።');
        }

        $filters = [
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'department' => $_GET['department'] ?? null,
        ];

        $model = new ReportModel($this->db);
        
        // አሁን $reportType ሁልጊዜም 'discipline' መሆኑ የተረጋገጠ ነው
        $data  = $model->getReport($reportType, $branchId, $filters);
        
        $genderSummary = $model->getGenderCounts($branchId, $filters);
        $departments = method_exists($model, 'getDepartments') ? $model->getDepartments($branchId) : [];

        // 🔄 የቪው መምሪያ ሎጂክ
        if ($reportType === 'education') {
            $viewName = 'report_education';
        } elseif ($reportType === 'age') {
            $viewName = 'report_age';
        } elseif ($reportType === 'level') {
            $viewName = 'report_level'; 
        } elseif ($reportType === 'discipline') {
            $viewName = 'report_despline'; // ➡️ ወደ report_discipline.php ይመራል
        } elseif ($reportType === 'performance') {
            $viewName = 'report_performance'; 
        } else {
            $viewName = 'report-view';
        }

        $this->renderPrintable($viewName, [
            'reportTitle'   => $this->reportTitles[$reportType] ?? $this->reportTitles['discipline'],
            'reportType'    => $reportType,
            'branchId'      => $branchId,
            'columns'       => $this->reportColumns[$reportType] ?? $this->reportColumns['discipline'],
            'reportData'    => $data, 
            'data'          => $data, 
            'departments'   => $departments,
            'genderSummary' => $genderSummary
        ]);
    }
}