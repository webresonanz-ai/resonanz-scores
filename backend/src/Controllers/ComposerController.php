<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Composer;

final class ComposerController
{
    private Composer $composers;

    public function __construct(Database $database)
    {
        $this->composers = new Composer($database);
    }

    public function index(Request $request): never
    {
        Response::json([
            'data' => $this->composers->all(),
        ]);
    }
}
