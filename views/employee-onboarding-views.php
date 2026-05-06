<?php 
use App\Helpers\EthiopianDateHelper; 
?>

<section class="content">
  <div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
      
      <!-- Professional Header -->
      <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title font-weight-bold text-uppercase">
            <i class="fas fa-id-card mr-2 text-primary"></i> የተመዘገበ ሰራተኛ ማጽደቂያ
          </h3>
          <div class="card-tools">
            <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-registration" class="btn btn-sm btn-outline-secondary">
           <i class="fas fa-times fa-lg"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
        <form action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-onboadring-approve" 
              method="POST" 
              enctype="multipart/form-data" 
              id="employee-edit-form"
              onsubmit="return confirm('እርግጠኛ ነዎት? የሰራተኛውን ምዝገባ ማጽደቅ ይፈልጋሉ?');">
          
          <input type="hidden" name="uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">

          <!-- Profile Header: Image and Primary Stats -->
          <div class="row align-items-center border-bottom pb-4 mb-4">
            <div class="col-md-2 text-center">
              <?php if (!empty($employee['employee_image'])): ?>
                  <img src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_image']) ?>&type=image" 
                       class="img-fluid rounded shadow-sm border" style="max-height: 180px; width: auto;">
              <?php else: ?>
                  <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="height: 160px;">
                      <i class="fas fa-user-tie fa-4x text-muted"></i>
                  </div>
              <?php endif; ?>
            </div>
            
            <div class="col-md-10 mt-3 mt-md-0">
              <div class="row">
                <div class="col-md-12">
                  <h2 class="font-weight-bold mb-1 text-dark">
                    <?= htmlspecialchars($employee['first_name'] ?? '') ?> <?= htmlspecialchars($employee['father_name'] ?? '') ?> <?= htmlspecialchars($employee['g_father_name'] ?? '') ?>
                  </h2>
                  <p class="text-muted mb-3"><i class="fas fa-hashtag mr-1"></i> ID: <strong><?= htmlspecialchars($employee['employee_id'] ?? '') ?></strong></p>
                </div>
                <div class="col-md-4">
                  <small class="text-muted text-uppercase d-block font-weight-bold">የስራ መደብ</small>
                  <p class="lead mb-0 text-primary font-weight-normal"><?= htmlspecialchars($employee['job_name'] ?? '---') ?></p>
                </div>
                <div class="col-md-4">
                  <small class="text-muted text-uppercase d-block font-weight-bold">የሙያ ዘርፍ</small>
                  <p class="lead mb-0 text-dark"><?= htmlspecialchars($employee['department'] ?? '---') ?></p>
                </div>
                <div class="col-md-4">
                  <small class="text-muted text-uppercase d-block font-weight-bold">የጡረታ መለያ ቁጥር</small>
                  <p class="lead mb-0"><?= htmlspecialchars($employee['pension_number'] ?? '---') ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Secondary Information Grid -->
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">ጾታ</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['sex'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የትውልድ ቀን</label>
              <?php
                $dateParts = explode('-', $employee['birth_date']);
                $ethDate = EthiopianDateHelper::toEthCalendar($dateParts[2] ?? 1, $dateParts[1] ?? 1, $dateParts[0] ?? 2000);
              ?>
              <div class="border-bottom py-1"><?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">ስልክ ቁጥር</label>
              <div class="border-bottom py-1 text-success"><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($employee['phone_number'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የጋብቻ ሁኔታ</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['yegabcha_huneta'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የቅጥር ቀን</label>
              <?php
                $empdateParts = explode('-', $employee['date_of_employed']);
                $empethDate = EthiopianDateHelper::toEthCalendar($empdateParts[2] ?? 1, $empdateParts[1] ?? 1, $empdateParts[0] ?? 2000);
              ?>
              <div class="border-bottom py-1 font-weight-bold"><?= EthiopianDateHelper::getMonthName($empethDate['month']) ?> <?= $empethDate['day'] ?> <?= $empethDate['year'] ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የቅጥር ሁኔታ</label>
              <div class="border-bottom py-1"><span class="badge badge-info"><?= htmlspecialchars($employee['employment_situation'] ?? '---') ?></span></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የትምህርት ደረጃ</label>
              <div class="border-bottom py-1"><?= str_replace('_', ' ', htmlspecialchars($employee['level_of_education'] ?? '---')) ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የቅርብ ተጠሪ</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['immidate_boss'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የስራ ልምድ</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['experience'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">Efficiency (%)</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['effeciency'] ?? '0') ?>%</div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">Efficiency Level</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['level_of_effeciency'] ?? '---') ?></div>
            </div>

            <div class="col-md-3 mb-3">
              <label class="text-muted small d-block">የዓመት እረፍት</label>
              <div class="border-bottom py-1"><?= htmlspecialchars($employee['annual_rest'] ?? '0') ?> ቀናት</div>
            </div>
          </div>

          <!-- Final Status Checks -->
          <div class="row mt-3 bg-light p-3 rounded">
            <div class="col-md-6 mb-3 mb-md-0">
              <label class="text-muted small d-block">የዲሲፕሊን ሁኔታ</label>
              <p class="mb-0 <?= ($employee['displin_situation'] != 'ምንም የቅጣት ሪኮርድ የሌለባቸው') ? 'text-danger' : 'text-success' ?>">
                <i class="fas fa-info-circle mr-1"></i> <strong><?= htmlspecialchars($employee['displin_situation'] ?? '---') ?></strong>
              </p>
            </div>
            <div class="col-md-6 text-md-right">
              <label class="text-muted small d-block">ተያያዥ ፋይሎች</label>
              <?php if (!empty($employee['employee_file201'])): ?>
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_file201']) ?>&type=document" 
                   target="_blank" class="btn btn-sm btn-danger shadow-sm mt-1">
                  <i class="fas fa-file-pdf"></i> ሰነዱን ተመልከት
                </a>
              <?php else: ?>
                <span class="text-muted italic small">ምንም ፋይል የለም</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="card-footer bg-white mt-4 border-top">
            <div class="d-flex justify-content-end align-items-center">
              <span class="text-muted mr-auto font-italic small"><i class="fas fa-shield-alt"></i> HR Director ማረጋገጫ ፎርም</span>
              
              <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-registration" class="btn btn-default border mr-2 px-4">
                ዝጋ
              </a>

              <?php if (isset($userRole) && $userRole === 'hr_director'): ?>
              <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                <i class="fas fa-check-circle mr-2"></i> አጽድቅ
              </button>
              <?php endif; ?>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</section>