<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Composer;
use App\Models\User;
use PDO;
use Throwable;

final class ComposerProfileController
{
    private Composer $composers;
    private User $users;
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->composers = new Composer($database);
        $this->users = new User($database);
        $this->db = $database->connection();
    }

    public function show(Request $request): never
    {
        $userId = $this->ensureComposer($request);
        $profile = $this->composers->findByUserId($userId);

        if ($profile === null) {
            Response::json([
                'message' => 'Composer profile not found.',
            ], 404);
        }

        Response::json([
            'data' => $profile,
        ]);
    }

    public function update(Request $request): never
    {
        $userId = $this->ensureComposer($request);
        $profile = $this->composers->findByUserId($userId);

        if ($profile === null) {
            Response::json([
                'message' => 'Composer profile not found.',
            ], 404);
        }

        $name = trim((string) $request->input('name', ''));
        $period = trim((string) $request->input('period', ''));
        $nationality = trim((string) $request->input('nationality', ''));
        $image = trim((string) $request->input('image', ''));
        $biography = trim((string) $request->input('biography', ''));
        $featuredWork = trim((string) $request->input('featured_work', ''));

        if ($name === '' || $period === '' || $nationality === '' || $image === '' || $biography === '' || $featuredWork === '') {
            Response::json([
                'message' => 'Please complete all composer profile fields.',
            ], 422);
        }

        try {
            $this->db->beginTransaction();
            $this->composers->updateProfile($userId, [
                'name' => $name,
                'period' => $period,
                'nationality' => $nationality,
                'image' => $image,
                'biography' => $biography,
                'featured_work' => $featuredWork,
            ]);
            $this->users->updateName($userId, $name);
            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Response::json([
                'message' => 'Failed to update composer profile.',
                'error' => $throwable->getMessage(),
            ], 500);
        }

        Response::json([
            'message' => 'Composer profile updated successfully.',
            'data' => $this->composers->findByUserId($userId),
        ]);
    }

    private function ensureComposer(Request $request): int
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $user = $this->users->findById($userId);

        if ($user === null) {
            Response::json([
                'message' => 'User not found.',
            ], 404);
        }

        if (($user['role'] ?? '') !== 'composer') {
            Response::json([
                'message' => 'Composer access is required for this action.',
            ], 403);
        }

        return $userId;
    }
}
