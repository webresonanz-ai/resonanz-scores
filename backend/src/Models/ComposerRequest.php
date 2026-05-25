<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ComposerRequest
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function findLatestForUser(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id AS userId, status, requested_at AS requestedAt, reviewed_at AS reviewedAt
             FROM composer_requests
             WHERE user_id = :user_id
             ORDER BY id DESC
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $request = $statement->fetch();

        return $request ?: null;
    }

    public function hasPendingForUser(int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT id FROM composer_requests WHERE user_id = :user_id AND status = :status LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function create(int $userId): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO composer_requests (user_id, status) VALUES (:user_id, :status)'
        );
        $statement->execute([
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function pendingWithUsers(): array
    {
        $statement = $this->db->query(
            'SELECT cr.id,
                    cr.user_id AS userId,
                    cr.status,
                    cr.requested_at AS requestedAt,
                    u.name,
                    u.email,
                    u.location,
                    u.bio
             FROM composer_requests cr
             INNER JOIN users u ON u.id = cr.user_id
             WHERE cr.status = "pending"
             ORDER BY cr.requested_at ASC'
        );

        return $statement->fetchAll();
    }

    public function findPendingById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id AS userId, status FROM composer_requests WHERE id = :id AND status = :status LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'pending',
        ]);
        $request = $statement->fetch();

        return $request ?: null;
    }

    public function markReviewed(int $id, string $status, int $adminId): void
    {
        $statement = $this->db->prepare(
            'UPDATE composer_requests
             SET status = :status, admin_id = :admin_id, reviewed_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => $status,
            'admin_id' => $adminId,
        ]);
    }
}
