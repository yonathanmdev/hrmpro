<?php
$is_report_view_page = true;
$branchId = $branchId ?? ($_SESSION['user']['branch_id'] ?? null);

// የቢሮ ስም
$branchName = $genderSummary['branch_name'] ?? '$branchName';
$reportTitle = 'የሠራተኞች የትምህርት ደረጃ ማጠቃለያ ሪፖርት';

// --- የትምህርት ደረጃዎችን ከዳታቤዝ እሴቶች ጋር ማጣመር ---
// ማሳሰቢያ፡ በዳታቤዝህ ውስጥ 'Level_of_Education' ላይ የተቀመጡትን እሴቶች እዚህ ድርድር (Array) ውስጥ አስረናቸዋል
$educationLevels = [
    ['id' => '01', 'title' => 'የቀለም (8ኛ ያጠናቀቀ)', 'db_val' => '8ኛ_ያጠናቀቀ'],
    ['id' => '02', 'title' => 'ከ1ኛ - 8ኛ ክፍል (10ኛ ያጠናቀቀ)', 'db_val' => '10ኛ_ያጠናቀቀ'],
    ['id' => '03', 'title' => 'ከ9ኛ - 10ኛ ክፍል (12ኛ ያጠናቀቀ)', 'db_val' => '12ኛ_ያጠናቀቀ'],
    ['id' => '04', 'title' => 'ከ11ኛ - 12ኛ ክፍል (10+1)', 'db_val' => '10+1'],
    ['id' => '05', 'title' => '10+1', 'db_val' => '10+1'],
    ['id' => '06', 'title' => '10+2', 'db_val' => '10+2'],
    ['id' => '07', 'title' => '10+3', 'db_val' => '10+3'],
    ['id' => '08', 'title' => 'Level 1 ያጠናቀቀ', 'db_val' => 'ደረጃ_1'],
    ['id' => '09', 'title' => 'Level 2 ያጠናቀቀ', 'db_val' => 'ደረጃ_2'],
    ['id' => '10', 'title' => 'Level 3 ያጠናቀቀ', 'db_val' => 'ደረጃ_3'],
    ['id' => '11', 'title' => 'Level 4 ያጠናቀቀ', 'db_val' => 'ደረጃ_4'],
    ['id' => '12', 'title' => 'Level 5 ያጠናቀቀ', 'db_val' => 'ደረጃ_5'],
    ['id' => '13', 'title' => 'ዲፕሎማ ያጠናቀቀ', 'db_val' => 'ዲፕሎማ_ያጠናቀቀ'],
    ['id' => '14', 'title' => 'የመጀመሪያ ዲግሪ', 'db_val' => 'የመጀመሪያ_ዲግሪ'],
    ['id' => '15', 'title' => 'ሁለተኛ_ዲግሪ', 'db_val' => 'ሁለተኛ_ዲግሪ'],
    ['id' => '16', 'title' => 'ዶክትሬት (PhD)', 'db_val' => 'ዶክትሬት']
];

// የፊርማ ባለሙያ መረጃ
$hr_firstnameu  = $_SESSION['user']['first_name'] ?? 'ያልታወቀ';
$hr_middlenameu = $_SESSION['user']['father_name'] ?? 'ባለሙያ';

$cuethday   = date('d');
$cuethmonth = date('m');
$cuethyear  = date('Y') - 8;
?>

