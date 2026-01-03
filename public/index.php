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

require_once APP_PATH . '/Core/functions.php';

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
use App\Middleware\LocaleMiddleware;

// Initialize Locale
$localeMiddleware = new LocaleMiddleware();
$localeMiddleware->handle();

$router = new Router();

// Define Routes
// Auth (Public)
$router->get('/login', ['App\Controllers\AuthController', 'login']);
$router->post('/login', ['App\Controllers\AuthController', 'login']);
$router->get('/logout', ['App\Controllers\AuthController', 'logout']);
$router->get('/register', ['App\Controllers\AuthController', 'register']);
$router->post('/register', ['App\Controllers\AuthController', 'register']);

// Language Switcher Route
$router->get('/language/switch', ['App\Controllers\LanguageController', 'switch']); // Use query param ?code=es

$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Protected Routes
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
$router->get('/admin', protectedRoute(['App\Controllers\DashboardController', 'admin']));

// Admin Language Routes
$router->get('/admin/languages', protectedRoute(['App\Controllers\Admin\LanguageController', 'index']));
$router->post('/admin/languages/toggle', protectedRoute(['App\Controllers\Admin\LanguageController', 'toggle']));
$router->get('/admin/languages/export', protectedRoute(['App\Controllers\Admin\LanguageController', 'export']));
$router->post('/admin/languages/import', protectedRoute(['App\Controllers\Admin\LanguageController', 'import']));

$router->dispatch($uri);
