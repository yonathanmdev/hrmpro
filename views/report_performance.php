<?php
use App\Helpers\EthiopianDateHelper; 
$is_report_view_page = true;
$branchId = $branchId ?? ($_SESSION['user']['branch_id'] ?? null);

// የቢሮ ስም
$branchName = $genderSummary['branch_name'] ?? '$branchName';
$reportTitle = 'የሠራተኞች የስራ አፈጻጸም ማጠቃለያ ሪፖርት';
// የሪፖርት ዳታው ባዶ ከሆነ ባዶ Array እንዲሆን ማድረግ
$reportData = $reportData ?? [];

// የቁልቁል ጠቅላላ ድምር ማጠራቀሚያዎች
$grand_p_m = 0; $grand_p_f = 0; $grand_p_t = 0;
$grand_t_m = 0; $grand_t_f = 0; $grand_t_t = 0;
$grand_all_m = 0; $grand_all_f = 0; $grand_total = 0;

// ለBSC አፈጻጸም ማጠቃለያ የሚሆኑ የረድፍ መለያዎች
$performanceRows = [
    'high'   => 'ከፍተኛ አፈጻጸም ያሳዩ (High)',
    'medium' => 'መካከለኛ አፈጻጸም ያሳዩ (Medium/Average)',
    'low'    => 'ዝቅተኛ አፈጻጸም ያሳዩ (Low)'
];

// የባለሙያ መረጃ
$hr_firstnameu  = $_SESSION['user']['first_name'] ?? 'ያልታወቀ';
$hr_middlenameu = $_SESSION['user']['father_name'] ?? 'ባለሙያ';

$today      = date('Y-m-d');
              $endParts   = explode('-', $today);
              $endEth     = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
?>

