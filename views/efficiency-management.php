<style>
    .bsc-page { font-family: 'Segoe UI', sans-serif; }

    .bsc-stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
        transition: transform .2s;
    }
    .bsc-stat-card:hover { transform: translateY(-2px); }
    .bsc-stat-card .stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin: 0 auto 10px;
    }
    .bsc-stat-card h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; color: #6c757d; margin-bottom: 4px; }
    .bsc-stat-card h3 { font-size: 2rem; font-weight: 700; margin: 0; }

    .stat-total  .stat-icon { background: #e8f4fd; color: #1a7abf; }
    .stat-done   .stat-icon { background: #e6f9f0; color: #1a9e5c; }
    .stat-pending .stat-icon { background: #fff3e0; color: #e67e22; }
    .stat-total  h3 { color: #1a7abf; }
    .stat-done   h3 { color: #1a9e5c; }
    .stat-pending h3 { color: #e67e22; }

    .season-badge {
        background: linear-gradient(135deg, #1a9e5c, #27ae60);
        color: #fff; border-radius: 20px;
        padding: 4px 14px; font-size: .78rem; font-weight: 600;
        letter-spacing: .04em;
    }

    .bsc-table thead th {
        background: #f8f9fa; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .06em;
        color: #495057; border-bottom: 2px solid #dee2e6; padding: 12px 16px;
    }
    .bsc-table tbody td { padding: 12px 16px; vertical-align: middle; }
    .bsc-table tbody tr:hover { background: #f8fbff; }

    .emp-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, #1a7abf, #1a9e5c);
        color: #fff; font-size: .8rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: 10px; flex-shrink: 0;
    }
    .emp-name { font-weight: 500; color: #2d3748; }

    .btn-attach {
        background: linear-gradient(135deg, #1a7abf, #2980b9);
        border: none; color: #fff; border-radius: 8px;
        padding: 6px 14px; font-size: .8rem; font-weight: 600;
        cursor: pointer; transition: opacity .2s, transform .1s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-attach:hover { opacity: .9; transform: translateY(-1px); color: #fff; }

    .btn-confirm {
        background: #fff; border: 1.5px solid #1a9e5c;
        color: #1a9e5c; border-radius: 8px;
        padding: 6px 14px; font-size: .8rem; font-weight: 600;
        cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-confirm:hover { background: #1a9e5c; color: #fff; }

    .empty-state { padding: 48px 0; text-align: center; color: #adb5bd; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: .9rem; margin: 0; }

    .page-header { border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; margin-bottom: 20px; }
    .page-header h4 { font-weight: 700; color: #2d3748; font-size: 1.3rem; }

    .season-card-header {
        background: linear-gradient(135deg, #f8fbff, #eef5ff);
        border: 1px solid #dce8f8; border-radius: 10px;
        padding: 14px 20px; margin-bottom: 20px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .season-card-header strong { color: #2d3748; font-size: 1rem; }
</style>

<div class="container-fluid bsc-page">

    <div class="page-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fas fa-file-alt mr-2 text-primary"></i>BSC እቅድ</h4>
    </div>

    <?php if (empty($openSeasons)): ?>

        <div class="alert alert-warning border-0 rounded-lg">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            የእርስዎ ቅርንጫፍ አሁን ክፍት የሆነ የግምገማ ወቅት የለም።
        </div>

    <?php else: ?>

        <?php if (count($openSeasons) > 1): ?>
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-body py-3">
                    <label for="seasonSelect" class="text-uppercase font-weight-bold text-muted" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="fas fa-calendar-alt mr-1"></i> በጀት ዓመት ይምረጡ
                    </label>
                    <select id="seasonSelect" class="form-control mt-1" style="border-radius:8px;">
                        <option value="" disabled selected>-- በጀት ዓመት ይምረጡ --</option>
                        <?php foreach ($openSeasons as $season): ?>
                            <option
                                value="<?= htmlspecialchars($season['season_id']); ?>"
                                <?= ($selectedSeason && $selectedSeason['season_id'] === $season['season_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($season['season_name'] . ' (' . $season['fiscal_year'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selectedSeason): ?>

            <div class="season-card-header">
                <div>
                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;">Active Season</div>
                    <strong><?= htmlspecialchars($selectedSeason['season_name']).' (' . $selectedSeason['fiscal_year'] . ')'; ?></strong>
                </div>
                <span class="season-badge">
                    <i class="fas fa-check-circle mr-1"></i> የሰራተኞች አፈጻጻም መመዝገቢያ
                </span>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-total">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <h6>BSC እቅድ ያያያዙ</h6>
                            <h3><?= $totalEmployees; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-done"
                         id="toggle-with-efficiency"
                         style="cursor:pointer;"
                         title="ዝርዝር ለማየት ጠቅ ያድርጉ">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-paperclip"></i></div>
                            <h6>አፈጻጸም የተሞላላቸው</h6>
                            <h3><?= $withPlanCount; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-pending">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <h6>አፈጻጸም ያልተሞላቸው</h6>
                            <h3><?= $withoutPlanCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Without Efficiency Table ── -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <span class="font-weight-700" style="font-size:.9rem;font-weight:600;color:#2d3748;">
                        <i class="fas fa-list mr-2 text-muted"></i> አፈጻጸም ያልተሞላላቸው ሰራተኞች
                    </span>
                    <span class="badge badge-warning px-3 py-2" style="border-radius:20px;font-size:.75rem;">
                        <?= $withoutPlanCount; ?> ሰራተኞች
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table bsc-table mb-0" id="example1" data-empty-msg="አፈጻጸም ያልተሞላለት ሰራትኛ የለም / የተያያዘ አፈጻጸም እቅድ አልተገኘም።">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>የሰራተኛ ሙሉ ስም</th>
                                    <th style="width:240px;">ድርጊት</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $index => $employee): ?>
                                    <?php
                                        $initials = strtoupper(
                                            mb_substr($employee['first_name'], 0, 1) .
                                            mb_substr($employee['father_name'], 0, 1)
                                        );
                                    ?>
                                    <tr>
                                        <td class="text-muted" style="font-size:.85rem;"><?= $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="emp-avatar"><?= htmlspecialchars($initials); ?></div>
                                                <span class="emp-name">
                                                    <?= htmlspecialchars(trim(
                                                        $employee['first_name'] . ' ' .
                                                        $employee['father_name'] . ' ' .
                                                        $employee['g_father_name']
                                                    )); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <form
                                                method="POST"
                                                enctype="multipart/form-data"
                                                id="eff-upload-form-<?= $employee['uuid']; ?>"
                                                action="<?= $_ENV['BASE_URL']; ?>/efficiency-file-upload/<?= urlencode($employee['uuid']); ?>/<?= urlencode($selectedSeason['season_id']); ?>"
                                                data-employee-uuid="<?= htmlspecialchars($employee['uuid']); ?>"
                                                data-employee-name="<?= htmlspecialchars(trim($employee['first_name'] . ' ' . $employee['father_name'])); ?>">

                                                <input type="hidden" name="bsc_file_id" value="<?= htmlspecialchars($employee['file_id']); ?>">

                                                <div class="d-flex align-items-center" style="gap:6px;">
                                                    <input
                                                        type="number"
                                                        name="efficiency_mark"
                                                        id="eff_mark_<?= $employee['uuid']; ?>"
                                                        min="0" max="100" step="any"
                                                        placeholder="ነጥብ"
                                                        class="form-control form-control-sm eff-mark-input"
                                                        style="width:72px;border-radius:8px;font-size:.78rem;"
                                                        data-uuid="<?= htmlspecialchars($employee['uuid']); ?>">

                                                    <input
                                                        type="file"
                                                        name="efficiency_file"
                                                        id="eff_file_<?= $employee['uuid']; ?>"
                                                        accept=".pdf,.doc,.docx,.xls,.xlsx"
                                                        class="form-control form-control-sm eff-file-input"
                                                        style="border-radius:8px;font-size:.78rem;width:150px;"
                                                        data-form-id="eff-upload-form-<?= $employee['uuid']; ?>"
                                                        data-uuid="<?= htmlspecialchars($employee['uuid']); ?>">

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm eff-submit-btn"
                                                        id="eff-btn-<?= $employee['uuid']; ?>"
                                                        data-form-id="eff-upload-form-<?= $employee['uuid']; ?>"
                                                        disabled
                                                        title="አፈጻጸም ነጥብ እና ፋይል ያስገቡ"
                                                        style="background:#7b2fa8;color:#fff;border-radius:8px;font-size:.78rem;padding:5px 10px;white-space:nowrap;">
                                                        <i class="fas fa-save mr-1"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── With Efficiency Table (toggled by stat card) ── -->
            <div id="with-efficiency-section" style="display:none;" class="mt-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                        <span style="font-size:.9rem;font-weight:600;color:#2d3748;">
                            <i class="fas fa-check-circle mr-2 text-success"></i> አፈጻጸም የተሞላላቸው ሰራተኞች
                        </span>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span class="badge badge-success px-3 py-2" style="border-radius:20px;font-size:.75rem;">
                                <?= $withPlanCount; ?> ሰራተኞች
                            </span>
                            <button type="button" id="close-with-efficiency"
                                class="btn btn-sm btn-outline-secondary"
                                style="border-radius:8px;font-size:.78rem;">
                                <i class="fas fa-times"></i> ዝጋ
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table bsc-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">#</th>
                                        <th>የሰራተኛ ሙሉ ስም</th>
                                        <th style="width:110px;">የአፈጻጸም ነጥብ</th>
                                        <th style="width:100px;">ፋይል</th>
                                        <th style="width:120px;">ድርጊት</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($withEfficiency)): ?>
                                        <?php foreach ($withEfficiency as $index => $emp): ?>
                                            <?php
                                                $initials = strtoupper(
                                                    mb_substr($emp['first_name'], 0, 1) .
                                                    mb_substr($emp['father_name'], 0, 1)
                                                );
                                                $fullName = htmlspecialchars(trim(
                                                    ($emp['first_name']    ?? '') . ' ' .
                                                    ($emp['father_name']   ?? '') . ' ' .
                                                    ($emp['g_father_name'] ?? '')
                                                ));
                                            ?>
                                            <tr>
                                                <td class="text-muted" style="font-size:.85rem;"><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="emp-avatar"><?= htmlspecialchars($initials) ?></div>
                                                        <span class="emp-name"><?= $fullName ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success" style="font-size:.82rem;padding:5px 10px;border-radius:8px;">
                                                        <?= htmlspecialchars($emp['efficiency_mark']) ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($emp['file_path'])): ?>
                                                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($emp['file_path']) ?>&type=document"
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-primary"
                                                           style="border-radius:8px;font-size:.78rem;">
                                                            <i class="fas fa-file-pdf"></i> ክፈት
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-edit-efficiency"
                                                        style="background:#7b2fa8;color:#fff;border-radius:8px;font-size:.78rem;padding:5px 12px;"
                                                        data-id="<?= htmlspecialchars($emp['efficiency_file_id']) ?>"
                                                        data-bsc-file-id="<?= htmlspecialchars($emp['bsc_file_id']) ?>"
                                                        data-mark="<?= htmlspecialchars($emp['efficiency_mark']) ?>"
                                                        data-file-path="<?= htmlspecialchars($emp['file_path'] ?? '') ?>"
                                                        data-name="<?= $fullName ?>"
                                                        data-toggle="modal"
                                                        data-target="#editEfficiencyModal"
                                                    >
                                                        <i class="fas fa-edit"></i> አስተካክል
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">ምንም አልተገኘም።</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Edit Efficiency Modal ── -->
            <div class="modal fade" id="editEfficiencyModal" tabindex="-1" role="dialog" aria-labelledby="editEfficiencyModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="border-radius:12px;border:none;">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title font-weight-700" id="editEfficiencyModalLabel" style="color:#2d3748;">
                                <i class="fas fa-edit mr-2" style="color:#7b2fa8;"></i> አፈጻጸም አስተካክል
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            id="edit-efficiency-form"
                            action="<?= $_ENV['BASE_URL']; ?>/efficiency-file-update">

                            <div class="modal-body pt-2">

                                <!-- Employee name display -->
                                <div class="mb-3 p-3 rounded" style="background:#f8f9fa;">
                                    <small class="text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">ሰራተኛ</small>
                                    <div class="font-weight-600 mt-1" id="edit-efficiency-name" style="color:#2d3748;font-size:.95rem;"></div>
                                </div>

                                <!-- Hidden fields -->
                                <input type="hidden" name="efficiency_file_id" id="edit-efficiency-id">
                                <input type="hidden" name="bsc_file_id"        id="edit-efficiency-bsc-id">

                                <!-- Mark input -->
                                <div class="form-group">
                                    <label style="font-size:.82rem;font-weight:600;color:#495057;">
                                        የአፈጻጸም ነጥብ <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="efficiency_mark"
                                        id="edit-efficiency-mark"
                                        class="form-control"
                                        min="0" max="100" step="any"
                                        placeholder="0 – 100"
                                        style="border-radius:8px;"
                                        required>
                                </div>

                                <!-- File input (optional replacement) -->
                                <div class="form-group mb-0">
                                    <label style="font-size:.82rem;font-weight:600;color:#495057;">
                                        አዲስ ፋይል <small class="text-muted font-weight-normal">(አስፈላጊ ካልሆነ ይተዉት)</small>
                                    </label>
                                    <input
                                        type="file"
                                        name="efficiency_file"
                                        id="edit-efficiency-file"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx"
                                        class="form-control"
                                        style="border-radius:8px;">
                                </div>

                            </div>

                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                                    style="border-radius:8px;"><i class="fas fa-times mr-1"></i> </button>
                                <button type="button" class="btn btn-sm" id="edit-efficiency-submit"
                                    style="background:#7b2fa8;color:#fff;border-radius:8px;">
                                    <i class="fas fa-save mr-1"></i> አስተካክል
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-info border-0 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>
                አፈጻጸም ለማያያዝ መጀመሪያ በጀት ዓመት ይምረጡ።
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script nonce="<?= htmlspecialchars($GLOBALS['nonce']); ?>">
document.addEventListener('DOMContentLoaded', function () {

    // ── Season select redirect
    const seasonSelect = document.getElementById('seasonSelect');
    if (seasonSelect) {
        seasonSelect.addEventListener('change', function () {
            if (!this.value) return;
            window.location.href = '<?= rtrim($_ENV['BASE_URL'], '/'); ?>/efficiency-management/' + this.value;
        });
    }

    // ── Enable upload button when file chosen
    document.querySelectorAll('.eff-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const uuid = this.dataset.uuid;
            const btn  = document.getElementById('eff-btn-' + uuid);
            btn.disabled = !(this.files && this.files.length > 0);
        });
    });

    // ── Upload submit with SweetAlert confirm
    document.querySelectorAll('.eff-submit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const formId    = this.dataset.formId;
            const form      = document.getElementById(formId);
            const uuid      = form.dataset.employeeUuid;
            const name      = form.dataset.employeeName;
            const input     = document.getElementById('eff_file_'  + uuid);
            const markInput = document.getElementById('eff_mark_'  + uuid);
            const fileName  = input.files[0] ? input.files[0].name : '';
            const mark      = markInput.value.trim();

            if (!mark || Number(mark) < 0 || Number(mark) > 100) {
                Swal.fire({
                    title: 'ነጥብ ያስገቡ',
                    text:  'እባክዎ ትክክለኛ ነጥብ (0–100) ያስገቡ።',
                    icon:  'warning',
                    confirmButtonColor: '#7b2fa8'
                });
                return;
            }

            if (!fileName) {
                Swal.fire({
                    title: 'ፋይል ይምረጡ',
                    text:  'እባክዎ የብቃት ምዘና ፋይል ይምረጡ።',
                    icon:  'warning',
                    confirmButtonColor: '#7b2fa8'
                });
                return;
            }

            Swal.fire({
                title: 'ፋይሉን ያያይዙ?',
                html:  '<b>' + name + '</b><br>ነጥብ: <b>' + mark + '</b><br><small class="text-muted">' + fileName + '</small>',
                icon:  'question',
                showCancelButton:   true,
                confirmButtonText:  'አዎ፣ አያይዝ',
                cancelButtonText:   'X',
                confirmButtonColor: '#7b2fa8',
                cancelButtonColor:  '#6c757d'
            }).then(function (result) {
                if (result.isConfirmed) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled  = true;
                    form.submit();
                } else {
                    input.value     = '';
                    markInput.value = '';
                    btn.disabled    = true;
                }
            });
        });
    });

    // ── Toggle with-efficiency table on stat card click
    const toggleCard = document.getElementById('toggle-with-efficiency');
    const effSection = document.getElementById('with-efficiency-section');

    if (toggleCard && effSection) {
        toggleCard.addEventListener('click', function () {
            const isHidden = effSection.style.display === 'none';
            effSection.style.display = isHidden ? 'block' : 'none';
            if (isHidden) effSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    document.getElementById('close-with-efficiency')?.addEventListener('click', function (e) {
        e.stopPropagation();
        if (effSection) effSection.style.display = 'none';
    });

    // ── Pre-fill edit efficiency modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-efficiency');
        if (!btn) return;
        document.getElementById('edit-efficiency-id').value          = btn.dataset.id;
        document.getElementById('edit-efficiency-bsc-id').value      = btn.dataset.bscFileId;
        document.getElementById('edit-efficiency-mark').value        = btn.dataset.mark;
        document.getElementById('edit-efficiency-name').textContent  = btn.dataset.name;
        document.getElementById('edit-efficiency-file').value        = '';
    });

    // ── Edit efficiency submit with SweetAlert confirm
    document.getElementById('edit-efficiency-submit')?.addEventListener('click', function () {
        const mark     = document.getElementById('edit-efficiency-mark').value.trim();
        const name     = document.getElementById('edit-efficiency-name').textContent;
        const fileInput = document.getElementById('edit-efficiency-file');
        const fileName  = fileInput.files[0] ? fileInput.files[0].name : null;

        if (!mark || Number(mark) < 0 || Number(mark) > 100) {
            Swal.fire({
                title: 'ነጥብ ያስገቡ',
                text:  'እባክዎ ትክክለኛ ነጥብ (0–100) ያስገቡ።',
                icon:  'warning',
                confirmButtonColor: '#7b2fa8'
            });
            return;
        }

        Swal.fire({
            title: 'ለውጡን ያስቀምጡ?',
            html:  '<b>' + name + '</b><br>አዲስ ነጥብ: <b>' + mark + '</b>' +
                   (fileName ? '<br><small class="text-muted">' + fileName + '</small>' : ''),
            icon:  'question',
            showCancelButton:   true,
            confirmButtonText:  'አዎ፣ አስቀምጥ',
            cancelButtonText:   'X',
            confirmButtonColor: '#7b2fa8',
            cancelButtonColor:  '#6c757d'
        }).then(function (result) {
            if (result.isConfirmed) {
                document.getElementById('edit-efficiency-form').submit();
            }
        });
    });

});
</script>