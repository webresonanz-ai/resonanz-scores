<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\JwtService;

final class AuthController
{
    private User $users;

    public function __construct(Database $database, private readonly JwtService $jwtService)
    {
        $this->users = new User($database);
    }

    public function register(Request $request): never
    {
        $name = trim((string) $request->input('name', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $location = trim((string) $request->input('location', ''));
        $bio = trim((string) $request->input('bio', 'Music Enthusiast'));

        if ($name === '' || $email === '' || $password === '') {
            Response::json([
                'message' => 'Name, email, and password are required.',
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json([
                'message' => 'Email format is invalid.',
            ], 422);
        }

        if (strlen($password) < 6) {
            Response::json([
                'message' => 'Password must be at least 6 characters long.',
            ], 422);
        }

        if ($this->users->findByEmail($email) !== null) {
            Response::json([
                'message' => 'Email is already registered.',
            ], 409);
        }

        $userId = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'customer',
            'location' => $location,
            'bio' => $bio,
        ]);

        $user = $this->users->findById($userId);
        $token = $this->jwtService->encode([
            'sub' => $userId,
            'email' => $email,
        ]);

        Response::json([
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => $this->users->sanitize($user),
        ], 201);
    }

    public function login(Request $request): never
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            Response::json([
                'message' => 'Email and password are required.',
            ], 422);
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            Response::json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $this->jwtService->encode([
            'sub' => (int) $user['id'],
            'email' => $user['email'],
        ]);

        Response::json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->users->sanitize($user),
        ]);
    }

    public function me(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $user = $this->users->findById((int) ($auth['sub'] ?? 0));

        if ($user === null) {
            Response::json([
                'message' => 'User not found.',
            ], 404);
        }

        Response::json([
            'user' => $this->users->sanitize($user),
        ]);
    }
}
