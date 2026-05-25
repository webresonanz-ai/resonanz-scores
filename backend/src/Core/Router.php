<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\CorsMiddleware;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly CorsMiddleware $corsMiddleware)
    {
    }

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function dispatch(Request $request): void
    {
        $this->corsMiddleware->handle($request);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method || $route['path'] !== $request->path) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                $middleware->handle($request);
            }

            $route['handler']($request);
            return;
        }

        Response::json([
            'message' => 'Route not found.',
        ], 404);
    }

    private function addRoute(string $method, string $path, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }
}
