<?php
$is_report_page = true;
$branch_id = $_SESSION['user']['branch_id'] ?? null;
?>

<section class="content">
  <div class="container-fluid">

    <div class="card card-default">
      <div class="card-header">
        <h6 class="card-title font-weight-bold mb-0">
          <i class="fas fa-chart-bar mr-1"></i> ሪፖርት
        </h6>
        <small class="text-muted">
          <i class="fas fa-code-branch mr-1"></i> Branch ID:
          <code><?= htmlspecialchars($branch_id) ?></code>
        </small>
      </div>

      <div class="card-body">

        <p class="text-muted mb-3">የሚፈልጉትን ሪፖርት ዓይነት ይምረጡ — በአዲስ ታብ ይከፈታል።</p>

        <div class="row">

          <!-- 1. የሰራተኞች ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-primary h-100"
                 data-report="employees"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h6 class="font-weight-bold mb-1">የሰራተኞች ጥቅል ሪፖርት</h6>
                <small class="text-muted">ሪፖርት በጾታና በቅጥር ሁኔታ</small>
              </div>
            </div>
          </div>

          <!-- 2. የትምህርት ደረጃ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-success h-100"
                 data-report="education"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                <h6 class="font-weight-bold mb-1">የትምህርት ደረጃ ሪፖርት</h6>
                <small class="text-muted">የትምህርት ደረጃ በጾታ</small>
              </div>
            </div>
          </div>

          <!-- 3. የእድሜ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-warning h-100"
                 data-report="age"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                <h6 class="font-weight-bold mb-1">የእድሜ ክልል ሪፖረት</h6>
                <small class="text-muted">የሰራተኞች የእድሜ ክልል ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 4. የስራ ደረጃ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-info h-100"
                 data-report="level"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-chalkboard-teacher fa-2x text-info mb-2"></i>
                <h6 class="font-weight-bold mb-1">የስራ ደረጃ ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የስራ ደረጃ ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 5. የምልመላ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-danger h-100"
                 data-report="displine"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-user-plus fa-2x text-danger mb-2"></i>
                <h6 class="font-weight-bold mb-1">የዲስፕሊን ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የዲስፕሊን ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 6. የአፈጻጸም ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-secondary h-100"
                 data-report="performance"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-chart-line fa-2x text-secondary mb-2"></i>
                <h6 class="font-weight-bold mb-1">የBSC አፈጻጸም ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች አፈጻጸም ምዘና ሪፖርት</small>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>