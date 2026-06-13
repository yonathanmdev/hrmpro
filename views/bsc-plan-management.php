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
                        <i class="fas fa-calendar-alt mr-1"></i> Season ይምረጡ
                    </label>
                    <select id="seasonSelect" class="form-control mt-1" style="border-radius:8px;">
                        <option value="">-- Season ይምረጡ --</option>
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
                    <div class="card bsc-stat-card stat-total">
                        <div class="card-body text-center py-4">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <h6>ጠቅላላ ሰራተኛ</h6>
                            <h3><?= $totalEmployees; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bsc-stat-card stat-done">
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

            <!-- Table -->
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
                        <table class="table bsc-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>የሰራተኛ ሙሉ ስም</th>
                                    <th style="width:240px;">ድርጊት</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($employees)): ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty-state">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <p>ሁሉም ሰራተኞች BSC እቅድ አያይዘዋል።</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-info border-0 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>
                BSC እቅድ ለማያያዝ መጀመሪያ Season ይምረጡ።
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script nonce="<?= htmlspecialchars($GLOBALS['nonce']); ?>">
document.addEventListener('DOMContentLoaded', function () {

    const seasonSelect = document.getElementById('seasonSelect');
    if (seasonSelect) {
        seasonSelect.addEventListener('change', function () {
            if (!this.value) return;
            window.location.href = '<?= rtrim($_ENV['BASE_URL'], '/'); ?>/bsc-plan-management/' + this.value;
        });
    }

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

});
</script>