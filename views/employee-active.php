<?php
use App\Helpers\EthiopianDateHelper; 
$is_employee_active_page = true;
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የተመዘገቡ ሰራተኞች ዝርዝር</h3>
      </div>
      <div class="card-body">
            <table id="example1" data-empty-msg="ምንም ሰራተኛ አልተመዘገበም።" class="table table-bordered table-striped table-hover small" style="color: #000;" aria-describedby="example2_info">
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
  

                <tr id="row-<?= $employee['uuid'] ?>" 
    <?= ($employee['is_deleted'] == 1) ? 'style="background-color: #fff3cd;"' : '' ?>>
    
    <td><?= $index + 1 ?></td>
    <td><?= htmlspecialchars($employee['employee_id'] ?? '') ?></td>
    
    <!-- Name + badge -->
    <td>
        <?= htmlspecialchars(trim(
            ($employee['first_name']   ?? '') . ' ' . 
            ($employee['father_name']  ?? '') . ' ' . 
            ($employee['g_father_name']?? '')
        )) ?>
        <?php if (($employee['is_deleted'] ?? 0) == 1): ?>
            <br>
            <span class="badge" style="
                background-color: #ff9800; 
                color: #fff; 
                font-size: 10px;
                padding: 3px 7px;
                border-radius: 20px;">
                <i class="fas fa-clock"></i> እንዲሰረዝ ተጠይቋል
            </span>
        <?php endif; ?>
    </td>

    <td><?= htmlspecialchars($employee['job_name'] ?? 'N/A') ?></td>
    <td><?= ($employee['sex'] ?? '') === 'Male' ? 'ወንድ' : 'ሴት' ?></td>
    <td><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></td>
    
    <td>
        <?php
        $status = $employee['status'] ?? 'Active';
        echo htmlspecialchars(match ($status) {
            'Active'           => 'በስራ ላይ',
            'Study Leave'         => 'በት/ት ላይ',
            'Onboarding'       => 'ምዝገባ ላይ',
            'Study Leave Pending' => 'የት/ት እድል ያገኙ',
            default            => $status,
        });
        ?>
    </td>

    <td><?= EthiopianDateHelper::getMonthName($regethDate['month']) ?> <?= $regethDate['day'] ?> <?= $regethDate['year'] ?></td>

    <!-- Actions -->
    <td>
        <!-- View — always visible -->
        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views/<?= htmlspecialchars($employee['uuid'] ?? '') ?>" 
           title="እይ" 
           class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye"></i>
        </a>

        <?php if (($employee['is_deleted'] ?? 0) !== 2): ?>
            <!-- Edit — hidden when pending deletion -->
            <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-edit/<?= htmlspecialchars($employee['uuid'] ?? '') ?>/employee-active" 
               title="አስተካክል" 
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit"></i>
            </a>
        <?php endif; ?>

        <!-- Experience — always visible -->
        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-experience/<?= htmlspecialchars($employee['uuid'] ?? '') ?>" 
           title="ልምድ" 
           class="btn btn-sm btn-outline-info">
            <i class="fas fa-user-tie"></i>
        </a>

        <!-- Archive — always visible -->
        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-archive/<?= htmlspecialchars($employee['uuid'] ?? '') ?>" 
           title="ማህደር" 
           class="btn btn-sm btn-outline-dark">
            <i class="fas fa-archive"></i>
        </a>

        <?php if (($employee['is_deleted'] ?? 0) !== 2): ?>
            <!-- Delete — hidden when pending deletion -->
            <button type="button"
                class="btn btn-sm btn-outline-danger shadow myapp-delete-btn"
                title="ሰርዝ"
                data-id="<?= htmlspecialchars($employee['uuid']) ?>"
                data-name="<?= htmlspecialchars(trim(
                    ($employee['first_name']  ?? '') . ' ' . 
                    ($employee['father_name'] ?? '') . ' ' . 
                    ($employee['g_father_name'] ?? '')
                )) ?>">
                <i class="fas fa-trash-alt me-1"></i>
            </button>
        <?php else: ?>
            <!-- Pending indicator — shown instead of delete button -->
            <span class="btn btn-sm btn-outline-warning disabled" 
                  title="የመሰረዝ ጥያቄ በመጠባበቅ ላይ">
                <i class="fas fa-hourglass-half"></i>
            </span>
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
