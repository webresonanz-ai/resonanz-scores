<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

final class StaffMiddleware
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
        $role = (string) ($user['role'] ?? '');

        if ($user === null || !in_array($role, ['admin', 'manager'], true)) {
            Response::json([
                'message' => 'Admin or manager access only.',
            ], 403);
        }

        $request->setAttribute('currentUser', $user);
    }
}
