<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (name, email, password, role, location, bio) VALUES (:name, :email, :password, :role, :location, :bio)'
        );

        $statement->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateRole(int $userId, string $role): void
    {
        $statement = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        $statement->execute([
            'id' => $userId,
            'role' => $role,
        ]);
    }

    public function updateName(int $userId, string $name): void
    {
        $statement = $this->db->prepare('UPDATE users SET name = :name WHERE id = :id');
        $statement->execute([
            'id' => $userId,
            'name' => $name,
        ]);
    }

    public function sanitize(?array $user): ?array
    {
        if ($user === null) {
            return null;
        }

        unset($user['password']);

        return $user;
    }
}
