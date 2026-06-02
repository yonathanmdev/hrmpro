<?php
/**
 * View: evaluation-settings.php
 * BSC & Efficiency Evaluation Settings
 *
 * Variables injected by EvaluationSettingsController::index():
 *   $title          string
 *   $seasons        array   — all evaluation seasons
 *   $selectedSeason string|null — season UUID currently being configured
 *   $branches       array   — branch rows with window config for $selectedSeason
 */

$baseUrl = rtrim($_ENV['BASE_URL'], '/');
?>

<div class="container-fluid">

    <!-- ── Page Header ───────────────────────────────────────────────── -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div>
                        <h3 class="mb-0">BSC &amp; Efficiency Evaluation Settings</h3>
                        <small class="text-muted">
                            Manage evaluation seasons and branch-specific evaluation windows.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Season Area ───────────────────────────────────────────────── -->
    <div class="row g-3">

        <!-- Create Season Card -->
        <div class="col-md-4">
            <div class="card card-primary shadow-sm h-100">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Create Evaluation Season
                    </h5>
                </div>

                <form method="POST" action="<?= $baseUrl ?>/save-season" id="createSeasonForm">

                    <div class="card-body">

                        <!-- Season Name -->
                        <div class="mb-3">
                            <label for="season_name" class="form-label fw-semibold">
                                Season Name <span class="text-danger">*</span>
                            </label>
                           <select
    id="season_name"
    name="season_name"
    class="form-select"
    required>

    <option value="">
        -- Season ይምረጡ --
    </option>

    <option value="የመጀመሪያው በጀት ዓመት">
        የመጀመሪያው በጀት ዓመት
    </option>

    <option value="ሁለተኛው በጀት ዓመት">
        ሁለተኛው በጀት ዓመት
    </option>

