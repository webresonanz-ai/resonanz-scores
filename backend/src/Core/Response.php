<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function file(string $path, string $contentType): never
    {
        http_response_code(200);
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
        exit;
    }

    public static function previewFile(string $path, string $contentType, string $filename = 'preview'): never
    {
        http_response_code(200);
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}
