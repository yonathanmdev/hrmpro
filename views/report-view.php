<?php
$is_report_view_page = true;
$branchId = $branchId ?? ($_SESSION['user']['branch_id'] ?? null);
$reportType = $reportType ?? ($_GET['type'] ?? 'employees');

// ─── ከባክኤንድ የመጣውን የብራንች ስም መውሰጃ ───
$branchName = $genderSummary['branch_name'] ?? 'የሥራና ሥልጠና ቢሮ';
$reportTitle = match($reportType) {
    'employees' => 'የሰራተኞች ብዛት ማጠቃለያ ሪፖርት (በፆታ እና ቅጥር ሁኔታ)',
    'payroll'   => 'የደመወዝ ሪፖርት ማጠቃለያ',
    default     => 'ሪፖርት'
};

// ቁጥሮች
$kuwend = $genderSummary['permanent_male'] ?? 0;
$kuset  = $genderSummary['permanent_female'] ?? 0;
$sumk   = $kuwend + $kuset;

$giwend = $genderSummary['temporary_male'] ?? 0;
$giset  = $genderSummary['temporary_female'] ?? 0;
$sumg   = $giwend + $giset;

// የባለሙያ መረጃ
$hr_firstnameu  = $_SESSION['user']['first_name'] ?? 'ያልታወቀ';
$hr_middlenameu = $_SESSION['user']['father_name'] ?? 'ባለሙያ';

$cuethday   = date('d');
$cuethmonth = date('m');
$cuethyear  = date('Y') - 8;
?>

<style>
  .report-page-container { background: #f4f6f9; padding: 20px; font-family: 'Segoe UI', sans-serif; }
  .table-summary-wrapper { padding: 24px; max-width: 1000px; margin: 20px auto; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
  
  #customers {
    width: 100%;
    border-collapse: collapse;
    color: #000;
    font-size: 14px;
    margin-top: 15px;
  }
  #customers th, #customers td {
    border: 1px solid #333 !important;
    text-align: center;
    padding: 10px;
    vertical-align: middle;
  }
  #customers th {
    background-color: #f2f2f2;
    font-weight: bold;
  }
</style>

<div class="report-page-container">

  <div class="table-summary-wrapper">

    <div class="text-center mb-4 pt-2">
      <h4 class="font-weight-bold" style="color: #222;"><?= htmlspecialchars($reportTitle) ?></h4>
      <p class="text-muted small mb-1">የመስሪያ ቤቱ ስም፦ <b><?= htmlspecialchars($branchName) ?></b></p>
      <p class="text-muted small">የተዘጋጀበት ቀን: <?= date('Y-m-d H:i') ?></p>
      <hr style="border-top: 2px solid #333; width: 100%;">
    </div>

    <table border="1" cellspacing="0" cellpadding="12" id="customers">
      <thead>
        <tr>
          <th rowspan="3">ተ.ቁ</th>
          <th rowspan="3">የመስሪያ ቤቱ ሥም</th>
          <th colspan="6">በክልሉ የሚገኙ ሠራተኞች ብዛት</th>
          <th rowspan="2" colspan="3">በክልሉ የሚገኙ ጠቅላላ ሠራተኞች</th>
        </tr>
        <tr>
          <th colspan="3">ቋሚ</th>
          <th colspan="3">ጊዜያዊ /ኩንትራት/</th>
        </tr>
        <tr>
          <th>ወንድ</th>
          <th>ሴት</th>
          <th>ድምር</th>
          <th>ወንድ</th>
          <th>ሴት</th>
          <th>ድምር</th>
          <th>ወንድ</th>
          <th>ሴት</th>
          <th>ድምር</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>01</td>
          <td style="text-align: left; padding-left: 15px; font-weight: bold;">
            <?= htmlspecialchars($branchName) ?>
          </td>
          <td><?= number_format($kuwend) ?></td>
          <td><?= number_format($kuset) ?></td>
          <td style="font-weight: bold; background-color: #fcfcfc;"><?= number_format($sumk) ?></td>
          <td><?= number_format($giwend) ?></td>
          <td><?= number_format($giset) ?></td>
          <td style="font-weight: bold; background-color: #fcfcfc;"><?= number_format($sumg) ?></td>
          <td style="font-weight: bold; color: #0056b3;"><?= number_format($kuwend + $giwend) ?></td>
          <td style="font-weight: bold; color: #28a745;"><?= number_format($kuset + $giset) ?></td>
          <td style="font-weight: bold; color: #dc3545; background-color: #f5f5f5;"><?= number_format($sumk + $sumg) ?></td>
        </tr>
      </tbody>
    </table>

    <div class="mt-5 pt-4 clearfix" style="font-size: 14px; color: #000;">
      <div style="float: right; text-align: right; width: 100%;">
        야ዘጋጀዉ ስም፦ <b><?= htmlspecialchars($hr_firstnameu . " " . $hr_middlenameu) ?></b> &nbsp;&nbsp;&nbsp;&nbsp;
        ፊርማ፦ ......................... &nbsp;&nbsp;&nbsp;&nbsp;
        ቀን፦ <b><?= htmlspecialchars($cuethday . "/" . $cuethmonth . "/" . $cuethyear) ?></b> ዓ.ም
      </div>
    </div>

  </div>
</div>