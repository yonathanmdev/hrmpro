<?php 
use App\Helpers\EthiopianDateHelper; 
$is_employee_scholarship_edit_page = true; 

// Convert agreement_date to Ethiopian calendar
$agreementDateEth = [];
if (!empty($scholarship['agreement_date'])) {
    $agreementDateParts = explode('-', $scholarship['agreement_date']);
    $agreementDateEth = EthiopianDateHelper::toEthCalendar($agreementDateParts[2], $agreementDateParts[1], $agreementDateParts[0]);
}
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">የት/ት ማስተካከያ (Scholarship Adjustment)</h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave" class="btn btn-outline-secondary" title="ተመለስ">
           <i class="fas fa-times fa-lg"></i>
          </a>
        </div>
      </div>

      <div class="card-body">
        <form action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-update" 
              method="POST" 
              enctype="multipart/form-data" 
              id="scholarshipEditForm"
              onsubmit="return confirm('እርግጠኛ ነዎት? ይህን የት/ት ማስተካከል ይፈልጋሉ?');">
          
          <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">
          <input type="hidden" name="record_id" value="<?= htmlspecialchars($scholarship['record_id'] ?? '') ?>">

          <div class="row border-bottom pb-3 mb-4">
            <div class="col-md-2 text-center">
              <div class="form-group">
                <label class="d-block">ፎቶ</label>
                <?php if (!empty($employee['employee_image'])): ?>
                    <img src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_image']) ?>&type=image" 
                         alt="Employee Photo" 
                         class="img-thumbnail rounded shadow-sm" 
                         style="width: 150px; height: 160px; object-fit: cover; border: 2px solid #dee2e6;">
                <?php else: ?>
                    <div class="img-thumbnail d-flex align-items-center justify-content-center bg-light" style="width: 150px; height: 160px; margin: 0 auto;">
                        <i class="fas fa-user-circle fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-10">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="employee_id_display" class="text-primary">የሰራተኛው መለያ ቁጥር</label>
                    <input type="text" class="form-control bg-light" id="employee_id_display" value="<?= htmlspecialchars($employee['employee_id'] ?? '') ?>" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="employee_name" class="text-primary">ሙሉ ስም</label>
                    <input type="text" class="form-control bg-light" 
                           value="<?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?>" 
                           readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="scholarship_type">የተሰጣቸው የትምህርት እድል</label>
                    <select class="form-control select2" name="scholarship_type" id="scholarship_type" required>
                      <option value="የመጀመሪያ_ዲግሪ" <?= ($scholarship['scholarship_type'] ?? '') === 'የመጀመሪያ_ዲግሪ' ? 'selected' : '' ?>>የመጀመሪያ_ዲግሪ</option>
                      <option value="ሁለተኛ_ዲግሪ" <?= ($scholarship['scholarship_type'] ?? '') === 'ሁለተኛ_ዲግሪ' ? 'selected' : '' ?>>ሁለተኛ_ዲግሪ</option>
                      <option value="ሶስተኛ_ዲግሪ" <?= ($scholarship['scholarship_type'] ?? '') === 'ሶስተኛ_ዲግሪ' ? 'selected' : '' ?>>ሶስተኛ_ዲግሪ</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="status">ሁኔታ</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($scholarship['status'] ?? '') ?>" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="eth_agreement_date"><i class="far fa-calendar-alt"></i> ውል የተያዘበት ቀን</label>
                <input type="text" 
                       class="ethiopian-date form-control border-info" 
                       name="eth_agreement_date" 
                       data-rule="past" 
                       data-gregorian="#agreement_date" 
                       placeholder="ቀን/ወር/ዓ.ም" 
                       readonly 
                       style="background-color: #fff; cursor: pointer;"
                       value="<?= !empty($agreementDateEth) ? EthiopianDateHelper::getMonthName($agreementDateEth['month']) . ' ' . $agreementDateEth['day'] . ' ' . $agreementDateEth['year'] : '' ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="agreement_date">ግሪጎሪያን ቀን (Gregorian)</label>
                <input type="date" class="form-control" id="agreement_date" name="agreement_date" required value="<?= htmlspecialchars($scholarship['agreement_date'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="scholarship_duration_years">የቆይታ ጊዜ (በዓመት)</label>
                <input type="number" class="form-control" name="scholarship_duration_years" id="scholarship_duration_years" required value="<?= htmlspecialchars($scholarship['scholarship_duration_years'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group p-3 border rounded bg-light">
                <label for="attachment" class="font-weight-bold">የት/ት ሰነድ (Letter/Agreement)</label>
                <div class="d-flex align-items-center mb-2">
                    <?php if (!empty($scholarship['file_url'])): ?>
                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($scholarship['file_url']) ?>&type=document" 
                           target="_blank" 
                           class="btn btn-outline-info btn-sm mr-3">
                            <i class="fas fa-eye"></i> ያለውን ፋይል ይመልከቱ
                        </a>
                    <?php endif; ?>
                    <div class="custom-file flex-grow-1">
                        <input type="file" name="scholarship_file" class="custom-file-input" id="attachment">
                        <label class="custom-file-label" for="attachment">አዲስ ፋይል ለመቀየር ይምረጡ...</label>
                    </div>
                </div>
                <small class="text-danger">* አዲስ ፋይል ከመረጡ የቆየው በዚሁ ይተካል::</small>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="fas fa-check-circle"></i> መረጃውን አስተካክል
                </button>
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave" class="btn btn-link text-secondary">
                    ዝጋ
                </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>