<?php
use App\Helpers\EthiopianDateHelper; 
$is_employee_deletion_page = true;
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">መረጃቸው እንዲጠፋ የተጠየቁ ሰራተኞች ዝርዝር</h3>
      </div>
      <div class="card-body">
<table id="example1" data-empty-msg=" ምንም የመሰረዝ ጥያቄ የለም።" class="table table-bordered table-striped table-hover small" style="color: #000;" aria-describedby="example2_info">
    <thead>
        <tr>
            <th>ሰራተኛ</th>
            <th>የስራ መደብ</th>
            <th>የጠየቀ</th>
            <th>ምክንያት</th>
            <th>የተጠየቀበት ቀን</th>
            <th>ድርጊት</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pending as $emp): ?>
        <tr id="row-<?= $emp['uuid'] ?>">
            <td>
                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['father_name']) ?>
            </td>
            <td><?= htmlspecialchars($emp['job_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($emp['deleted_by_name'] ?? '-') ?></td>
            <td>
                <span title="<?= htmlspecialchars($emp['deletion_reason']) ?>">
                    <?= htmlspecialchars(substr($emp['deletion_reason'], 0, 30)) ?>...
                </span>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($emp['deleted_at'])) ?></td>
            <td>
                <!-- Approve button -->
                <button 
                    class="btn btn-success btn-sm director-approval-delete-btn"
                    data-id="<?= $emp['uuid'] ?>"
                    data-name="<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['father_name']) ?>">
                    <i class="fas fa-check"></i> አፅድቅ
                </button>

                <!-- Reject button -->
                <button 
                    class="btn btn-danger btn-sm director-reject-delete-btn"
                    data-id="<?= $emp['uuid'] ?>"
                    data-name="<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['father_name']) ?>">
                    <i class="fas fa-times"></i> ውድቅ አድርግ
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
      </div>
    </div>
  </div>
</section>
