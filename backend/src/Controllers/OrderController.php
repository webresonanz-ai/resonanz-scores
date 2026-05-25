<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use Throwable;

final class OrderController
{
    private Order $orders;

    public function __construct(Database $database)
    {
        $this->orders = new Order($database);
    }

    public function index(Request $request): never
    {
        $auth = $request->attribute('auth', []);

        Response::json([
            'data' => $this->orders->forUser((int) ($auth['sub'] ?? 0)),
        ]);
    }

    public function store(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $items = $request->input('items', []);

        if (!is_array($items) || $items === []) {
            Response::json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        try {
            $created = $this->orders->create($userId, $items);
        } catch (Throwable $exception) {
            Response::json([
                'message' => 'Unable to create order.',
            ], 500);
        }

        if ($created === []) {
            Response::json([
                'message' => 'Some cart items could not be processed.',
            ], 422);
        }

        Response::json([
            'message' => 'Order created. Waiting for payment.',
            'data' => $created,
        ], 201);
    }
}
