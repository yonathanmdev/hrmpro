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
        <table id="example1" data-empty-msg="ምንም በት/ት ያሉ የተመዘገበ ሰራተኛ የለም።" class="table table-bordered table-striped small" style="color: #000;">
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
                $dateParts = explode('-', $employee['birth_date']);
                $ethDate = EthiopianDateHelper::toEthCalendar($dateParts[2] ?? 0, $dateParts[1] ?? 0, $dateParts[0] ?? 0);
                $regdateParts = explode('-', $employee['rdate']);
                $regethDate = EthiopianDateHelper::toEthCalendar($regdateParts[2] ?? 0, $regdateParts[1] ?? 0, $regdateParts[0] ?? 0);
              ?>
                <tr id="row-<?= $employee['record_id'] ?>">
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= (isset($ethDate['month']) ? EthiopianDateHelper::getMonthName($ethDate['month']) : '') ?> <?= $ethDate['day'] ?? '' ?> <?= $ethDate['year'] ?? '' ?></td>
                  <td><?= (isset($regethDate['month']) ? EthiopianDateHelper::getMonthName($regethDate['month']) : '') ?> <?= $regethDate['day'] ?? '' ?> <?= $regethDate['year'] ?? '' ?></td>
                  <td class="text-center align-middle">
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-onleave-views/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary" title="እይ">
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
                    <?php if ($employee['registered_by'] === $_SESSION['user']['id']): ?>
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
      <div class="modal-header">
        <h6 class="modal-title font-weight-bold">
          <i class="fas fa-plus mr-1"></i> የትምህርት እድል መዝገባ
        </h6>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="scholarshipForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-store" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <!-- Employee Search -->
          <div class="form-group position-relative">
            <label for="empSearchInput" class="font-weight-bold mb-1"><small class="font-weight-bold">ሰራተኛ ይፈልጉ</small></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
              <input type="text" id="empSearchInput" class="form-control form-control-sm" placeholder="የሰራተኛ ስም ወይም መታወቂያ..." autocomplete="off">
            </div>
            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1051; display: none; max-height: 200px; overflow-y: auto;"></div>
          </div>

          <!-- Employee Preview -->
          <div id="selectedEmployeePreview" class="card card-widget widget-user-2 shadow-sm" style="display: none; background-color: #f4f6f9;">
            <div class="widget-user-header">
              <div class="widget-user-image">
                <img id="display_image" class="img-circle elevation-2" src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/public/dist/img/avatar5.png" alt="User Avatar" style="width: 60px; height: 60px; object-fit: cover;">
              </div>
              <h3 id="display_name" class="widget-user-username text-primary ml-3" style="font-size: 1.1rem; font-weight: 600;">---</h3>
              <h5 id="display_id_text" class="widget-user-desc ml-3 text-muted">ID: ---</h5>
              <input type="hidden" name="employee_id" id="selected_employee_id">
            </div>
          </div>

          <!-- Historical Toggle -->
          <div class="alert alert-light border mt-3 mb-2 py-2 px-3 d-flex align-items-center justify-content-between">
            <div>
              <i class="fas fa-history text-warning mr-1"></i>
              <small class="font-weight-bold">ያለፈ (የተጠናቀቀ) የትምህርት እድል ነው?</small>
              <small class="text-muted d-block" style="font-size:0.75rem;">
                ካጠናቀቁ የማጠናቀቂያ ቀን ያስገቡ፤ ፋይል አማራጭ ይሆናል።
              </small>
            </div>
            <div class="custom-control custom-switch ml-3">
              <input type="checkbox" class="custom-control-input" id="is_historical" name="is_historical" value="1">
              <label class="custom-control-label" for="is_historical"></label>
            </div>
          </div>

          <!-- Main Fields Row -->
          <div class="row mt-2">
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label for="scholarship_type" class="mb-1"><small class="font-weight-bold">የተሰጣቸው የትምህርት እድል</small></label>
                <select class="form-control form-control-sm" name="scholarship_type" id="scholarship_type">
                  <option selected disabled value="">ይምረጡ</option>
                  <option value="የመጀመሪያ_ዲግሪ">የመጀመሪያ ዲግሪ</option>
                  <option value="ሁለተኛ_ዲግሪ">ሁለተኛ ዲግሪ</option>
                  <option value="ሶስተኛ_ዲግሪ">ሶስተኛ ዲግሪ</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label for="scholarship_duration_years" class="mb-1"><small class="font-weight-bold">የቆይታ ጊዜ (ዓመት)</small></label>
                <input type="number" name="scholarship_duration_years" class="form-control form-control-sm" id="scholarship_duration_years" min="1" step="1" value="1">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">ውል የተያዘበት ቀን</small></label>
                <input type="text"
                    class="ethiopian-date form-control form-control-sm"
                    name="eth_agreement_date"
                    data-rule="past"
                    data-gregorian="#agreement_date"
                    placeholder="ቀን/ወር/ዓ.ም ይምረጡ"
                    readonly
                    style="background-color: #fff; cursor: pointer;">
                <input type="date" class="d-none" id="agreement_date" name="agreement_date" readonly>
              </div>
            </div>
          </div>

          <!-- End Date Row -->
          <div class="row mt-1" id="end_date_row" style="display: none;">
            <div class="col-md-12">
              <div class="form-group mb-2">
                <label class="mb-1">
                  <small class="font-weight-bold">
                    <i class="fas fa-calendar-check text-success mr-1"></i>
                    ያጠናቀቁበት ቀን
                    <span class="text-danger" id="end_date_required_star" style="display:none;">*</span>
                  </small>
                </label>
                <input type="text"
                    class="ethiopian-date form-control form-control-sm"
                    name="eth_end_date"
                    data-rule="past"
                    data-gregorian="#end_date"
                    placeholder="ቀን/ወር/ዓ.ም ይምረጡ"
                    readonly
                    id="eth_end_date_input"
                    style="background-color: #fff; cursor: pointer;">
                <input type="date" class="d-none" id="end_date" name="end_date" readonly>
              </div>
            </div>
          </div>

          <!-- File Upload -->
          <div class="row mt-2">
            <div class="col-md-12">
              <div class="form-group mb-2">
                <label for="attachment" class="mb-1">
                  <small class="font-weight-bold">የትምህርት እድል ሰነድ (Letter/Evidence)</small>
                  <span id="file_optional_badge" class="badge badge-secondary ml-1" style="display:none; font-size:0.7rem;">አማራጭ</span>
                  <span id="file_required_badge" class="badge badge-danger ml-1" style="font-size:0.7rem;">አስፈላጊ</span>
                </label>
                <div class="custom-file">
                  <input type="file" name="scholarship_file" class="custom-file-input" id="attachment" accept=".pdf,.jpg,.jpeg,.png">
                  <label class="custom-file-label" for="attachment">ፋይል ይምረጡ (PDF/Image)...</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">ዝጋ</button>
          <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
            <i class="fas fa-save"></i> መረጃውን መዝግብ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script nonce="<?= $GLOBALS['nonce'] ?>">
