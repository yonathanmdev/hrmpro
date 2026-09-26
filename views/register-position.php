<?php $is_position_page = true; ?>
 
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
<div class="card-header bg-white d-flex align-items-center">

  <div class="ml-auto">
    <button 
      type="button" 
      class="btn btn-primary btn-sm"
      data-toggle="modal" 
      data-target="#positionModal"
    >
     <i class="fas fa-plus mr-1"></i>
      መደብ መዝግብ
    </button>
  </div>

</div>

      </div>

      <div class="card-body">
      <!-- Header -->
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 font-weight-bold text-dark">
      የስራ መደብ ዝርዝር
    </h6>
  </div>
        <!-- Example Table (optional) -->
      <table id="example1" data-empty-msg="ምንም መደብ የለም።" class="table table-bordered table-hover dataTable dtr-inline small" style="color: #000;" aria-describedby="example2_info">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>ዳይሪክተር </th>
        <th>መደብ</th>
        <th>ደረጃ</th>
        <th>ደመወዝ</th>
        <th>የመደብ መታወቂያ ቁጥር</th>
        <th>Status</th>
        <th>action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($positions)): ?>
        <?php foreach ($positions as $index => $row): ?>
          <tr id="row-<?= $row['id'] ?>">
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['director_name']) ?></td>
            <td><?= htmlspecialchars($row['job_name']) ?></td>
            <td><?= htmlspecialchars($row['dereja']) ?></td>
            <td><?= htmlspecialchars($row['salary']) ?></td>
            <td><?= htmlspecialchars($row['job_identifier_no']) ?></td>
            <td><?= $row['status'] ?></td>
    
            <td class="text-center align-middle">
              <div class="btn-group btn-group-sm shadow-sm" role="group">

               <button class="btn btn-outline-secondary btn-sm edit-position" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['job_name']) ?>" title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
             <!-- Professional Delete Button Example -->
<button class="btn btn-sm btn-outline-danger shadow-sm delete-position" 
        data-id="<?= $row['id'] ?>" 
        data-name="<?= htmlspecialchars($row['job_name']) ?>"
        data-toggle="tooltip" 
        data-placement="top"
        title="<?= $row['current_filled'] != 0 ? 'ሰራተኞቹን አስቀድሞ ያስተካክሉ' : 'ይህንን መደብ ሰርዝ' ?>"
        <?= $row['current_filled'] != 0 ? 'disabled' : '' ?>>
    <i class="fas fa-trash-alt me-1"></i> ሰርዝ
</button>
  </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
      </div>

    </div>
    <!-- /.card -->

  </div>
</section>


