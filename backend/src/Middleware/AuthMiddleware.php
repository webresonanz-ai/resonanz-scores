<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\JwtService;
use Throwable;

final class AuthMiddleware
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function handle(Request $request): void
    {
        $token = $request->bearerToken();

        if ($token === null) {
            Response::json([
                'message' => 'Missing authorization token.',
            ], 401);
        }

        try {
            $payload = $this->jwtService->decode($token);
            $request->setAttribute('auth', $payload);
        } catch (Throwable $throwable) {
            Response::json([
                'message' => 'Invalid or expired token.',
                'error' => $throwable->getMessage(),
            ], 401);
        }
    }
}
