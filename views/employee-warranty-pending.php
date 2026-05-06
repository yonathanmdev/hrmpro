<?php 
use App\Helpers\EthiopianDateHelper; 
$is_employee_warranty_edit_page = true; 

// Convert start_date to Ethiopian calendar
$startDateEth = [];
if (!empty($warranty['created_at'])) {
    $startDateParts = explode('-', $warranty['created_at']);
    $startDateEth = EthiopianDateHelper::toEthCalendar($startDateParts[2], $startDateParts[1], $startDateParts[0]);
}
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">ዋስትና ደብዳቤ ማያያዝ </h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty" class="btn btn-secondary text-white">
           <i class="fas fa-times fa-lg"></i>
          </a>
        </div>
      </div>

      <div class="card-body">
        <form action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty-file-attachment-process" 
              method="POST" 
              enctype="multipart/form-data" 
              id="warrantyEditForm"
              onsubmit="return confirm('እርግጠኛ ነዎት? ይህን የዋስትና ሰነድ ማያያዝ ይፈልጋሉ?');">
         
         <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">
         <input type="hidden" name="record_id" value="<?= htmlspecialchars($warranty['id'] ?? '') ?>">

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
                
  <!-- To Whom (Institution) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to_whom">
                                    <i class="fas fa-building text-muted"></i> ተቋም / መስሪያ ቤት
                                </label>
                                <input type="text" name="to_whom" id="to_whom"
                                    class="form-control" placeholder="ለምሳሌ፡ ኢትዮጵያ ንግድ ባንክ" required value="<?= htmlspecialchars($warranty['to_whom'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <!-- The Person Being Guaranteed -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="the_person">
                                    <i class="fas fa-user-tie text-muted"></i> የሚዋሱት ሰው ስም
                                </label>
                                <input type="text" name="the_person" id="the_person"
                                    class="form-control" placeholder="ሙሉ ስም" required value="<?= htmlspecialchars($warranty['the_person'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <!-- Valid From -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valid_from">
                                     የዋስትና ዓይነት
                                </label>
                                <input type="text" name="warranty_type" id="warranty_type"
                                    class="form-control" required value="<?= htmlspecialchars($warranty['warranty_type'] ?? '') ?>" readonly>
                            </div>
                        </div>

 <div class="col-md-6">
              <div class="form-group">
                <label for="eth_start_date"><i class="far fa-calendar-check"></i> የተሰጥበት ቀን</label>
                <input type="text" 
                       class="ethiopian-date form-control border-info" 
                       name="eth_start_date" 
                       data-rule="past" 
                       placeholder="ቀን/ወር/ዓ.ም" 
                       readonly 
                       style="background-color: #fff; cursor: pointer;"
                       value="<?= !empty($startDateEth) ? EthiopianDateHelper::getMonthName($startDateEth['month']) . ' ' . $startDateEth['day'] . ' ' . $startDateEth['year'] : '' ?>" >
              </div>
            </div>
        <div class="col-md-12">
              <div class="form-group p-3 border rounded bg-light">
                <label for="attachment" class="font-weight-bold">የዋስትና ሰነድ (Evidence)</label>
                <div class="d-flex align-items-center mb-2">
                    <div class="custom-file flex-grow-1">
                        <input type="file" name="warranty_file" class="custom-file-input" id="attachment" required>
                        <label class="custom-file-label" for="attachment">ሰነድ ይምረጡ...</label>
                    </div>
                </div>
                   </div>
            </div>
              </div>
            </div>
          </div>

          


          

          <div class="row mt-4">
            <div class="col-md-12 d-flex justify-content-end">
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty" class="btn btn-default mr-2 px-4">
                    ዝጋ
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save"></i> መረጃውን መዝግብ
                </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>