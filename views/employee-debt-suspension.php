<?php
use App\Helpers\EthiopianDateHelper; 
  ?>
<section class="content">
  <div class="container-fluid">
    

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h6 class="card-title">እዳ እገዳ ያለባቸው</h6>
      </div>

      <div class="card-body">
        <table id="example1" data-empty-msg="እዳ/እገዳ ያለበት ሰራተኛ የለም።" class="table table-bordered table-striped small" style="color: #000;" aria-describedby="example2_info">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>መለያ ቁጥር</th>
              <th>ስም</th>
              <th>የስራ መደብ</th>
              <th>ጾታ</th>
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
  

                <tr id="row-<?= $employee['record_id'] ?>">
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
                  <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
                  <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>
                   <td>
                     <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-clearing/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary" title="እይ">
                      <i class="fas fa-eye"></i> 
                    </a>
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-edit/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="ማስተካከያ">
                      <i class="fas fa-edit"></i> 
                    </a>
                   <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-debt-suspension-clearing/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/<?= htmlspecialchars($employee['record_id'] ?? '') ?>" 
   class="btn btn-sm btn-outline-success" 
   title="እዳ/እገዳ ማንሳት">
    <i class="fas fa-key"></i> 
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


