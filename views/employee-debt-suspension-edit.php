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
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">እዳ/እገዳ ማስተካከያ </h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension" class="btn btn-secondary text-white">
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

          <div class="row border-bottom pb-3 mb-4">
            <div class="col-md-2 text-center">
              <div class="form-group">
                <label class="d-block">ፎቶ</label>
                <?php if (!empty($employee['employee_image'])): ?>
                    <img src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_image']) ?>&type=image" 
                         alt="Employee Photo" 
                         class="img-thumbnail rounded shadow-sm" 
                         style="width: 140px; height: 150px; object-fit: cover; border: 2px solid #dee2e6;">
                <?php else: ?>
                    <div class="img-thumbnail d-flex align-items-center justify-content-center bg-light" style="width: 140px; height: 150px; margin: 0 auto;">
                        <i class="fas fa-user-circle fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-10">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="employee_name" class="text-primary">ሰራተኛ (Full Name)</label>
                    <input type="text" class="form-control bg-light font-weight-bold" 
                           value="<?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?>" 
                           readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="employee_id_display" class="text-primary">የሰራተኛው መለያ ቁጥር</label>
                    <input type="text" class="form-control bg-light" id="employee_id_display" value="<?= htmlspecialchars($employee['employee_id'] ?? '') ?>" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="debt_suspension_type">የእዳ/እገዳ አይነት</label>
                    <select class="form-control select2 shadow-sm" name="debt_suspension_type" id="debt_suspension_type" required>
                      <option value="እዳ" <?= ($debtSuspension['debt_suspension_type'] ?? '') === 'እዳ' ? 'selected' : '' ?>>እዳ</option>
                      <option value="እገዳ" <?= ($debtSuspension['debt_suspension_type'] ?? '') === 'እገዳ' ? 'selected' : '' ?>>እገዳ</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="status">ሁኔታ (Status)</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($debtSuspension['status'] ?? '') ?>" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="eth_start_date"><i class="far fa-calendar-check"></i> የተያዘበት ቀን (ኢትዮጵያ)</label>
                <input type="text" 
                       class="ethiopian-date form-control border-info" 
                       name="eth_start_date" 
                       data-rule="past" 
                       data-gregorian="#start_date" 
                       placeholder="ቀን/ወር/ዓ.ም" 
                       readonly 
                       style="background-color: #fff; cursor: pointer;"
                       value="<?= !empty($startDateEth) ? EthiopianDateHelper::getMonthName($startDateEth['month']) . ' ' . $startDateEth['day'] . ' ' . $startDateEth['year'] : '' ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="start_date">ግሪጎሪያን ቀን</label>
                <input type="date" class="form-control bg-light" id="start_date" name="start_date" required value="<?= htmlspecialchars($debtSuspension['start_date'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="reason" class="font-weight-bold">የእዳ/እገዳ ምክንያት (Reason)</label>
                <textarea name="reason" id="reason" class="form-control shadow-sm" rows="3" placeholder="ምክንያቱን እዚህ ያብራሩ..." required><?= htmlspecialchars($debtSuspension['reason'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group p-3 border rounded bg-light">
                <label for="attachment" class="font-weight-bold">የእዳ/እገዳ ሰነድ (Evidence)</label>
                <div class="d-flex align-items-center mb-2">
                    <?php if (!empty($debtSuspension['file_url'])): ?>
                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($debtSuspension['file_url']) ?>&type=document" 
                           target="_blank" 
                           class="btn btn-outline-info btn-sm mr-3">
                            <i class="fas fa-file-pdf"></i> ፋይሉን ይመልከቱ
                        </a>
                    <?php endif; ?>
                    <div class="custom-file flex-grow-1">
                        <input type="file" name="debt_suspension_file" class="custom-file-input" id="attachment">
                        <label class="custom-file-label" for="attachment">አዲስ ሰነድ ለመቀየር ይምረጡ...</label>
                    </div>
                </div>
                <small class="text-danger">* አዲስ ፋይል ካልመረጡ ነባሩ ሰነድ እንደተጠበቀ ይቆያል::</small>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-md-12 d-flex justify-content-end">
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension" class="btn btn-default mr-2 px-4">
                    ዝጋ
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save"></i> መረጃውን አስተካክል
                </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>