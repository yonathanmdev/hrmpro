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
                    <strong><?= htmlspecialchars($selectedSeason['season_name']); ?></strong>
                </div>
                <span class="season-badge">
                    <i class="fas fa-check-circle mr-1"></i> Evaluation Window Open
                </span>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
    <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-active" style="text-decoration:none;">
        <div class="card bsc-stat-card stat-total" style="cursor:pointer;">
            <div class="card-body text-center py-4">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <h6>ጠቅላላ ሰራተኛ</h6>
                <h3><?= $totalEmployees; ?></h3>
            </div>
        </div>
    </a>
</div>
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-done"
                         id="toggle-with-plan"
                         style="cursor:pointer;"
                         title="ዝርዝር ለማየት ጠቅ ያድርጉ">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-paperclip"></i></div>
                            <h6>BSC እቅድ ያያያዙ</h6>
                            <h3><?= $withPlanCount; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-pending">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <h6>BSC እቅድ ያልያያዙ</h6>
                            <h3><?= $withoutPlanCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Without Plan Table (always visible) ── -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <span class="font-weight-700" style="font-size:.9rem;font-weight:600;color:#2d3748;">
                        <i class="fas fa-list mr-2 text-muted"></i>BSC እቅድ ያልያያዙ ሰራተኞች
                    </span>
                    <span class="badge badge-warning px-3 py-2" style="border-radius:20px;font-size:.75rem;">
                        <?= $withoutPlanCount; ?> ሰራተኞች
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table bsc-table mb-0" id="example1" data-empty-msg="BSC እቅድ ያልተያያዘለት ሰራተኛ አልተገኘም።">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>የሰራተኛ ሙሉ ስም</th>
                                    <th style="width:240px;">Action</th>
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
                                            <!--
                                            <form
                                                method="POST"
                                                enctype="multipart/form-data"
                                                id="bsc-upload-form-<?= $employee['uuid']; ?>"
                                                action="<?= $_ENV['BASE_URL']; ?>/bsc-plan-upload/<?= urlencode($employee['uuid']); ?>/<?= urlencode($selectedSeason['season_id']); ?>"
                                                style="display:inline;">
                                                <input
                                                    type="file"
                                                    name="bsc_plan"
                                                    id="bsc_plan_<?= $employee['uuid']; ?>"
                                                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                                                    style="display:none;">
                                                <label
                                                    for="bsc_plan_<?= $employee['uuid']; ?>"
                                                    class="btn-attach">
                                                    <i class="fas fa-upload"></i> BSC አያይዝ
                                                </label>
                                            </form>
                                -->
                                            <button
                                                type="button"
                                                class="btn-confirm btn-mark-confirmed"
                                                data-uuid="<?= htmlspecialchars($employee['uuid']); ?>"
                                                data-season="<?= htmlspecialchars($selectedSeason['season_id']); ?>">
                                                <i class="fas fa-check"></i> ተያይዟል
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── With Plan Table (toggled by stat card) ── -->
            <div id="with-plan-section" style="display:none;" class="mt-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                        <span style="font-size:.9rem;font-weight:600;color:#2d3748;">
                            <i class="fas fa-check-circle mr-2 text-success"></i> BSC እቅድ ያያያዙ ሰራተኞች
                        </span>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span class="badge badge-success px-3 py-2" style="border-radius:20px;font-size:.75rem;">
                                <?= $withPlanCount; ?> ሰራተኞች
                            </span>
                            <button type="button" id="close-with-plan"
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
                                        <th style="width:100px;">ፋይል</th>
                                        <th style="width:130px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($employeesWithPlan)): ?>
                                        <?php foreach ($employeesWithPlan as $index => $emp): ?>
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
                                                    <?php if (!empty($emp['file_path'])): ?>
                                                        <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($emp['file_path']) ?>&type=document"
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-primary"
                                                           style="border-radius:8px;font-size:.78rem;">
                                                            <i class="fas fa-file-pdf"></i> ክፈት
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary" style="border-radius:8px;font-size:.75rem;">ተያይዟል</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    
<button
    type="button"
    class="btn btn-sm btn-delete-bsc-plan"
    style="background:#c0392b;color:#fff;border-radius:8px;font-size:.78rem;padding:5px 12px;"
    data-id="<?= htmlspecialchars($emp['file_id']) ?>"
    data-uuid="<?= htmlspecialchars($emp['uuid']) ?>"
    data-name="<?= $fullName ?>"
