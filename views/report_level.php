<?php
use App\Helpers\EthiopianDateHelper; 
$is_report_view_page = true;
$branchId = $branchId ?? ($_SESSION['user']['branch_id'] ?? null);

// የቢሮ ስም
$branchName = $genderSummary['branch_name'] ?? '$branchName';
$reportTitle = 'የሠራተኞች የስራ መደብ ደረጃ ማጠቃለያ ሪፖርት';
// ከኮንትሮለር የሚመጣው ዳታ $reportData በሚል አሬይ ነው
$reportData = $reportData ?? [];

// የቁልቁል ጠቅላላ ድምር ማጠራቀሚያዎች
$grand_p_m = 0; $grand_p_f = 0;
$grand_t_m = 0; $grand_t_f = 0;
$grand_all_m = 0; $grand_all_f = 0; $grand_all = 0;

// 💡 ማስተካከያ፦ ቁልፎቹ (Keys) በቀጥታ በዳታቤዝህ ውስጥ ካሉት የሮማን ቁጥሮች ጋር እኩል ተደርገዋል
$levelLabels = [
    'I'      => 'I',   'II'    => 'II',  'III'  => 'III', 'IV'   => 'IV',  'V'    => 'V',
    'VI'     => 'VI',  'VII'   => 'VII', 'VIII' => 'VIII', 'IX'   => 'IX',  'X'    => 'X',
    'XI'     => 'XI',  'XII'   => 'XII', 'XIII' => 'XIII', 'XIV'  => 'XIV', 'XV'   => 'XV',
    'XVI'    => 'XVI', 'XVII'  => 'XVII','XVIII'=> 'XVIII','XIX'  => 'XIX', 'XX'   => 'XX',
    'XXI'    => 'XXI', 'XXII'  => 'XXII','ሹመት'  => 'ሹመት'
];

// የባለሙያ መረጃ
$hr_firstnameu  = $_SESSION['user']['first_name'] ?? 'ያልታወቀ';
$hr_middlenameu = $_SESSION['user']['father_name'] ?? 'ባለሙያ';

$today      = date('Y-m-d');
              $endParts   = explode('-', $today);
              $endEth     = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
?>

<div style="font-family: Arial, sans-serif; margin: 20px;">
  <h3 style="text-align: center; color: #333; font-weight: bold;">
    <?= htmlspecialchars($reportTitle ?? 'የቢሮው ሠራተኞች ብዛት በስራ ደረጃ እና በፆታ') ?>
  </h3>
  
      <center><p class="text-muted small mb-1">የመስሪያ ቤቱ ስም፦ <b><?= htmlspecialchars($branchName) ?></b></p>
      <p class="text-muted small">የተዘጋጀበት ቀን: <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> /<?= $endEth['year'] ?> ዓ.ም</p>
      <hr style="border-top: 2px solid #333; width: 100%;"></center>

  <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; border: 1px solid #aaa;">
    <thead>
     <tr style="background-color: #e2e3e5; color: black;">
        <th rowspan="3" style="text-align: center; border: 1px solid #5a6268;">ተ.ቁ</th>
        <th rowspan="3" style="text-align: center; border: 1px solid #5a6268;">የስራ ደረጃ</th>
        <th colspan="6" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ ያሉ ሠራተኞች ብዛት በስራ ደረጃ እና በፆታ</th>
        <th rowspan="2" colspan="3" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ ያሉ የቢሮው ሠራተኞች በስራ ደረጃ</th>
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
      foreach ($levelLabels as $key => $label):
          // 💡 ዳታቤዝ የሚመልሰው ቁልፍ (Key) ለምሳሌ 'I' ከሆነ ከ $reportData['I'] ላይ ይፈልጋል
          $p_male   = $reportData[$key]['permanent_male']   ?? 0;
          $p_female = $reportData[$key]['permanent_female'] ?? 0;
          $t_male   = $reportData[$key]['temporary_male']   ?? 0;
          $t_female = $reportData[$key]['temporary_female'] ?? 0;

          // የአግድም ድምሮች ስሌት
          $row_perm_total = $p_male + $p_female;
          $row_temp_total = $t_male + $t_female;
          
          $row_grand_male   = $p_male + $t_male;
          $row_grand_female = $p_female + $t_female;
          $row_grand_total  = $row_perm_total + $row_temp_total;

          // የቁልቁል ድምሮች ማጠраቀሚያ
          $grand_p_m   += $p_male;   
          $grand_p_f   += $p_female;
          $grand_t_m   += $t_male;   
          $grand_t_f   += $t_female;
          $grand_all_m += $row_grand_male;
          $grand_all_f += $row_grand_female;
          $grand_all   += $row_grand_total;

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
        <td colspan="2" style="text-align: center; padding: 12px; border: 1px solid #bcbebf;">ድምር</td>
        
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_p_m ?></td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_p_f ?></td>
        <td style="text-align: center; background-color: #d6d8db; border: 1px solid #bcbebf;"><?= $grand_p_m + $grand_p_f ?></td>
        
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_t_m ?></td>
        <td style="text-align: center; border: 1px solid #bcbebf;"><?= $grand_t_f ?></td>
        <td style="text-align: center; background-color: #d6d8db; border: 1px solid #bcbebf;"><?= $grand_t_m + $grand_t_f ?></td>
        
        <td style="text-align: center; color: #0056b3; border: 1px solid #bcbebf;"><?= $grand_all_m ?></td>
        <td style="text-align: center; color: #28a745; border: 1px solid #bcbebf;"><?= $grand_all_f ?></td>
        <td style="text-align: center; color: #dc3545; background-color: #c8cbcf; border: 1px solid #bcbebf;"><?= $grand_all ?></td>
      </tr>
    </tbody>
  </table>
</div>
<center>ያዘጋጀዉ ባለሙያ ስም፦ <b><?= htmlspecialchars($hr_firstnameu . " " . $hr_middlenameu) ?></b> &nbsp;&nbsp;&nbsp;&nbsp;
        ፊርማ፦ ......................... &nbsp;&nbsp;&nbsp;&nbsp;
        ቀን፦ <b><?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?>/ <?= $endEth['year'] ?>
</b> ዓ.ም</center>