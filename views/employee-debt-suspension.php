<?php
use App\Helpers\EthiopianDateHelper; 
  ?>
<section class="content">
  <div class="container-fluid">
    

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">እዳ እገዳ ያለባቸው</h3>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
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
                  <td><?= htmlspecialchars($employee['status'] ?? 'Active') ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>
                   <td>
                     <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-clearing?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>&record_id=<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-secondary" title="እይ">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-edit?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>&record_id=<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-primary" title="ማስተካከያ">
                      <i class="fas fa-edit"></i> 
                    </a>
                   <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-clearing?uuid=<?= htmlspecialchars($employee['uuid'] ?? '') ?>&record_id=<?= htmlspecialchars($employee['record_id'] ?? '') ?>" 
   class="btn btn-sm btn-info" 
   title="እዳ/እገዳ ማንሳት">
    <i class="fas fa-trash"></i> 
</a>
                    
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center">እዳ/እገዳ ያለባቸው ምንም ሰራተኛ አልተመዘገበም።</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>


