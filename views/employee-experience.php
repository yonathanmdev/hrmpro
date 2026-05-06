<?php
use App\Helpers\EthiopianDateHelper; 
$is_exprience_registration_page = true; ?>
<section class="content">
  <div class="container-fluid">
    <div class="card shadow-sm border-0">

      <!-- Header -->
<div class="card-header bg-white d-flex align-items-center justify-content-between card-primary card-outline py-2">
    <h6 class="m-0 font-weight-bold text-dark">
    <?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? ''))) ?> የስራ ልምድ
    </h6>
    
    <div class="ml-auto">
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addExperienceModal">
            <i class="fas fa-plus mr-1"></i> የስራ ልምድ መዝግብ
        </button>
    </div>
</div>

      <!-- Table -->
      <div class="card-body p-0">
        <table id="example1" data-empty-msg="ምንም ልምድ አልተመዘገበም።" class="table table-bordered table-striped table-hover small mb-0" style="color: #000;">
          <thead class="thead-light">
  <tr>
    <th>#</th>
    <th>የሰሩበት መስሪያ ቤት</th>
    <th>የስራ መደብ</th>
    <th>የጀመሩበት ቀን</th>
    <th>የጨረሱበት ቀን</th>     <!-- ← new -->
    <th>ልምድ</th>
    <th>Actions</th>
  </tr>
</thead>
          <?php
// ── Helper: calculate duration between two Gregorian dates ──────
function calcDuration(string $start, ?string $end): array {
    $s = new DateTime($start);
    $e = $end ? new DateTime($end) : new DateTime(); // today if current
    $diff = $s->diff($e);
    return [
        'years'  => $diff->y,
        'months' => $diff->m,
        'days'   => $diff->d,
        // total in days for summing
        'total_days' => (int)$s->diff($e)->days,
    ];
}

// ── Helper: format duration array to Amharic string ─────────────
function formatDuration(array $d): string {
    $parts = [];
    if ($d['years']  > 0) $parts[] = $d['years']  . ' ዓ';
    if ($d['months'] > 0) $parts[] = $d['months'] . ' ወ';
    if ($d['days']   > 0) $parts[] = $d['days']   . ' ቀ';
    return $parts ? implode(' ', $parts) : '0 ቀ';
}

// ── Helper: convert total days back to years/months/days ────────
function daysToYMD(int $totalDays): array {
    $years  = intdiv($totalDays, 365);
    $remain = $totalDays % 365;
    $months = intdiv($remain, 30);
    $days   = $remain % 30;
    return ['years' => $years, 'months' => $months, 'days' => $days, 'total_days' => $totalDays];
}
?>

