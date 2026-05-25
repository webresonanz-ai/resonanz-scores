<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Purchase;

final class PurchaseController
{
    private Purchase $purchases;

    public function __construct(Database $database)
    {
        $this->purchases = new Purchase($database);
    }

    public function index(Request $request): never
    {
        $auth = $request->attribute('auth', []);

        Response::json([
            'data' => $this->purchases->forUser((int) ($auth['sub'] ?? 0)),
        ]);
    }
}
