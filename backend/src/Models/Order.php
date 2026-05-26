<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Support\Currency;
use PDO;
use Throwable;

final class Order
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connection();
    }

    public function create(int $userId, array $items): array
    {
        $scoreIds = array_values(array_unique(array_map(
            static fn (array $item): int => (int) ($item['score_id'] ?? 0),
            $items,
        )));

        if ($scoreIds === []) {
            return [];
        }

        $scores = $this->findScoresForCheckout($scoreIds);

        if (count($scores) !== count($scoreIds)) {
            return [];
        }

        $mappedScores = [];
        foreach ($scores as $score) {
            $mappedScores[(int) $score['id']] = $score;
        }

        $lineItems = [];
        $totalAmount = 0.0;

        foreach ($scoreIds as $scoreId) {
            if (!isset($mappedScores[$scoreId])) {
                return [];
            }

            $score = $mappedScores[$scoreId];
            $price = (float) $score['price'];
            $lineItems[] = [
                'score_id' => $scoreId,
                'score_title' => (string) $score['title'],
                'price' => Currency::formatStorage($price),
            ];
            $totalAmount += $price;
        }

        $orderNumber = $this->generateOrderNumber();

        try {
            $this->db->beginTransaction();

            $orderStatement = $this->db->prepare(
                'INSERT INTO orders (order_number, user_id, total_items, total_amount, status, payment_status)
                 VALUES (:order_number, :user_id, :total_items, :total_amount, :status, :payment_status)'
            );
            $orderStatement->execute([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'total_items' => count($lineItems),
                'total_amount' => Currency::formatStorage($totalAmount),
                'status' => 'pending',
                'payment_status' => 'waiting_payment',
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare(
                'INSERT INTO order_items (order_id, score_id, score_title, price)
                 VALUES (:order_id, :score_id, :score_title, :price)'
            );

            foreach ($lineItems as $lineItem) {
                $itemStatement->execute([
                    'order_id' => $orderId,
                    'score_id' => $lineItem['score_id'],
                    'score_title' => $lineItem['score_title'],
                    'price' => $lineItem['price'],
                ]);
            }

            $this->db->commit();

            return $this->find($orderId) ?? [];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, order_number AS orderNumber, total_items AS totalItems, total_amount AS totalAmount, status, payment_status AS paymentStatus, created_at AS createdAt
             FROM orders
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );
        $statement->execute([
            'user_id' => $userId,
        ]);

        $orders = $statement->fetchAll();

        foreach ($orders as &$order) {
            $order['items'] = $this->itemsForOrder((int) $order['id']);
        }

        unset($order);

        return $orders;
    }

    public function userCanDownloadScore(int $userId, int $scoreId): bool
    {
        $orderStatement = $this->db->prepare(
            'SELECT 1
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = :user_id
               AND oi.score_id = :score_id
               AND o.payment_status = :payment_status
             LIMIT 1'
        );
        $orderStatement->execute([
            'user_id' => $userId,
            'score_id' => $scoreId,
            'payment_status' => 'paid',
        ]);

        if ($orderStatement->fetchColumn()) {
            return true;
        }

        $purchaseStatement = $this->db->prepare(
            'SELECT 1 FROM purchases WHERE user_id = :user_id AND score_id = :score_id LIMIT 1'
        );
        $purchaseStatement->execute([
            'user_id' => $userId,
            'score_id' => $scoreId,
        ]);

        return (bool) $purchaseStatement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, order_number AS orderNumber, user_id AS userId, total_items AS totalItems, total_amount AS totalAmount, status, payment_status AS paymentStatus, created_at AS createdAt
             FROM orders
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
        ]);

        $order = $statement->fetch();

        if (!is_array($order)) {
            return null;
        }

        $order['items'] = $this->itemsForOrder((int) $order['id']);

        return $order;
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, order_number AS orderNumber, user_id AS userId, total_items AS totalItems, total_amount AS totalAmount, status, payment_status AS paymentStatus, created_at AS createdAt
             FROM orders
             WHERE order_number = :order_number
             LIMIT 1'
        );
        $statement->execute([
            'order_number' => $orderNumber,
        ]);

        $order = $statement->fetch();

        return is_array($order) ? $order : null;
    }

    public function findForUser(int $orderId, int $userId): ?array
    {
        $order = $this->find($orderId);

        if ($order === null || (int) ($order['userId'] ?? 0) !== $userId) {
            return null;
        }

        unset($order['userId']);

        return $order;
    }

    public function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        $statement = $this->db->prepare(
            'UPDATE orders SET payment_status = :payment_status WHERE id = :id'
        );

        return $statement->execute([
            'id' => $orderId,
            'payment_status' => $paymentStatus,
        ]);
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $statement = $this->db->prepare(
            'UPDATE orders SET status = :status WHERE id = :id'
        );

        return $statement->execute([
            'id' => $orderId,
            'status' => $status,
        ]);
    }

    public function applyMidtransStatus(int $orderId, string $transactionStatus): void
    {
        $normalized = strtolower(trim($transactionStatus));

        if (in_array($normalized, ['settlement', 'capture'], true)) {
            $this->updatePaymentStatus($orderId, 'paid');
            $this->updateStatus($orderId, 'paid');
            return;
        }

        if (in_array($normalized, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $this->updatePaymentStatus($orderId, 'failed');
            $this->updateStatus($orderId, 'cancelled');
        }
    }

    public function cancel(int $orderId): bool
    {
        $statement = $this->db->prepare(
            "UPDATE orders
             SET status = 'cancelled', payment_status = 'failed'
             WHERE id = :id
               AND status = 'pending'
               AND payment_status = 'waiting_payment'"
        );

        $statement->execute([
            'id' => $orderId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function isAwaitingPayment(array $order): bool
    {
        return ($order['status'] ?? '') === 'pending'
            && ($order['paymentStatus'] ?? $order['payment_status'] ?? '') === 'waiting_payment';
    }

    private function generateOrderNumber(): string
    {
        return 'SMS-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    private function findScoresForCheckout(array $scoreIds): array
    {
        $placeholders = implode(', ', array_fill(0, count($scoreIds), '?'));
        $statement = $this->db->prepare(
            "SELECT id, title, price
             FROM scores
             WHERE id IN ($placeholders)"
        );
        $statement->execute($scoreIds);

        return $statement->fetchAll();
    }

    private function itemsForOrder(int $orderId): array
    {
        $statement = $this->db->prepare(
            'SELECT oi.id,
                    oi.score_id AS scoreId,
                    oi.score_title AS scoreTitle,
                    oi.price,
                    CASE
                      WHEN s.pdf_path IS NOT NULL
                       AND s.pdf_path <> \'\'
                       AND s.pdf_path NOT LIKE \'http%\'
                      THEN 1
                      ELSE 0
                    END AS hasPdfDownload
             FROM order_items oi
             LEFT JOIN scores s ON s.id = oi.score_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id ASC'
        );
        $statement->execute([
            'order_id' => $orderId,
        ]);

        $items = $statement->fetchAll();

        foreach ($items as &$item) {
            $item['hasPdfDownload'] = (bool) ($item['hasPdfDownload'] ?? false);
        }

        unset($item);

        return $items;
    }
}
