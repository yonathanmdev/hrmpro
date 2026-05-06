<?php
use App\Helpers\EthiopianDateHelper; 
 $is_employee_warranty_page = true;
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
      data-target="#warrantyModal"
    >
      <i class="fas fa-plus mr-1"></i>
      ዋስትና መዝግብ
    </button>
  </div>

</div>

      </div>

      <div class="card-body">
      <!-- Header -->
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-dark">
      የዋስትና ሰነድ ያልተያያዘላቸው ዝርዝር 
    </h6>
  </div>
        <table id="example1" data-empty-msg="ምንም የዋስትና ሰነድ ያልተያያዘላቸው ይዘት የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>ተቋም/መስሪያ ቤት</th>
               <th>ዋስ የሆኑት ስም</th>
              <th>የምዝገባ ቀን</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $index => $employee): 
                             
                // Split the database date (YYYY-MM-DD)
$dateParts = explode('-', $employee['warranty_created_at']);
$ethDate = EthiopianDateHelper::toEthCalendar($dateParts[2], $dateParts[1], $dateParts[0]);
?>
  

                <tr id="row-<?= $employee['record_id'] ?>">
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                   <td><?= htmlspecialchars($employee['to_whom'] ?? 'N/A') ?></td>
                   <td><?= htmlspecialchars($employee['the_person'] ?? 'N/A') ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></td>
                  

                  <td>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty-pending/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-success" title="ሰነድ ማያያዝ">
                   <i class="fas fa-check"></i>
                    </a>
                    
   <?php if ($employee['registered_by'] ===  $_SESSION['user']['id']): ?>
<button class="btn btn-outline-danger btn-sm delete-warranty" 
                      data-id="<?= $employee['record_id']?>" title="ሰርዝ">

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
<div class="modal fade" id="warrantyModal" tabindex="-1" role="dialog" aria-labelledby="warrantyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
<form id="warrantyForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty-store" method="post" enctype="multipart/form-data">
           
           <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ የዋስትና ምዝገባ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

                 <div class="modal-body">

                    <!-- ── Employee Search ─────────────────────────────── -->
                    <div class="form-group position-relative">
                        <label for="empSearchInput" class="font-weight-bold mb-1"><small class="font-weight-bold">ሰራተኛ ይፈልጉ </small></label>
                           
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-search text-primary"></i></span>
                            </div>
                            <input type="text" id="empSearchInput" class="form-control form-control-sm" placeholder="የሰራተኛ ስም ወይም መታወቂያ..." autocomplete="off">
                        </div>
                        <div id="searchResults"
                            class="list-group position-absolute w-100 shadow-lg"
                            style="z-index: 1051; display: none; max-height: 200px; overflow-y: auto;">
                        </div>
                    </div>

                    <!-- ── Selected Employee Preview ───────────────────── -->
                    <div id="selectedEmployeePreview" class="card card-widget widget-user-2 shadow-sm mb-3"
                        style="display: none; background-color: #f4f6f9;">
                        <div class="widget-user-header">
                            <div class="widget-user-image">
                                <img id="display_image" class="img-circle elevation-2"
                                    src="public/dist/img/avatar5.png" alt="User Avatar"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <h3 id="display_name" class="widget-user-username text-primary ml-3"
                                style="font-size: 1.1rem; font-weight: 600;">---</h3>
                            <h5 id="display_id_text" class="widget-user-desc ml-3 text-muted">ID: ---</h5>
                            <input type="hidden" name="employee_id" id="selected_employee_id">
                        </div>
                    </div>

                    <hr>

                    <!-- ── Warranty Details ────────────────────────────── -->
                    <div class="row">

                        <!-- To Whom (Institution) -->
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="to_whom" class="mb-1"><small class="font-weight-bold">ተቋም / መስሪያ ቤት</small></label>
                                <input type="text" name="to_whom" id="to_whom"
                                    class="form-control form-control-sm" placeholder="ለምሳሌ፡ ለኢትዮጵያ ንግድ ባንክ" required>
                            </div>
                        </div>

                        <!-- The Person Being Guaranteed -->
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="the_person" class="mb-1"><small class="font-weight-bold">ዋስ የሚሆኑት ስም</small></label>
                                </label>
                                <input type="text" name="the_person" id="the_person"
                                    class="form-control form-control-sm" placeholder="ሙሉ ስም" required>
                            </div>
                        </div>

                        <!-- Valid From -->
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="valid_from" class="mb-1"><small class="font-weight-bold">የዋስትና ዓይነት</small></label>
                                <input type="text" name="warranty_type" id="warranty_type"
                                    class="form-control form-control-sm" placeholder="ለምሳሌ፡ የብድር ዋስ" required>
                            </div>
                        </div>

                       

                       
                        

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /.modal-body -->

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> ዝጋ
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> መረጃውን መዝግብ
                    </button>
                </div>

            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /#warrantyModal -->