<div style="font-family: Arial, sans-serif; margin: 20px;">
  <h3 style="text-align: center; color: #333; font-weight: bold; margin-bottom: 5px;">
    <?= htmlspecialchars($reportTitle ?? 'የሰራተኞች የBSC አፈጻጸም ምዘና ማጠቃለያ ሪፖርት') ?>
  </h3>
  
  <center><p class="text-muted small mb-1">የመስሪያ ቤቱ ስም፦ <b><?= htmlspecialchars($branchName) ?></b></p>
      <p class="text-muted small">የተዘጋጀበት ቀን: <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> /<?= $endEth['year'] ?> ዓ.ም</p>
      <hr style="border-top: 2px solid #333; width: 100%;"></center>
  </p>

  <?php if (!empty($_GET['from']) || !empty($_GET['to'])): ?>
    <p style="text-align: center; font-size: 13px; color: #666; margin-top: -15px; margin-bottom: 20px;">
      <?php if (!empty($_GET['from'])) echo "ከ " . htmlspecialchars($_GET['from']) . " "; ?>
      <?php if (!empty($_GET['to'])) echo "እስከ " . htmlspecialchars($_GET['to']) . " "; ?> የተመዘነ የአፈጻጸም ማጠቃለያ
    </p>
  <?php endif; ?>

  <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse; font-size: 14px; border: 1px solid #aaa;">
    <thead>
      <tr style="background-color: #e2e3e5; color: black;">
        <th rowspan="3" style="text-align: center; width: 50px; border: 1px solid #5a6268;">ተ.ቁ</th>
        <th rowspan="3" style="text-align: left; padding-left: 15px; border: 1px solid #5a6268;">የአፈጻጸም ደረጃ</th>
        <th colspan="6" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ ያሉ ሠራተኞች ብዛት በቅጥር ሁኔታ እና በፆታ</th>
        <th rowspan="2" colspan="3" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ የአፈጻጸም ማጠቃለያ ድምር</th>
      </tr>
      <tr style="background-color: #e2e3e5; color: black;">
        <th colspan="3" style="text-align: center; border: 1px solid #5a6268;">ቋሚ</th>
        <th colspan="3" style="text-align: center; border: 1px solid #5a6268;">ጊዜያዊ</th>
      </tr>
      <tr style="background-color: #e2e3e5; color: black;">
        <th style="text-align: center; border: 1px solid #5a6268;">ወንድ</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ሴት</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ድምር</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ወንድ</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ሴት</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ድምር</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ወንድ</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ሴት</th>
        <th style="text-align: center; border: 1px solid #5a6268;">ድምር</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $index = 1;
      foreach ($performanceRows as $key => $label):
          // ከዳታቤዝ የመጣውን መረጃ መለየት (ባዶ ከሆነ 0 ይሆናል) - ቁልፎቹ: high, medium, low
          $p_male   = isset($reportData[$key]['permanent_male'])   ? (int)$reportData[$key]['permanent_male']   : 0;
          $p_female = isset($reportData[$key]['permanent_female']) ? (int)$reportData[$key]['permanent_female'] : 0;
          $t_male   = isset($reportData[$key]['temporary_male'])   ? (int)$reportData[$key]['temporary_male']   : 0;
          $t_female = isset($reportData[$key]['temporary_female']) ? (int)$reportData[$key]['temporary_female'] : 0;

          // የአግድም ድምር ስሌቶች
          $row_perm_total = $p_male + $p_female;
          $row_temp_total = $t_male + $t_female;
          
          $row_grand_male   = $p_male + $t_male;
          $row_grand_female = $p_female + $t_female;
          $row_grand_total  = $row_perm_total + $row_temp_total;

          // የቁልቁል ጠቅላላ ድምር ማከማቸት
          $grand_p_m   += $p_male;   
          $grand_p_f   += $p_female; 
          $grand_p_t   += $row_perm_total;
          $grand_t_m   += $t_male;   
          $grand_t_f   += $t_female; 
          $grand_t_t   += $row_temp_total;
          $grand_all_m += $row_grand_male; 
          $grand_all_f += $row_grand_female; 
          $grand_total += $row_grand_total;

          // የረድፎች ቀለም መቀያየሪያ (Zebra striping)
          $bg = ($index % 2 == 0) ? 'background-color: #f9f9f9;' : '';
      ?>
      <tr style="<?= $bg ?>">
        <td style="text-align: center; border: 1px solid #ddd;"><?= sprintf("%02d", $index++) ?></td>
        <td style="font-weight: bold; padding-left: 15px; border: 1px solid #ddd;"><?= htmlspecialchars($label) ?></td>
        
        <td style="text-align: center; border: 1px solid #ddd;"><?= $p_male ?: '-' ?></td>
        <td style="text-align: center; border: 1px solid #ddd;"><?= $p_female ?: '-' ?></td>
        <td style="text-align: center; font-weight: bold; background-color: #f1f1f1; border: 1px solid #ddd;"><?= $row_perm_total ?: '-' ?></td>
        
        <td style="text-align: center; border: 1px solid #ddd;"><?= $t_male ?: '-' ?></td>
        <td style="text-align: center; border: 1px solid #ddd;"><?= $t_female ?: '-' ?></td>
        <td style="text-align: center; font-weight: bold; background-color: #f1f1f1; border: 1px solid #ddd;"><?= $row_temp_total ?: '-' ?></td>
        
        <td style="text-align: center; font-weight: bold; color: #0056b3; border: 1px solid #ddd;"><?= $row_grand_male ?: '-' ?></td>
        <td style="text-align: center; font-weight: bold; color: #28a745; border: 1px solid #ddd;"><?= $row_grand_female ?: '-' ?></td>
        <td style="text-align: center; font-weight: bold; color: #dc3545; background-color: #e9e9e9; border: 1px solid #ddd;"><?= $row_grand_total ?: '-' ?></td>
      </tr>
      <?php endforeach; ?>

      <tr style="background-color: #e2e3e5; font-weight: bold; border-top: 2px solid #666;">
        <td colspan="2" style="text-align: center; padding: 12px; border: 1px solid #bcbebf;">ጠቅላላ ድምር</td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_p_m ?></td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_p_f ?></td>
        <td style="text-align: center; background-color: #d6d8db; border: 1px solid #bcbebf;"><?= $grand_p_t ?></td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_t_m ?></td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_t_f ?></td>
        <td style="text-align: center; background-color: #d6d8db; border: 1px solid #bcbebf;"><?= $grand_t_t ?></td>
        <td style="text-align: center; color: #0056b3; border: 1px solid #bcbebf;"><?= $grand_all_m ?></td>
        <td style="text-align: center; color: #28a745; border: 1px solid #bcbebf;"><?= $grand_all_f ?></td>
        <td style="text-align: center; color: #dc3545; background-color: #c8cbcf; border: 1px solid #bcbebf;"><?= $grand_total ?></td>
      </tr>
    </tbody>
  </table>

  <div style="margin-top: 20px; text-align: right;" class="no-print">
   
  </div>
</div>
<center>ያዘጋጀዉ ባለሙያ ስም፦ <b><?= htmlspecialchars($hr_firstnameu . " " . $hr_middlenameu) ?></b> &nbsp;&nbsp;&nbsp;&nbsp;
        ፊርማ፦ ......................... &nbsp;&nbsp;&nbsp;&nbsp;
        ቀን፦ <b><?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?>/ <?= $endEth['year'] ?>
</b> ዓ.ም</center>
