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
}
