<?php
use App\Helpers\EthiopianDateHelper; 
 $is_employee_scholarship_page = true; ?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የትምህርት እድል መመዝገቢያ</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#scholarshipModal">
            <i class="fas fa-user-plus"></i> የትምህርት እድል መዝግብ
          </button>
        </div>
      </div>
    </div>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">በት/ት ላይ ያሉ</h3>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>የልደት ቀን</th>
              <th>Status</th>
              <th>የተመዘገቡበት ቀን</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $index => $employee): 
                            
                // Split the database date (YYYY-MM-DD)
$dateParts = explode('-', $employee['birth_date']);
$ethDate = EthiopianDateHelper::toEthCalendar($dateParts[2], $dateParts[1], $dateParts[0]);
$regdateParts = explode('-', $employee['rdate']);
$regethDate = EthiopianDateHelper::toEthCalendar($regdateParts[2], $regdateParts[1], $regdateParts[0]);
?>
  

                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></td>
                  <td><?= 'በት/ት ላይ' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>
                  <td>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" title="እይ" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-edit?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" title="አስተካክል" class="btn btn-sm btn-secondary">
                      <i class="fas fa-edit"></i> 
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center">ምንም ሰራተኛ አልተመዘገበም።</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<div class="modal fade" id="scholarshipModal" tabindex="-1" role="dialog" aria-labelledby="scholarshipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
               <h5 class="modal-title text-white" id="scholarshipModalLabel"><i class="fas fa-graduation-cap"></i> አዲስ የትምህርት እድል ምዝገባ</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="scholarshipForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-store" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    
                    <div class="form-group position-relative">
                        <label for="empSearchInput" class="font-weight-bold">ሰራተኛ ይፈልጉ</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="empSearchInput" class="form-control" placeholder="የሰራተኛ ስም ወይም መታወቂያ..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1051; display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <div id="selectedEmployeePreview" class="card card-widget widget-user-2 shadow-sm" style="display: none; background-color: #f4f6f9;">
                        <div class="widget-user-header">
                            <div class="widget-user-image">
                                <img id="display_image" class="img-circle elevation-2" src="public/dist/img/avatar5.png" alt="User Avatar" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <h3 id="display_name" class="widget-user-username text-primary ml-3" style="font-size: 1.1rem; font-weight: 600;">---</h3>
                            <h5 id="display_id_text" class="widget-user-desc ml-3 text-muted">ID: ---</h5>
                            <input type="hidden" name="employee_id" id="selected_employee_id">
                        </div>
                    </div>

                    <div class="row mt-3">
                      <div class="col-md-4">
                            <div class="form-group">
                                <label for="scholarship_type">የተሰጣቸው የትምህርት እድል </label>
                                 <select class="form-control" name="scholarship_type" id="scholarship_type" required>
                                  <option selected="" disabled="" value="">ይምረጡ </option>
                                  <option value="የመጀመሪያ_ዲግሪ ">የመጀመሪያ ዲግሪ </option>
                                  <option value="ሁለተኛ_ዲግሪ">ሁለተኛ ዲግሪ</option>
                                  <option value="ሶስተኛ_ዲግሪ">ሶስተኛ ዲግሪ </option>
                                 
                                  </select> 
                          </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="scholarship_duration_years">የቆይታ ጊዜ </label>
                                  <input type="number" name="scholarship_duration_years" class="form-control" id="scholarship_duration_years" required>
                                 
                          </div>
                        </div>
                          <div class="col-md-4">
                            <div class="form-group">
                                <label for="agreement_date">ውል የተያዘበት ቀን (ቀን/ወር/ዓመት) </label>
                                  <input type="text" 
       class="ethiopian-date form-control" 
       name="eth_agreement_date" 
       data-rule="past" 
       data-gregorian="#agreement_date" 
       placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
       readonly 
       style="background-color: #fff; cursor: pointer;" required>
                <input type="date" class="form-control" id="agreement_date" name="agreement_date" required readonly>

                          </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="attachment">የትምህርት እድል ሰነድ (Letter/Evidence)</label>
                                <div class="custom-file">
                                    <input type="file" name="scholarship_file" class="custom-file-input" id="attachment" required>
                                    <label class="custom-file-label" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> መረጃውን መዝግብ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

