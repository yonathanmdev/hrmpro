<?php
namespace App\Helpers;

class AuditHelper {
    private static $auditLogModel = null;

    /**
     * Initialize the audit log model
     */
    public static function init($db) {
        if (self::$auditLogModel === null) {
            self::$auditLogModel = new \App\Models\AuditLog($db);
        }
    }

    /**
     * Log a user action
     */
    public static function log($action, $entityType, $entityId = null, $oldValues = null, $newValues = null, $metadata = []) {
        if (self::$auditLogModel === null) {
            throw new \Exception("AuditHelper not initialized. Call AuditHelper::init(\$db) first.");
        }

        $userId = $_SESSION['user']['id'] ?? null;

        self::$auditLogModel->log($userId, $action, $entityType, $entityId, $oldValues, $newValues, $metadata);
    }

    /**
     * Log user creation
     */
    public static function logUserCreation($userId, $userData) {
        self::log('user_created', 'user', $userId, null, $userData);
    }

    /**
     * Log user update
     */
    public static function logUserUpdate($userId, $oldData, $newData) {
        self::log('user_updated', 'user', $userId, $oldData, $newData);
    }

    /**
     * Log user deletion
     */
    public static function logUserDeletion($userId, $userData) {
        self::log('user_deleted', 'user', $userId, $userData, null);
    }

    /**
     * Log login
     */
    public static function logLogin($userId, $success = true) {
        $action = $success ? 'login_success' : 'login_failed';
        self::log($action, 'auth', $userId);
    }

    /**
     * Log logout
     */
    public static function logLogout($userId) {
        self::log('logout', 'auth', $userId);
    }

    /**
     * Log organization creation
     */
    public static function logOrgCreation($orgId, $orgData) {
        self::log('organization_created', 'organization', $orgId, null, $orgData);
    }

    /**
     * Log organization update
     */
    public static function logOrgUpdate($orgId, $oldData, $newData) {
        self::log('organization_updated', 'organization', $orgId, $oldData, $newData);
    }

    /**
     * Log branch creation
     */
    public static function logBranchCreation($branchId, $branchData) {
        self::log('branch_created', 'branch', $branchId, null, $branchData);
    }

    /**
     * Log branch update
     */
    public static function logBranchUpdate($branchId, $oldData, $newData) {
        self::log('branch_updated', 'branch', $branchId, $oldData, $newData);
    }

    /**
     * Log director creation
     */
    public static function logDirectorCreation($directorId, $directorData) {
        self::log('director_created', 'director', $directorId, null, $directorData);
    }

    /**
     * Log director update
     */
    public static function logDirectorUpdate($directorId, $oldData, $newData) {
        self::log('director_updated', 'director', $directorId, $oldData, $newData);
    }

    /**
     * Log position creation
     */
    public static function logPositionCreation($positionId, $positionData) {
        self::log('position_created', 'position', $positionId, null, $positionData);
    }

    /**
     * Log position update
     */
    public static function logPositionUpdate($positionId, $oldData, $newData) {
        self::log('position_updated', 'position', $positionId, $oldData, $newData);
    }

    /**
     * Log developer creation
     */
    public static function logDeveloperCreation($developerId, $developerData) {
        self::log('developer_created', 'developer', $developerId, null, $developerData);
    }

    /**
     * Log developer update
     */
    public static function logDeveloperUpdate($developerId, $oldData, $newData) {
        self::log('developer_updated', 'developer', $developerId, $oldData, $newData);
    }

    /**
     * Log employee registration
     */
    public static function logEmployeeRegistration($employeeId, $employeeData) {
        self::log('employee_registered', 'employee', $employeeId, null, $employeeData);
    }

    /**
     * Log employee job change
     */
    public static function logEmployeeJobChange($employeeId, $jobChangeData) {
        self::log('employee_job_changed', 'employee', $employeeId, null, $jobChangeData, [
            'change_type' => 'job_assignment'
        ]);
    }
     public static function logOnBoardingEmployeeApproval($employeeId, $jobChangeData) {
        self::log('employee_hiring_approved', 'employee', $employeeId, null, $jobChangeData, [
            'change_type' => 'hiring_approved'
        ]);
    }
      public static function logOnLeaveEmployeeApproval($employeeId, $jobChangeData) {
        self::log('employee_scholarship_approved', 'employee', $employeeId, null, $jobChangeData, [
            'change_type' => 'Scholarship_approved'
        ]);
    }

    /**
     * Get audit logs
     */
    public static function getLogs($filters = [], $limit = 100, $offset = 0) {
        if (self::$auditLogModel === null) {
            throw new \Exception("AuditHelper not initialized.");
        }

        return self::$auditLogModel->getLogs($filters, $limit, $offset);
    }

    /**
     * Get audit statistics
     */
    public static function getStats($dateFrom = null, $dateTo = null) {
        if (self::$auditLogModel === null) {
            throw new \Exception("AuditHelper not initialized.");
        }

        return self::$auditLogModel->getStats($dateFrom, $dateTo);
    }
}