</select>
  </div>

                        <!-- Default Period -->
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="default_start" class="form-label fw-semibold">
                                    Default Start <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="default_start"
                                    name="default_start"
                                    class="form-control"
                                    placeholder="MM-DD (e.g. 01-01)"
                                    pattern="\d{2}-\d{2}"
                                    maxlength="5"
                                    required>
                            </div>
                            <div class="col-6">
                                <label for="default_end" class="form-label fw-semibold">
                                    Default End <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="default_end"
                                    name="default_end"
                                    class="form-control"
                                    placeholder="MM-DD (e.g. 06-30)"
                                    pattern="\d{2}-\d{2}"
                                    maxlength="5"
                                    required>
                            </div>
                            <div class="col-12">
                                <div class="form-text">Format: MM-DD (month-day, e.g. 01-01)</div>
                            </div>
                        </div>

                    </div><!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Save Season
                        </button>
                    </div>

                </form>

            </div>
        </div><!-- /col Create Season -->

        <!-- Existing Seasons Card -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>Existing Seasons
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Season Name</th>
                                    <th width="80">Label</th>
                                    <th width="180">Default Period</th>
                                    <th width="130" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if (empty($seasons)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No seasons created yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($seasons as $season): ?>
                                    <tr class="<?= ($selectedSeason === $season['id']) ? 'table-primary' : '' ?>">

                                        <td><?= htmlspecialchars($season['season_name']) ?></td>

                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($season['season_label']) ?>
                                            </span>
                                        </td>

                                        <td class="font-monospace">
                                            <?= htmlspecialchars($season['default_start']) ?>
                                            &rarr;
                                            <?= htmlspecialchars($season['default_end']) ?>
                                        </td>

                                        <td class="text-center">
                                            <a
    href="<?= rtrim($baseUrl, '/') ?>/evaluation-settings/<?= urlencode($season['id']) ?>"
    class="btn btn-sm btn-primary"
    title="Configure branch windows for this season">
    <i class="bi bi-sliders me-1"></i>Configure
</a>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div><!-- /.card-body -->

            </div>
        </div><!-- /col Existing Seasons -->

    </div><!-- /.row Season Area -->


    <!-- ── Branch Configuration (only when a season is selected) ─────── -->
    <?php if (!empty($selectedSeason)): ?>

        <?php
        // Find selected season's name for display
        $selectedSeasonName = '';
        foreach ($seasons as $s) {
            if ($s['id'] === $selectedSeason) {
                $selectedSeasonName = $s['season_name'];
                break;
            }
        }
        ?>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-info shadow-sm">

                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0">
                            <i class="bi bi-building me-2"></i>
                            Branch Evaluation Windows
                            <?php if ($selectedSeasonName): ?>
                                <span class="text-muted fw-normal fs-6 ms-2">
                                    — <?= htmlspecialchars($selectedSeasonName) ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <label for="branchPageSize" class="form-label mb-0 text-muted small">
                                Rows per page:
                            </label>
                            <select id="branchPageSize" class="form-select form-select-sm" style="width:80px;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <a href="<?= $baseUrl ?>/evaluation-settings" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-x-circle me-1"></i>Deselect Season
                            </a>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="<?= $baseUrl ?>/save-branch-settings"
                        id="branchSettingsForm">

                        <input type="hidden" name="season_id" value="<?= htmlspecialchars($selectedSeason) ?>">

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th width="22%">Branch</th>
                                            <th width="16%">Mode</th>
                                            <th width="18%">Custom Start</th>
                                            <th width="18%">Custom End</th>
                                            <th width="10%" class="text-center">Locked</th>
                                            <th width="16%" class="text-center">Status</th>
                                        </tr>
                                    </thead>

                                    <tbody id="branchTableBody">
                                    <?php if (empty($branches)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No branches found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($branches as $branch): ?>
                                            <?php
                                            $mode        = $branch['mode']         ?? 'default';
                                            $customStart = $branch['custom_start'] ?? '';
                                            $customEnd   = $branch['custom_end']   ?? '';
                                            $isLocked    = !empty($branch['is_locked']);
                                            $branchId    = $branch['id'];
                                            ?>
                                            <tr data-branch-id="<?= htmlspecialchars($branchId) ?>">

                                                <!-- Branch Name -->
                                                <td class="fw-semibold align-middle">
                                                    <?= htmlspecialchars($branch['name']) ?>
                                                </td>

                                                <!-- Mode Select -->
                                                <td class="align-middle">
                                                    <select
                                                        class="form-select form-select-sm branch-mode-select"
                                                        name="branches[<?= htmlspecialchars($branchId) ?>][mode]"
                                                        data-branch="<?= htmlspecialchars($branchId) ?>">
                                                        <option value="default"      <?= $mode === 'default'      ? 'selected' : '' ?>>Default</option>
                                                        <option value="custom"       <?= $mode === 'custom'       ? 'selected' : '' ?>>Custom</option>
                                                        <option value="always_open"  <?= $mode === 'always_open'  ? 'selected' : '' ?>>Always Open</option>
                                                    </select>
                                                </td>

                                                <!-- Custom Start -->
                                                <td class="align-middle">
                                                    <input
                                                        type="date"
                                                        class="form-control form-control-sm custom-date-field"
                                                        name="branches[<?= htmlspecialchars($branchId) ?>][custom_start]"
                                                        value="<?= htmlspecialchars($customStart) ?>"
                                                        <?= $mode !== 'custom' ? 'disabled' : '' ?>>
                                                </td>

                                                <!-- Custom End -->
                                                <td class="align-middle">
                                                    <input
                                                        type="date"
                                                        class="form-control form-control-sm custom-date-field"
                                                        name="branches[<?= htmlspecialchars($branchId) ?>][custom_end]"
                                                        value="<?= htmlspecialchars($customEnd) ?>"
                                                        <?= $mode !== 'custom' ? 'disabled' : '' ?>>
                                                </td>

                                                <!-- Locked -->
                                                <td class="text-center align-middle">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="branches[<?= htmlspecialchars($branchId) ?>][is_locked]"
                                                            value="1"
                                                            <?= $isLocked ? 'checked' : '' ?>
                                                            title="Lock this branch window">
                                                    </div>
                                                </td>

                                                <!-- Status Badge -->
                                                <td class="text-center align-middle">
                                                    <?php if ($isLocked): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-lock-fill me-1"></i>Locked
                                                        </span>
                                                    <?php elseif ($mode === 'always_open'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-unlock-fill me-1"></i>Always Open
                                                        </span>
                                                    <?php elseif ($mode === 'custom'): ?>
                                                        <span class="badge bg-info text-dark">
                                                            <i class="bi bi-calendar-range me-1"></i>Custom
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-calendar me-1"></i>Default
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>

                                </table>
                            </div>
                        </div><!-- /.card-body -->

                        <div class="card-footer">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                                <!-- Save button + hint -->
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Save Branch Configuration
                                    </button>
                                    <small class="text-muted">
                                        Custom dates required only when mode is <strong>Custom</strong>.
                                    </small>
                                </div>

                                <!-- Pagination controls -->
                                <div class="d-flex align-items-center gap-2" id="branchPagination">
                                    <small class="text-muted" id="branchPageInfo"></small>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item" id="branchPrevLi">
                                                <button type="button" class="page-link" id="branchPrevBtn">
                                                    <i class="bi bi-chevron-left"></i>
                                                </button>
                                            </li>
                                            <li class="page-item disabled">
                                                <span class="page-link" id="branchCurrentPage">1</span>
                                            </li>
                                            <li class="page-item" id="branchNextLi">
                                                <button type="button" class="page-link" id="branchNextBtn">
                                                    <i class="bi bi-chevron-right"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>

                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div><!-- /.row Branch Config -->

    <?php endif; // end selectedSeason ?>

</div><!-- /.container-fluid -->


<!-- ── Inline JS: toggle custom date fields based on mode ────────── -->
<script nonce="<?php echo $GLOBALS['nonce']; ?>">
(function () {
    'use strict';

    /**
     * Enable/disable custom date inputs for a branch row
     * based on the selected mode value.
     *
     * @param {HTMLSelectElement} select
     */
    function toggleDateFields(select) {
        const branchId  = select.dataset.branch;
        const isCustom  = select.value === 'custom';

        // Find the two date inputs in the same <tr>
        const row       = select.closest('tr');
        if (!row) return;

        row.querySelectorAll('.custom-date-field').forEach(function (input) {
            input.disabled = !isCustom;
            if (!isCustom) {
                input.value = '';   // clear stale custom dates when switching away
            }
        });
    }

    // Apply on page load (respects server-rendered state)
    document.querySelectorAll('.branch-mode-select').forEach(function (select) {
        toggleDateFields(select);

        select.addEventListener('change', function () {
            toggleDateFields(this);
        });
    });

    // Client-side validation before branch settings form submission
    const branchForm = document.getElementById('branchSettingsForm');
    if (branchForm) {
        branchForm.addEventListener('submit', function (e) {
            let valid = true;

            document.querySelectorAll('.branch-mode-select').forEach(function (select) {
                if (select.value !== 'custom') return;

                const row   = select.closest('tr');
                const dates = row.querySelectorAll('.custom-date-field');
                dates.forEach(function (input) {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            });

            if (!valid) {
                e.preventDefault();
                alert('Please fill in both custom start and end dates for all branches set to "Custom" mode.');
            }
        });
    }
})();
</script>