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

<style nonce="<?= $GLOBALS['nonce'] ?>">
/* ── Reset & base ───────────────────────────────────────────────── */
.es-wrap * { box-sizing: border-box; }
.es-wrap {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    color: #212529;
    padding: 1.5rem 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── Page header ────────────────────────────────────────────────── */
.es-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding-bottom: 0.25rem;
}
.es-page-title {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.es-page-title i { font-size: 20px; color: #14532d; }
.es-page-subtitle { font-size: 13px; color: #6c757d; }

/* ── Cards ──────────────────────────────────────────────────────── */
.es-card {
    background: #fff;
    border: 0.5px solid rgba(0,0,0,0.12);
    border-radius: 12px;
    overflow: hidden;
}
.es-card-header {
    padding: 0.8rem 1.25rem;
    border-bottom: 0.5px solid rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.es-card-header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    font-size: 14px;
}
.es-card-header-title i { font-size: 16px; color: #6c757d; }
.es-card-body { padding: 1.25rem; }
.es-card-body-flush { padding: 0; }
.es-card-footer {
    padding: 0.8rem 1.25rem;
    border-top: 0.5px solid rgba(0,0,0,0.1);
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

/* ── Two-column layout ──────────────────────────────────────────── */
.es-two-col {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    gap: 1rem;
}
@media (max-width: 768px) {
    .es-two-col { grid-template-columns: 1fr; }
}

/* ── Form elements ──────────────────────────────────────────────── */
.es-form-row { margin-bottom: 1rem; }
.es-form-row:last-child { margin-bottom: 0; }
.es-field-label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.es-required { color: #dc3545; margin-left: 2px; }
.es-hint { font-size: 11px; color: #9ca3af; margin-top: 5px; }

.es-input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

.es-select, .es-input {
    width: 100%;
    height: 34px;
    padding: 0 10px;
    font-size: 13px;
    background: #fff;
    color: #212529;
    border: 0.5px solid rgba(0,0,0,0.2);
    border-radius: 8px;
    outline: none;
    transition: border-color 0.15s;
    font-family: inherit;
}
.es-select:focus, .es-input:focus { border-color: #14532d; box-shadow: 0 0 0 2px rgba(20,83,45,0.1); }

/* ── Buttons ────────────────────────────────────────────────────── */
.es-btn {
    height: 32px;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 500;
    font-family: inherit;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 0.5px solid rgba(0,0,0,0.18);
    background: #fff;
    color: #212529;
    text-decoration: none;
    transition: background 0.15s;
    white-space: nowrap;
}
.es-btn:hover { background: #f8f9fa; color: #212529; text-decoration: none; }
.es-btn i { font-size: 15px; }

.es-btn-primary {
    background: #14532d;
    color: #fff;
    border-color: #14532d;
}
.es-btn-primary:hover { background: #166534; color: #fff; }

.es-btn-accent {
    color: #14532d;
    border-color: #14532d;
    background: #fff;
}
.es-btn-accent:hover { background: #f0fdf4; }

.es-btn-sm { height: 26px; padding: 0 10px; font-size: 12px; }
.es-btn-sm i { font-size: 13px; }

/* ── Tables ─────────────────────────────────────────────────────── */
.es-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.es-table thead th {
    padding: 9px 12px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #6c757d;
    background: #f8f9fa;
    border-bottom: 0.5px solid rgba(0,0,0,0.1);
    text-align: left;
    white-space: nowrap;
}
.es-table tbody tr { border-bottom: 0.5px solid rgba(0,0,0,0.07); }
.es-table tbody tr:last-child { border-bottom: none; }
.es-table tbody tr:hover { background: #f8f9fa; }
.es-table tbody tr.es-row-active { background: rgba(20,83,45,0.05); }
.es-table td { padding: 9px 12px; vertical-align: middle; }

/* ── Badges ─────────────────────────────────────────────────────── */
.es-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 500;
    line-height: 1;
    white-space: nowrap;
}
.es-badge i { font-size: 11px; }
.es-badge-green  { background: #dcfce7; color: #166534; }
.es-badge-blue   { background: #dbeafe; color: #1e40af; }
.es-badge-amber  { background: #fef3c7; color: #92400e; }
.es-badge-gray   { background: #f1f5f9; color: #475569; border: 0.5px solid rgba(0,0,0,0.1); }
.es-badge-red    { background: #fee2e2; color: #991b1b; }

/* ── Season pill in header ──────────────────────────────────────── */
.es-season-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6c757d;
    padding: 4px 10px;
    border: 0.5px solid rgba(0,0,0,0.12);
    border-radius: 100px;
    background: #f0fdf4;
}
.es-season-pill i { font-size: 13px; color: #14532d; }
.es-season-pill span { font-weight: 500; color: #14532d; }

/* ── Branch table inline selects / inputs ───────────────────────── */
.es-mode-select {
    height: 28px;
    padding: 0 8px;
    font-size: 12px;
    font-family: inherit;
    width: auto;
    min-width: 120px;
    background: #fff;
    color: #212529;
    border: 0.5px solid rgba(0,0,0,0.18);
    border-radius: 6px;
    outline: none;
    cursor: pointer;
}
.es-mode-select:focus { border-color: #14532d; }

.es-date-input {
    height: 28px;
    padding: 0 8px;
    font-size: 12px;
    font-family: monospace;
    width: 140px;
    background: #fff;
    color: #212529;
    border: 0.5px solid rgba(0,0,0,0.18);
    border-radius: 6px;
    outline: none;
}
.es-date-input:focus { border-color: #14532d; }
.es-date-input:disabled { opacity: 0.35; pointer-events: none; background: #f8f9fa; }
.es-date-input.is-invalid { border-color: #dc3545; }

/* ── Pagination ─────────────────────────────────────────────────── */
.es-pagination { display: flex; align-items: center; gap: 4px; }
.es-page-btn {
    width: 28px;
    height: 28px;
    border: 0.5px solid rgba(0,0,0,0.15);
    border-radius: 6px;
    background: #fff;
    color: #212529;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-family: inherit;
    transition: background 0.1s;
}
.es-page-btn:hover:not(:disabled) { background: #f8f9fa; }
.es-page-btn:disabled { opacity: 0.35; cursor: default; }
.es-page-btn.es-current { background: #f0fdf4; color: #14532d; font-weight: 500; pointer-events: none; border-color: #14532d; }
.es-page-info { font-size: 12px; color: #6c757d; white-space: nowrap; }

.es-count-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 0.5px solid rgba(0,0,0,0.1);
}

/* ── Inline season editing ──────────────────────────────────────── */
.es-season-edit-input {
    height: 28px;
    padding: 0 8px;
    font-size: 12px;
    font-family: inherit;
    background: #fff;
    color: #212529;
    border: 0.5px solid rgba(0,0,0,0.22);
    border-radius: 6px;
    outline: none;
    width: 100%;
    transition: border-color 0.15s;
}
.es-season-edit-input:focus { border-color: #14532d; box-shadow: 0 0 0 2px rgba(20,83,45,0.1); }
.es-season-edit-input.es-period { width: 72px; font-family: monospace; }

.es-edit-row td { background: #f0fdf4 !important; }
.es-edit-row { outline: 1.5px solid rgba(20,83,45,0.25); outline-offset: -1px; }

.es-btn-save {
    height: 26px; padding: 0 10px; font-size: 12px;
    background: #14532d; color: #fff; border-color: #14532d;
}
.es-btn-save:hover { background: #166534; color: #fff; }
.es-btn-cancel {
    height: 26px; padding: 0 10px; font-size: 12px;
}
</style>

<div class="es-wrap">

    <!-- ── Page Header ──────────────────────────────────────────── -->
    <div class="es-page-header">
        <div>
            <div class="es-page-title">
                <i class="bi bi-sliders2"></i>
                BSC &amp; Efficiency Evaluation Settings
            </div>
            <div class="es-page-subtitle">
                Manage evaluation seasons and configure branch-specific evaluation windows
            </div>
        </div>
    </div>

    <!-- ── Season Area ──────────────────────────────────────────── -->
    <div class="es-two-col">

        <!-- Create Season -->
        <div class="es-card">
            <div class="es-card-header">
                <div class="es-card-header-title">
                    <i class="bi bi-calendar-plus"></i>Create season
                </div>
            </div>

            <form method="POST" action="<?= $baseUrl ?>/save-season" id="createSeasonForm">
                <div class="es-card-body">
<div class="es-form-row">
    <label for="fiscal_year" class="es-field-label">
        Fiscal year <span class="es-required">*</span>
    </label>
    <input
        type="number"
        id="fiscal_year"
        name="fiscal_year"
        class="es-input"
        placeholder="e.g. 2026"
        min="2018"
        max="2100"
        required>
</div>
                    <div class="es-form-row">
                        <label for="season_name" class="es-field-label">
                            Season name <span class="es-required">*</span>
                        </label>
                        <select
                            id="season_name"
                            name="season_name"
                            class="es-select"
                            required>
                            <option value="">— Season ይምረጡ —</option>
                            <option value="የመጀመሪያው በጀት ዓመት">የመጀመሪያው በጀት ዓመት</option>
                            <option value="ሁለተኛው በጀት ዓመት">ሁለተኛው በጀት ዓመት</option>
                        </select>
                    </div>

                    <div class="es-form-row">
                        <label class="es-field-label">
                            Default period <span class="es-required">*</span>
                        </label>
                        <div class="es-input-grid">
                            <div>
                                <input
                                    type="text"
                                    id="default_start"
                                    name="default_start"
                                    class="es-input"
                                    placeholder="MM-DD"
                                    pattern="\d{2}-\d{2}"
                                    maxlength="5"
                                    required>
                                <div class="es-hint">Start</div>
                            </div>
                            <div>
                                <input
                                    type="text"
                                    id="default_end"
                                    name="default_end"
                                    class="es-input"
                                    placeholder="MM-DD"
                                    pattern="\d{2}-\d{2}"
                                    maxlength="5"
                                    required>
                                <div class="es-hint">End</div>
                            </div>
                        </div>
                        <div class="es-hint" style="margin-top:7px">
                            Format: MM-DD &nbsp;·&nbsp; e.g. <code>01-01</code> → <code>06-30</code>
                        </div>
                    </div>

                </div><!-- /.es-card-body -->

                <div class="es-card-footer" style="justify-content:flex-end;">
                    <button type="submit" class="es-btn es-btn-primary">
                        <i class="bi bi-check-circle"></i>Save season
                    </button>
                </div>
            </form>
        </div><!-- /Create Season -->

        <!-- Existing Seasons -->
        <div class="es-card">
            <div class="es-card-header">
                <div class="es-card-header-title">
                    <i class="bi bi-calendar3"></i>Existing seasons
                </div>
                <?php if (!empty($seasons)): ?>
                    <span class="es-count-badge"><?= count($seasons) ?> season<?= count($seasons) !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <div class="es-card-body-flush">
                <table class="es-table" id="seasonsTable">
                    <thead>
                        <tr>
                            <th>Season name</th>
                            <th width="90">Label</th>
                            <th>በጀት ዓመት</th>
                            <th width="170">Default period</th>
                            <th width="150" style="text-align:center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($seasons)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#9ca3af; padding:2rem;">
                                No seasons created yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($seasons as $season): ?>
                            <tr
                                class="es-season-row <?= ($selectedSeason === $season['id']) ? 'es-row-active' : '' ?>"
                                data-id="<?= htmlspecialchars($season['id']) ?>"
                                data-name="<?= htmlspecialchars($season['season_name']) ?>"
                                data-label="<?= htmlspecialchars($season['season_label']) ?>"
                                data-start="<?= htmlspecialchars($season['default_start']) ?>"
                                data-end="<?= htmlspecialchars($season['default_end']) ?>">

                                <!-- view cells -->
                                <td class="es-cell-name" style="font-weight:500">
                                    <?= htmlspecialchars($season['season_name']) ?>
                                </td>
                                <td class="es-cell-label">
                                    <span class="es-badge es-badge-gray">
                                        <?= htmlspecialchars($season['season_label']) ?>
                                    </span>
                                </td>
                                 <td class="es-cell-label">
                                    <span class="es-badge es-badge-fiscal_year">
                                        <?= htmlspecialchars($season['fiscal_year']) ?>
                                    </span>
                                </td>
                                <td class="es-cell-period" style="font-family:monospace; font-size:12px;">
                                    <?= htmlspecialchars($season['default_start']) ?>
                                    &rarr;
                                    <?= htmlspecialchars($season['default_end']) ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:5px;">
                                        <button
                                            type="button"
                                            class="es-btn es-btn-sm es-season-edit-btn"
                                            title="Edit this season inline">
                                            <i class="bi bi-pencil"></i>Edit
                                        </button>
                                        <a
                                            href="<?= $baseUrl ?>/evaluation-settings/<?= urlencode($season['id']) ?>"
                                            class="es-btn es-btn-sm <?= ($selectedSeason === $season['id']) ? 'es-btn-accent' : '' ?>"
                                            title="Configure branch windows for this season">
                                            <i class="bi bi-sliders"></i>Configure
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div><!-- /.es-card-body-flush -->
        </div><!-- /Existing Seasons -->

    </div><!-- /.es-two-col -->


    <!-- ── Branch Configuration (only when a season is selected) ── -->
    <?php if (!empty($selectedSeason)): ?>

        <?php
        $selectedSeasonName = '';
        foreach ($seasons as $s) {
            if ($s['id'] === $selectedSeason) {
                $selectedSeasonName = $s['season_name'];
                break;
            }
        }
        ?>

        <div class="es-card">

            <div class="es-card-header">
                <div class="es-card-header-title">
                    <i class="bi bi-building"></i>Branch evaluation windows
                    <?php if ($selectedSeasonName): ?>
                        <span class="es-season-pill">
                            <i class="bi bi-calendar-event"></i>
                            <span><?= htmlspecialchars($selectedSeasonName) ?></span>
                        </span>
                    <?php endif; ?>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <label for="branchPageSize" style="font-size:12px; color:#6c757d; white-space:nowrap; margin:0;">
                        Rows per page
                    </label>
                    <select id="branchPageSize" style="height:28px; padding:0 8px; font-size:12px; font-family:inherit; width:72px; border:0.5px solid rgba(0,0,0,0.18); border-radius:6px; background:#fff; color:#212529; outline:none; cursor:pointer;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <a href="<?= $baseUrl ?>/evaluation-settings" class="es-btn es-btn-sm">
                        <i class="bi bi-x-circle"></i>Deselect season
                    </a>
                </div>
            </div>

            <form
                method="POST"
                action="<?= $baseUrl ?>/save-branch-settings"
                id="branchSettingsForm">

                <input type="hidden" name="season_id" value="<?= htmlspecialchars($selectedSeason) ?>">

                <div class="es-card-body-flush">
                    <div style="overflow-x:auto;">
                        <table class="es-table">
                            <thead>
                                <tr>
                                    <th style="width:22%">Branch</th>
                                    <th style="width:16%">Mode</th>
                                    <th style="width:18%">Custom start</th>
                                    <th style="width:18%">Custom end</th>
                                    <th style="width:10%; text-align:center;">Locked</th>
                                    <th style="width:16%; text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="branchTableBody">
                            <?php if (empty($branches)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; color:#9ca3af; padding:2rem;">
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
                                        <td style="font-weight:500;">
                                            <?= htmlspecialchars($branch['name']) ?>
                                        </td>

                                        <!-- Mode Select -->
                                        <td>
                                            <select
                                                class="es-mode-select branch-mode-select"
                                                name="branches[<?= htmlspecialchars($branchId) ?>][mode]"
                                                data-branch="<?= htmlspecialchars($branchId) ?>">
                                                <option value="default"     <?= $mode === 'default'     ? 'selected' : '' ?>>Default</option>
                                                <option value="custom"      <?= $mode === 'custom'      ? 'selected' : '' ?>>Custom</option>
                                                <option value="always_open" <?= $mode === 'always_open' ? 'selected' : '' ?>>Always open</option>
                                            </select>
                                        </td>

                                        <!-- Custom Start -->
                                        <td>
                                            <input
                                                type="date"
                                                class="es-date-input custom-date-field"
                                                name="branches[<?= htmlspecialchars($branchId) ?>][custom_start]"
                                                value="<?= htmlspecialchars($customStart) ?>"
                                                <?= $mode !== 'custom' ? 'disabled' : '' ?>>
                                        </td>

                                        <!-- Custom End -->
                                        <td>
                                            <input
                                                type="date"
                                                class="es-date-input custom-date-field"
                                                name="branches[<?= htmlspecialchars($branchId) ?>][custom_end]"
                                                value="<?= htmlspecialchars($customEnd) ?>"
                                                <?= $mode !== 'custom' ? 'disabled' : '' ?>>
                                        </td>

                                        <!-- Locked -->
                                        <td style="text-align:center;">
                                            <input
                                                type="checkbox"
                                                style="width:15px; height:15px; cursor:pointer; accent-color:#14532d;"
                                                name="branches[<?= htmlspecialchars($branchId) ?>][is_locked]"
                                                value="1"
                                                <?= $isLocked ? 'checked' : '' ?>
                                                title="Lock this branch window">
                                        </td>

                                        <!-- Status Badge -->
                                        <td style="text-align:center;">
                                            <?php if ($isLocked): ?>
                                                <span class="es-badge es-badge-red">
                                                    <i class="bi bi-lock-fill"></i>Locked
                                                </span>
                                            <?php elseif ($mode === 'always_open'): ?>
                                                <span class="es-badge es-badge-green">
                                                    <i class="bi bi-unlock-fill"></i>Always open
                                                </span>
                                            <?php elseif ($mode === 'custom'): ?>
                                                <span class="es-badge es-badge-blue">
                                                    <i class="bi bi-calendar-range"></i>Custom
                                                </span>
                                            <?php else: ?>
                                                <span class="es-badge es-badge-gray">
                                                    <i class="bi bi-calendar"></i>Default
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- /.es-card-body-flush -->

                <div class="es-card-footer">
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="es-btn es-btn-primary">
                            <i class="bi bi-floppy"></i>Save configuration
                        </button>
                        <small style="color:#6c757d;">
                            Custom dates required only when mode is set to <strong>Custom</strong>.
                        </small>
                    </div>

                    <!-- Pagination -->
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="es-page-info" id="branchPageInfo"></span>
                        <div class="es-pagination">
                            <button type="button" class="es-page-btn" id="branchPrevBtn" disabled>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="es-page-btn es-current" id="branchCurrentPage">1</button>
                            <button type="button" class="es-page-btn" id="branchNextBtn" disabled>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div><!-- /.es-card Branch Config -->

    <?php endif; // end selectedSeason ?>

</div><!-- /.es-wrap -->


<!-- ── JS: mode toggle + pagination + validation ─────────────────── -->
<script nonce="<?= $GLOBALS['nonce'] ?>">
(function () {
    'use strict';

    /* ── 1. Toggle custom date fields on mode change ────────────── */
    function toggleDateFields(select) {
        var isCustom = select.value === 'custom';
        var row = select.closest('tr');
        if (!row) return;

        row.querySelectorAll('.custom-date-field').forEach(function (input) {
            input.disabled = !isCustom;
            if (!isCustom) {
                input.value = '';
                input.classList.remove('is-invalid');
            }
        });

        /* Update status badge live */
        var badgeCell = row.querySelector('td:last-child');
        if (!badgeCell) return;
        var locked = row.querySelector('input[type="checkbox"]');
        if (locked && locked.checked) return; // locked overrides

        var mode = select.value;
        var html;
        if (mode === 'always_open') {
            html = '<span class="es-badge es-badge-green"><i class="bi bi-unlock-fill"></i>Always open</span>';
        } else if (mode === 'custom') {
            html = '<span class="es-badge es-badge-blue"><i class="bi bi-calendar-range"></i>Custom</span>';
        } else {
            html = '<span class="es-badge es-badge-gray"><i class="bi bi-calendar"></i>Default</span>';
        }
        badgeCell.innerHTML = html;
    }

    document.querySelectorAll('.branch-mode-select').forEach(function (select) {
        toggleDateFields(select);
        select.addEventListener('change', function () { toggleDateFields(this); });
    });

    /* Update badge when locked checkbox changes */
    document.querySelectorAll('#branchTableBody input[type="checkbox"]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var row = this.closest('tr');
            if (!row) return;
            var badgeCell = row.querySelector('td:last-child');
            var modeSelect = row.querySelector('.branch-mode-select');
            if (!badgeCell || !modeSelect) return;

            var html;
            if (this.checked) {
                html = '<span class="es-badge es-badge-red"><i class="bi bi-lock-fill"></i>Locked</span>';
            } else {
                var mode = modeSelect.value;
                if (mode === 'always_open') {
                    html = '<span class="es-badge es-badge-green"><i class="bi bi-unlock-fill"></i>Always open</span>';
                } else if (mode === 'custom') {
                    html = '<span class="es-badge es-badge-blue"><i class="bi bi-calendar-range"></i>Custom</span>';
                } else {
                    html = '<span class="es-badge es-badge-gray"><i class="bi bi-calendar"></i>Default</span>';
                }
            }
            badgeCell.innerHTML = html;
        });
    });

    /* ── 2. Client-side pagination ──────────────────────────────── */
    var tbody      = document.getElementById('branchTableBody');
    var pageSizeEl = document.getElementById('branchPageSize');
    var prevBtn    = document.getElementById('branchPrevBtn');
    var nextBtn    = document.getElementById('branchNextBtn');
    var curPageEl  = document.getElementById('branchCurrentPage');
    var pageInfoEl = document.getElementById('branchPageInfo');

    if (tbody && pageSizeEl) {
        var currentPage = 1;

        function getRows() {
            return Array.from(tbody.querySelectorAll('tr[data-branch-id]'));
        }

        function render() {
            var rows     = getRows();
            var pageSize = parseInt(pageSizeEl.value, 10);
            var total    = rows.length;
            var pages    = Math.max(1, Math.ceil(total / pageSize));

            if (currentPage > pages) currentPage = pages;

            var start = (currentPage - 1) * pageSize;
            var end   = Math.min(start + pageSize, total);

            rows.forEach(function (row, i) {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });

            if (curPageEl)  curPageEl.textContent = currentPage;
            if (pageInfoEl) {
                pageInfoEl.textContent = total > 0
                    ? 'Showing ' + (start + 1) + '–' + end + ' of ' + total + ' branches'
                    : '';
            }
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= pages;
        }

        pageSizeEl.addEventListener('change', function () {
            currentPage = 1;
            render();
        });
        if (prevBtn) prevBtn.addEventListener('click', function () {
            currentPage--;
            render();
        });
        if (nextBtn) nextBtn.addEventListener('click', function () {
            currentPage++;
            render();
        });

        render();
    }

    /* ── 3. Form validation before submit ───────────────────────── */
    var branchForm = document.getElementById('branchSettingsForm');
    if (branchForm) {
        branchForm.addEventListener('submit', function (e) {
            var valid = true;

            document.querySelectorAll('.branch-mode-select').forEach(function (select) {
                if (select.value !== 'custom') return;
                var row = select.closest('tr');
                if (!row) return;
                row.querySelectorAll('.custom-date-field').forEach(function (input) {
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

    /* ── 4. Inline season row editing ──────────────────────────────── */
    document.querySelectorAll('.es-season-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = this.closest('tr');
            if (!row) return;

            /* Collapse any other open edit row first */
            document.querySelectorAll('.es-season-row.es-edit-row').forEach(function (r) {
                if (r !== row) cancelEdit(r);
            });

            openEdit(row);
        });
    });

    function openEdit(row) {
        var id    = row.dataset.id;
        var name  = row.dataset.name;
        var label = row.dataset.label;
        var start = row.dataset.start;
        var end   = row.dataset.end;

        row.classList.add('es-edit-row');

        /* Season name cell */
        row.querySelector('.es-cell-name').innerHTML =
            '<select class="es-season-edit-input es-edit-name">' +
            '<option value="የመጀመሪያው በጀት ዓመት"' + (name === 'የመጀመሪያው በጀት ዓመት' ? ' selected' : '') + '>የመጀመሪያው በጀት ዓመት</option>' +
            '<option value="ሁለተኛው በጀት ዓመት"'   + (name === 'ሁለተኛው በጀት ዓመት'   ? ' selected' : '') + '>ሁለተኛው በጀት ዓመት</option>' +
            '</select>';

        /* Label cell */
        row.querySelector('.es-cell-label').innerHTML =
            '<input type="text" class="es-season-edit-input es-edit-label" value="' + escHtml(label) + '" placeholder="Label" maxlength="20">';

        /* Period cell */
        row.querySelector('.es-cell-period').innerHTML =
            '<div style="display:flex;align-items:center;gap:4px;">' +
            '<input type="text" class="es-season-edit-input es-period es-edit-start" value="' + escHtml(start) + '" placeholder="MM-DD" maxlength="5">' +
            '<span style="color:#9ca3af;font-size:12px;">→</span>' +
            '<input type="text" class="es-season-edit-input es-period es-edit-end" value="' + escHtml(end) + '" placeholder="MM-DD" maxlength="5">' +
            '</div>';

        /* Actions cell */
        var actCell = row.querySelector('td:last-child');
        actCell.innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;gap:5px;">' +
            '<button type="button" class="es-btn es-btn-sm es-btn-save es-season-save-btn"><i class="bi bi-check-lg"></i>Save</button>' +
            '<button type="button" class="es-btn es-btn-sm es-btn-cancel es-season-cancel-btn"><i class="bi bi-x-lg"></i>Cancel</button>' +
            '</div>';

        actCell.querySelector('.es-season-save-btn').addEventListener('click',   function () { submitEdit(row); });
        actCell.querySelector('.es-season-cancel-btn').addEventListener('click', function () { cancelEdit(row); });
    }

    function cancelEdit(row) {
        row.classList.remove('es-edit-row');

        /* Restore from data attributes */
        row.querySelector('.es-cell-name').innerHTML   = '<span style="font-weight:500">' + escHtml(row.dataset.name) + '</span>';
        row.querySelector('.es-cell-label').innerHTML  = '<span class="es-badge es-badge-gray">' + escHtml(row.dataset.label) + '</span>';
        row.querySelector('.es-cell-period').innerHTML =
            '<span style="font-family:monospace;font-size:12px;">' +
            escHtml(row.dataset.start) + ' → ' + escHtml(row.dataset.end) +
            '</span>';

        /* Restore action buttons */
        var configActive = row.classList.contains('es-row-active') ? ' es-btn-accent' : '';
        var configHref   = row.querySelector('td:last-child') ? '' : '#'; /* fallback */
        row.querySelector('td:last-child').innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;gap:5px;">' +
            '<button type="button" class="es-btn es-btn-sm es-season-edit-btn"><i class="bi bi-pencil"></i>Edit</button>' +
            '<a href="' + escHtml(baseUrl) + '/evaluation-settings/' + encodeURIComponent(row.dataset.id) + '" class="es-btn es-btn-sm' + configActive + '"><i class="bi bi-sliders"></i>Configure</a>' +
            '</div>';

        row.querySelector('.es-season-edit-btn').addEventListener('click', function () {
            document.querySelectorAll('.es-season-row.es-edit-row').forEach(function (r) {
                if (r !== row) cancelEdit(r);
            });
            openEdit(row);
        });
    }

    function submitEdit(row) {
        var nameVal  = row.querySelector('.es-edit-name').value.trim();
        var labelVal = row.querySelector('.es-edit-label').value.trim();
        var startVal = row.querySelector('.es-edit-start').value.trim();
        var endVal   = row.querySelector('.es-edit-end').value.trim();

        /* Basic validation */
        var periodRe = /^\d{2}-\d{2}$/;
        if (!nameVal || !labelVal || !startVal || !endVal) {
            alert('All fields are required.');
            return;
        }
        if (!periodRe.test(startVal) || !periodRe.test(endVal)) {
            alert('Start and end must be in MM-DD format (e.g. 01-01).');
            return;
        }

        /* Build and submit a hidden form */
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = baseUrl + '/update-season';

        var fields = {
            season_id:      row.dataset.id,
            season_name:    nameVal,
            season_label:   labelVal,
            default_start:  startVal,
            default_end:    endVal
        };

        Object.keys(fields).forEach(function (key) {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = key;
            input.value = fields[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    /* HTML-escape helper for JS-generated markup */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* Expose baseUrl for cancelEdit restore */
    var baseUrl = <?= json_encode(rtrim($_ENV['BASE_URL'], '/')) ?>;

})();
</script>