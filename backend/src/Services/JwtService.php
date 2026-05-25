<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Config;
use RuntimeException;

final class JwtService
{
    public function encode(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $claims = array_merge($payload, [
            'iss' => Config::get('JWT_ISSUER', 'the-resonanz'),
            'aud' => Config::get('JWT_AUDIENCE', 'the-resonanz-users'),
            'iat' => $now,
            'exp' => $now + (int) Config::get('JWT_TTL', 86400),
        ]);

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret(), true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Token structure is invalid.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $signature = $this->base64UrlDecode($encodedSignature);
        $expectedSignature = hash_hmac(
            'sha256',
            $encodedHeader . '.' . $encodedPayload,
            $this->secret(),
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Token signature does not match.');
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);

        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Token has expired.');
        }

        return $payload;
    }

    private function secret(): string
    {
        $secret = (string) Config::get('JWT_SECRET', '');

        if ($secret === '') {
            throw new RuntimeException('JWT secret is not configured.');
        }

        return $secret;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('Failed to decode token.');
        }

        return $decoded;
    }
}
