<?php
session_start();

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // project root
$dotenv->safeLoad(); // safeLoad avoids fatal error if .env missing

// Display errors for development only
if ($_ENV['APP_ENV'] === 'local') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Database connection
$db = \App\Config\Database::getConnection();

// Route map
$baseRoutes = [
    'login'                         => ['AuthController', 'showLoginForm', false],
    'login_process'                 => ['AuthController', 'handleLogin', false],
    'dashboard'                     => ['DashboardController', 'index', true],
    'register-user'                  => ['UserController', 'showRegisterForm', true],
    'register-process'               => ['UserController', 'handleRegistration', true],
    'register-organization'          => ['OrgController', 'showRegisterForm', true],
    'register-organization-process'  => ['OrgController', 'handleRegistration', true],
    'update-organization-process'    => ['OrgController', 'handleEditOrganization', true],
    'register-branch'                => ['OrgController', 'showRegisterForm', true],
    'register-branch-process'        => ['OrgController', 'handleBranchRegistration', true],
];

// Include extra routes if needed
$teddyRoutes = require_once __DIR__ . '/../src/Routes/Teddyroutes.php';
$yoniRoutes  = require_once __DIR__ . '/../src/Routes/Yoniroutes.php';

$routes = array_merge($baseRoutes, $teddyRoutes, $yoniRoutes);

// Get action from query string
$action = $_GET['action'] ?? 'login';

// Check route exists
if (!isset($routes[$action])) {
    header("Location: " . $_ENV['BASE_URL'] . "/login");
    exit();
}

[$controllerName, $method, $requiresAuth] = $routes[$action];

// Check authentication if required
if ($requiresAuth) {
    \App\Controllers\AuthController::checkAuth();
}

// Dynamic controller
$controllerClass = "\\App\\Controllers\\$controllerName";

if (!class_exists($controllerClass)) {
    die("Controller '$controllerClass' not found");
}

$controller = new $controllerClass($db);

if (!method_exists($controller, $method)) {
    die("Method '$method' not found in controller '$controllerName'");
}

$controller->$method();