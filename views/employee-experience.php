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
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-experience-letter/<?= htmlspecialchars($employee['uuid']) ?>" 
             class="btn btn-sm btn-success mr-2" target="_blank">
            <i class="fas fa-file-pdf mr-1"></i>ደብዳቤ
          </a>
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
              <th>የስራ ዘመን</th>
              <th>የቅጥር ዓይነት</th>
              <th>ልምድ</th>
              <th>Actions</th>
            </tr>
          </thead>
          <?php
          function calcDuration(string $start, ?string $end): array {
    $s    = new DateTime($start);
    $e    = $end ? new DateTime($end) : new DateTime();
    $diff = $s->diff($e);

    $years  = $diff->y;
    $months = $diff->m;
    $days   = $diff->d;

    // Normalize: if days >= 30, roll into months
    if ($days >= 30) {
        $months += intdiv($days, 30);
        $days    = $days % 30;
    }

    // Normalize: if months >= 12, roll into years
    if ($months >= 12) {
        $years  += intdiv($months, 12);
        $months  = $months % 12;
    }

    return [
        'years'      => $years,
        'months'     => $months,
        'days'       => $days,
        'total_days' => (int)$diff->days,
    ];
}

          function formatDuration(array $d): string {
              $parts = [];
              if ($d['years']  > 0) $parts[] = $d['years']  . ' ዓ';
              if ($d['months'] > 0) $parts[] = $d['months'] . ' ወ';
              if ($d['days']   > 0) $parts[] = $d['days']   . ' ቀ';
              return $parts ? implode(' ', $parts) : '0 ቀ';
          }

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

            // ── Primary (201) row ──
            if (!empty($employee['date_of_employed'])):
              $startParts = explode('-', $employee['date_of_employed']);
              $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);
              $today      = date('Y-m-d');
              $endParts   = explode('-', $today);
              $endEth     = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
              $duration   = calcDuration($employee['date_of_employed'], $today);
            ?>
            <tr>
              <td><?= $counter++ ?></td>
              <td><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($employee['job_name'] ?? '') ?></td>
              <td class="text-nowrap">
                ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?> <?= $startEth['day'] ?> <?= $startEth['year'] ?>
                እስከ <span class="badge badge-success">ዛሬ</span>
                <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> <?= $endEth['year'] ?>
              </td>
              <td><?= htmlspecialchars($employee['employment_situation'] ?? '') ?></td>
              <td class="text-nowrap">
                <?php $seen = 0; ?>
                <?php if ($duration['years'] > 0): ?><span class="badge badge-light border"><?= $duration['years'] ?> ዓመት</span><?php $seen++; endif; ?>
                <?php if ($duration['months'] > 0): ?><span class="badge badge-light border"><?= ($seen > 0 ? 'ከ' : '') . $duration['months'] ?> ወር</span><?php $seen++; endif; ?>
                <?php if ($duration['days'] > 0): ?><span class="badge badge-light border"><?= ($seen > 0 ? 'ከ' : '') . $duration['days'] ?> ቀን</span><?php endif; ?>
                <?php if ($duration['years'] === 0 && $duration['months'] === 0 && $duration['days'] === 0): ?><span class="text-muted small">—</span><?php endif; ?>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary edit-exp-btn" title="አስተካክል" disabled>
                  <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-exp-btn" title="ሰርዝ" disabled>
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endif; ?>

            <?php if (!empty($experiences)):
              $grandTotalDays = 0;
              foreach ($experiences as $exp):
                $startParts = explode('-', $exp['start_date']);
                $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);
                $endEth     = null;
                if (!empty($exp['end_date'])) {
                  $endParts = explode('-', $exp['end_date']);
                  $endEth   = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
                }
                $duration        = calcDuration($exp['start_date'], $exp['end_date'] ?? null);
                $grandTotalDays += $duration['total_days'];
            ?>
            <tr id="row-<?= htmlspecialchars($exp['id']) ?>">
              <td><?= $counter++ ?></td>
              <td><?= htmlspecialchars($exp['company_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($exp['job_title'] ?? '') ?></td>
              <td class="text-nowrap">
                ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?> <?= $startEth['day'] ?> <?= $startEth['year'] ?>
                እስከ
                <?php if ($endEth): ?>
                  <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> <?= $endEth['year'] ?>
                <?php else: ?>
                  <span class="badge badge-success">አሁን</span>
                <?php endif; ?>
              </td>
              <?php
$employmentTypeLabels = [
    'Full-time' => 'ቋሚ',
    'Contract'  => 'ኮንትራት',
    'Delegate'  => 'ውክልና',
];
?>

<td><?= htmlspecialchars($employmentTypeLabels[$exp['employment_type']] ?? $exp['employment_type'] ?? '') ?></td>
              <td class="text-nowrap">
                <?php $seen = 0; ?>
                <?php if ($duration['years'] > 0): ?><span class="badge badge-light border"><?= $duration['years'] ?> ዓመት</span><?php $seen++; endif; ?>
                <?php if ($duration['months'] > 0): ?><span class="badge badge-light border"><?= ($seen > 0 ? 'ከ' : '') . $duration['months'] ?> ወር</span><?php $seen++; endif; ?>
                <?php if ($duration['days'] > 0): ?><span class="badge badge-light border"><?= ($seen > 0 ? 'ከ' : '') . $duration['days'] ?> ቀን</span><?php endif; ?>
                <?php if ($duration['years'] === 0 && $duration['months'] === 0 && $duration['days'] === 0): ?><span class="text-muted small">—</span><?php endif; ?>
              </td>
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
            <?php endforeach; endif; ?>
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
      <form id="addExperienceForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-experience-store" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ የስራ ልምድ መዝግብ
          </h6>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="employee_uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">
          <div class="row">
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_company_name" class="mb-1"><small class="font-weight-bold">የሰሩበት መስሪያ ቤት </small><span class="text-danger">*</span></label>
              <input type="text" name="company_name" id="add_company_name" class="form-control form-control-sm" placeholder="የመስሪያ ቤቱን ስም ያስገቡ..." autocomplete="off" required>
              <ul id="company_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_job_title" class="mb-1"><small class="font-weight-bold">የስራ መደብ </small><span class="text-danger">*</span></label>
              <input type="text" name="job_title" id="add_job_title" class="form-control form-control-sm" placeholder="የስራ መደቡን ያስገቡ..." autocomplete="off" required>
              <ul id="job_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 form-group">
              <label for="add_employment_type" class="mb-1"><small class="font-weight-bold">የቅጥር አይነት </small><span class="text-danger">*</span></label>
              <select name="employment_type" id="add_employment_type" class="form-control form-control-sm" required>
                <option value="">-- ይምረጡ --</option>
                <option value="Full-time">ቋሚ</option>
                <option value="Contract">ኮንትራት</option>
                <option value="Delegate">ውክልና</option>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label class="mb-1"><small class="font-weight-bold">የጀመሩበት ቀን </small><span class="text-danger">*</span></label>
              <input type="text" class="ethiopian-date form-control form-control-sm" id="add_eth_start_date" data-gregorian="#add_start_date" placeholder="ቀን/ወር/ዓ.ም ይምረጡ" readonly style="background-color: #fff; cursor: pointer;" required>
              <input type="date" name="start_date" id="add_start_date" class="d-none" required>
              <span id="start_date_error_msg" class="text-danger mt-1" style="display:none; font-size:11px; font-weight:bold;">
                <i class="fas fa-exclamation-circle mr-1"></i>
              </span>
            </div>
            <div class="col-md-4 form-group">
              <label class="mb-1"><small class="font-weight-bold">የጨረሱበት ቀን </small><span class="text-danger">*</span></label>
              <input type="text" class="ethiopian-date form-control form-control-sm" id="add_eth_end_date" data-gregorian="#add_end_date" placeholder="ቀን/ወር/ዓ.ም ይምረጡ" readonly style="background-color: #fff; cursor: pointer;" required>
              <input type="date" name="end_date" id="add_end_date" class="d-none" required>
              <span id="date_error_msg" class="text-danger mt-1" style="display: none; font-size: 11px; font-weight: bold;">
                <i class="fas fa-exclamation-circle mr-1"></i> ስህተት፡ ስራ የጨረሱበት ቀን ከጀመሩበት ቀን ማነስ የለበትም።
              </span>
            </div>
          </div>
        </div>
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