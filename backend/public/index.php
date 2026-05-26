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
use App\Controllers\ComposerProfileController;
use App\Controllers\ComposerRequestController;
use App\Controllers\HealthController;
use App\Controllers\OrderController;
use App\Controllers\PurchaseController;
use App\Controllers\ScoreController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Services\JwtService;
use App\Services\MailService;

Config::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/.env.example', false);

$database = new Database();
$jwtService = new JwtService();
$mailService = new MailService();
$authMiddleware = new AuthMiddleware($jwtService);
$adminMiddleware = new AdminMiddleware($database);
$corsMiddleware = new CorsMiddleware();

$router = new Router($corsMiddleware);

$healthController = new HealthController();
$authController = new AuthController($database, $jwtService);
$scoreController = new ScoreController($database);
$composerController = new ComposerController($database);
$composerProfileController = new ComposerProfileController($database);
$composerRequestController = new ComposerRequestController($database, $mailService);
$purchaseController = new PurchaseController($database);
$orderController = new OrderController($database);

$router->get('/api/health', [$healthController, 'index']);
$router->get('/api/score', [$scoreController, 'show']);
$router->get('/api/score-image', [$scoreController, 'image']);
$router->get('/api/score-pdf-preview', [$scoreController, 'pdfPreview']);
$router->post('/api/auth/register', [$authController, 'register']);
$router->post('/api/auth/login', [$authController, 'login']);
$router->get('/api/auth/me', [$authController, 'me'], [$authMiddleware]);
$router->get('/api/scores', [$scoreController, 'index']);
$router->get('/api/composer/scores', [$scoreController, 'mine'], [$authMiddleware]);
$router->post('/api/composer/scores', [$scoreController, 'store'], [$authMiddleware]);
$router->get('/api/composer/profile', [$composerProfileController, 'show'], [$authMiddleware]);
$router->post('/api/composer/profile', [$composerProfileController, 'update'], [$authMiddleware]);
$router->get('/api/composers', [$composerController, 'index']);
$router->get('/api/composer-requests/me', [$composerRequestController, 'mine'], [$authMiddleware]);
$router->post('/api/composer-requests', [$composerRequestController, 'submit'], [$authMiddleware]);
$router->get('/api/admin/composer-requests', [$composerRequestController, 'index'], [$authMiddleware, $adminMiddleware]);
$router->post('/api/admin/composer-requests/approve', [$composerRequestController, 'approve'], [$authMiddleware, $adminMiddleware]);
$router->post('/api/admin/composer-requests/decline', [$composerRequestController, 'decline'], [$authMiddleware, $adminMiddleware]);
$router->get('/api/purchases', [$purchaseController, 'index'], [$authMiddleware]);
$router->get('/api/orders', [$orderController, 'index'], [$authMiddleware]);
$router->post('/api/orders', [$orderController, 'store'], [$authMiddleware]);

$router->dispatch(Request::capture());
