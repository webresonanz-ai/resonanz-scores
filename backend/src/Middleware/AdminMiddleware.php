<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

final class AdminMiddleware
{
    private User $users;

    public function __construct(Database $database)
    {
        $this->users = new User($database);
    }

    public function handle(Request $request): void
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $user = $this->users->findById($userId);

        if ($user === null || ($user['role'] ?? '') !== 'admin') {
            Response::json([
                'message' => 'Admin access only.',
            ], 403);
        }

        $request->setAttribute('currentUser', $user);
    }
}
