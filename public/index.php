<?php
require_once __DIR__ . '/../vendor/autoload.php';

// 1. .env ፋይሉን መጫን
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// 2. ኮንዲሽኑን (Condition) እዚህ ጋር እንጠቀማለን
// 'SESSION_PATH' በ .env ውስጥ ተጽፎ ከሆነ እና ባዶ ካልሆነ
if (!empty($_ENV['SESSION_PATH'])) {
    
    // የፎልደሩን መንገድ ለ PHP ንገረው
    $path = $_ENV['SESSION_PATH'];

    // ፎልደሩ መኖሩን ቼክ አድርግ፣ ከሌለ ፍጠርለት
    if (!is_dir($path)) {
        mkdir($path, 0700, true);
    }

    // የሴሽን መቀመጫውን ቀይር
    session_save_path($path);
}

// 3. በመጨረሻ ሴሽኑን አስጀምር
session_start();

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