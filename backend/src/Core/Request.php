<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files,
        public readonly array $headers,
        public array $attributes = [],
    ) {
    }

    public static function capture(): self
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isMultipart = str_contains(strtolower($contentType), 'multipart/form-data');
        $rawBody = file_get_contents('php://input');
        $decodedBody = json_decode($rawBody ?: '[]', true);
        $body = $isMultipart ? $_POST : (is_array($decodedBody) ? $decodedBody : []);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            rtrim($uri, '/') ?: '/',
            $_GET,
            is_array($body) ? $body : [],
            self::normalizeFiles($_FILES ?? []),
            self::headers(),
        );
    }

    public function header(string $key, ?string $default = null): ?string
    {
        $lookup = strtolower($key);

        return $this->headers[$lookup] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('authorization');
        if ($header === null || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return substr($header, 7);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ? $file
            : null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    private static function headers(): array
    {
        $headers = [];

        foreach (getallheaders() as $key => $value) {
            $headers[strtolower($key)] = $value;
        }

        return $headers;
    }

    private static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $file) {
            if (!is_array($file) || !array_key_exists('name', $file)) {
                continue;
            }

            $normalized[$key] = $file;
        }

        return $normalized;
    }
}