>
    <i class="fas fa-trash-alt"></i> ሰርዝ
</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">ምንም አልተገኘም።</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


        <?php else: ?>
            <div class="alert alert-info border-0 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>
                BSC እቅድ ለማያያዝ መጀመሪያ በጀት ዓመት ይምረጡ።
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
            window.location.href = '<?= rtrim($_ENV['BASE_URL'], '/'); ?>/bsc-plan-management/' + this.value;
        });
    }

    // ── Mark confirmed
    document.querySelectorAll('.btn-mark-confirmed').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const uuid   = this.dataset.uuid;
            const season = this.dataset.season;

            Swal.fire({
                title: 'እርግጠኛ ነዎት?',
                text: 'የዚህን ሰራተኛ የBSC እቅድ እንደተያያዘ ለማረጋገጥ ይፈልጋሉ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'አዎ፣ አረጋግጥ',
                cancelButtonText: 'ይቅር',
                confirmButtonColor: '#1a7abf',
                cancelButtonColor: '#6c757d'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch('<?= $_ENV['BASE_URL']; ?>/bsc-plan-mark-confirmed', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_uuid: uuid, season_id: season })
                })
                .then(function (res) { return res.json(); })
                .then(function () { location.reload(); })
                .catch(function () { location.reload(); });
            });
        });
    });
/*
    // ── File upload confirm
    document.querySelectorAll('input[id^="bsc_plan_"]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (this.files.length === 0) return;

            const form     = this.closest('form');
            const fileName = this.files[0].name;

            Swal.fire({
                title: 'ፋይሉን ያያይዙ?',
                text: fileName + ' የሚለውን ፋይል ለዚህ ሰራተኛ ማያያዝ ይፈልጋሉ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'አዎ፣ አያይዝ',
                cancelButtonText: 'ይቅር',
                confirmButtonColor: '#1a7abf',
                cancelButtonColor: '#6c757d'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    input.value = '';
                }
            });
        });
    });
*/
    // ── Toggle with-plan table on stat card click
    const toggleCard = document.getElementById('toggle-with-plan');
    const planSection = document.getElementById('with-plan-section');

    if (toggleCard && planSection) {
        toggleCard.addEventListener('click', function () {
            const isHidden = planSection.style.display === 'none';
            planSection.style.display = isHidden ? 'block' : 'none';
            if (isHidden) planSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    document.getElementById('close-with-plan')?.addEventListener('click', function (e) {
        e.stopPropagation();
        if (planSection) planSection.style.display = 'none';
    });

    // ── Pre-fill edit BSC plan modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-bsc-plan');
        if (!btn) return;
        document.getElementById('edit-bsc-plan-id').value        = btn.dataset.id;
        document.getElementById('edit-bsc-plan-uuid').value      = btn.dataset.uuid;
        document.getElementById('edit-bsc-plan-name').textContent = btn.dataset.name;
        document.getElementById('edit-bsc-plan-file').value      = '';
    });

   document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-bsc-plan');
    if (!btn) return;

    const name = btn.dataset.name;

    confirmDelete({
        endpoint:    'bsc-plan-remove',
        id:          btn.dataset.id,
        uuid:        btn.dataset.uuid,
        name:        btn.dataset.name,
        type:        'bsc_plan',
        task:        'delete',
        title:       'እቅድ ማጥፋት?',
        warning:     `<strong>${name}</strong> - እርግጠኛ ነዎት? እቅዱ በስህተት ነው የተያያዘው?`,
        confirmText: '<i class="fas fa-trash-alt"></i> አዎ፣ ሰርዝ!',
        successText: `${name} - እቅዱ ተሰርዟል።`,
        requireReason:   true,
        requirePassword: true,
        onSuccess: () => btn.closest('tr')?.remove()
    });
});
});
</script>