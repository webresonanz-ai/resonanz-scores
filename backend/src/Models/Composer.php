<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Composer
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function all(): array
    {
        $statement = $this->db->query(
            'SELECT id, name, period, nationality, image, works, biography, featured_work AS featuredWork
             FROM composers
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id,
                    user_id AS userId,
                    name,
                    period,
                    nationality,
                    image,
                    works,
                    biography,
                    featured_work AS featuredWork
             FROM composers
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $composer = $statement->fetch();

        return $composer ?: null;
    }

    public function createOrSyncApprovedComposer(int $userId, string $name): void
    {
        $statement = $this->db->prepare('SELECT id FROM composers WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $existingId = $statement->fetchColumn();

        if ($existingId !== false) {
            $update = $this->db->prepare('UPDATE composers SET name = :name WHERE id = :id');
            $update->execute([
                'id' => $existingId,
                'name' => $name,
            ]);
            return;
        }

        $insert = $this->db->prepare(
            'INSERT INTO composers (user_id, name, period, nationality, image, works, biography, featured_work)
             VALUES (:user_id, :name, :period, :nationality, :image, :works, :biography, :featured_work)'
        );
        $insert->execute([
            'user_id' => $userId,
            'name' => $name,
            'period' => 'To be updated',
            'nationality' => 'To be updated',
            'image' => 'https://picsum.photos/400/300?random=200',
            'works' => 0,
            'biography' => 'Composer profile is being prepared.',
            'featured_work' => 'To be updated',
        ]);
    }

    public function updateProfile(int $userId, array $payload): void
    {
        $statement = $this->db->prepare(
            'UPDATE composers
             SET name = :name,
                 period = :period,
                 nationality = :nationality,
                 image = :image,
                 biography = :biography,
                 featured_work = :featured_work
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => $payload['name'],
            'period' => $payload['period'],
            'nationality' => $payload['nationality'],
            'image' => $payload['image'],
            'biography' => $payload['biography'],
            'featured_work' => $payload['featured_work'],
        ]);
    }
}
