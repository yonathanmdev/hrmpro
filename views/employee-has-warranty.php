<?php
use App\Helpers\EthiopianDateHelper; 
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
     <div class="card-header">
        <h6 class="card-title">ዋስትና ያለባቸው</h6>
      </div>
      <div class="card-body">
        <table id="example1" data-empty-msg="ምንም ዋስትና ያለበት ሰራተኛ የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
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
               <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty-views/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-warning" title="ዋስትና ማውረድ">
                 <i class="fas fa-unlock-alt"></i>
                    </a>      
               
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

            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="warrantyModalLabel">
                    <i class="fas fa-shield-alt"></i> አዲስ የዋስትና ምዝገባ
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="warrantyForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-warranty-store" method="post" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- ── Employee Search ─────────────────────────────── -->
                    <div class="form-group position-relative">
                        <label for="empSearchInput" class="font-weight-bold">
                            <i class="fas fa-search text-primary"></i> ሰራተኛ ይፈልጉ
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" id="empSearchInput" class="form-control"
                                placeholder="የሰራተኛ ስም ወይም መታወቂያ..." autocomplete="off">
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
                            <div class="form-group">
                                <label for="to_whom">
                                    <i class="fas fa-building text-muted"></i> ተቋም / መስሪያ ቤት
                                </label>
                                <input type="text" name="to_whom" id="to_whom"
                                    class="form-control" placeholder="ለምሳሌ፡ ኢትዮጵያ ንግድ ባንክ" required>
                            </div>
                        </div>

                        <!-- The Person Being Guaranteed -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="the_person">
                                    <i class="fas fa-user-tie text-muted"></i> የሚዋሱት ሰው ስም
                                </label>
                                <input type="text" name="the_person" id="the_person"
                                    class="form-control" placeholder="ሙሉ ስም" required>
                            </div>
                        </div>

                        <!-- Valid From -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valid_from">
                                     የዋስትና ዓይነት
                                </label>
                                <input type="text" name="warranty_type" id="warranty_type"
                                    class="form-control" required>
                            </div>
                        </div>

                       

                       
                        

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /.modal-body -->

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> ዝጋ
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
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