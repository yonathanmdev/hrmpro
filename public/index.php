<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// ለዴቨሎፕመንት ጊዜ ብቻ
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. የዳታቤዝ ኮኔክሽን
$db = \App\Config\Database::getConnection();

/**
 * 2. የRoute ካርታ (Routes Map)
 */
$baseRoutes = [
    // Authentication
    'login'                         => ['AuthController', 'showLoginForm', false],
    'login_process'                 => ['AuthController', 'handleLogin', false],
    'dashboard'                     => ['DashboardController', 'index', true],

    // User Management
    'register-user'                 => ['UserController', 'showRegisterForm', true],
    'register-process'              => ['UserController', 'handleRegistration', true],

    // Organization Management
    'register-organization'         => ['OrgController', 'showRegisterForm', true],
    'register-organization-process' => ['OrgController', 'handleRegistration', true],
    'update-organization-process'   => ['OrgController', 'handleEditOrganization', true],
    'register-branch'               => ['OrgController', 'showRegisterForm', true],
    'register-branch-process'       => ['OrgController', 'handleBranchRegistration', true],
];

// የውጭ የRoute ፋይሎችን እዚህ ጋር ኢንክሉድ እናደርጋለን
$teddyRoutes = require_once __DIR__ . '/../src/Routes/Teddyroutes.php';
$yoniRoutes  = require_once __DIR__ . '/../src/Routes/Yoniroutes.php';

// ሁሉንም Route በአንድ ላይ ቀላቅል (Merge)
$routes = array_merge($baseRoutes, $teddyRoutes, $yoniRoutes);

// 3. Action መቀበል
$action = $_GET['action'] ?? 'login';

// 4. Route መኖሩን ማረጋገጥ
if (!isset($routes[$action])) {
    header("Location: /HRM/login");
    exit();
}

[$controllerName, $method, $requiresAuth] = $routes[$action];

// 5. የAuth ማረጋገጫ (Centralized)
if ($requiresAuth) {
    \App\Controllers\AuthController::checkAuth();
}

// 6. Controller-ን በዳይናሚክ መንገድ መጥራት
$controllerClass = "\\App\\Controllers\\$controllerName";

if (class_exists($controllerClass)) {
    // እዚህ ጋር $db ን ወደ ኮንስትራክተሩ እንልካለን
    $controller = new $controllerClass($db);
    
    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        die("ስህተት: ሜተዱ '$method' በክላሱ '$controllerName' ውስጥ አልተገኘም።");
    }
} else {
    die("ስህተት: Controller ክላሱ '$controllerClass' አልተገኘም።");
}