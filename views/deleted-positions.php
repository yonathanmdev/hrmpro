<?php
use App\Helpers\EthiopianDateHelper;
$is_position_deleted_page = true;
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">

      <div class="container-fluid py-4">

        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <h5 class="mb-0">የተሰረዙ የስራ መደቦች</h5>
            <small style="color: var(--bs-danger);">
              <i class="fas fa-exclamation-triangle me-1"></i>
              ምንም ዓይነት ስራ ያልተሰራባቸው መደቦች ከ90 ቀናት በኋላ ይሰረዛሉ
            </small>
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover small">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>የስራ ክፍል</th>
                  <th>የስራ መደቡ መጠሪያ</th>
                  <th>መለያ ቁጥር</th>
                  <th>ደረጃ / እርከን</th>
                  <th>ደመወዝ</th>
                  <th>የሰረዘው</th>
                  <th>የተሰረዘበት ቀን</th>
                  <th>የስረዛ አይነት</th>
                  <th>ሰራተኛ</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($deletedPositions)): ?>
                  <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                      <i class="fas fa-check-circle me-2 text-success"></i>
                      የተሰረዘ የስራ መደብ የለም
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($deletedPositions as $index => $row): ?>
                    <?php
                      $isDirectorCascade = (bool) $row['is_director_cascade'];
                      $affectedEmployees = (int)  $row['affected_employees'];
                      $canPurge          = (bool) $row['can_purge'];
                    ?>

                    <tr id="row-<?= htmlspecialchars($row['id']) ?>">

                      <!-- # -->
                      <td><?= $index + 1 ?></td>

                      <!-- የስራ ክፍል -->
                      <td>
                        <?= htmlspecialchars($row['director_name'] ?? '—') ?>
                        <?php if ($isDirectorCascade): ?>
                          <span class="badge bg-warning ms-1"
                                title="ዳይሬክተር ሲሰረዝ አብሮ የተሰረዘ">
                            <i class="fas fa-link"></i> Cascade
                          </span>
                        <?php endif; ?>
                      </td>

                      <!-- የስራ መደቡ መጠሪያ -->
                      <td><?= htmlspecialchars($row['job_name']) ?></td>

                      <!-- መለያ ቁጥር -->
                      <td>
                        <span class="badge bg-secondary">
                          <?= htmlspecialchars($row['job_identifier_no']) ?>
                        </span>
                      </td>

                      <!-- ደረጃ / እርከን -->
                      <td><?= htmlspecialchars($row['dereja']) ?> / <?= htmlspecialchars($row['scale']) ?></td>

                      <!-- ደመወዝ -->
                      <td class="text-danger fw-bold">
                        <?= number_format($row['salary'], 2) ?> ብር
                      </td>

                      <!-- የሰረዘው -->
                      <td><?= htmlspecialchars($row['deleted_by_name'] ?? 'N/A') ?></td>

                      <!-- የተሰረዘበት ቀን -->
                      <td><?= date('Y-m-d h:i A', strtotime($row['deleted_at'])) ?></td>

                      <!-- የምክንያት አይነት -->
                      <td>
                        <?php if ($isDirectorCascade): ?>
                          <span class="badge bg-warning">
                            <i class="fas fa-sitemap me-1"></i> ዳይሬክተር Cascade
                          </span>
                        <?php else: ?>
                          <span class="badge bg-danger">
                            <i class="fas fa-user me-1"></i> በቀጥታ የተሰረዘ
                          </span>
                        <?php endif; ?>
                      </td>

                      <!-- ሰራተኞች -->
                      <td>
                        <?php if ($affectedEmployees > 0): ?>
                          <span class="badge bg-danger">
                            <?= $affectedEmployees ?> ሰራተኛ
                          </span>
                        <?php else: ?>
                          <span class="badge bg-success">ምንም የለም</span>
                        <?php endif; ?>
                      </td>

                     <!-- Actions -->
          <td class="text-center">
            <div class="btn-group shadow-sm" role="group">
              <?php if (!$isDirectorCascade): ?>
                <button class="btn btn-sm btn-outline-success restore-position"
                        data-id="<?= htmlspecialchars($row['id']) ?>"
                        data-name="<?= htmlspecialchars($row['job_name']) ?>"
                        data-affected="<?= $affectedEmployees ?>"
                        title="ወደ ነበረበት መልስ">
                  <i class="fas fa-undo"></i>
                </button>
              <?php else: ?>
                <button class="btn btn-sm btn-light text-muted border" disabled 
                        title="ዳይሬክተሩን መልስ ካደረጉ በኋላ ይህ አብሮ ይመለሳል">
                  <i class="fas fa-lock"></i>
                </button>
              <?php endif; ?>

              <?php if (!$isDirectorCascade): ?>
                <button class="btn btn-sm btn-outline-danger purge-position"
                        data-id="<?= htmlspecialchars($row['id']) ?>"
                        data-name="<?= htmlspecialchars($row['job_name']) ?>"
                        data-affected="<?= $affectedEmployees ?>"
                        data-toggle="tooltip" 
                        <?= !$canPurge ? 'disabled title="ሰራተኞቹን አስቀድሞ ያስተካክሉ"' : 'title="በቋሚነት አጥፋ"' ?>>
                  <i class="fas fa-trash-alt"></i>
                </button>
              <?php endif; ?>
            </div>
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