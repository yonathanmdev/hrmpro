<?php
use App\Helpers\EthiopianDateHelper; 
 $is_employee_scholarship_page = true;
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
<div class="card-header bg-white d-flex align-items-center">

  <div class="ml-auto">
    <button 
      type="button" 
      class="btn btn-primary btn-sm"
      data-toggle="modal" 
      data-target="#scholarshipModal"
    >
      <i class="fas fa-plus mr-1"></i>
      የትምህርት እድል መዝግብ
    </button>
  </div>

</div>

      </div>
    
  
      <div class="card-body">
        <table id="example1" data-empty-msg="ምንም በት/ት ያሉ የተመዘገበ ሰራተኛ የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>የልደት ቀን</th>
              <th>የምዝገባ ቀን</th>
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
  

                <tr id="row-<?= $employee['record_id'] ?>">
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></td>
                   <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>

                  <td class="text-center align-middle">
                   <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave-views/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>"   class="btn btn-sm btn-outline-primary" title="እይ">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-edit/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="ማስተካከያ">
                      <i class="fas fa-edit"></i> 
                    </a>
                    <?php if (isset($userRole) && $userRole === 'hr_director'): ?>
    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave-views/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" 
       class="btn btn-sm btn-outline-success" 
       title="አጽድቅ">
        <i class="fas fa-check"></i> 
    </a>
<?php endif; ?>
   <?php if ($employee['registered_by'] ===  $_SESSION['user']['id']): ?>
<button class="btn btn-outline-danger btn-sm delete-scholarship" 
                      data-id="<?= $employee['record_id']?>"
                      data-name="<?= htmlspecialchars($employee['first_name'] ?? '') . ' ' . htmlspecialchars($employee['father_name'] ?? '') . ' ' . htmlspecialchars($employee['g_father_name'] ?? '') ?>"
                      title="ሰርዝ">

                <i class="fas fa-trash"></i>
              </button>  
                <?php endif; ?>   
                                 
                  </td>
                </tr>
              <?php endforeach; ?>
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
            <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ የትምህርት እድል መዝገባ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
            <form id="scholarshipForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-store" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    
                    <div class="form-group position-relative">
                        <label for="empSearchInput" class="font-weight-bold mb-1"><small class="font-weight-bold">ሰራተኛ ይፈልጉ </small></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="empSearchInput" class="form-control form-control-sm" placeholder="የሰራተኛ ስም ወይም መታወቂያ..." autocomplete="off">
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
                            <div class="form-group mb-2">
                                <label for="scholarship_type" class="mb-1"><small class="font-weight-bold">የተሰጣቸው የትምህርት እድል </small></label>
                                 <select class="form-control form-control-sm" name="scholarship_type" id="scholarship_type" required>
                                  <option selected="" disabled="" value="">ይምረጡ </option>
                                  <option value="የመጀመሪያ_ዲግሪ ">የመጀመሪያ ዲግሪ </option>
                                  <option value="ሁለተኛ_ዲግሪ">ሁለተኛ ዲግሪ</option>
                                  <option value="ሶስተኛ_ዲግሪ">ሶስተኛ ዲግሪ </option>
                                 
                                  </select> 
                          </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label for="scholarship_duration_years" class="mb-1"><small class="font-weight-bold">የቆይታ ጊዜ </small></label>
                                  <input type="number" name="scholarship_duration_years" class="form-control form-control-sm" id="scholarship_duration_years" required>
                                 
                          </div>
                        </div>
                          <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label for="agreement_date" class="mb-1"><small class="font-weight-bold">ውል የተያዘበት ቀን (ቀን/ወር/ዓመት) </small></label>
                                  <input type="text" 
       class="ethiopian-date form-control form-control-sm" 
       name="eth_agreement_date" 
       data-rule="past" 
       data-gregorian="#agreement_date" 
       placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
       readonly 
       style="background-color: #fff; cursor: pointer;" required>
                <input type="date" class="d-none" id="agreement_date" name="agreement_date" required readonly>

                          </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label for="attachment" class="mb-1"><small class="font-weight-bold">የትምህርት እድል ሰነድ (Letter/Evidence)</small></label>
                                <div class="custom-file">
                                    <input type="file" name="scholarship_file" class="custom-file-input" id="attachment" required>
                                    <label class="custom-file-label" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> መረጃውን መዝግብ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>