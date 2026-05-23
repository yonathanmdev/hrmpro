<?php
$reportData = $reportData ?? [];

$grand_p_m = 0; $grand_p_f = 0; $grand_p_t = 0;
$grand_t_m = 0; $grand_t_f = 0; $grand_t_t = 0;
$grand_all_m = 0; $grand_all_f = 0; $grand_total = 0;

$disciplineRows = [
    'no_discipline' => 'በዲሲፕሊን ምንም የቅጣት ሪኮርድ የሌለባቸው',
    'salary_cut_15' => 'እስከ 15 ቀን የሚደርስ የደመወዝ ቅጣት የተቀጡ',
    'warning'       => 'የፅሁፍ ማስጠንቀቂያ የተሰጣቸው',
    'suspension'    => 'ከስራ የታገዱ',
    'dismissal'     => 'ከስራ የተሰናበቱ'
];
?>

<div style="font-family: Arial, sans-serif; margin: 20px;">
  <h3 style="text-align: center; color: #333; font-weight: bold; margin-bottom: 5px;">
    <?= htmlspecialchars($reportTitle ?? 'የቢሮው ሠራተኞች የዲሲፕሊን ሁኔታ ማጠቃለያ ሪፖርት') ?>
  </h3>
  <p style="text-align: center; font-size: 15px; color: #444; font-weight: bold; margin-bottom: 20px;">
    <i class="fas fa-code-branch mr-1"></i> የቅርንጫፍ ስም፦ 
    <span style="color: #0056b3;"><?= htmlspecialchars($genderSummary['branch_name'] ?? 'ያልተገኘ ቅርንጫፍ') ?></span>
  </p>

  <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse; font-size: 14px; border: 1px solid #aaa;">
    <thead>
      <tr style="background-color: #6c757d; color: white;">
        <th rowspan="3" style="text-align: center; width: 50px; border: 1px solid #5a6268;">ተ.ቁ</th>
        <th rowspan="3" style="text-align: left; padding-left: 15px; border: 1px solid #5a6268;">የዲሲፕሊን የቅጣት ሁኔታዎች</th>
        <th colspan="6" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ ያሉ ሠራተኞች ብዛት በቅጥር ሁኔታ እና በፆታ</th>
        <th rowspan="2" colspan="3" style="text-align: center; border: 1px solid #5a6268;">ጠቅላላ የዲሲፕሊን ማጠቃለያ ድምር</th>
      </tr>
      <tr style="background-color: #6c757d; color: white;">
        <th colspan="3" style="text-align: center; border: 1px solid #5a6268;">ቋሚ</th>
        <th colspan="3" style="text-align: center; border: 1px solid #5a6268;">ጊዜያዊ</th>
      </tr>
      <tr style="background-color: #6c757d; color: white;">
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
      foreach ($disciplineRows as $key => $label):
          $p_male   = $reportData[$key]['permanent_male']   ?? 0;
          $p_female = $reportData[$key]['permanent_female'] ?? 0;
          $t_male   = $reportData[$key]['temporary_male']   ?? 0;
          $t_female = $reportData[$key]['temporary_female'] ?? 0;

          $row_perm_total = $p_male + $p_female;
          $row_temp_total = $t_male + $t_female;
          
          $row_grand_male   = $p_male + $t_male;
          $row_grand_female = $p_female + $t_female;
          $row_grand_total  = $row_perm_total + $row_temp_total;

          $grand_p_m   += $p_male;   $grand_p_f   += $p_female; $grand_p_t   += $row_perm_total;
          $grand_t_m   += $t_male;   $grand_t_f   += $t_female; $grand_t_t   += $row_temp_total;
          $grand_all_m += $row_grand_male; $grand_all_f += $row_grand_female; $grand_total += $row_grand_total;

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
    <button onclick="window.print();" style="background: #4e73df; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px;">
      <i class="fas fa-print mr-2"></i> ሪፖርቱን አትም (Print)
    </button>
  </div>
</div>

<style>
@media print {
  .no-print { display: none !important; }
  body { background: white; color: black; }
  table { width: 100% !important; border-collapse: collapse; }
  th { -webkit-print-color-adjust: exact; }
}
</style>