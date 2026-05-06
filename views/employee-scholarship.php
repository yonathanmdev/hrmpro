<?php
use App\Helpers\EthiopianDateHelper; 
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
        <table id="example1" data-empty-msg="ምንም በት/ት ያሉ የተመዘገበ ሰራተኛ የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
              <th>የልደት ቀን</th>
              <th>Status</th>
              <th>የተመዘገቡበት ቀን</th>
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
  

                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></td>
                  <td><?= 'በት/ት ላይ' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>
                  <td>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" title="እይ" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-scholarship-edit?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>" title="አስተካክል" class="btn btn-sm btn-outline-secondary">
                      <i class="fas fa-edit"></i> 
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


