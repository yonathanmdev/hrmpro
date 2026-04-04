<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\AuditHelper;

class AuditController extends BaseController {

    public function showAuditLogs() {
        AuthHelper::checkRole(['system_admin']); // Only system admins can view audit logs

        $filters = [];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Apply filters from GET parameters
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = $_GET['user_id'];
        }
        if (!empty($_GET['action'])) {
            $filters['action'] = $_GET['action'];
        }
        if (!empty($_GET['entity_type'])) {
            $filters['entity_type'] = $_GET['entity_type'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'] . ' 00:00:00';
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'] . ' 23:59:59';
        }

        $logs = AuditHelper::getLogs($filters, $limit, $offset);

        // Get statistics for the last 30 days
        $dateFrom = date('Y-m-d H:i:s', strtotime('-30 days'));
        $stats = AuditHelper::getStats($dateFrom);

        $this->render('audit-logs', [
            'title' => 'የኦዲት ሎግ',
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $filters,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    public function getAuditStats() {
        AuthHelper::checkRole(['system_admin']);

        header('Content-Type: application/json');

        $dateFrom = !empty($_GET['date_from']) ? $_GET['date_from'] . ' 00:00:00' : null;
        $dateTo = !empty($_GET['date_to']) ? $_GET['date_to'] . ' 23:59:59' : null;

        $stats = AuditHelper::getStats($dateFrom, $dateTo);

        echo json_encode([
            'status' => 'success',
            'stats' => $stats
        ]);
    }
}