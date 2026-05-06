<?php
use App\Helpers\EthiopianDateHelper; 
$is_director_deleted_page = true;
 ?>
<section class="content">
  <div class="container-fluid">
      <div class="card card-primary card-outline">
    

      <div class="container-fluid py-4">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0">የተሰረዙ ዳይሬክተሮች</h5>
    <small style="color: var(--bs-danger);">
    <i class="fas fa-exclamation-triangle me-1"></i>
    ምንም ዓይነተ ስራ ያልተሰራባቸው ዳይሬክተሮች ከ90 ቀናት በኋላ ይሰረዛሉ
</small> 
</div>
    
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
     <table id="example1" class="table table-bordered table-striped table-hover small" style="color: #000;" >
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>ስም</th>
      <th>የሰረዘው</th>
      <th>የተሰረዘበት ቀን</th>
      <th>መስተካከል ያለባቸው</th>
      <th>ማጥፋት ይቻላል?</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($users)): ?>
      <tr>
        <td colspan="7" class="text-center text-muted py-4">
          <i class="fas fa-inbox me-2"></i> የተሰረዘ ዳይሬክተር የለም
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($users as $index => $user): ?>
        <?php
          $affected = [
            'የስራ መደብ' => (int)$user['affected_job_properties'],
            'ሰራተኛ'    => (int)$user['affected_employees'],
            ];
          $canPurge      = (bool)$user['can_purge'];
          $totalAffected = array_sum($affected);
        ?>

        <tr id="row-<?= htmlspecialchars($user['id']) ?>">

          <td><?= $index + 1 ?></td>

          <!-- ስም -->
          <td>
            <p class="mb-0 fw-500"><?= htmlspecialchars($user['director_name']) ?></p>
            </td>

          <!-- የሰረዘው -->
          <td><?= htmlspecialchars($user['deleted_by_name'] ?? 'N/A') ?>
         
        </td>

          <!-- የተሰረዘበት ቀን -->
          <td><?= date('Y-m-d h:i A', strtotime($user['deleted_at'])) ?></td>

          <!-- የሰሯቸው — affected records -->
          <td>
            <?php if ($canPurge): ?>
              <span class="badge bg-success">ምንም የለም</span>
            <?php else: ?>
              <?php foreach ($affected as $label => $count): ?>
                <?php if ($count > 0): ?>
                  <span class="badge bg-danger me-1 mb-1">
                    <?= $count ?> <?= $label ?>
                  </span>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </td>

          <!-- ማጥፋት ይቻላል? -->
          <td class="text-center">
            <?php if ($canPurge): ?>
              <span class="badge bg-success">
                <i class="fas fa-check me-1"></i> ይቻላል
              </span>
            <?php else: ?>
              <span class="badge bg-secondary"
                    title="ከላይ ያሉትን መዝገቦች አስቀድሞ ያስተካክሉ">
                <i class="fas fa-lock me-1"></i> አይቻልም
              </span>
            <?php endif; ?>
          </td>

          <!-- Actions -->
          <td>
            <!-- Restore button — always available -->
            <button class="btn btn-sm btn-success restore-director me-1"
              data-id="<?= htmlspecialchars($user['id']) ?>"
              data-name="<?= htmlspecialchars($user['director_name']) ?>"
              data-affected="<?= $totalAffected ?>">
              <i class="fas fa-undo me-1"></i> መልስ
            </button>

            <!-- Purge button — only enabled when can_purge = 1 -->
            <button class="btn btn-sm btn-danger purge-director"
              data-id="<?= htmlspecialchars($user['id']) ?>"
              data-name="<?= htmlspecialchars($user['director_name']) ?>"
              data-affected="<?= $totalAffected ?>"
              <?= !$canPurge ? 'disabled title="ከላይ ያሉትን መዝገቦች አስቀድሞ ያስተካክሉ"' : '' ?>>
              <i class="fas fa-trash-alt me-1"></i> አጥፋ
            </button>
          </td>

        </tr>

      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
    </div>
  </div>

</div>
    </div>
  </div>
</section>


