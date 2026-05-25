<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Score;

final class ScoreController
{
    private Score $scores;

    public function __construct(Database $database)
    {
        $this->scores = new Score($database);
    }

    public function index(Request $request): never
    {
        Response::json([
            'data' => $this->scores->all(),
        ]);
    }
}
