<?php
use App\Helpers\EthiopianDateHelper; 
$is_employee_debt_suspension_page = true;
?>
<section class="content">
  <div class="container-fluid">
      <!-- Card -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
<div class="card-header bg-white d-flex align-items-center">

  <div class="ml-auto">
    <button 
      type="button" 
      class="btn btn-primary btn-sm"
      data-toggle="modal" 
      data-target="#debtSuspensionModal"
    >
       <i class="fas fa-plus mr-1"></i>
      እዳ/እገዳ መዝግብ
    </button>
  </div>

</div>

      </div>

      <div class="card-body">
      <!-- Header -->
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 font-weight-bold text-dark">
      የእዳ/እገዳ ዝርዝር
    </h6>
  </div>
      
      <div class="card-body">
        <table id="example1" data-empty-msg="ያልጸደቀ እዳ/እገዳ የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
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

                  <td>
                     <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-approval-view/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary" title="እይ">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-edit/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="ማስተካከያ">
                      <i class="fas fa-edit"></i> 
                    </a>
                      <?php if (isset($userRole) && $userRole === 'hr_director'): ?>
    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-approval-view/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" 
   class="btn btn-sm btn-outline-success" 
   title="አጽድቅ">
    <i class="fas fa-check"></i> 
</a>
<?php endif; ?>
<?php if ($employee['registered_by'] ===  $_SESSION['user']['id']): ?>
<button class="btn btn-sm btn-outline-danger delete-debt-suspension" 
                      data-id="<?= $employee['record_id']?>"
                      data-name="<?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?>"
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
<div class="modal fade" id="debtSuspensionModal" tabindex="-1" role="dialog" aria-labelledby="debtSuspensionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
              <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> እዳ/እገዳ መዝግብ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
            <form id="debtSuspensionForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-store" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    
                    <!-- Search Section -->
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

                    <!-- Selected Employee Section -->
                    <div id="selectedEmployeePreview" class="card card-widget widget-user-2 shadow-sm" style="display: none; background-color: #f4f6f9;">
                        <div class="widget-user-header p-2">
                            <div class="widget-user-image">
                                <img id="display_image" class="img-circle elevation-2" src="public/dist/img/avatar5.png" alt="User Avatar" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div class="d-inline-block align-middle ml-2" style="max-width: calc(100% - 80px);">
                                <h3 id="display_name" class="widget-user-username text-primary m-0" style="font-size: 1.1rem; font-weight: 600; white-space: normal; word-break: break-word;">---</h3>
                                <h5 id="display_id_text" class="widget-user-desc m-0 text-muted">ID: ---</h5>
                            </div>
                            <input type="hidden" name="employee_id" id="selected_employee_id">
                        </div>
                    </div>

                    <!-- Type and Date Section -->
                    <div class="row mt-3">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-2">
                                <label for="debt_suspension_type" class="mb-1"><small class="font-weight-bold">አይነት </small></label>
                                <select class="form-control form-control-sm" name="debt_suspension_type" id="debt_suspension_type" required>
                                    <option selected="" disabled="" value="">ይምረጡ </option>
                                    <option value="እዳ">እዳ </option>
                                    <option value="እገዳ">እገዳ</option>
                                </select> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-2">
                                <label for="start_date" class="mb-1"><small class="font-weight-bold">እዳ/እገዳ የተያዘበት ቀን (ቀን/ወር/ዓመት) </small></label>
                                <input type="text" 
                                    class="ethiopian-date form-control form-control-sm" 
                                    name="eth_start_date" 
                                    data-rule="past" 
                                    data-gregorian="#start_date" 
                                    placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
                                    readonly 
                                    style="background-color: #fff; cursor: pointer;" required>
            
                                <!-- Kept Label-less to align with your original logic, but wrapped in col-12 for stacking -->
                                <label class="d-none d-md-block mb-1">&nbsp;</label>
                                <input type="date" class="d-none" id="start_date" name="start_date" required readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment Section -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="attachment" class="mb-1"><small class="font-weight-bold">የእዳ/እገዳ ሰነድ (Letter/Evidence)</small></label>
                                <div class="custom-file">
                                    <input type="file" name="debt_suspension_file" class="custom-file-input" id="attachment" required>
                                    <label class="custom-file-label text-truncate" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason Section -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="reason" class="mb-1"><small class="font-weight-bold">የእዳ/እገዳ ምክንያት</small></label>
                                <textarea name="reason" id="reason" class="form-control form-control-sm" rows="3" placeholder="የእዳ/እገዳ ምክንያት ያስገቡ..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> መረጃውን መዝግብ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>