<?php $is_employee_registration_page = true; ?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የሰራተኛ መመዝገቢያ</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#employeeRegistrationModal">
            <i class="fas fa-user-plus"></i> አዲስ ሰራተኛ መዝግብ
          </button>
        </div>
      </div>
    </div>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የሰራተኛ ዝርዝር</h3>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Employee ID</th>
              <th>Full Name</th>
              <th>Job</th>
              <th>Sex</th>
              <th>Birth Date</th>
              <th>Status</th>
              <th>Registered At</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $index => $employee): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($employee['sex'] ?? '') ?></td>
                  <td><?= htmlspecialchars($employee['birth_date'] ?? '') ?></td>
                  <td><?= htmlspecialchars($employee['status'] ?? 'Active') ?></td>
                  <td><?= htmlspecialchars($employee['rdate'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center">ምንም ሰራተኛ አልተመዘገበም።</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="employeeRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="employeeRegistrationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <form action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-registration-save" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="employeeRegistrationModalLabel">አዲስ ሰራተኛ መዝግብ</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="employee_id">የሰራተኛው መለያ ቁጥር</label>
                <input type="text" class="form-control" id="employee_id" name="employee_id" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="first_name">ስም</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="father_name">የአባት ስም</label>
                <input type="text" class="form-control" id="father_name" name="father_name" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="g_father_name">የአያት ስም</label>
                <input type="text" class="form-control" id="g_father_name" name="g_father_name" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="mother_name">የእናት ሙሉ ስም</label>
                <input type="text" class="form-control" id="mother_name" name="mother_name" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="sex">ጾታ</label>
                <select class="form-control" id="sex" name="sex" required>
                     <option value="">ይምረጡ</option>
                  <option value="Male">ወንድ</option>
                  <option value="Female">ሴት</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="birth_date">የትውልድ ቀን</label>
                <input type="date" class="form-control" id="birth_date" name="birth_date" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="phone_number">ስቁ.</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="yegabcha_huneta">የጋብቻ ሁኔታ</label>
                <input type="text" class="form-control" id="yegabcha_huneta" name="yegabcha_huneta" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="job_property_id">የስራ መደብ</label>
                <select class="form-control" id="job_property_id" name="job_property_id" required>
                  <option value="">-- ይምረጡ --</option>
                  <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                      <option value="<?= htmlspecialchars($job['id']) ?>"><?= htmlspecialchars($job['job_name']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="date_of_employed">የቅጥር ቀን</label>
                <input type="date" class="form-control" id="date_of_employed" name="date_of_employed">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="level_of_education">የትምህርት ደረጃ</label>
                <input type="text" class="form-control" id="level_of_education" name="level_of_education" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="department">የሙያ ዘርፍ</label>
                <input type="text" class="form-control" id="department" name="department">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="employment_situation">Employment Situation</label>
                <input type="text" class="form-control" id="employment_situation" name="employment_situation" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="immidate_boss">Immediate Boss</label>
                <input type="text" class="form-control" id="immidate_boss" name="immidate_boss">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="annual_rest">የዓመት እረፍት</label>
                <input type="number" step="1" min="0" class="form-control" id="annual_rest" name="annual_rest" value="0">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="effeciency">Efficiency (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="effeciency" name="effeciency">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="level_of_effeciency">Level of Efficiency</label>
                <input type="text" class="form-control" id="level_of_effeciency" name="level_of_effeciency" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="no_of_files_in_folder">የማህደር የፋይል ብዛት</label>
                <input type="number" step="1" min="0" class="form-control" id="no_of_files_in_folder" name="no_of_files_in_folder" value="0">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="experience">የስራ ልምድ</label>
                <input type="text" class="form-control" id="experience" name="experience">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="displin_situation">የዲሲፕሊን ሁኔታ</label>
                <input type="text" class="form-control" id="displin_situation" name="displin_situation" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="competency_situation">Competency Situation</label>
                <input type="text" class="form-control" id="competency_situation" name="competency_situation">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="employee_image">ፎቶ</label>
                <input type="file" class="form-control-file" id="employee_image" name="employee_image" accept="image/*" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="employee_file201">የት/ት ማስረጃ እና ሌሎች</label>
                <input type="file" class="form-control-file" id="employee_file201" name="employee_file201" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="remark">Remark</label>
                <textarea class="form-control" id="remark" name="remark" rows="2"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ዝጋ</button>
          <button type="submit" class="btn btn-primary">መዝግብ</button>
        </div>
      </form>
    </div>
  </div>
</div>