<style>
  .report-page-container { background: #f4f6f9; padding: 20px; font-family: 'Segoe UI', sans-serif; }
  .table-summary-wrapper { padding: 24px; max-width: 1100px; margin: 20px auto; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
  
  #customers {
    width: 100%;
    border-collapse: collapse;
    color: #000;
    font-size: 13px;
    margin-top: 15px;
  }
  #customers th, #customers td {
    border: 1px solid #333 !important;
    text-align: center;
    padding: 8px;
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

    <table border="1" cellspacing="1" cellpadding="12" id="customers">
      <thead>
        <tr>
          <th rowspan="3">ተ.ቁ</th>
          <th rowspan="3">የትምህርት ደረጃ</th>
          <th colspan="6">ሠራተኞች የቅጥር ሁኔታ በጾታ</th>
          <th colspan="3" rowspan="2">ጠቅላላ ሠራተኞች </th>
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
        <?php 
        // ለጠቅላላ የሪፖርቱ ድምር ማጠራቀሚያዎች
        $grand_ku_w = $grand_ku_s = $grand_gi_w = $grand_gi_s = 0;

        foreach ($educationLevels as $level): 
          $val = $level['db_val'];

          // ከባክኤንድ በ $data የመጣውን የቁጥር ስሌት ማገናኛ (ቁጥሮቹ ከሌሉ 0 ይሆናሉ)
          $ku_wend  = $data[$val]['permanent_male'] ?? 0;
          $ku_set   = $data[$val]['permanent_female'] ?? 0;
          $ku_sum   = $ku_wend + $ku_set;

          $gi_wend  = $data[$val]['temporary_male'] ?? 0;
          $gi_set   = $data[$val]['temporary_female'] ?? 0;
          $gi_sum   = $gi_wend + $gi_set;

          $tot_wend = $ku_wend + $gi_wend;
          $tot_set  = $ku_set + $gi_set;
          $tot_sum  = $ku_sum + $gi_sum;

          // ለግራንድ ቶታል መደመር
          $grand_ku_w += $ku_wend; $grand_ku_s += $ku_set;
          $grand_gi_w += $gi_wend; $grand_gi_s += $gi_set;
        ?>
        <tr>
          <td><?= $level['id'] ?></td>
          <td style="text-align: left; padding-left: 10px; font-weight: 500;"><?= htmlspecialchars($level['title']) ?></td>
          
          <td><?= number_format($ku_wend) ?></td>
          <td><?= number_format($ku_set) ?></td>
          <td style="font-weight: bold; background-color: #fbfbfb;"><?= number_format($ku_sum) ?></td>
          
          <td><?= number_format($gi_wend) ?></td>
          <td><?= number_format($gi_set) ?></td>
          <td style="font-weight: bold; background-color: #fbfbfb;"><?= number_format($gi_sum) ?></td>
          
          <td style="font-weight: bold; color: #0056b3;"><?= number_format($tot_wend) ?></td>
          <td style="font-weight: bold; color: #28a745;"><?= number_format($tot_set) ?></td>
          <td style="font-weight: bold; color: #dc3545; background-color: #f5f5f5;"><?= number_format($tot_sum) ?></td>
        </tr>
        <?php endforeach; ?>
        
        <tr style="background-color: #eee; font-weight: bold;">
          <td colspan="2">ጠቅላላ ድምር</td>
          <td><?= number_format($grand_ku_w) ?></td>
          <td><?= number_format($grand_ku_s) ?></td>
          <td style="background-color: #e0e0e0;"><?= number_format($grand_ku_w + $grand_ku_s) ?></td>
          <td><?= number_format($grand_gi_w) ?></td>
          <td><?= number_format($grand_gi_s) ?></td>
          <td style="background-color: #e0e0e0;"><?= number_format($grand_gi_w + $grand_gi_s) ?></td>
          <td style="color: #0056b3;"><?= number_format($grand_ku_w + $grand_gi_w) ?></td>
          <td style="color: #28a745;"><?= number_format($grand_ku_s + $grand_gi_s) ?></td>
          <td style="color: #dc3545; background-color: #dcdcdc;"><?= number_format($grand_ku_w + $grand_ku_s + $grand_gi_w + $grand_gi_s) ?></td>
        </tr>
      </tbody>
    </table>

    <div class="mt-5 pt-4 clearfix" style="font-size: 14px; color: #000;">
      <div style="float: right; text-align: right; width: 100%;">
        ያዘጋጀዉ ስም፦ <b><?= htmlspecialchars($hr_firstnameu . " " . $hr_middlenameu) ?></b> &nbsp;&nbsp;&nbsp;&nbsp;
        ፊርማ፦ ......................... &nbsp;&nbsp;&nbsp;&nbsp;
        ቀን፦ <b><?= htmlspecialchars($cuethday . "/" . $cuethmonth . "/" . $cuethyear) ?></b> ዓ.ም
      </div>
    </div>

  </div>
</div>