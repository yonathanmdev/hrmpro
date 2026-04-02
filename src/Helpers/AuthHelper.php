<?php
// src/Helpers/AuthHelper.php
namespace App\Helpers;

class AuthHelper {

    /**
     * Check if current user has one of the allowed roles
     *
     * @param array $allowedRoles Example: ['system_admin','org_admin']
     */
    public static function checkRole(array $allowedRoles) {
        $userRole = $_SESSION['user']['role'] ?? null;

        if (!in_array($userRole, $allowedRoles)) {
            $_SESSION['error'] = "Access Denied: You do not have permission to perform this action.";
            header("Location: /HRM/login");
            exit();
        }
    }
}