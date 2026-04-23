<?php 
use App\Helpers\EthiopianDateHelper; 
$is_employee_scholarship_edit_page = true; 

// Convert agreement_date to Ethiopian calendar
$agreementDateEth = [];
if (!empty($scholarship['agreement_date'])) {
    $agreementDateParts = explode('-', $scholarship['agreement_date']);
    $agreementDateEth = EthiopianDateHelper::toEthCalendar($agreementDateParts[0], $agreementDateParts[1], $agreementDateParts[2]);
}
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የት/ት ማስተካከያ</h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ተመለስ
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

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="employee_image">ፎቶ</label>
                <?php if (!empty($employee['employee_image'])): ?>
                    <div class="mt-2">
                        <img src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_image']) ?>&type=image" 
                             alt="Employee Photo" 
                             class="img-thumbnail" 
                             style="max-width: 100px; height: 100; display: block;">
                    </div>
                <?php else: ?>
                    <p class="text-muted">ምንም ፎቶ የለም</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-8">
              <div class="form-group">
                <label for="employee_name">ሰራተኛ</label>
                <input type="text" class="form-control" 
                       value="<?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?>" 
                       readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="employee_id_display">የሰራተኛው መለያ ቁጥር</label>
                <input type="text" class="form-control" id="employee_id_display" name="employee_id_display" value="<?= htmlspecialchars($employee['employee_id'] ?? '') ?>" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="scholarship_type">የት/ት አይነት</label>
                <select class="form-control" name="scholarship_type" id="scholarship_type" required>
                  <option value="ሙሉ ጊዜ" <?= ($scholarship['scholarship_type'] ?? '') === 'ሙሉ ጊዜ' ? 'selected' : '' ?>>ሙሉ ጊዜ</option>
                  <option value="ግማሽ ጊዜ" <?= ($scholarship['scholarship_type'] ?? '') === 'ግማሽ ጊዜ' ? 'selected' : '' ?>>ግማሽ ጊዜ</option>
                  <option value="ተጨማሪ ስልጠና" <?= ($scholarship['scholarship_type'] ?? '') === 'ተጨማሪ ስልጠና' ? 'selected' : '' ?>>ተጨማሪ ስልጠና</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="status">ሁኔታ</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($scholarship['status'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="eth_agreement_date">የመስርያ ቀን (ቀን/ወር/ዓመት)</label>
                <input type="text" 
                       class="ethiopian-date form-control" 
                       name="eth_agreement_date" 
                       data-rule="past" 
                       data-gregorian="#agreement_date" 
                       placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
                       readonly 
                       style="background-color: #fff; cursor: pointer;"
                       value="<?= !empty($agreementDateEth) ? EthiopianDateHelper::getMonthName($agreementDateEth['month']) . ' ' . $agreementDateEth['day'] . ' ' . $agreementDateEth['year'] : '' ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="agreement_date">ግሪጎሪያን ቀን</label>
                <input type="date" class="form-control" id="agreement_date" name="agreement_date" required value="<?= htmlspecialchars($scholarship['agreement_date'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="scholarship_duration_years">የት/ት ጊዜ (ዓመት)</label>
                <select class="form-control" name="scholarship_duration_years" id="scholarship_duration_years" required>
                  <option value="1" <?= ($scholarship['scholarship_duration_years'] ?? '') == '1' ? 'selected' : '' ?>>1 ዓመት</option>
                  <option value="2" <?= ($scholarship['scholarship_duration_years'] ?? '') == '2' ? 'selected' : '' ?>>2 ዓመታት</option>
                  <option value="3" <?= ($scholarship['scholarship_duration_years'] ?? '') == '3' ? 'selected' : '' ?>>3 ዓመታት</option>
                  <option value="4" <?= ($scholarship['scholarship_duration_years'] ?? '') == '4' ? 'selected' : '' ?>>4 ዓመታት</option>
                  <option value="5" <?= ($scholarship['scholarship_duration_years'] ?? '') == '5' ? 'selected' : '' ?>>5 ዓመታት</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="attachment">የት/ት ሰነድ (Letter/Agreement)</label>
                <?php if (!empty($scholarship['file_url'])): ?>
                    <div class="mt-2 mb-2">
                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($scholarship['file_url']) ?>&type=document" 
                           target="_blank" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-file"></i> ያለውን ፋይል ይመልከቱ
                        </a>
                        <span class="text-muted ml-2">(አዲስ ፋይል ካላትህ ይተካል። ካላትህ ደግሞ ያለው ይጠቀማል።)</span>
                    </div>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" name="scholarship_file" class="custom-file-input" id="attachment">
                    <label class="custom-file-label" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                </div>
                <small class="text-muted">አዲስ ፋይል ካላትህ ይተካል። ካላትህ ደግሞ ያለው ይጠቀማል።</small>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> መረጃውን አስተካክል
                </button>
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave" class="btn btn-default">
                    ዝጋ
                </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>