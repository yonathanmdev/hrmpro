<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Branch;
use App\Models\BranchEvaluationWindow;
use App\Models\EvaluationSeason;
use Ramsey\Uuid\Uuid;

class EvaluationSettingsController extends BaseController
{
    // ──────────────────────────────────────────────────────────────────
    //  Dashboard / Index
    // ──────────────────────────────────────────────────────────────────

    /**
     * Render the Evaluation Settings page.
     *
     * The season to configure is resolved from, in priority order:
     *   1. A clean-URL path segment passed by the router  → $params[0]
     *   2. The ?season= query-string parameter            → $_GET['season']
     *
     * @param  array $params  Route parameters injected by the router.
     */
  public function index(array $params = []): void
{
    AuthHelper::checkRole(['system_admin']);

    $seasonModel = new EvaluationSeason($this->db);
    $windowModel = new BranchEvaluationWindow($this->db);

    // Router sends ['uuid' => value]
    $seasonId = $params['uuid'] ?? ($_GET['season'] ?? null);

    if (
        $seasonId !== null &&
        !preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $seasonId
        )
    ) {
        $seasonId = null;
    }

    $branches = [];

    if ($seasonId) {
        $branches = $windowModel->getBranchSettingsBySeason($seasonId);
    }

    $this->render('evaluation-settings', [
        'title'          => 'BSC & Efficiency Settings',
        'seasons'        => $seasonModel->getAll(),
        'selectedSeason' => $seasonId,
        'branches'       => $branches
    ]);
}

    // ──────────────────────────────────────────────────────────────────
    //  Create / Save Season
    // ──────────────────────────────────────────────────────────────────

    /**
     * Handle POST: create a new evaluation season.
     */
    public function saveSeason(): void
    {
        AuthHelper::checkRole(['system_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToSettings();
        }

        try {
            $seasonName   = trim($_POST['season_name']   ?? '');
            $fiscalYear   = (int) trim($_POST['fiscal_year']   ?? 0);
            $seasonLabel ='S1';
            $defaultStart = trim($_POST['default_start'] ?? '');
            $defaultEnd   = trim($_POST['default_end']   ?? '');

            // ── Validation ────────────────────────────────────────────
            $errors = [];

            if ($seasonName === '') {
                $errors[] = 'Season Name is required.';
            }
            if ($seasonName === 'ሁለተኛው በጀት ዓመት') {
                $seasonLabel ='S2';
            }

            if ($seasonLabel === '') {
                $errors[] = 'Season Label is required.';
            }

            if ($defaultStart === '') {
                $errors[] = 'Default Start is required.';
            } elseif (!preg_match('/^\d{2}-\d{2}$/', $defaultStart)) {
                $errors[] = 'Default Start must be in MM-DD format.';
            }

            if ($defaultEnd === '') {
                $errors[] = 'Default End is required.';
            } elseif (!preg_match('/^\d{2}-\d{2}$/', $defaultEnd)) {
                $errors[] = 'Default End must be in MM-DD format.';
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode(' ', $errors);
                $this->redirectToSettings();
            }
// add to $errors checks:
if ($fiscalYear < 2018 || $fiscalYear > 2100) {
    $errors[] = 'Valid fiscal year is required (e.g. 2026).';
}
            // ── Persist ───────────────────────────────────────────────
            $seasonModel = new EvaluationSeason($this->db);

            $seasonModel->create([
                'id'            => Uuid::uuid4()->toString(),
                'fiscal_year'   => $fiscalYear,
                'season_name'   => $seasonName,
                'season_label'  => $seasonLabel,
                'default_start' => $defaultStart,
                'default_end'   => $defaultEnd,
                'created_by'    => $_SESSION['user']['id'],
            ]);

            $_SESSION['success'] = 'Evaluation season created successfully.';

        } catch (\Exception $e) {
            error_log('EvaluationSettingsController::saveSeason — ' . $e->getMessage());
            $_SESSION['error'] = 'Unable to create season. Please try again.';
        }

        $this->redirectToSettings();
    }
public function updateSeason(): void
{
    AuthHelper::checkRole(['system_admin']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToSettings();
    }

    try {
        $seasonId     = trim($_POST['season_id']    ?? '');
        $seasonName   = trim($_POST['season_name']  ?? '');
        $seasonLabel  = trim($_POST['season_label'] ?? '');
        $defaultStart = trim($_POST['default_start'] ?? '');
        $defaultEnd   = trim($_POST['default_end']   ?? '');

        // ── Validation ────────────────────────────────────────────
        $errors = [];

        if ($seasonId === '') {
            $errors[] = 'Season ID is missing.';
        }

        if ($seasonName === '') {
            $errors[] = 'Season Name is required.';
        }

        // Re-derive label from name (same logic as saveSeason)
        if ($seasonName === 'ሁለተኛው በጀት ዓመት') {
            $seasonLabel = 'S2';
        } elseif ($seasonName === 'የመጀመሪያው በጀት ዓመት') {
            $seasonLabel = 'S1';
        }

        if ($seasonLabel === '') {
            $errors[] = 'Season Label could not be determined.';
        }

        if ($defaultStart === '') {
            $errors[] = 'Default Start is required.';
        } elseif (!preg_match('/^\d{2}-\d{2}$/', $defaultStart)) {
            $errors[] = 'Default Start must be in MM-DD format.';
        }

        if ($defaultEnd === '') {
            $errors[] = 'Default End is required.';
        } elseif (!preg_match('/^\d{2}-\d{2}$/', $defaultEnd)) {
            $errors[] = 'Default End must be in MM-DD format.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            $this->redirectToSettings();
        }

        
$fiscalYear = (int) trim($_POST['fiscal_year'] ?? 0);
// add to $errors checks:
if ($fiscalYear < 2018 || $fiscalYear > 2100) {
    $errors[] = 'Valid fiscal year is required (e.g. 2026).';
}
        // ── Persist ───────────────────────────────────────────────
        $seasonModel = new EvaluationSeason($this->db);
        $updated = $seasonModel->update($seasonId, [
            'season_name'   => $seasonName,
            'season_label'  => $seasonLabel,
            'default_start' => $defaultStart,
            'default_end'   => $defaultEnd,
            'fiscal_year'   => $fiscalYear,
            'updated_by'    => $_SESSION['user']['id'],
        ]);

        if (!$updated) {
            $_SESSION['error'] = 'Season not found or no changes were made.';
            $this->redirectToSettings();
        }

        $_SESSION['success'] = 'Evaluation season updated successfully.';

    } catch (\Exception $e) {
        error_log('EvaluationSettingsController::updateSeason — ' . $e->getMessage());
        $_SESSION['error'] = 'Unable to update season. Please try again.';
    }

    $this->redirectToSettings();
}
    // ──────────────────────────────────────────────────────────────────
    //  Save Branch Configuration
    // ──────────────────────────────────────────────────────────────────

    /**
     * Handle POST: upsert branch evaluation windows for a season.
     */
    public function saveBranchSettings(): void
    {
        AuthHelper::checkRole(['system_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToSettings();
        }

        $seasonId = trim($_POST['season_id'] ?? '');

        if ($seasonId === '') {
            $_SESSION['error'] = 'No evaluation season selected.';
            $this->redirectToSettings();
        }

        // Validate season ID format.
        if (!preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $seasonId
        )) {
            $_SESSION['error'] = 'Invalid season identifier.';
            $this->redirectToSettings();
        }

        if (empty($_POST['branches']) || !is_array($_POST['branches'])) {
            $_SESSION['error'] = 'No branch configuration received.';
            $this->redirectToSettings($seasonId);
        }

        try {
            $windowModel = new BranchEvaluationWindow($this->db);
            $skipped     = 0;

            foreach ($_POST['branches'] as $branchId => $branchData) {
                if (!is_array($branchData)) {
                    continue;
                }

                $mode        = $branchData['mode']         ?? 'default';
                $customStart = !empty($branchData['custom_start']) ? $branchData['custom_start'] : null;
                $customEnd   = !empty($branchData['custom_end'])   ? $branchData['custom_end']   : null;
                $isLocked    = isset($branchData['is_locked'])     ? 1 : 0;

                // Validate mode value.
                if (!in_array($mode, ['default', 'custom', 'always_open'], true)) {
                    $mode = 'default';
                }

                // Custom mode requires both dates — skip silently (JS also enforces this).
                if ($mode === 'custom' && ($customStart === null || $customEnd === null)) {
                    $skipped++;
                    continue;
                }

                // Clear dates when not in custom mode.
                if ($mode !== 'custom') {
                    $customStart = null;
                    $customEnd   = null;
                }

                $windowModel->saveOrUpdate([
                    'branch_id'    => $branchId,
                    'season_id'    => $seasonId,
                    'mode'         => $mode,
                    'custom_start' => $customStart,
                    'custom_end'   => $customEnd,
                    'is_locked'    => $isLocked,
                    'override_by'  => $_SESSION['user']['id'],
                    'notes'        => null,
                ]);
            }

            $_SESSION['success'] = 'Branch settings updated successfully.'
                . ($skipped > 0 ? " ({$skipped} branch(es) skipped — missing custom dates.)" : '');

        } catch (\Exception $e) {
            error_log('EvaluationSettingsController::saveBranchSettings — ' . $e->getMessage());
            $_SESSION['error'] = 'Unable to save branch settings. Please try again.';
        }

        // ✅ Return to the same season so the branch config remains visible.
        $this->redirectToSettings($seasonId);
    }

    // ──────────────────────────────────────────────────────────────────
    //  Delete Season  (optional)
    // ──────────────────────────────────────────────────────────────────

    /**
     * Handle GET: delete an evaluation season by ID.
     */
    public function deleteSeason(): void
    {
        AuthHelper::checkRole(['system_admin']);

        $id = trim($_GET['id'] ?? '');

        if ($id === '') {
            $_SESSION['error'] = 'No season ID provided.';
            $this->redirectToSettings();
        }

        try {
            $seasonModel = new EvaluationSeason($this->db);

            if (!method_exists($seasonModel, 'delete')) {
                throw new \RuntimeException('EvaluationSeason::delete() not implemented.');
            }

            $seasonModel->delete($id);
            $_SESSION['success'] = 'Season deleted successfully.';

        } catch (\Exception $e) {
            error_log('EvaluationSettingsController::deleteSeason — ' . $e->getMessage());
            $_SESSION['error'] = 'Unable to delete season. Please try again.';
        }

        $this->redirectToSettings();
    }

    // ──────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Redirect to the evaluation-settings page.
     * If a season ID is provided the season will remain selected.
     *
     * @param  string|null $seasonId  Optional season UUID to keep selected.
     */
  private function redirectToSettings(?string $seasonId = null): void
{
    $baseUrl = rtrim($_ENV['BASE_URL'] ?? '', '/');

    if (
        !empty($seasonId) &&
        preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            trim($seasonId)
        )
    ) {
        $url = $baseUrl . '/evaluation-settings/' . urlencode(trim($seasonId));
    } else {
        $url = $baseUrl . '/evaluation-settings';
    }

    if (!headers_sent()) {
        header("Location: {$url}");
        exit;
    }

    echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
    exit;
}
}