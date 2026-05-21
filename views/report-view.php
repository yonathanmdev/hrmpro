<?php
$is_report_view_page = true;
$branchId = $_SESSION['user']['branch_id'] ?? null;
$reportType = $_GET['type'] ?? 'employees';
$reportTitle = match($reportType) {
    'employees' => 'የሰራተኞች ዝርዝር ሪፖርት',
    default => 'ሪፖርት'
};
?>
  <title><?= htmlspecialchars($reportTitle) ?> — ሪፖርት</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
    .report-header { background: #fff; border-bottom: 2px solid #dee2e6; padding: 16px 24px; }
    .report-header h5 { margin: 0; font-weight: 700; }
    .filter-bar { background: #fff; padding: 12px 24px; border-bottom: 1px solid #dee2e6; }
    .table-wrapper { padding: 24px; }
    .badge-type { font-size: 12px; }
    @media print {
      .no-print { display: none !important; }
      body { background: #fff; }
      .table-wrapper { padding: 0; }
    }
  </style>

  <div class="report-header d-flex align-items-center justify-content-between no-print">
    <div>
      <h5>
        <i class="fas fa-chart-bar mr-2 text-primary"></i>
        <?= htmlspecialchars($reportTitle) ?>
      </h5>
      <small class="text-muted">
        Branch ID: <code><?= htmlspecialchars($branchId) ?></code>
      </small>
    </div>
    <div>
      <button class="btn btn-outline-secondary btn-sm mr-1" onclick="window.print()">
        <i class="fas fa-print mr-1"></i> አትም
      </button>
      <button class="btn btn-outline-success btn-sm mr-1" onclick="exportCSV()">
        <i class="fas fa-file-csv mr-1"></i> CSV
      </button>
      <button class="btn btn-outline-danger btn-sm" onclick="window.print()">
        <i class="fas fa-file-pdf mr-1"></i> PDF
      </button>
    </div>
  </div>

  <div class="filter-bar no-print">
    <form method="GET" action="" class="form-row align-items-end">
      
      <input type="hidden" name="action" value="getReport">
      <input type="hidden" name="branch_id" value="<?= htmlspecialchars($branchId) ?>">
      <input type="hidden" name="type" value="<?= htmlspecialchars($reportType) ?>">

      <div class="col-md-3 mb-2">
        <label class="small font-weight-bold mb-1">ከ</label>
        <input type="date" name="from" class="form-control form-control-sm"
               value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
      </div>

      <div class="col-md-3 mb-2">
        <label class="small font-weight-bold mb-1">እስከ</label>
        <input type="date" name="to" class="form-control form-control-sm"
               value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
      </div>

      <div class="col-md-3 mb-2">
        <label class="small font-weight-bold mb-1">ዲፓርትመንት</label>
        <select name="department" class="form-control form-control-sm">
          <option value="">-- ሁሉም --</option>
          <?php if (!empty($departments)): ?>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= htmlspecialchars($dept['id']) ?>"
                <?= (($_GET['department'] ?? '') === $dept['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($dept['name']) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <div class="col-md-3 mb-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">
          <i class="fas fa-search mr-1"></i> አጣራ
        </button>
      </div>
    </form>
  </div>

  <div class="table-wrapper">

    <div class="d-none d-print-block mb-3">
      <h5 class="font-weight-bold"><?= htmlspecialchars($reportTitle) ?></h5>
      <p class="text-muted small mb-1">Branch ID: <?= htmlspecialchars($branchId) ?></p>
      <p class="text-muted small">የታተመበት ቀን: <?= date('Y-m-d H:i') ?></p>
      <hr>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <table id="reportTable" class="table table-bordered table-striped table-hover small mb-0" style="color:#000;">
          <thead class="thead-dark">
            <tr>
              <?php foreach ($columns as $col): ?>
                <th><?= htmlspecialchars($col) ?></th>
              <?php endforeach; ?>
              <th class="no-print">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($data)): ?>
              <?php foreach ($data as $index => $row): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <?php foreach (array_slice($row, 1) as $cell): ?>
                    <td><?= htmlspecialchars($cell ?? '—') ?></td>
                  <?php endforeach; ?>
                  <td class="no-print">
                    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee/<?= htmlspecialchars($row['id'] ?? '') ?>"
                       target="_blank"
                       class="btn btn-xs btn-outline-info"
                       title="ዝርዝር">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= count($columns) + 1 ?>" class="text-center text-muted py-4">
                  ምንም ዳታ አልተገኘም።
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <small class="text-muted mt-2 d-block">
      ጠቅላላ: <strong><?= count($data) ?></strong> መዝገቦች
    </small>

  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
            </section>