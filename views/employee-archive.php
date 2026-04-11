<?php
use App\Helpers\EthiopianDateHelper; 
 $is_employee_registration_page = true; ?>
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
              <th>የፋይል አይነት</th>
              <th>የተያያዘ ፋይል</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($documentData)): ?>
              <?php foreach ($documentData as $index => $document):?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($document['document_type'] ?? '') ?></td>
                  <td>                <?php if (!empty($document['file_url'])): ?>
                  <small class="form-text text-muted">የተያያዘ ፋይል: <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($document['file_url']) ?>&type=document" target="_blank">ተመልክት</a>
          </small>
                <?php endif; ?></td>
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
