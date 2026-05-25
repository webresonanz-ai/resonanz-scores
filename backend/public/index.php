<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Config;
use App\Controllers\AuthController;
use App\Controllers\ComposerController;
use App\Controllers\HealthController;
use App\Controllers\PurchaseController;
use App\Controllers\ScoreController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Services\JwtService;

Config::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/.env.example', false);

$database = new Database();
$jwtService = new JwtService();
$authMiddleware = new AuthMiddleware($jwtService);
$corsMiddleware = new CorsMiddleware();

$router = new Router($corsMiddleware);

$healthController = new HealthController();
$authController = new AuthController($database, $jwtService);
$scoreController = new ScoreController($database);
$composerController = new ComposerController($database);
$purchaseController = new PurchaseController($database);

$router->get('/api/health', [$healthController, 'index']);
$router->post('/api/auth/register', [$authController, 'register']);
$router->post('/api/auth/login', [$authController, 'login']);
$router->get('/api/auth/me', [$authController, 'me'], [$authMiddleware]);
$router->get('/api/scores', [$scoreController, 'index']);
$router->get('/api/composers', [$composerController, 'index']);
$router->get('/api/purchases', [$purchaseController, 'index'], [$authMiddleware]);

$router->dispatch(Request::capture());