<?php include 'partials/edit-position-modal.php'; ?>
<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="positionModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <form id="orgForm" method="POST" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-position-process">

       
        <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-plus mr-1"></i> አዲስ መደብ መዝግብ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body px-4 py-3">

          <!-- Row 1 -->
          <div class="row">
            <div class="col-md-8">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የስራ ክፍል</small></label>
                <select name="director_name" class="form-control form-control-sm">
                  <option value="" selected disabled>ይምረጡ</option>
                  <?php if (!empty($directors)): ?>
                    <?php foreach ($directors as $row): ?>
                      <option value="<?= htmlspecialchars($row['id']) ?>">
                        <?= htmlspecialchars($row['director_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option value="">No directors available</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የመደቡ መለያ ቁጥር</small></label>
                <input type="text" class="form-control form-control-sm" placeholder="መለያ ቁጥር" name="position_code" required>
              </div>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የስራ መደቡ መጠሪያ</small></label>
                <input type="text" class="form-control form-control-sm" placeholder="የስራ መደቡ መጠሪያ" name="position_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የመደቡ ደመወዝ</small></label>
                <input type="number" class="form-control form-control-sm" placeholder="ደመወዝ" name="salary" required>
              </div>
            </div>
          </div>

          <!-- Row 3 -->
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የስራ ደረጃ</small></label>
                <select name="sera_dereja" class="form-control form-control-sm" required>
                  <option value="" disabled selected>← ይምረጡ →</option>
                  <option>ሹመት</option>
                  <?php foreach (['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX','Career'] as $r): ?>
                    <option><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የደረጃ እርከን</small></label>
                <select name="sera_rken" class="form-control form-control-sm" required>
                  <option value="" disabled selected>← ይምረጡ →</option>
                  <option>ሹመት</option>
                  <?php for ($i = 1; $i <= 9; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="mb-1"><small class="font-weight-bold">የተያዥ ሁኔታ</small></label>
                <select name="yeteyash_huneta" class="form-control form-control-sm" required>
                  <option value="" disabled selected>← ይምረጡ →</option>
                  <option value="ተያዥ የሚያስፈልገዉ">ተያዥ የሚያስፈልገዉ</option>
                  <option value="ተያዥ የማያስፈልገዉ">ተያዥ የማያስፈልገዉ</option>
                </select>
              </div>
            </div>
          </div>

       <!-- Row 3.5 — Allow Multiple + Vacancy Count -->
<div class="row mt-1" style="display: none;">
    <div class="col-md-6">
        <div class="toggle-card p-2" id="add_card_multiple">
            <label class="card-label mb-1">
                <small class="font-weight-bold">የሰራተኛ ብዛት ፍቃድ</small>
            </label>
            <div class="toggle-row">
                <label class="switch">
                    <input type="checkbox" id="add_multiple_check">
                    <!-- ← removed onchange -->
                    <span class="slider"></span>
                </label>
                <span class="toggle-label-text ml-2">
                    <small>ብዙ ሰራተኞች ይፈቀዳል</small>
                </span>
                <span class="badge-status ml-2" id="add_multiple_badge">
                    አይፈቀድም
                </span>
            </div>
            <input type="hidden"
                   name="allow_multiple"
                   id="add_allow_multiple"
                   value="0">
            <small class="text-muted mt-1 d-block">
                <i class="fas fa-info-circle"></i>
                ካልተፈቀደ አንድ ሰራተኛ ብቻ ሊያዝ ይችላል።
            </small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-2">
            <label class="mb-1">
                <small class="font-weight-bold">
                    የቦታ ብዛት
                    <span class="text-muted">(ባዶ = ያልተወሰነ)</span>
                </small>
            </label>
            <input type="number"
                   class="form-control form-control-sm"
                   placeholder="የቦታ ብዛት (ለምሳሌ 5)"
                   name="vacancy_count"
                   id="add_vacancy_count"
                   min="1"
                   disabled>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                ብዙ ሰራተኞች ከተፈቀደ ብቻ ያስገቡ።
            </small>
        </div>
    </div>
</div>

<!-- Row 4 — Nesa + Cloth -->
<div class="row mt-1">
    <div class="col-md-6">
        <div class="toggle-card p-2" id="add_card_nesa">
            <label class="card-label mb-1">
                <small class="font-weight-bold">ነፃ ህክምና</small>
            </label>
            <div class="toggle-row">
                <label class="switch">
                    <input type="checkbox" id="add_nesa_check">
                    <!-- ← removed onchange -->
                    <span class="slider"></span>
                </label>
                <span class="toggle-label-text ml-2">
                    <small>ተጠቃሚ ነዉ</small>
                </span>
                <span class="badge-status ml-2" id="add_nesa_badge">
                    አይደለም
                </span>
            </div>
            <input type="hidden"
                   name="nesa_hkmna"
                   id="add_nesa_hkmna"
                   value="no">
        </div>
    </div>

    <div class="col-md-6">
        <div class="toggle-card p-2" id="add_card_cloth">
            <label class="card-label mb-1">
                <small class="font-weight-bold">ደንብ ልብስ ተጠቃሚ</small>
            </label>
            <div class="toggle-row">
                <label class="switch">
                    <input type="checkbox" id="add_cloth_check">
                    <!-- ← removed onchange -->
                    <span class="slider"></span>
                </label>
                <span class="toggle-label-text ml-2">
                    <small>ተጠቃሚ ነዉ</small>
                </span>
                <span class="badge-status ml-2" id="add_cloth_badge">
                    አይደለም
                </span>
            </div>
            <!-- ← added hidden input for cloth enabled state -->
            <input type="hidden"
                   name="cloth_enabled"
                   id="add_cloth_value"
                   value="no">
            <select name="cloth_duration"
                    id="add_cloth_duration"
                    class="form-control form-control-sm mt-1"
                    disabled>
                <option value="" disabled selected>&larr; ይምረጡ &rarr;</option>
                <option value="በአመት አንድ">በአመት አንድ</option>
                <option value="በአመት ሁለት">በአመት ሁለት</option>
                <option value="በአመት አንድ ጥንድ">በአመት አንድ ጥንድ</option>
                <option value="በአመት ሁለት ጥንድ">በአመት ሁለት ጥንድ</option>
                <option value="በአመት ሶስት ጥንድ">በአመት ሶስት ጥንድ</option>
                <option value="እንደ ስራ መሳሪያ የሚሰጥ በአመት ሁለት">እንደ ስራ መሳሪያ የሚሰጥ በአመት ሁለት</option>
                <option value="እንደ ስራ መሳሪያ የሚሰጥ">እንደ ስራ መሳሪያ የሚሰጥ</option>
                <option value="በሦስት ዓመት አንድ">በሦስት ዓመት አንድ</option>
                <option value="እንደ ስራ መሳሪያ የሚሰጥ በአመት አንድ">እንደ ስራ መሳሪያ የሚሰጥ በአመት አንድ</option>
            </select>
        </div>
    </div>
</div>

          <!-- Row 5: Description -->
          <div class="row mt-2">
            <div class="col-md-12">
              <div class="form-group mb-0">
                <label class="mb-1"><small class="font-weight-bold">Remarks</small></label>
                <textarea rows="2" class="form-control form-control-sm" name="description" id="textBox" placeholder="Remarks"></textarea>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer py-2 justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> ዝጋ
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i> መዝግብ
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

