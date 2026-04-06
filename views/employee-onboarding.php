<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የተመዘገቡ ሰራተኞችን ማጽደቂያ</h3>
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
              <th>Registered At</th>
              <th>Actions</th>
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
                  <td><?= htmlspecialchars($employee['rdate'] ?? '') ?></td>
                  <td>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-onboarding-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" class="btn btn-sm btn-secondary" title="እይ">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-onboarding-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" class="btn btn-sm btn-primary" title="አጽድቅ">
                      <i class="fas fa-check"></i> 
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
