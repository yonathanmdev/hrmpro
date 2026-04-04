    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="totalLogs">0</h3>
                            <p>ጠቅላላ ሎጎች</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="userActions">0</h3>
                            <p>የተጠቃሚ ተግባራት</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="loginAttempts">0</h3>
                            <p>የሎጊን ሙከራዎች</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-log-in"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="failedLogins">0</h3>
                            <p>ያልተሳካ ሎጊኖች</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-close"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ፍለጋ እና ማጣሪያዎች</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ተግባር</label>
                                    <select name="action" class="form-control">
                                        <option value="">ሁሉም ተግባራት</option>
                                        <option value="user_created" <?= ($_GET['action'] ?? '') === 'user_created' ? 'selected' : '' ?>>ተጠቃሚ መመዝገቢያ</option>
                                        <option value="login_success" <?= ($_GET['action'] ?? '') === 'login_success' ? 'selected' : '' ?>>ሎጊን ተሳካ</option>
                                        <option value="login_failed" <?= ($_GET['action'] ?? '') === 'login_failed' ? 'selected' : '' ?>>ሎጊን አልተሳካም</option>
                                        <option value="logout" <?= ($_GET['action'] ?? '') === 'logout' ? 'selected' : '' ?>>ሎግአውት</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>እንግዳ አይነት</label>
                                    <select name="entity_type" class="form-control">
                                        <option value="">ሁሉም አይነቶች</option>
                                        <option value="user" <?= ($_GET['entity_type'] ?? '') === 'user' ? 'selected' : '' ?>>ተጠቃሚ</option>
                                        <option value="auth" <?= ($_GET['entity_type'] ?? '') === 'auth' ? 'selected' : '' ?>>ማረጋገጫ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ከቀን</label>
                                    <input type="date" name="date_from" class="form-control" value="<?= $_GET['date_from'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>እስከ ቀን</label>
                                    <input type="date" name="date_to" class="form-control" value="<?= $_GET['date_to'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">ፍለጋ</button>
                                <a href="?action=<?= $_GET['action'] ?? '' ?>&entity_type=<?= $_GET['entity_type'] ?? '' ?>" class="btn btn-secondary">አጽዳ</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">የኦዲት ሎጎች</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ቀን እና ሰዓት</th>
                                <th>ተጠቃሚ</th>
                                <th>ተግባር</th>
                                <th>እንግዳ አይነት</th>
                                <th>እንግዳ ID</th>
                                <th>IP አድራሻ</th>
                                <th>ዝርዝሮች</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                <td><?= htmlspecialchars($log['user_id'] ?? 'እንግዳ') ?></td>
                                <td>
                                    <?php
                                    $actionLabels = [
                                        'user_created' => 'ተጠቃሚ ተመዝግቧል',
                                        'user_updated' => 'ተጠቃሚ ተሻሽሏል',
                                        'user_deleted' => 'ተጠቃሚ ተሰረዟል',
                                        'login_success' => 'ሎጊን ተሳካ',
                                        'login_failed' => 'ሎጊን አልተሳካም',
                                        'logout' => 'ሎግአውት',
                                        'organization_created' => 'ድርጅት ተመዝግቧል',
                                        'organization_updated' => 'ድርጅት ተሻሽሏል',
                                        'branch_created' => 'ቅርንጫፍ ተመዝግቧል',
                                        'branch_updated' => 'ቅርንጫፍ ተሻሽሏል'
                                    ];
                                    echo $actionLabels[$log['action']] ?? $log['action'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $entityLabels = [
                                        'user' => 'ተጠቃሚ',
                                        'organization' => 'ድርጅት',
                                        'branch' => 'ቅርንጫፍ',
                                        'auth' => 'ማረጋገጫ'
                                    ];
                                    echo $entityLabels[$log['entity_type']] ?? $log['entity_type'];
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($log['entity_id'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                <td>
                                    <?php if ($log['new_values']): ?>
                                        <button class="btn btn-sm btn-info" onclick="showDetails(<?= $log['id'] ?>)">ዝርዝሮች</button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <!-- Pagination can be added here -->
                </div>
            </div>
        </div>
    </section>

<!-- Modal for log details -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">የሎግ ዝርዝሮች</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="logDetailsContent"></pre>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(logId) {
    // In a real implementation, you would fetch the log details via AJAX
    // For now, just show a placeholder
    document.getElementById('logDetailsContent').textContent = 'ዝርዝሮች እዚህ ይታያሉ...';
    $('#logDetailsModal').modal('show');
}

// Load statistics
$(document).ready(function() {
    $.get('?action=audit-stats', function(data) {
        if (data.status === 'success') {
            let totalLogs = 0;
            let userActions = 0;
            let loginAttempts = 0;
            let failedLogins = 0;

            data.stats.forEach(function(stat) {
                totalLogs += parseInt(stat.count);
                if (stat.action.includes('user_')) {
                    userActions += parseInt(stat.count);
                }
                if (stat.action.includes('login')) {
                    loginAttempts += parseInt(stat.count);
                    if (stat.action === 'login_failed') {
                        failedLogins += parseInt(stat.count);
                    }
                }
            });

            $('#totalLogs').text(totalLogs);
            $('#userActions').text(userActions);
            $('#loginAttempts').text(loginAttempts);
            $('#failedLogins').text(failedLogins);
        }
    });
});
</script>