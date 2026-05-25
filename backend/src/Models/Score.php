<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Score
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function all(): array
    {
        $statement = $this->db->query(
            'SELECT id, title, composer, genre, difficulty, price, image, description, pages, rating
             FROM scores
             ORDER BY rating DESC, title ASC'
        );

        return $statement->fetchAll();
    }
}
