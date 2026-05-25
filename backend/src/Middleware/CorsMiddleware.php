<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Config;
use App\Core\Request;
use App\Core\Response;

final class CorsMiddleware
{
    public function handle(Request $request): void
    {
        header('Access-Control-Allow-Origin: ' . Config::get('CORS_ALLOWED_ORIGIN', '*'));
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');

        if ($request->method === 'OPTIONS') {
            Response::json(['message' => 'OK']);
        }
    }
}
