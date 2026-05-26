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

    public function show(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $orderId = (int) ($request->query['id'] ?? 0);

        if ($orderId <= 0) {
            Response::json([
                'error' => 'Order id is required.',
            ], 400);
        }

        $order = $this->orders->findForUser($orderId, $userId);

        if ($order === null) {
            Response::json([
                'error' => 'Order not found.',
            ], 404);
        }

        Response::json([
            'data' => $order,
        ]);
    }

    public function byOrderNumber(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $orderNumber = trim((string) ($request->query['order_number'] ?? ''));

        if ($orderNumber === '') {
            Response::json([
                'error' => 'order_number is required.',
            ], 400);
        }

        $order = $this->orders->findByOrderNumber($orderNumber);

        if ($order === null || (int) ($order['userId'] ?? 0) !== $userId) {
            Response::json([
                'error' => 'Order not found.',
            ], 404);
        }

        $order = $this->orders->findForUser((int) $order['id'], $userId);

        Response::json([
            'data' => $order,
        ]);
    }

    public function cancel(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $orderId = (int) $request->input('order_id', 0);

        if ($orderId <= 0) {
            Response::json([
                'error' => 'order_id is required.',
            ], 422);
        }

        $order = $this->orders->findForUser($orderId, $userId);

        if ($order === null) {
            Response::json([
                'error' => 'Order not found.',
            ], 404);
        }

        if (!$this->orders->isAwaitingPayment($order)) {
            Response::json([
                'error' => 'Only pending orders awaiting payment can be cancelled.',
            ], 422);
        }

        if (!$this->orders->cancel($orderId)) {
            Response::json([
                'error' => 'Unable to cancel order.',
            ], 500);
        }

        $updated = $this->orders->findForUser($orderId, $userId);

        Response::json([
            'message' => 'Order cancelled.',
            'data' => $updated,
        ]);
    }
}
