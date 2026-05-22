<?php
namespace App\Controllers;

use App\Models\ReportModel;
use App\Helpers\AuthHelper; 

class ReportController extends BaseController {
    
    // Column definitions
    private array $reportColumns = [
        // 🆕 'ፆታ' የሚለውን አምድ እዚህ ላይ ጨምረነዋል
        'employees'   => ['#', 'ሙሉ ስም', 'ፆታ', 'ዲፓርትመንት', 'ቦታ', 'የተቀጠሩበት ቀን'],
        'payroll'     => ['#', 'ሙሉ ስም', 'ፆታ', 'ዲፓርትመንት', 'የተቀጠሩበት ቀን'],
    ];

    // Report Titles
    private array $reportTitles = [
        'employees'   => 'የሰራተኞች ዝርዝር ሪፖርት',
        'payroll'     => 'የደመወዝ ሪፖርት',
    ];

    /**
     * 🆕 ከ index.php የሚመጣውን የ $params አደራደር ተቀብሎ የሚያስተናግድ ዋና ተግባር
     */
    public function handleReport(array $params = []) {
        // ሁለተኛው segment ($params['uuid']) ባዶ ከሆነ መደበኛውን የካርድ ገጽ አሳይ
        if (empty($params['uuid'])) {
            $this->showReportCards();
        } else {
            // ሁለተኛው segment ካለው ወደ ሪፖርት ማሳያው ገጽ መምራት
            $this->generateReportView($params);
        }
    }

    /**
     * መደበኛውን የሪፖርት መምረጫ ካርዶች ገጽ ያሳያል (የድሮው showReport)
     */
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
            'branchId' => $branch_id // ለጃቫስክሪፕቱ ካርድ መገንቢያ እንዲጠቅም
        ]);
    }  

    /**
     * 🆕 ንጹሑን URL ሰባብሮ የ report-view.php ገጽን በአዲስ ታብ ይከፍታል
     */
    private function generateReportView(array $params) {
        AuthHelper::checkRole(['hr_director', 'hr_officer']);

        // URL መዋቅር: report/{reportType}/{branchId}
        // index.php segment አከፋፈል: $params['uuid'] = 2ኛ segment, $params['record_id'] = 3ኛ segment
        $reportType = $params['uuid']      ?? ''; // ምሳሌ: employees
        $branchId   = $params['record_id'] ?? ''; // ምሳሌ: 87667f3a-4fe5-...

        // የደህንነት ማረጋገጫ (Validation)
        if (!$this->isValidUuid($branchId) || !array_key_exists($reportType, $this->reportColumns)) {
            die('የማይፈቀድ ቅርንጫፍ ወይም የሪፖርት አይነት።');
        }

        // ማጣሪያዎች (Filters - ከቅጹ ላይ በ GET የሚመጡ)
        
        $filters = [
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'department' => $_GET['department'] ?? null,
        ];

        // ከዳታቤዝ መረጃ ማምጣት
        $model = new ReportModel($this->db);
        $data  = $model->getReport($reportType, $branchId, $filters);
        
        // 🆕 የወንድ እና የሴት ብዛት ስታቲስቲክስን ከሞዴሉ ማምጣት
        $genderSummary = $model->getGenderCounts($branchId, $filters);
        
        // ማጣሪያው ላይ ለመጠቀም የዲፓርትመንት ዝርዝር
        $departments = method_exists($model, 'getDepartments') ? $model->getDepartments($branchId) : [];

        // የሪፖርት ገጹን (HTML) ሬንደር ማድረግ
        // 🆕 'genderSummary' የሚለውን አሬይ ወደ ቪው ፋይሉ አሳልፈነዋል
        $this->render('report-view', [
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
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}