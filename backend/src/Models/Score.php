<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Score
{
    public const DEFAULT_IMAGE = '/default-score-cover.svg';

    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function all(): array
    {
        $statement = $this->db->query(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating
             FROM scores
             ORDER BY rating DESC, title ASC'
        );

        return $statement->fetchAll();
    }

    public function byComposerId(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating
             FROM scores
             WHERE user_id = :user_id
             ORDER BY id DESC'
        );
        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function create(array $payload): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO scores (user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating)
             VALUES (:user_id, :title, :composer, :arranger, :is_arranged, :genre, :difficulty, :price, :image, :pdf_path, :description, :pages, 0)'
        );
        $statement->execute([
            'user_id' => $payload['user_id'],
            'title' => $payload['title'],
            'composer' => $payload['composer'],
            'arranger' => $payload['arranger'],
            'is_arranged' => $payload['is_arranged'],
            'genre' => $payload['genre'],
            'difficulty' => $payload['difficulty'],
            'price' => $payload['price'],
            'image' => $payload['image'],
            'pdf_path' => $payload['pdf_path'],
            'description' => $payload['description'],
            'pages' => $payload['pages'],
        ]);

        $id = (int) $this->db->lastInsertId();
        $created = $this->find($id);

        if ($created !== null) {
            $this->syncComposerWorkCount((int) $payload['user_id']);
        }

        return $created ?? [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating
             FROM scores
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
        ]);

        $score = $statement->fetch();

        return is_array($score) ? $score : null;
    }

    private function syncComposerWorkCount(int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE composers c
             JOIN (
                 SELECT user_id, COUNT(*) AS work_count
                 FROM scores
                 WHERE user_id = :user_id
                 GROUP BY user_id
             ) s ON s.user_id = c.user_id
             SET c.works = s.work_count
             WHERE c.user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId,
        ]);
    }
}
