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
                <small class="text-muted">ሪፖርት በጾታ</small>
              </div>
            </div>
          </div>

          <!-- 2. የደመወዝ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-success h-100"
                 data-report="payroll"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                <h6 class="font-weight-bold mb-1">የደሞዝ ሪፖርት</h6>
                <small class="text-muted">ወርሃዊ የደሞዝ ክፍያ ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 3. የፈቃድ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-warning h-100"
                 data-report="leave"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                <h6 class="font-weight-bold mb-1">የፈቃድ ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የፈቃድ አጠቃቀም ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 4. የስልጠና ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-info h-100"
                 data-report="training"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-chalkboard-teacher fa-2x text-info mb-2"></i>
                <h6 class="font-weight-bold mb-1">የስልጠና ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የስልጠና ሁኔታ ሪፖርት</small>
              </div>
            </div>
          </div>

          <!-- 5. የምልመላ ሪፖርት -->
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="report-type-card card card-outline card-danger h-100"
                 data-report="recruitment"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-user-plus fa-2x text-danger mb-2"></i>
                <h6 class="font-weight-bold mb-1">የምልመላ ሪፖርት</h6>
                <small class="text-muted">አዲስ ሰራተኞች ምልመላ ሪፖርት</small>
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
                <h6 class="font-weight-bold mb-1">የአፈጻጸም ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች አፈጻጸም ምዘና ሪፖርት</small>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>