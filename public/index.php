<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (($_ENV['APP_ENV'] ?? '') === 'local') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$db = \App\Config\Database::getConnection();

/* ---------------- ROUTES ---------------- */
$baseRoutes = [
    'login' => ['AuthController', 'showLoginForm', false],
    'login_process' => ['AuthController', 'handleLogin', false],
    'dashboard' => ['DashboardController', 'index', true],
];

$teddyRoutes = require __DIR__ . '/../src/Routes/Teddyroutes.php';
$yoniRoutes  = require __DIR__ . '/../src/Routes/Yoniroutes.php';

$routes = array_merge($baseRoutes, $teddyRoutes, $yoniRoutes);

/* ---------------- ROUTING FIX ---------------- */

// Get full action string
$rawAction = $_GET['action'] ?? 'login';

// Normalize
$rawAction = trim($rawAction, '/');

// Split into segments
$segments = explode('/', $rawAction);

// Route name
$action = $segments[0] ?? 'login';

/* ---------------- DEBUG (temporary if needed) */
// var_dump($segments); die();

/* ---------------- PARAMS ---------------- */
$params = [
    'uuid' => $segments[1] ?? null,
    'record_id' => $segments[2] ?? null,
    'extra' => array_slice($segments, 3)
];

/* ---------------- ROUTE CHECK ---------------- */
if (!isset($routes[$action])) {
    header("Location: " . rtrim($_ENV['BASE_URL'], '/') . "/login");
    exit();
}

[$controllerName, $method, $requiresAuth] = $routes[$action];

/* ---------------- AUTH ---------------- */
if ($requiresAuth) {
    \App\Controllers\AuthController::checkAuth();
}

/* ---------------- CONTROLLER ---------------- */
$controllerClass = "\\App\\Controllers\\$controllerName";

if (!class_exists($controllerClass)) {
    die("Controller '$controllerClass' not found");
}

$controller = new $controllerClass($db);

/* ---------------- METHOD CALL ---------------- */
if (!method_exists($controller, $method)) {
    die("Method '$method' not found in $controllerClass");
}

$ref = new ReflectionMethod($controller, $method);

if ($ref->getNumberOfParameters() > 0) {
    $controller->$method($params);
} else {
    $controller->$method();
}