document.addEventListener('DOMContentLoaded', function () {
    // Base URL from PHP
    const BASE_URL = '<?= rtrim($_ENV['BASE_URL'], '/') ?>';
    let searchTimeout = null;

    /* -----------------------------------------------------------
        1. ሞዳሉ ሲከፈት መረጃዎችን ማጽዳት
    ----------------------------------------------------------- */
    $('#scholarshipModal').on('show.bs.modal', function () {
        $('#scholarshipForm')[0].reset();
        $('#selected_employee_id').val('');
        $('#selectedEmployeePreview').hide();
        $('#searchResults').hide().empty();
        $('#submitBtn').prop('disabled', false); // Button always enabled
        $('.custom-file-label').html('ፋይል ይምረጡ (PDF/Image)...');
        
        // Reset toggle state
        $('#is_historical').prop('checked', false);
        $('#end_date_row').hide();
        $('#end_date_required_star').hide();
        $('#file_optional_badge').hide();
        $('#file_required_badge').show();
        
        // Clear hidden date inputs
        $('#agreement_date').val('');
        $('#end_date').val('');
        $('[name="eth_agreement_date"]').val('');
        $('[name="eth_end_date"]').val('');
        
        // Clear validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    /* -----------------------------------------------------------
        2. ሰራተኛ ፍለጋ (Debounce Logic)
    ----------------------------------------------------------- */
    $(document).on('input', '#empSearchInput', function() {
        const query = $(this).val().trim();
        const $resultsDiv = $('#searchResults');

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $resultsDiv.hide().empty();
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${BASE_URL}/employee-scholarship-search?query=${encodeURIComponent(query)}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(employees => {
                $resultsDiv.empty().show();

                if (employees.length === 0) {
                    $resultsDiv.append('<div class="list-group-item text-danger">ምንም ሰራተኛ አልተገኘም</div>');
                    return;
                }

                employees.forEach(emp => {
                    const fullName = `${emp.first_name} ${emp.father_name} ${emp.g_father_name}`;
                    
                    let imgFullUrl = BASE_URL + '/public/dist/img/avatar5.png';
                    if (emp.employee_image) {
                        imgFullUrl = `${BASE_URL}/serve-file?file=${encodeURIComponent(emp.employee_image)}&type=image`;
                    }

                    const $btn = $(`
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center">
                            <img src="${imgFullUrl}" width="35" height="35" class="rounded-circle mr-3 border" onerror="this.src='${BASE_URL}/public/dist/img/avatar5.png'">
                            <div>
                                <div class="font-weight-bold text-sm">${fullName}</div>
                                <small class="text-muted">መታወቂያ: ${emp.employee_id}</small>
                            </div>
                        </button>
                    `);

                    $btn.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        selectEmployee(emp.uuid, fullName, emp.employee_id, imgFullUrl);
                    });

                    $resultsDiv.append($btn);
                });
            })
            .catch(err => {
                console.error("Search Error:", err);
            });
        }, 300);
    });

    /* -----------------------------------------------------------
        3. ሰራተኛ የመምረጥ ተግባር (Selection)
    ----------------------------------------------------------- */
    window.selectEmployee = function(id, name, empId, imageUrl) {
        $('#selected_employee_id').val(id); 
        $('#empSearchInput').val(name);
        $('#selectedEmployeePreview').fadeIn();
        $('#display_name').text(name);
        $('#display_id_text').text('መታወቂያ: ' + empId);
        $('#display_image').attr('src', imageUrl);
        $('#searchResults').hide().empty();
        
        // Trigger validation
        validateForm();
    };

    /* -----------------------------------------------------------
        4. የፋይል ስም መቀየር (Bootstrap Custom File Input)
    ----------------------------------------------------------- */
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').html(fileName);
        }
        validateForm();
    });

    /* -----------------------------------------------------------
        5. Historical Toggle Functionality
    ----------------------------------------------------------- */
    $('#is_historical').on('change', function() {
        const isHistorical = $(this).is(':checked');
        
        if (isHistorical) {
            $('#end_date_row').show();
            $('#end_date_required_star').show();
            $('#file_optional_badge').show();
            $('#file_required_badge').hide();
        } else {
            $('#end_date_row').hide();
            $('#end_date_required_star').hide();
            $('#file_optional_badge').hide();
            $('#file_required_badge').show();
            $('#end_date').val('');
            $('#eth_end_date_input').val('');
        }
        validateForm();
    });

    /* -----------------------------------------------------------
        6. Form Validation Function
    ----------------------------------------------------------- */
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate employee selection
        const employeeId = $('#selected_employee_id').val();
        if (!employeeId) {
            showError('#empSearchInput', 'እባክዎ ሰራተኛ ይምረጡ።');
            isValid = false;
        }
        
        // Validate scholarship type
        const scholarshipType = $('#scholarship_type').val();
        if (!scholarshipType) {
            showError('#scholarship_type', 'እባክዎ የትምህርት እድል ይምረጡ።');
            isValid = false;
        }
        
        // Validate duration
        const duration = parseInt($('#scholarship_duration_years').val());
        if (!duration || duration < 1) {
            showError('#scholarship_duration_years', 'እባክዎ ትክክለኛ የቆይታ ጊዜ ያስገቡ (ቢያንስ 1 ዓመት)።');
            isValid = false;
        }
        
        // Validate agreement date
        const agreementDate = $('#agreement_date').val();
        if (!agreementDate) {
            showError('[name="eth_agreement_date"]', 'እባክዎ የውል ቀን ይምረጡ።');
            isValid = false;
        }
        
        const isHistorical = $('#is_historical').is(':checked');
        
        if (isHistorical) {
            // Validate end date for historical records
            const endDate = $('#end_date').val();
            if (!endDate) {
                showError('#eth_end_date_input', 'እባክዎ የማጠናቀቂያ ቀን ይምረጡ።');
                isValid = false;
            } else if (agreementDate && endDate) {
                const agDt = new Date(agreementDate);
                const endDt = new Date(endDate);
                if (endDt <= agDt) {
                    showError('#eth_end_date_input', 'የማጠናቀቂያ ቀን ከውል ቀን በኋላ መሆን አለበት።');
                    isValid = false;
                }
            }
            
            // File is optional in historical mode - only validate if provided
            const file = $('#attachment')[0].files[0];
            if (file) {
                const allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (!allowed.includes(file.type)) {
                    showError('#attachment', 'እባክዎ PDF ወይም ምስል (JPG, PNG) ፋይል ብቻ ይምረጡ።');
                    isValid = false;
                } else if (file.size > 5 * 1024 * 1024) {
                    showError('#attachment', 'የፋይል መጠን ከ5MB መብለጥ የለበትም።');
                    isValid = false;
                }
            }
        } else {
            // Validate file is required for active records
            const file = $('#attachment')[0].files[0];
            if (!file) {
                showError('#attachment', 'እባክዎ የትምህርት እድል ሰነድ ይምረጡ።');
                isValid = false;
            } else {
                const allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (!allowed.includes(file.type)) {
                    showError('#attachment', 'እባክዎ PDF ወይም ምስል (JPG, PNG) ፋይል ብቻ ይምረጡ።');
                    isValid = false;
                } else if (file.size > 5 * 1024 * 1024) {
                    showError('#attachment', 'የፋይል መጠን ከ5MB መብለጥ የለበትም።');
                    isValid = false;
                }
            }
        }
        
        return isValid;
    }
    
    function showError(selector, message) {
        const $element = $(selector);
        $element.addClass('is-invalid');
        const $feedback = $('<div class="invalid-feedback" style="display: block;">' + message + '</div>');
        $element.after($feedback);
    }
    
    /* -----------------------------------------------------------
        7. Ethiopian Date Picker Change Handlers
    ----------------------------------------------------------- */
    $(document).on('change', '[name="eth_agreement_date"]', function() {
        validateForm();
    });
    
    $(document).on('change', '[name="eth_end_date"]', function() {
        validateForm();
    });
    
    /* -----------------------------------------------------------
        8. Form Submission - Button always enabled, validation prevents submission
    ----------------------------------------------------------- */
    $('#scholarshipForm').on('submit', function(e) {
        const isValid = validateForm();
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = $('.is-invalid').first();
            if (firstError.length) {
                firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return false;
        }
        
        // Disable button during submission to prevent double submission
        const $submitBtn = $('#submitBtn');
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> በመዝገብ ላይ...');
        
        // Re-enable after 30 seconds if something goes wrong
        setTimeout(function() {
            $submitBtn.prop('disabled', false);
            $submitBtn.html('<i class="fas fa-save"></i> መረጃውን መዝግብ');
        }, 30000);
        
        return true;
    });
    
    /* -----------------------------------------------------------
        9. Search Results Click Outside Handler
    ----------------------------------------------------------- */
    $(document).on('mousedown', function(e) {
        if (!$(e.target).closest('#searchResults, #empSearchInput').length) {
            $('#searchResults').hide();
        }
    });
    
    /* -----------------------------------------------------------
        10. Delete Scholarship Handler
    ----------------------------------------------------------- */
    $(document).on('click', '.delete-scholarship', function(e) {
        const $btn = $(this);
        const recordId = $btn.data('id');
        const employeeName = $btn.data('name');
        
        // You need to implement confirmDelete function or use your existing one
        if (typeof confirmDelete === 'function') {
            confirmDelete({
                endpoint:    'delete-scholarship-process',
                id:          recordId,
                name:        employeeName,
                task:        'delete',
                title:       `"የ${employeeName}" ት/ት እድል ይሰረዝ?`,
                warning:     `<strong>"የ${employeeName}"</strong> ን ት/ት እድል ሊያስወግዱ ነው።`,
                confirmText: '<i class="fas fa-user-times"></i> አዎ፣ ሰርዝ!',
                successText: 'የት/ት እድሉ ተሰርዟል።',
                requireReason:   true,
                requirePassword: true,
                onSuccess: () => $(`#row-${recordId}`).remove()
            });
        } else {
            // Fallback confirmation
            if (confirm(`"${employeeName}" ን ት/ት እድል መሰረዝ እንደሚፈልጉ እርግጠኛ ነዎት?`)) {
                // Implement delete logic here
                console.log('Delete scholarship:', recordId);
            }
        }
    });
});
</script>