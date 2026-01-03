<?php

// Define base path
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Simple PSR-4 style autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load Config if exists
if (file_exists(ROOT_PATH . '/config.php')) {
    require_once ROOT_PATH . '/config.php';
}

session_start();

// Basic Routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check if installer is needed
if (!file_exists(ROOT_PATH . '/storage/install.lock') && strpos($uri, '/install') !== 0) {
    header('Location: /install/index.php');
    exit;
}

if (strpos($uri, '/install') === 0) {
    if (file_exists(ROOT_PATH . '/storage/install.lock')) {
        die("Application is already installed.");
    }
    require_once ROOT_PATH . '/install/index.php';
    exit;
}

use App\Core\Router;
use App\Middleware\AuthMiddleware;

$router = new Router();

// Define Routes
// Auth (Public)
$router->get('/login', ['App\Controllers\AuthController', 'login']);
$router->post('/login', ['App\Controllers\AuthController', 'login']);
$router->get('/logout', ['App\Controllers\AuthController', 'logout']);
$router->get('/register', ['App\Controllers\AuthController', 'register']);
$router->post('/register', ['App\Controllers\AuthController', 'register']);
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Protected Routes
// We don't have a middleware pipeline in our simple Router, so we'll wrap or check manually in Controller
// Or we can add a helper to route protected routes.
// For Phase 01, simple check at top of protected controllers or methods is fine,
// but let's try to be cleaner.
// I'll add a helper function `protectedRoute`?
// Or just check in the callback.

function protectedRoute($callback) {
    return function() use ($callback) {
        $middleware = new AuthMiddleware();
        $middleware->handle();

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $action = $callback[1];
            return $controller->$action();
        }
        return call_user_func($callback);
    };
}

$router->get('/dashboard', protectedRoute(['App\Controllers\DashboardController', 'index']));
$router->get('/onboarding', protectedRoute(['App\Controllers\OnboardingController', 'index']));
$router->post('/onboarding', protectedRoute(['App\Controllers\OnboardingController', 'complete']));
$router->get('/admin', protectedRoute(['App\Controllers\DashboardController', 'admin'])); // Minimal admin

$router->dispatch($uri);
