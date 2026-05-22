<?php
$is_report_view_page = true;
$branchId = $_SESSION['user']['branch_id'] ?? null;
$reportType = $_GET['type'] ?? 'employees';
$reportTitle = match($reportType) {
    'employees' => 'የሰራተኞች ብዛት ማጠቃለያ ሪፖርት (በፆታ)',
    'payroll'   => 'የደመወዝ ሪፖርት ማጠቃለያ',
    default     => 'ሪፖርት'
};

$maleCount   = $genderSummary['male_count'] ?? 0;
$femaleCount = $genderSummary['female_count'] ?? 0;
$totalCount  = $genderSummary['total_count'] ?? ($maleCount + $femaleCount);
?>

<style>
  .report-page-container { background: #f4f6f9; padding: 20px; font-family: 'Segoe UI', sans-serif; }
  .report-header-bar { background: #fff; border-bottom: 2px solid #dee2e6; padding: 16px 24px; margin-bottom: 20px; }
  .table-summary-wrapper { padding: 4px; max-width: 650px; margin: 10px auto; }
  
  /* ለህትመት ሰንጠረዡ ብቻ እንዲወጣ ማድረጊያ */
  @media print {
    .no-print-element { display: none !important; }
    body, .report-page-container { background: #fff !important; padding: 0 !important; }
    .table-summary-wrapper { padding: 0; max-width: 100%; margin: 0; }
    .card { border: none !important; box-shadow: none !important; }
    .table { border: 1px solid #000 !important; }
    .table th, .table td { border: 1px solid #000 !important; }
  }
</style>

<div class="report-page-container">

  <div class="report-header-bar d-flex align-items-center justify-content-between no-print-element">
    <div>
      <h5 style="margin: 0; font-weight: 700;">
        <i class="fas fa-users mr-2 text-primary"></i>
        <?= htmlspecialchars($reportTitle) ?>
      </h5>
      <small class="text-muted">
        Branch ID: <code><?= htmlspecialchars($branchId) ?></code>
      </small>
    </div>
    <div>
      <a href="#" id="printGenderReportBtn" class="btn btn-sm btn-success mr-2">
    <i class="fas fa-print mr-1"></i> አትም
</a>
      <a href="javascript:window.print()" class="btn btn-danger btn-sm" style="color: white; text-decoration: none;">
        <i class="fas fa-file-pdf mr-1"></i> በ PDF አስቀምጥ
      </a>
    </div>
  </div>

  <div class="table-summary-wrapper">

    <div class="text-center mb-4 pt-2">
      <h4 class="font-weight-bold" style="color: #222;"><?= htmlspecialchars($reportTitle) ?></h4>
      <p class="text-muted small mb-1">Branch ID: <?= htmlspecialchars($branchId) ?></p>
      <p class="text-muted small">የተዘጋጀበት ቀን: <?= date('Y-m-d H:i') ?></p>
      <hr style="border-top: 2px solid #333; width: 100%;">
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-dark text-white text-center font-weight-bold py-3" style="font-size: 18px;">
        የተቀጣሪዎች ስታቲስቲክስ ማጠቃለያ
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered table-striped table-hover mb-0" style="color:#000; font-size: 16px;">
          <thead class="bg-secondary text-white">
            <tr>
              <th class="py-3 pl-4">የሰራተኞች ፆታ</th>
              <th class="text-center py-3" style="width: 40%;">የሰራተኞች ብዛት</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="font-weight-bold text-primary py-3 pl-4">
                <i class="fas fa-mars mr-2"></i> ወንድ ሰራተኞች
              </td>
              <td class="text-center font-weight-bold text-dark py-3">
                <?= number_format($maleCount) ?>
              </td>
            </tr>
            <tr>
              <td class="font-weight-bold text-success py-3 pl-4">
                <i class="fas fa-venus mr-2"></i> ሴት ሰራተኞች
              </td>
              <td class="text-center font-weight-bold text-dark py-3">
                <?= number_format($femaleCount) ?>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-light font-weight-bold" style="border-top: 3px solid #6c757d; font-size: 18px;">
            <tr>
              <td class="text-dark py-3 pl-4">
                <i class="fas fa-users mr-2"></i> ጠቅላላ ድምር
              </td>
              <td class="text-center text-danger py-3">
                <?= number_format($totalCount) ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
</div>
