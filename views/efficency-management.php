<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">BSC Plan Management</h4>
    </div>

    <?php if (empty($openSeasons)): ?>

        <div class="alert alert-warning">
            No evaluation period is currently open for your branch.
        </div>

    <?php else: ?>

        <!-- Season Selector -->
        <?php if (count($openSeasons) > 1): ?>

            <div class="card mb-3">
                <div class="card-body">

                    <div class="form-group mb-0">

    <label for="seasonSelect">
        <strong>Select Evaluation Season</strong>
    </label>

    <select
        id="seasonSelect"
        class="form-control">

        <option value="">
            -- Select Season --
        </option>

        <?php foreach ($openSeasons as $season): ?>

            <option
                value="<?= htmlspecialchars($season['season_id']); ?>"
                <?= (
                    $selectedSeason &&
                    $selectedSeason['season_id'] === $season['season_id']
                )
                    ? 'selected'
                    : ''; ?>>

                <?= htmlspecialchars(
                    $season['season_name']
                    . ' (' .
                    $season['season_label']
                    . ')'
                ); ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

                </div>
            </div>

        <?php endif; ?>

        <?php if ($selectedSeason): ?>

            <?php

            $totalEmployees = count($employees);

            $attachedCount = count(
                array_filter(
                    $employees,
                    fn($e) => !empty($e['file_id']) || $e['attachment_status'] === 'manually_confirmed'
                )
            );

            $notAttachedCount =
                $totalEmployees - $attachedCount;

            ?>

            <!-- Season Information -->
            <div class="card mb-3">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <strong>
                        <?= htmlspecialchars(
                            $selectedSeason['season_name']
                        ); ?>
                    </strong>

                    <span class="badge badge-success">
                        Evaluation Window Open
                    </span>

                </div>

            </div>

            <!-- Statistics -->
            <div class="row mb-3">

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Total Employees</h6>
                            <h3><?= $totalEmployees; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Attachments Available</h6>
                            <h3><?= $attachedCount; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Not Attached</h6>
                            <h3><?= $notAttachedCount; ?></h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Employee Table -->
            <div class="card">

                <div class="card-header">
                    Employee BSC Plan Attachments
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover mb-0">

                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Employee Name</th>
                                    <th>Attachment Status</th>
                                    <th>Uploaded Date</th>
                                    <th style="width: 260px;">Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($employees)): ?>

                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No employees found.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($employees as $index => $employee): ?>

                                        <?php
                                            $hasFile      = !empty($employee['file_id']);
                                            $isManual     = $employee['attachment_status'] === 'manually_confirmed';
                                            $employeeUuid = urlencode($employee['uuid']);
                                            $seasonId     = urlencode($selectedSeason['season_id']);
                                            $baseUrl      = $_ENV['BASE_URL'];
                                        ?>

                                        <tr>

                                            <td><?= $index + 1; ?></td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    trim(
                                                        $employee['first_name']
                                                        . ' '
                                                        . $employee['father_name']
                                                        . ' '
                                                        . $employee['g_father_name']
                                                    )
                                                ); ?>
                                            </td>

                                            <!-- Status Badge -->
                                            <td>
                                                <?php if ($hasFile): ?>
                                                    <span class="badge badge-success">
                                                        Available
                                                    </span>
                                                <?php elseif ($isManual): ?>
                                                    <span class="badge badge-info">
                                                        Manually Confirmed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">
                                                        Not Attached
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Uploaded Date -->
                                            <td>
                                                <?php if (!empty($employee['uploaded_at'])): ?>
                                                    <?= date('d M Y', strtotime($employee['uploaded_at'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Actions -->
                                            <td>

                                                <?php if ($hasFile): ?>

                                                    <a href="<?= $baseUrl; ?>/bsc-plan/view/<?= urlencode($employee['file_id']); ?>"
                                                        class="btn btn-info btn-sm">
                                                        View
                                                    </a>

                                                    <a href="<?= $baseUrl; ?>/bsc-plan/replace/<?= $employeeUuid; ?>/<?= $seasonId; ?>"
                                                        class="btn btn-warning btn-sm">
                                                        Replace
                                                    </a>

                                                <?php elseif ($isManual): ?>

                                                    <a href="<?= $baseUrl; ?>/bsc-plan/upload/<?= $employeeUuid; ?>/<?= $seasonId; ?>"
                                                        class="btn btn-primary btn-sm">
                                                        Upload File
                                                    </a>

                                                    <button
                                                        type="button"
                                                        class="btn btn-danger btn-sm btn-unconfirm"
                                                        data-uuid="<?= htmlspecialchars($employee['uuid']); ?>"
                                                        data-season="<?= htmlspecialchars($selectedSeason['season_id']); ?>">
                                                        Undo
                                                    </button>

                                                <?php else: ?>

                                                    <a href="<?= $baseUrl; ?>/bsc-plan/upload/<?= $employeeUuid; ?>/<?= $seasonId; ?>"
                                                        class="btn btn-primary btn-sm">
                                                        Attach Plan
                                                    </a>

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary btn-sm btn-mark-confirmed"
                                                        data-uuid="<?= htmlspecialchars($employee['uuid']); ?>"
                                                        data-season="<?= htmlspecialchars($selectedSeason['season_id']); ?>">
                                                        Mark as Attached
                                                    </button>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script nonce="<?= htmlspecialchars($GLOBALS['nonce']); ?>">

document.addEventListener('DOMContentLoaded', function () {

    const seasonSelect =
        document.getElementById('seasonSelect');

    if (!seasonSelect) {
        return;
    }

    seasonSelect.addEventListener(
        'change',
        function () {

            if (!this.value) {
                return;
            }

            window.location.href =
                '<?= rtrim($_ENV['BASE_URL'], '/'); ?>'
                + '/bsc-plan-management/'
                + this.value;
        }
    );

    // Mark as Attached
    document.querySelectorAll('.btn-mark-confirmed').forEach(function (btn) {
        btn.addEventListener('click', function () {

            const uuid   = this.dataset.uuid;
            const season = this.dataset.season;

            if (!confirm('Mark this employee BSC plan as manually confirmed?')) {
                return;
            }

            fetch('<?= $_ENV['BASE_URL']; ?>/bsc-plan/mark-confirmed', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    employee_uuid: uuid,
                    season_id: season
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update status.');
                }
            })
            .catch(function () {
                alert('An error occurred. Please try again.');
            });

        });
    });

    // Undo Manual Confirmation
    document.querySelectorAll('.btn-unconfirm').forEach(function (btn) {
        btn.addEventListener('click', function () {

            const uuid   = this.dataset.uuid;
            const season = this.dataset.season;

            if (!confirm('Remove the manually confirmed status for this employee?')) {
                return;
            }

            fetch('<?= $_ENV['BASE_URL']; ?>/bsc-plan/unconfirm', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    employee_uuid: uuid,
                    season_id: season
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update status.');
                }
            })
            .catch(function () {
                alert('An error occurred. Please try again.');
            });

        });
    });

});

</script>