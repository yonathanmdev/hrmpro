<?php
use App\Helpers\EthiopianDateHelper; 
$is_employee_on_scholarship_page = true; 
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-body">

        <!-- Header -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h6 class="mb-0 font-weight-bold text-dark">
            በት/ት ላይ ያሉ
          </h6>
        </div>

        <table id="example1" 
               data-empty-msg="ምንም በት/ት ያሉ የተመዘገበ ሰራተኛ የለም።" 
               class="table table-bordered table-striped small" 
               style="color: #000;" 
               aria-describedby="example2_info">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>Status</th>
              <th>ውል የያዙበት ቀን</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $index => $employee): 

                $dateParts    = explode('-', $employee['birth_date']);
                $ethDate      = EthiopianDateHelper::toEthCalendar(
                                    $dateParts[2], $dateParts[1], $dateParts[0]
                                );

                $regdateParts = explode('-', $employee['agreement_date']);
                $regethDate   = EthiopianDateHelper::toEthCalendar(
                                    $regdateParts[2], $regdateParts[1], $regdateParts[0]
                                );

                $ethAgreementDate =
                    EthiopianDateHelper::getMonthName($regethDate['month']) . ' ' .
                    $regethDate['day'] . ' ' .
                    $regethDate['year'];
              ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(
                        ($employee['first_name']    ?? '') . ' ' .
                        ($employee['father_name']   ?? '') . ' ' .
                        ($employee['g_father_name'] ?? '')
                      )) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td>
                    <?= EthiopianDateHelper::getMonthName($ethDate['month']) ?>
                    <?= $ethDate['day'] ?>
                    <?= $ethDate['year'] ?>
                  </td>
                  <td><?= 'በት/ት ላይ' ?></td>
                  <td>
                    <?= EthiopianDateHelper::getMonthName($regethDate['month']) ?>
                    <?= $regethDate['day'] ?>
                    <?= $regethDate['year'] ?>
                  </td>
                  <td>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" 
                       title="እይ" 
                       class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>

                    <a href="#"
                       class="btn btn-sm btn-outline-success scholarship-return-btn"
                       data-uuid="<?= htmlspecialchars($employee['uuid']) ?>"
                       data-record-id="<?= htmlspecialchars($employee['record_id']) ?>"
                       data-agreement-date="<?= htmlspecialchars($employee['agreement_date']) ?>"
                       data-eth-agreement-date="<?= htmlspecialchars($ethAgreementDate) ?>"
                       title="Register Return Date & Attach Tempo">
                      <i class="fas fa-calendar-check"></i>
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
<!-- Modal -->
<div class="modal fade" id="scholarshipReturnModal" tabindex="-1" role="dialog" aria-labelledby="scholarshipReturnModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <form id="scholarshipReturnForm"
            action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-return-store" method="POST"
            enctype="multipart/form-data"
            novalidate>

        <div class="modal-header">
          <h5 class="modal-title font-weight-bold" id="scholarshipReturnModalLabel">
            <i class="fas fa-user-graduate mr-1"></i>
            የትምህርት ተመላሽ ምዝገባ
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <!-- Hidden Values -->
          <input type="hidden" name="employee_uuid" id="employee_uuid">
          <input type="hidden" name="record_id"     id="record_id">

          <div class="row">

            <!-- Agreement Date (read-only display) -->
            <div class="col-md-4">
              <div class="form-group">
                <label>ውል የያዙበት ቀን</label>
                <input type="text"
                       class="form-control form-control-sm"
                       id="eth_agreement_date"
                       readonly
                       style="background-color:#e9ecef;">
                <input type="hidden"
                       name="agreement_date"
                       id="agreement_date">
              </div>
            </div>

            <!-- Return Date -->
            <div class="col-md-4">
              <div class="form-group">
                <label>
                  የተመለሰበት ቀን
                  <span class="text-danger">*</span>
                </label>
                <input type="text"
                       class="ethiopian-date form-control form-control-sm"
                       name="eth_return_date"
                       id="eth_return_date"
                       data-rule="past"
                       data-gregorian="#return_date"
                       placeholder="ቀን/ወር/ዓ.ም ይምረጡ"
                       readonly
                       style="background-color:#fff; cursor:pointer;">
                <input type="date"
                       name="return_date"
                       id="return_date"
                       class="d-none">
                <div class="invalid-feedback">
                  የተመለሰበትን ቀን ያስገቡ።
                </div>
              </div>
            </div>

            <!-- Tempo File -->
            <div class="col-md-4">
              <div class="form-group">
                <label>
                  ቴምፖ ፋይል
                  <span class="text-danger">*</span>
                </label>
                <input type="file"
                       name="tempo_file"
                       id="tempo_file"
                       class="form-control form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png">
                <div class="invalid-feedback">
                  የቴምፖ ፋይል ያያይዙ።
                </div>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary btn-sm"
                  data-dismiss="modal">
            <i class="fas fa-times"></i>
          </button>
          <button type="submit"
                  id="scholarshipReturnSubmitBtn"
                  class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i>
            መዝግብ
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<script nonce="<?= $GLOBALS['nonce'] ?>">
document.addEventListener('DOMContentLoaded', function () {

    const form      = document.getElementById('scholarshipReturnForm');
    const submitBtn = document.getElementById('scholarshipReturnSubmitBtn');

    if (!form) return;

    const DEFAULT_BTN_HTML = '<i class="fas fa-save"></i> መዝግብ';

    // Single place that restores the button — called from every
    // possible exit path (success, error, network failure, modal
    // close, bfcache restore) so it can never get stuck.
    function resetSubmitButton() {
        submitBtn.disabled  = false;
        submitBtn.innerHTML = DEFAULT_BTN_HTML;
    }

    // --------------------
    // Open Modal
    // --------------------
    document.addEventListener('click', function (e) {

        const btn = e.target.closest('.scholarship-return-btn');

        if (!btn) return;

        e.preventDefault();

        document.getElementById('employee_uuid').value       = btn.dataset.uuid             || '';
        document.getElementById('record_id').value           = btn.dataset.recordId          || '';
        document.getElementById('agreement_date').value      = btn.dataset.agreementDate     || '';
        document.getElementById('eth_agreement_date').value  = btn.dataset.ethAgreementDate  || '';

        // Reset fields
        document.getElementById('return_date').value     = '';
        document.getElementById('eth_return_date').value = '';

        const tempoInput = document.getElementById('tempo_file');
        if (tempoInput) tempoInput.value = '';

        // Reset submit button in case modal is reopened after a failed submit
        resetSubmitButton();

        form.classList.remove('was-validated');

        $('#scholarshipReturnModal').modal('show');
    });

    // --------------------
    // Submit (AJAX — no full page navigation, so the button
    // state is fully under our control)
    // --------------------
    form.addEventListener('submit', function (e) {

        e.preventDefault();

        form.classList.remove('was-validated');

        const agreementDateValue = document.getElementById('agreement_date').value;
        const returnDateValue    = document.getElementById('return_date').value;
        const tempoInput         = document.getElementById('tempo_file');

        // 1. Return date required
        if (!returnDateValue) {
            Swal.fire({
                icon: 'warning',
                title: 'ማስጠንቀቂያ',
                text: 'የተመለሰበትን ቀን ያስገቡ።'
            });
            return;
        }

        // 2. Tempo file required
        if (!tempoInput || !tempoInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'ማስጠንቀቂያ',
                text: 'ቴምፖ ፋይል ያያይዙ።'
            });
            return;
        }

        const agreementDate = new Date(agreementDateValue);
        const returnDate    = new Date(returnDateValue);
        const today         = new Date();

        agreementDate.setHours(0, 0, 0, 0);
        returnDate.setHours(0, 0, 0, 0);
        today.setHours(0, 0, 0, 0);

        // 3. Future date check
        if (returnDate > today) {
            Swal.fire({
                icon: 'error',
                title: 'የማይፈቀድ',
                text: 'የተመለሰበት ቀን ከዛሬ ቀን በኋላ መሆን አይችልም።'
            });
            return;
        }

        // 4. Before agreement date check
        if (returnDate < agreementDate) {
            Swal.fire({
                icon: 'error',
                title: 'የማይፈቀድ',
                text: 'የተመለሰበት ቀን ከውል ቀን በፊት መሆን አይችልም።'
            });
            return;
        }

        // 5. Minimum one year check
        const minimumReturnDate = new Date(agreementDate);
        minimumReturnDate.setFullYear(minimumReturnDate.getFullYear() + 1);

        

        // 6. Browser validity check
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // All checks passed — disable button to prevent double submit
        submitBtn.disabled  = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> እየተመዘገበ...';

        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(async (response) => {
                const contentType = response.headers.get('content-type') || '';
                let data = null;

                // Only attempt to parse JSON if the server actually
                // says it sent JSON. This avoids silently treating an
                // HTML fallback/404 page as a parsed (and falsy) data
                // object.
                if (contentType.includes('application/json')) {
                    try { data = await response.json(); } catch (_) { /* malformed JSON */ }
                }

                // Require an EXPLICIT success === true in a real JSON
                // response. This is what catches the "route isn't
                // defined yet" case — where the server returns HTTP 200
                // with an HTML fallback page instead of JSON, which
                // previously slipped through as a false success.
                if (!response.ok || !data || data.success !== true) {
                    const msg = (data && data.message) || 'ምዝገባው አልተሳካም። እንደገና ይሞክሩ።';
                    throw new Error(msg);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'ተሳክቷል',
                    text: data.message || 'በተሳካ ሁኔታ ተመዝግቧል።'
                }).then(() => {
                    // Reload so the table reflects the updated record.
                    location.reload();
                });
            })
            .catch((err) => {
                Swal.fire({
                    icon: 'error',
                    title: 'ስህተት',
                    text: err.message || 'ግንኙነት ላይ ችግር ተፈጥሯል። እንደገና ይሞክሩ።'
                });
            })
            .finally(() => {
                // Runs on success, on a handled error, AND on a network
                // failure — so the button is *always* restored and the
                // user can immediately fix the issue and retry.
                resetSubmitButton();
            });
    });

    // --------------------
    // Clear validation state on any change
    // --------------------
    form.addEventListener('input',  function () { form.classList.remove('was-validated'); });
    form.addEventListener('change', function () { form.classList.remove('was-validated'); });

    // --------------------
    // Reset button if modal is closed without submitting
    // --------------------
    $('#scholarshipReturnModal').on('hidden.bs.modal', function () {
        resetSubmitButton();
        form.classList.remove('was-validated');
    });

    // --------------------
    // Safety net: if the browser restores this page from its
    // back/forward cache (bfcache) instead of reloading it fresh,
    // make sure the button isn't left frozen mid-submit.
    // --------------------
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            resetSubmitButton();
            form.classList.remove('was-validated');
        }
    });

});
</script>