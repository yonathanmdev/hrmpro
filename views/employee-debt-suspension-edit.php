<?php 
use App\Helpers\EthiopianDateHelper; 
$is_employee_debt_suspension_edit_page = true; 

// Convert start_date to Ethiopian calendar
$startDateEth = [];
if (!empty($debtSuspension['start_date'])) {
    $startDateParts = explode('-', $debtSuspension['start_date']);
    $startDateEth = EthiopianDateHelper::toEthCalendar($startDateParts[2], $startDateParts[1], $startDateParts[0]);
}
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">እዳ/እገዳ ማስተካከያ</h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ተመለስ
          </a>
        </div>
      </div>

      <div class="card-body">
        <form action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-update" 
              method="POST" 
              enctype="multipart/form-data" 
              id="debtSuspensionEditForm"
              onsubmit="return confirm('እርግጠኛ ነዎት? ይህን እዳ/እገዳ ማስተካከል ይፈልጋሉ?');">
         <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">
         <input type="hidden" name="record_id" value="<?= htmlspecialchars($debtSuspension['record_id'] ?? '') ?>">

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
                <label for="debt_suspension_type">አይነት</label>
                <select class="form-control" name="debt_suspension_type" id="debt_suspension_type" required>
                  <option value="እዳ" <?= ($debtSuspension['debt_suspension_type'] ?? '') === 'እዳ' ? 'selected' : '' ?>>እዳ</option>
                  <option value="እገዳ" <?= ($debtSuspension['debt_suspension_type'] ?? '') === 'እገዳ' ? 'selected' : '' ?>>እገዳ</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="status">ሁኔታ</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($debtSuspension['status'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="eth_start_date">እዳ/እገዳ የተያዘበት ቀን (ቀን/ወር/ዓመት)</label>
                <input type="text" 
                       class="ethiopian-date form-control" 
                       name="eth_start_date" 
                       data-rule="past" 
                       data-gregorian="#start_date" 
                       placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
                       readonly 
                       style="background-color: #fff; cursor: pointer;"
                       value="<?= !empty($startDateEth) ? EthiopianDateHelper::getMonthName($startDateEth['month']) . ' ' . $startDateEth['day'] . ' ' . $startDateEth['year'] : '' ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="start_date">ግሪጎሪያን ቀን</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required value="<?= htmlspecialchars($debtSuspension['start_date'] ?? '') ?>" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="registered_by">የመዝገበያ</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($debtSuspension['registered_by'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="reason">የእዳ/እገዳ ምክንያት</label>
                <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="የእዳ/እገዳ ምክንያት ያስገቡ..." required><?= htmlspecialchars($debtSuspension['reason'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="attachment">የእዳ/እገዳ ሰነድ (Letter/Evidence)</label>
                <?php if (!empty($debtSuspension['file_url'])): ?>
                    <div class="mt-2 mb-2">
                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($debtSuspension['file_url']) ?>&type=document" 
                           target="_blank" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-file"></i> ያለውን ፋይል ይመልከቱ
                        </a>
                    </div>
                <?php endif; ?>
                <div class="custom-file">
                    <input type="file" name="debt_suspension_file" class="custom-file-input" id="attachment">
                    <label class="custom-file-label" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                </div>
                <small class="text-muted">አዲስ ፋይል ካላህ ይተካል። ካልመረጡ ደግሞ ያለው ይጠቀማል።</small>
              </div>
            </div>
          </div>

          <div class="row mt-3">
    <div class="col-md-12 d-flex justify-content-end">
        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension" class="btn btn-default mr-2">
            ዝጋ
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> መረጃውን አስተካክል
        </button>
    </div>
</div>
        </form>
      </div>
    </div>
  </div>
</section>