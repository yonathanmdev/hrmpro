<?php
$is_report_page = true;
$branch_id = $_SESSION['user']['branch_id'] ?? null;
$base_url = $_ENV['BASE_URL'] ?? '';
?>

<style>
  .report-type-card {
    transition: all 0.3s ease-in-out;
    border-width: 2px !important;
  }
  .report-type-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
</style>

<section class="content">
  <div class="container-fluid">

    <div class="card card-default shadow-sm">
      <div class="card-header bg-white">
        <h6 class="card-title font-weight-bold mb-0 text-dark">
          <i class="fas fa-chart-pie mr-1 text-muted"></i> የሪፖርት ማውጫ ማዕከል
        </h6>
        <small class="text-muted float-right">
          <i class="fas fa-code-branch mr-1"></i> የቅርንጫፍ መለያ (Branch ID)፦ 
          <code><?= htmlspecialchars($branch_id) ?></code>
        </small>
      </div>

      <div class="card-body">
        <p class="text-muted mb-4">የሚፈልጉትን የሪፖርት ዓይነት ሲመርጡ ማጠቃለያው በተለየ አዲስ ገጽ (Tab) ላይ ይከፈታል።</p>

        <div class="row">

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-primary h-100"
                 data-report="employees"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የሰራተኞች ጥቅል ሪፖርት</h6>
                <small class="text-muted">ሪፖርት በጾታና በቅጥር ሁኔታ ማጠቃለያ</small>
              </div>
            </div>
          </div>

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-success h-100"
                 data-report="education"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-graduation-cap fa-2x text-success mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የትምህርት ደረጃ ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የትምህርት ደረጃ በጾታ ማጠቃለያ</small>
              </div>
            </div>
          </div>

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-warning h-100"
                 data-report="age"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-calendar-alt fa-2x text-warning mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የዕድሜ ክልል ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች ስርጭት በዕድሜ ክልል ማጠቃለያ</small>
              </div>
            </div>
          </div>

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-info h-100"
                 data-report="level"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-layer-group fa-2x text-info mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የስራ ደረጃ ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች ብዛት በስራ መደብ ደረጃ</small>
              </div>
            </div>
          </div>

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-danger h-100"
                 data-report="discipline"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-gavel fa-2x text-danger mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የዲሲፕሊን ሁኔታ ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የዲሲፕሊን የቅጣት ሁኔታዎች ማጠቃለያ</small>
              </div>
            </div>
          </div>

          <div class="col-md-4 col-sm-6 mb-4">
            <div class="report-type-card card card-outline card-secondary h-100"
                 data-report="performance"
                 data-branch="<?= htmlspecialchars($branch_id) ?>"
                 style="cursor:pointer;">
              <div class="card-body text-center py-4">
                <i class="fas fa-chart-line fa-2x text-secondary mb-3"></i>
                <h6 class="font-weight-bold mb-1 text-dark">የBSC አፈጻጸም ሪፖርት</h6>
                <small class="text-muted">የሰራተኞች የውጤት ተኮር አፈጻጸም ምዘና ማጠቃለያ</small>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const reportCards = document.querySelectorAll('.report-type-card');
    
    reportCards.forEach(card => {
        card.addEventListener('click', function() {
            const reportType = this.getAttribute('data-report');
            const branchId   = this.getAttribute('data-branch');
            
            if (!reportType || !branchId) {
                alert('ስህተት፦ የቅርንጫፍ መለያ ወይም የሪፖርት አይነት አልተገኘም!');
                return;
            }
            
            const baseUrl = "<?= rtrim($base_url, '/') ?>";
            
            // 🚀 ዩአርኤሉን ልክ በራውተርህ Pattern መሠረት ይገነባል፦ /report/{uuid}/{record_id}
            const reportUrl = `${baseUrl}/report/${reportType}/${branchId}`;
            
            window.open(reportUrl, '_blank');
        });
    });
});
</script>