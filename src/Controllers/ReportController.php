<?php
namespace App\Controllers;

use App\Models\ReportModel;
use App\Helpers\AuthHelper; 

class ReportController extends BaseController {
    
    // የሪፖርት አምዶች ዝርዝር
    private array $reportColumns = [
        'employees'   => ['#', 'ሙሉ ስም', 'ፆታ', 'የቅጥር ሁኔታ', 'የተቀጠሩበት ቀን'],
        'payroll'     => ['#', 'ሙሉ ስም', 'ፆታ', 'ዲፓርትመንት', 'የተቀጠሩበት ቀን'],
        'education'   => ['#', 'ሙሉ ስም', 'ፆታ', 'የቅጥር ሁኔታ', 'የተቀጠሩበት ቀን'], 
    ];

    // የሪፖርት ርዕሶች ዝርዝር
    private array $reportTitles = [
        'employees'   => 'የሰራተኞች ብዛት ማጠቃለያ ሪፖርት (በፆታ እና ቅጥር ሁኔታ)',
        'payroll'     => 'የደመወዝ ሪፖርት ማጠቃለያ',
        'education'   => 'የሰራተኞች የትምህርት ደረጃ ማጠቃለያ ሪፖርት', 
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

        $reportType = $params['uuid']      ?? ''; 
        $branchId   = $params['record_id'] ?? ''; 

        // የደህንነት ማረጋገጫ መከላከያ መስመር
        if (!$this->isValidUuid($branchId) || !array_key_exists($reportType, $this->reportColumns)) {
            die('የማይፈቀድ ቅርንጫፍ ወይም የሪፖርት አይነት።');
        }

        $filters = [
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'department' => $_GET['department'] ?? null,
        ];

        $model = new ReportModel($this->db);
        $data  = $model->getReport($reportType, $branchId, $filters);
        
        // ከሞዴሉ የፆታ፣ የቅጥር እና የብራንች ስም መረጃዎችን ማምጣት
        $genderSummary = $model->getGenderCounts($branchId, $filters);
        
        $departments = method_exists($model, 'getDepartments') ? $model->getDepartments($branchId) : [];

        // 🔄 ሎጂክ፦ የሪፖርቱን አይነት አይቶ ወደ ተለያዩ የቪው ፋይሎች መምሪያ
        // 'education' ከሆነ -> report_education.php ን ይከፍታል፤ ካልሆነ -> report-view.php ን ይከፍታል
        $viewName = ($reportType === 'education') ? 'report_education' : 'report-view';

        $this->render($viewName, [
            'reportTitle'   => $this->reportTitles[$reportType],
            'reportType'    => $reportType,
            'branchId'      => $branchId,
            'columns'       => $this->reportColumns[$reportType],
            'data'          => $data,
            'departments'   => $departments,
            'genderSummary' => $genderSummary
        ]);
    }

    private function isValidUuid(string $uuid): bool {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }
}