<tbody>
     <?php $counter = 1; 
    
    // 1. Manually check and display the Primary Registration File (201)
    if (!empty($employee['date_of_employed'])): 
     // Ethiopian date conversion
        $startParts = explode('-', $employee['date_of_employed']);
        $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);

        // Ethiopian date conversion for current date to calculate ongoing duration
        $today = date('Y-m-d');
        $endParts = explode('-',$today);
        $endEth   = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
        

        // Duration calculation
        $duration        = calcDuration($employee['date_of_employed'], $today);
        //$grandTotalDays += $duration['total_days'];
        ?>
      <tr>
        <td><?= $counter++ ?></td>
        <td><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></td>
      <td><?= htmlspecialchars($employee['job_name'] ?? '') ?></td>
     <td>
        <?= EthiopianDateHelper::getMonthName($startEth['month']) ?>
        <?= $startEth['day'] ?> <?= $startEth['year'] ?>
      </td>

      <!-- End date -->
      <td>
        <span class="badge badge-success">ዛሬ</span>
          <?= EthiopianDateHelper::getMonthName($endEth['month']) ?>
          <?= $endEth['day'] ?> <?= $endEth['year'] ?>
      </td>

      <!-- Duration per row -->
      <td class="text-nowrap">
        <?php if ($duration['years'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['years'] ?> ዓመት</span>
        <?php endif; ?>
        <?php if ($duration['months'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['months'] ?> ወር</span>
        <?php endif; ?>
        <?php if ($duration['days'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['days'] ?> ቀን</span>
        <?php endif; ?>
        <?php if ($duration['years'] === 0 && $duration['months'] === 0 && $duration['days'] === 0): ?>
          <span class="text-muted small">—</span>
        <?php endif; ?>
      </td>

      <!-- Employment type -
      <td>
        <?= htmlspecialchars(match($exp['employment_type'] ?? '') {
          'Full-time'  => 'ሙሉ ጊዜ',
          'Part-time'  => 'ትርፍ ጊዜ',
          'Contract'   => 'ኮንትራት',
          'Freelance'  => 'ፍሪላንስ',
          default      => $exp['employment_type'] ?? ''
        }) ?>
      </td>-->

      <!-- Actions -->
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-secondary edit-exp-btn" title="አስተካክል" disabled>
           <i class="fas fa-edit"></i>
          <i class="fas fa-edit"></i>
        </button>
        <button type="button"
          class="btn btn-sm btn-outline-danger delete-exp-btn"
          title="ሰርዝ" disabled>
           <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
    <?php endif; ?>
  <?php if (!empty($experiences)): ?>
    <?php 
      $grandTotalDays = 0;
      foreach ($experiences as $exp): 

        // Ethiopian date conversion
        $startParts = explode('-', $exp['start_date']);
        $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);

        $endEth = null;
        if (!empty($exp['end_date'])) {
          $endParts = explode('-', $exp['end_date']);
          $endEth   = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
        }

        // Duration calculation
        $duration        = calcDuration($exp['start_date'], $exp['end_date'] ?? null);
        $grandTotalDays += $duration['total_days'];
    ?>
    <tr id="row-<?= htmlspecialchars($exp['id']) ?>">
      <td><?= $counter++ ?></td>

      <td><?= htmlspecialchars($exp['company_name'] ?? '') ?></td>
      <td><?= htmlspecialchars($exp['job_title'] ?? '') ?></td>

      <!-- Start date -->
      <td>
        <?= EthiopianDateHelper::getMonthName($startEth['month']) ?>
        <?= $startEth['day'] ?> <?= $startEth['year'] ?>
      </td>

      <!-- End date -->
      <td>
        <?php if ($endEth): ?>
          <?= EthiopianDateHelper::getMonthName($endEth['month']) ?>
          <?= $endEth['day'] ?> <?= $endEth['year'] ?>
        <?php else: ?>
          <span class="badge badge-success">አሁን</span>
        <?php endif; ?>
      </td>

      <!-- Duration per row -->
      <td class="text-nowrap">
        <?php if ($duration['years'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['years'] ?> ዓመት</span>
        <?php endif; ?>
        <?php if ($duration['months'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['months'] ?> ወር</span>
        <?php endif; ?>
        <?php if ($duration['days'] > 0): ?>
          <span class="badge badge-light border"><?= $duration['days'] ?> ቀን</span>
        <?php endif; ?>
        <?php if ($duration['years'] === 0 && $duration['months'] === 0 && $duration['days'] === 0): ?>
          <span class="text-muted small">—</span>
        <?php endif; ?>
      </td>

      <!-- Employment type -
      <td>
        <?= htmlspecialchars(match($exp['employment_type'] ?? '') {
          'Full-time'  => 'ሙሉ ጊዜ',
          'Part-time'  => 'ትርፍ ጊዜ',
          'Contract'   => 'ኮንትራት',
          'Freelance'  => 'ፍሪላንስ',
          default      => $exp['employment_type'] ?? ''
        }) ?>
      </td>-->

      <!-- Actions -->
      <td class="text-center">
        <button type="button"
          class="btn btn-sm btn-outline-secondary edit-exp-btn"
          title="አስተካክል"
          data-uuid="<?= htmlspecialchars($exp['id']) ?>"
          data-employee="<?= htmlspecialchars($exp['employee_uuid']) ?>"
          data-toggle="modal" data-target="#editExperienceModal">
          <i class="fas fa-edit"></i>
        </button>
        <button type="button"
          class="btn btn-sm btn-outline-danger delete-exp-btn"
          title="ሰርዝ"
          data-id="<?= htmlspecialchars($exp['id']) ?>"
          data-name="<?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? ''))) ?>">
           <i class="fas fa-trash-alt me-1"></i>
        </button>
      </td>
    </tr>
    <?php endforeach; ?>


  <?php endif; ?>
</tbody> 
</table>
      </div>
    </div>
  </div>
</section>
<?php include 'partials/edit-experience-modal.php'; ?>
<!-- ===== Add Experience Modal ===== -->
<div class="modal fade" id="addExperienceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <!-- Form wraps everything to include the footer button -->
      <form id="addExperienceForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-experience-store" method="post" enctype="multipart/form-data">
        
        <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ የስራ ልምድ መዝግብ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- 2. Modal Body -->
        <div class="modal-body">
          <input type="hidden" name="employee_uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">

          <div class="row">
            <!-- Company Name -->
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_company_name" class="mb-1"><small class="font-weight-bold">የሰሩበት መስሪያ ቤት </small><span class="text-danger">*</span></label>
              <input type="text" name="company_name" id="add_company_name" class="form-control form-control-sm" placeholder="የመስሪያ ቤቱን ስም ያስገቡ..." autocomplete="off" required>
              <ul id="company_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>

            <!-- Job Title -->
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_job_title" class="mb-1"><small class="font-weight-bold">የስራ መደብ </small><span class="text-danger">*</span></label>
              <input type="text" name="job_title" id="add_job_title" class="form-control form-control-sm" placeholder="የስራ መደቡን ያስገቡ..." autocomplete="off" required>
              <ul id="job_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>
          </div>

          <div class="row">
            <!-- Employment Type -->
            <div class="col-md-4 form-group">
              <label for="add_employment_type" class="mb-1"><small class="font-weight-bold">የቅጥር አይነት </small><span class="text-danger">*</span></label>
              <select name="employment_type" id="add_employment_type" class="form-control form-control-sm" required>
                <option value="">-- ይምረጡ --</option>
                <option value="Full-time">ሙሉ ጊዜ</option>
                <option value="Part-time">ትርፍ ጊዜ</option>
                <option value="Contract">ኮንትራት</option>
                <option value="Freelance">ፍሪላንስ</option>
              </select>
            </div>

<!-- Start Date -->
    <div class="col-md-4 form-group">
        <label class="mb-1"><small class="font-weight-bold">የጀመሩበት ቀን </small><span class="text-danger">*</span></label>
        <input type="text" 
               class="ethiopian-date form-control form-control-sm" 
               id="add_eth_start_date" 
               data-gregorian="#add_start_date" 
               placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
               readonly 
               style="background-color: #fff; cursor: pointer;" required>
        <input type="date" name="start_date" id="add_start_date" class="d-none" required>
    <!-- Add Modal — below #add_eth_start_date input -->
<span id="start_date_error_msg" class="text-danger mt-1" style="display:none; font-size:11px; font-weight:bold;">
    <i class="fas fa-exclamation-circle mr-1"></i>
</span>
      </div>

    <!-- End Date -->
    <div class="col-md-4 form-group">
        <label class="mb-1">
            <small class="font-weight-bold">የጨረሱበት ቀን </small>
            <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="ethiopian-date form-control form-control-sm" 
               id="add_eth_end_date"
               data-gregorian="#add_end_date" 
               placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
               readonly 
               style="background-color: #fff; cursor: pointer;" required>
        <input type="date" name="end_date" id="add_end_date" class="d-none" required>
        
        <!-- Red Error Message -->
        <span id="date_error_msg" class="text-danger mt-1" style="display: none; font-size: 11px; font-weight: bold;">
            <i class="fas fa-exclamation-circle mr-1"></i> ስህተት፡ ስራ የጨረሱበት ቀን ከጀመሩበት ቀን ማነስ የለበትም።
        </span>
    </div>
          </div>
        </div>

        <!-- 3. Modal Footer -->
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
          <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
            <i class="fas fa-save mr-1"></i> መረጃውን መዝግብ
          </button>
        </div>

      </form>
    </div>
  </div>
</div>