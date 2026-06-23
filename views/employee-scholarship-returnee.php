<?php
use App\Helpers\EthiopianDateHelper;
$is_employee_scholarship_returnee = true; 
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-body">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h6 class="mb-0 font-weight-bold text-dark">የተጠናቀቁ የትምህርት እድሎች</h6>
        </div>
        <table id="example1" data-empty-msg="ምንም የተጠናቀቀ የትምህርት እድል የለም።"
               class="table table-bordered table-striped small" style="color: #000;">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>ት/ት የሄዱበት ቀን</th>
              <th>የተመለሱበት ቀን</th>
              <th>የት/ት እድል ዓይነት</th>
              <th>የት/ት ቆይታ</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $index => $employee): 

                // Format agreement date
                $agreementDateParts = explode('-', $employee['agreement_date'] ?? '');
                if (count($agreementDateParts) == 3 && !empty($employee['agreement_date'])) {
                    $ethStartDate = EthiopianDateHelper::toEthCalendar($agreementDateParts[2], $agreementDateParts[1], $agreementDateParts[0]);
                    $formattedStartDate = EthiopianDateHelper::getMonthName($ethStartDate['month']) . ' ' . $ethStartDate['day'] . ' ' . $ethStartDate['year'];
                } else {
                    $formattedStartDate = 'N/A';
                }

                // Format end date
                $endDateParts = explode('-', $employee['end_date'] ?? '');
                if (count($endDateParts) == 3 && !empty($employee['end_date'])) {
                    $ethEndDate = EthiopianDateHelper::toEthCalendar($endDateParts[2], $endDateParts[1], $endDateParts[0]);
                    $formattedEndDate = EthiopianDateHelper::getMonthName($ethEndDate['month']) . ' ' . $ethEndDate['day'] . ' ' . $ethEndDate['year'];
                } else {
                    $formattedEndDate = $employee['is_historical'] == 1 ? 'ያለፈ ታሪካዊ' : 'በሂደት ላይ';
                }

                // Scholarship type mapping
                switch($employee['scholarship_type'] ?? '') {
                    case 'የመጀመሪያ_ዲግሪ': $scholarshipType = 'የመጀመሪያ ዲግሪ'; break;
                    case 'ሁለተኛ_ዲግሪ':   $scholarshipType = 'ሁለተኛ ዲግሪ';   break;
                    case 'ሶስተኛ_ዲግሪ':   $scholarshipType = 'ሶስተኛ ዲግሪ';   break;
                    default:              $scholarshipType = $employee['scholarship_type'] ?? 'N/A';
                }
              ?>
                <tr id="row-<?= $employee['record_id'] ?>">
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= $formattedStartDate ?></td>
                  <td><?= $formattedEndDate ?></td>
                  <td><?= htmlspecialchars($scholarshipType) ?></td>
                  <td><?= htmlspecialchars($employee['scholarship_duration_years'] ?? '') ?></td>
                  <td>
                    <?php if (!empty($employee['file_url'])): ?>
                      <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['file_url']) ?>&type=document"
                         target="_blank" title="ሰነድ ይመልከቱ" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-file-pdf"></i>
                      </a>
                    <?php endif; ?>

                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>"
                       title="እይ" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>

                     <button type="button"
                        class="btn btn-sm btn-outline-secondary btn-edit-scholarship"
                        data-id="<?= $employee['record_id'] ?>"
                        data-uuid="<?= htmlspecialchars($employee['uuid'] ?? '') ?>"
                        data-toggle="modal"
                        data-target="#scholarshipEditModal">
                        <i class="fas fa-edit"></i>
                      </button>
              
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center text-muted">ምንም የተጠናቀቀ የትምህርት እድል የለም።</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Edit Scholarship Modal -->
<div class="modal fade" id="scholarshipEditModal" tabindex="-1" role="dialog" aria-labelledby="scholarshipEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scholarshipEditModalLabel">
                    <i class="fas fa-edit mr-1"></i> የትምህርት እድል መረጃ ያስተካክሉ
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="scholarshipEditForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="record_id" id="edit_record_id">
                    <input type="hidden" name="employee_id" id="edit_employee_id_hidden">
                    <input type="hidden" name="is_historical" id="edit_is_historical_hidden" value="0">
                    
                    <!-- Status Banner -->
                    <div id="status_banner" class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <span id="status_message">መረጃውን ማስተካከል ይችላሉ</span>
                    </div>
                    
                    <div class="row">
                        <!-- Employee Information (Read-only) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_employee_id" class="mb-1">
                                    <small class="font-weight-bold">መለያ ቁጥር</small>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="edit_employee_id" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_full_name" class="mb-1">
                                    <small class="font-weight-bold">ሙሉ ስም</small>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="edit_full_name" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_job_name" class="mb-1">
                                    <small class="font-weight-bold">የስራ መደብ</small>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="edit_job_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_sex" class="mb-1">
                                    <small class="font-weight-bold">ጾታ</small>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="edit_sex" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Start Date - ት/ት የሄዱበት ቀን -->
                        <div class="col-md-6 form-group">
                            <label class="mb-1">
                                <small class="font-weight-bold">ት/ት የሄዱበት ቀን</small>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="ethiopian-date form-control form-control-sm"
                                name="eth_agreement_date"
                                data-rule="past"
                                data-gregorian="#edit_agreement_date"
                                placeholder="ቀን/ወር/ዓ.ም ይምረጡ"
                                readonly
                                id="edit_eth_start_date_display"
                                style="background-color: #fff; cursor: pointer;">
                            <input type="date" class="d-none" id="edit_agreement_date" name="agreement_date">
                        </div>

                        <!-- End Date - የተመለሱበት ቀን -->
                        <div class="col-md-6 form-group">
                            <label class="mb-1">
                                <small class="font-weight-bold">የተመለሱበት ቀን</small>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="ethiopian-date form-control form-control-sm"
                                name="eth_end_date"
                                data-rule="past"
                                data-gregorian="#edit_end_date"
                                placeholder="ቀን/ወር/ዓ.ም ይምረጡ"
                                readonly
                                id="edit_eth_end_date_display"
                                style="background-color: #fff; cursor: pointer;">
                            <input type="date" class="d-none" id="edit_end_date" name="end_date">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Scholarship Type - የት/ት እድል ዓይነት -->
                        <div class="col-md-6 form-group">
                            <label for="edit_scholarship_type" class="mb-1">
                                <small class="font-weight-bold">የት/ት እድል ዓይነት</small>
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-sm" id="edit_scholarship_type" name="scholarship_type" required>
                                <option value="">-- ይምረጡ --</option>
                                <option value="የመጀመሪያ_ዲግሪ">የመጀመሪያ ዲግሪ</option>
                                <option value="ሁለተኛ_ዲግሪ">ሁለተኛ ዲግሪ</option>
                                <option value="ሶስተኛ_ዲግሪ">ሶስተኛ ዲግሪ</option>
                            </select>
                            <small class="form-text text-muted edit-field-status">(እንደገና ማስተካከል ይቻላል)</small>
                        </div>

                        <!-- Duration - የትምህርት ቆይታ -->
                        <div class="col-md-6 form-group">
                            <label for="edit_duration" class="mb-1">
                                <small class="font-weight-bold">የትምህርት ቆይታ (በዓመት)</small>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control form-control-sm" 
                                   id="edit_duration" 
                                   name="scholarship_duration_years"
                                   placeholder="ቆይታ ያስገቡ"
                                   min="0.5"
                                   step="0.5"
                                   required>
                            <small class="form-text text-muted edit-field-status">(እንደገና ማስተካከል ይቻላል)</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status Display -->
                        <div class="col-md-12 form-group">
                            <label class="mb-1">
                                <small class="font-weight-bold">የመዝገብ ሁኔታ</small>
                            </label>
                            <div class="form-control form-control-sm" style="background-color: #f8f9fa;" id="edit_status_display">
                                <span id="status_badge" class="badge badge-success">በሂደት ላይ</span>
                            </div>
                            <small class="form-text text-muted">ሁኔታው በራስ-ሰር ይወሰናል</small>
                        </div>
                    </div>

                    <!-- File Upload Section - ALWAYS ENABLED -->
                    <div class="row">
                        <div class="col-12 form-group">
                            <label for="edit_file" class="mb-1">
                                <small class="font-weight-bold">ሰነድ ያስገቡ</small>
                            </label>
                            
                            <!-- Current File Display -->
                            <div id="edit_current_file" class="mb-2">
                                <!-- Current file will be displayed here -->
                            </div>
                            
                            <!-- File Upload Input -->
                            <input type="file" class="form-control-file" id="edit_file" name="scholarship_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                <i class="fas fa-upload"></i> አዲስ ሰነድ ለመጫን ይምረጡ። ባዶ ከሆነ የአሁኑ ሰነድ ይቀራል
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-warning btn-sm" id="submit_edit_btn">
                        <i class="fas fa-save mr-1"></i> አስተካክል
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['nonce'] ?>">
// Wait for DOM and jQuery to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is available
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }

    // Use jQuery in no-conflict mode
    (function($) {
        'use strict';

        // ── Edit Button Click ────────────────────────────────────────────────────────
        $(document).on('click', '.btn-edit-scholarship', function () {
            const recordId = $(this).data('id');
            
            // Validate record_id
            if (!recordId) {
                Swal.fire({
                    icon: 'error',
                    title: 'ስህተት',
                    text: 'መለያ ቁጥር አልተገኘም።'
                });
                return;
            }
            
            // Clear previous data and errors
            $('#scholarshipEditForm')[0].reset();
            $('#edit_current_file').html('');
            
            // Clear date fields
            $('#edit_eth_start_date_display').val('');
            $('#edit_eth_end_date_display').val('');
            $('#edit_agreement_date').val('');
            $('#edit_end_date').val('');
            $('#edit_duration').val('');
            
            // Set Record ID
            $('#edit_record_id').val(recordId);
            
            // Show loading state
            $('#submit_edit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> በማስተካከል ላይ...');
            
            // Pass only record_id
            $.ajax({
                url: '<?= rtrim($_ENV['BASE_URL'], '/') ?>/get-scholarship-details',
                method: 'GET',
                data: {
                    record_id: recordId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status !== 'success') {
                        Swal.fire({
                            icon: 'error',
                            title: 'ስህተት',
                            text: response.message ?? 'መረጃ አልተገኘም።'
                        });
                        $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
                        return;
                    }
                    
                    const data = response.data;
                    const isHistorical = data.is_historical == 1;
                    
                    // Set hidden fields
                    $('#edit_record_id').val(data.record_id || recordId);
                    $('#edit_employee_id_hidden').val(data.uuid || '');
                    $('#edit_is_historical_hidden').val(isHistorical ? 1 : 0);
                    
                    // Populate employee info
                    $('#edit_employee_id').val(data.employee_id || '');
                    $('#edit_full_name').val(data.full_name || '');
                    $('#edit_job_name').val(data.job_name || 'N/A');
                    $('#edit_sex').val(data.sex === 'Male' ? 'ወንድ' : 'ሴት');
                    
                    // Set scholarship type
                    if (data.scholarship_type) {
                        $('#edit_scholarship_type').val(data.scholarship_type);
                    } else {
                        $('#edit_scholarship_type').val('');
                    }
                    
                    // Set duration
                    if (data.scholarship_duration_years) {
                        $('#edit_duration').val(data.scholarship_duration_years);
                    } else {
                        $('#edit_duration').val('');
                    }
                    
                    // Update status display based on historical flag
                    if (isHistorical) {
                        $('#status_banner').removeClass('alert-info').addClass('alert-warning');
                        $('#status_message').html('ሁሉንም መረጃዎች ማስተካከል ይችላሉ (ቀን፣ የትምህርት ዓይነት እና ሰነድ)');
                        $('#status_badge').removeClass('badge-success').addClass('badge-secondary').text('ያለፈ ታሪካዊ');
                        
                        // ENABLE all fields for editing
                        $('#edit_eth_start_date_display').prop('disabled', false);
                        $('#edit_eth_end_date_display').prop('disabled', false);
                        $('#edit_scholarship_type').prop('disabled', false);
                        $('#edit_duration').prop('disabled', false);
                        $('#edit_file').prop('disabled', false);
                        $('.edit-field-status').show();
                        
                    } else {
                        $('#status_banner').removeClass('alert-warning').addClass('alert-info');
                        $('#status_message').html('<strong>ሰነድ ብቻ እና የተመለሱበትን ቀን ብቻ ማስተካከል ይችላሉ (ውል የያዙበት ቀን እና የትምህርት ዓይነት አይቀየሩም)');
                        $('#status_badge').removeClass('badge-secondary').addClass('badge-success').text('በሂደት ላይ');
                        
                        // DISABLE all fields EXCEPT return date and file upload
                        $('#edit_eth_start_date_display').prop('disabled', true);
                        $('#edit_scholarship_type').prop('disabled', true);
                        $('#edit_duration').prop('disabled', true);
                        $('#edit_eth_end_date_display').prop('disabled', false);
                        $('#edit_file').prop('disabled', false);
                        $('.edit-field-status').hide();
                    }
                    
                    // Set dates - Ethiopian display fields
                    if (data.eth_start_date && data.eth_start_date !== 'N/A') {
                        $('#edit_eth_start_date_display').val(data.eth_start_date);
                    } else {
                        $('#edit_eth_start_date_display').val('');
                    }
                    
                    if (data.eth_end_date && data.eth_end_date !== 'N/A' && data.eth_end_date !== 'ያለፈ ታሪካዊ' && data.eth_end_date !== 'በሂደት ላይ') {
                        $('#edit_eth_end_date_display').val(data.eth_end_date);
                    } else if (!data.end_date || data.end_date === '') {
                        $('#edit_eth_end_date_display').val('');
                    } else {
                        $('#edit_eth_end_date_display').val(data.eth_end_date || '');
                    }
                    
                    // Set Gregorian dates (hidden fields for form submission)
                    if (data.agreement_date) {
                        $('#edit_agreement_date').val(data.agreement_date);
                    } else {
                        $('#edit_agreement_date').val('');
                    }
                    
                    if (data.end_date) {
                        $('#edit_end_date').val(data.end_date);
                    } else {
                        $('#edit_end_date').val('');
                    }
                    
                    // Display current file
                    if (data.file_url) {
                        const fileUrl = '<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=' + encodeURIComponent(data.file_url) + '&type=document';
                        const fileName = data.file_url.split('/').pop();
                        
                        $('#edit_current_file').html(
                            '<div class="d-flex align-items-center">' +
                                '<i class="fas fa-file-pdf text-danger mr-2"></i>' +
                                '<span class="text-muted small mr-3">' + fileName + '</span>' +
                                '<a href="' + fileUrl + '" target="_blank" class="btn btn-outline-info btn-sm">' +
                                    '<i class="fas fa-eye"></i> ይመልከቱ' +
                                '</a>' +
                            '</div>'
                        );
                    } else {
                        $('#edit_current_file').html('<span class="text-muted">ምንም ሰነድ የለም</span>');
                    }
                    
                    // Enable submit button
                    $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
                },
                error: function (xhr) {
                    console.log('HTTP Error:', xhr.status);
                    console.log('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'ስህተት',
                        text: 'ከሰርቨር ጋር መገናኘት አልተቻለም።'
                    });
                    $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
                }
            });
        });

        // ── Form Submission ─────────────────────────────────────────────────────────
        $('#scholarshipEditForm').on('submit', function(e) {
            e.preventDefault();
            
            const isHistorical = $('#edit_is_historical_hidden').val() == 1;
            const formData = new FormData(this);
            
            console.log('=== FORM SUBMISSION DEBUG ===');
            console.log('isHistorical:', isHistorical);
            
            // If not historical, only allow file upload and end_date changes
            if (!isHistorical) {
                console.log('Non-historical record - keeping only end_date');
                // Remove fields that shouldn't be updated
                formData.delete('agreement_date');
                formData.delete('scholarship_type');
                formData.delete('scholarship_duration_years');
                formData.append('update_file_only', '0');
            } else {
                console.log('Historical record - updating all fields');
                // Make sure duration is included
                const duration = $('#edit_duration').val();
                if (duration) {
                    formData.set('scholarship_duration_years', duration);
                }
                formData.append('update_file_only', '0');
            }
            
            // Log all form data for debugging
            console.log('Form Data entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Show loading state
            $('#submit_edit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> በማስቀመጥ ላይ...');
            
            $.ajax({
                url: '<?= rtrim($_ENV['BASE_URL'], '/') ?>/update-scholarship-returnee',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Success Response:', response);
                    
                    if (response.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        
                        Toast.fire({
                            icon: 'success',
                            title: 'መረጃ በተሳካ ሁኔታ ተሻሽሏል'
                        });
                        
                        $('#scholarshipEditModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ስህተት',
                            text: response.message || 'መረጃውን ማስተካከል አልተቻለም።'
                        });
                        $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('XHR Status:', status);
                    console.log('XHR Error:', error);
                    console.log('XHR Response:', xhr.responseText);
                    console.log('XHR Status Code:', xhr.status);
                    
                    let errorMsg = 'የመረጃ ማስተካከያ አልተሳካም';
                    
                    try {
                        if (xhr.responseText) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        }
                    } catch (e) {
                        console.log('Could not parse response as JSON');
                        if (xhr.responseText && xhr.responseText.includes('<html')) {
                            errorMsg = 'የሰርቨር ስህተት ተከስቷል። እባክዎን ዳግም ይሞክሩ።';
                        }
                    }
                    
                    if (xhr.status === 0) {
                        errorMsg = 'ከሰርቨር ጋር መገናኘት አልተቻለም። የኢንተርኔት ግንኙነትዎን ያረጋግጡ።';
                    } else if (xhr.status === 404) {
                        errorMsg = 'የተጠየቀው ገጽ አልተገኘም።';
                    } else if (xhr.status === 500) {
                        errorMsg = 'የሰርቨር ስህተት ተከስቷል። እባክዎን አስተዳዳሪዎን ያነጋግሩ።';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'ስህተት',
                        text: errorMsg
                    });
                    
                    $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
                }
            });
        });

        // Reset form when modal is closed
        $('#scholarshipEditModal').on('hidden.bs.modal', function() {
            $('#scholarshipEditForm')[0].reset();
            $('#edit_current_file').html('');
            $('#edit_eth_start_date_display').val('');
            $('#edit_eth_end_date_display').val('');
            $('#edit_agreement_date').val('');
            $('#edit_end_date').val('');
            $('#edit_duration').val('');
            $('#submit_edit_btn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> አስተካክል');
        });

        // ── Ethiopian Date Picker Initialization ────────────────────────────────────
        if (typeof $('.ethiopian-date').ethiopianDatePicker === 'function') {
            $('.ethiopian-date').ethiopianDatePicker();
        }

    })(jQuery);
});
</script>