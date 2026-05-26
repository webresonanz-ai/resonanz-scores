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

    public function allApproved(): array
    {
        $statement = $this->db->query(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating, approval_status
             FROM scores
             WHERE approval_status = "approved"
             ORDER BY rating DESC, title ASC'
        );

        return $statement->fetchAll();
    }

    public function byComposerId(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating, approval_status, reviewed_at
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
            'INSERT INTO scores (user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating, approval_status)
             VALUES (:user_id, :title, :composer, :arranger, :is_arranged, :genre, :difficulty, :price, :image, :pdf_path, :description, :pages, 0, :approval_status)'
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
            'approval_status' => $payload['approval_status'] ?? 'pending',
        ]);

        $id = (int) $this->db->lastInsertId();
        $created = $this->find($id);

        return $created ?? [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating, approval_status, reviewed_at
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

    public function findApproved(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, composer, arranger, is_arranged, genre, difficulty, price, image, pdf_path, description, pages, rating, approval_status
             FROM scores
             WHERE id = :id AND approval_status = "approved"
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
        ]);

        $score = $statement->fetch();

        return is_array($score) ? $score : null;
    }

    public function pendingWithComposers(): array
    {
        $statement = $this->db->query(
            'SELECT s.id,
                    s.user_id AS userId,
                    s.title,
                    s.composer,
                    s.arranger,
                    s.is_arranged AS isArranged,
                    s.genre,
                    s.difficulty,
                    s.price,
                    s.image,
                    s.description,
                    s.pages,
                    s.approval_status AS approvalStatus,
                    s.created_at AS createdAt,
                    u.name AS submitterName,
                    u.email AS submitterEmail
             FROM scores s
             INNER JOIN users u ON u.id = s.user_id
             WHERE s.approval_status = "pending"
             ORDER BY s.created_at ASC'
        );

        return $statement->fetchAll();
    }

    public function findPendingById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, title, approval_status
             FROM scores
             WHERE id = :id AND approval_status = :status
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'pending',
        ]);
        $score = $statement->fetch();

        return is_array($score) ? $score : null;
    }

    public function markReviewed(int $id, string $status, int $reviewerId): void
    {
        $statement = $this->db->prepare(
            'UPDATE scores
             SET approval_status = :status, reviewed_by = :reviewed_by, reviewed_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => $status,
            'reviewed_by' => $reviewerId,
        ]);
    }

    public function syncComposerWorkCount(int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE composers c
             JOIN (
                 SELECT user_id, COUNT(*) AS work_count
                 FROM scores
                 WHERE user_id = :user_id AND approval_status = "approved"
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
