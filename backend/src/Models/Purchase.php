<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Purchase
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT p.id, s.title, p.purchase_date AS purchaseDate, p.price
             FROM purchases p
             INNER JOIN scores s ON s.id = p.score_id
             WHERE p.user_id = :user_id
             ORDER BY p.purchase_date DESC'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }
}
