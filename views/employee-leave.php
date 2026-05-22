<?php
use App\Helpers\EthiopianDateHelper; 
$is_anual_rest_registration = true; ?>
<section class="content">
  <div class="container-fluid">
    <div class="card shadow-sm border-0">

      <!-- Header -->
<div class="card-header bg-white d-flex align-items-center justify-content-between card-primary card-outline py-2">
    <h6 class="m-0 font-weight-bold text-dark">
    <?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? ''))) ?> የአመት እረፍት
    </h6>
    
    <div class="ml-auto">
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addExperienceModal">
            <i class="fas fa-plus mr-1"></i> የአመት እረፍት መዝግብ
        </button>
    </div>
</div>

      <!-- Table -->
      <div class="card-body p-0">
        <table id="example1" data-empty-msg="ምንም የአመት እረፍት አልተመዘገበም።" class="table table-bordered table-striped table-hover small mb-0" style="color: #000;">
          <thead class="thead-light">
  <tr>
    <th>#</th>
    <th>የስራ መደብ</th>
    <th>በጀት</th>
    <th>የአመት እረፍት ብዛት (በቀን)</th>     <!-- ← new -->
    <th>ሁኔታ</th>
    <th>Actions</th>
  </tr>
</thead>
        
<tbody>
      
</tbody> 
</table>
      </div>
    </div>
  </div>
</section>
<?php include 'partials/edit-experience-modal.php'; ?>
<!-- ===== Add Experience Modal ===== -->
<div class="modal fade" id="addExperienceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <!-- Form wraps everything to include the footer button -->
      <form id="addExperienceForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-experience-store" method="post" enctype="multipart/form-data">
        
        <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ የስራ ልምድ መዝግብ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- 2. Modal Body -->
        <div class="modal-body">
          <input type="hidden" name="employee_uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">

          <div class="row">
            <!-- Company Name -->
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_company_name" class="mb-1"><small class="font-weight-bold">የሰሩበት መስሪያ ቤት </small><span class="text-danger">*</span></label>
              <input type="text" name="company_name" id="add_company_name" class="form-control form-control-sm" placeholder="የመስሪያ ቤቱን ስም ያስገቡ..." autocomplete="off" required>
              <ul id="company_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>

            <!-- Job Title -->
            <div class="col-md-6 form-group mb-2 position-relative">
              <label for="add_job_title" class="mb-1"><small class="font-weight-bold">የስራ መደብ </small><span class="text-danger">*</span></label>
              <input type="text" name="job_title" id="add_job_title" class="form-control form-control-sm" placeholder="የስራ መደቡን ያስገቡ..." autocomplete="off" required>
              <ul id="job_suggestions" class="list-group position-absolute w-100" style="z-index:9999; display:none;"></ul>
            </div>
          </div>

          <div class="row">
            <!-- Employment Type -->
            <div class="col-md-4 form-group">
              <label for="add_employment_type" class="mb-1"><small class="font-weight-bold">የቅጥር አይነት </small><span class="text-danger">*</span></label>
              <select name="employment_type" id="add_employment_type" class="form-control form-control-sm" required>
                <option value="">-- ይምረጡ --</option>
                <option value="Full-time">ሙሉ ጊዜ</option>
                <option value="Part-time">ትርፍ ጊዜ</option>
                <option value="Contract">ኮንትራት</option>
                <option value="Freelance">ፍሪላንስ</option>
              </select>
            </div>

<!-- Start Date -->
    <div class="col-md-4 form-group">
        <label class="mb-1"><small class="font-weight-bold">የጀመሩበት ቀን </small><span class="text-danger">*</span></label>
        <input type="text" 
               class="ethiopian-date form-control form-control-sm" 
               id="add_eth_start_date" 
               data-gregorian="#add_start_date" 
               placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
               readonly 
               style="background-color: #fff; cursor: pointer;" required>
        <input type="date" name="start_date" id="add_start_date" class="d-none" required>
    <!-- Add Modal — below #add_eth_start_date input -->
<span id="start_date_error_msg" class="text-danger mt-1" style="display:none; font-size:11px; font-weight:bold;">
    <i class="fas fa-exclamation-circle mr-1"></i>
</span>
      </div>

    <!-- End Date -->
    <div class="col-md-4 form-group">
        <label class="mb-1">
            <small class="font-weight-bold">የጨረሱበት ቀን </small>
            <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="ethiopian-date form-control form-control-sm" 
               id="add_eth_end_date"
               data-gregorian="#add_end_date" 
               placeholder="ቀን/ወር/ዓ.ም ይምረጡ" 
               readonly 
               style="background-color: #fff; cursor: pointer;" required>
        <input type="date" name="end_date" id="add_end_date" class="d-none" required>
        
        <!-- Red Error Message -->
        <span id="date_error_msg" class="text-danger mt-1" style="display: none; font-size: 11px; font-weight: bold;">
            <i class="fas fa-exclamation-circle mr-1"></i> ስህተት፡ ስራ የጨረሱበት ቀን ከጀመሩበት ቀን ማነስ የለበትም።
        </span>
    </div>
          </div>
        </div>

        <!-- 3. Modal Footer -->
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
          <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
            <i class="fas fa-save mr-1"></i> መረጃውን መዝግብ
          </button>
        </div>

      </form>
    </div>
  </div>
</div>