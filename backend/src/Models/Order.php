<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
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
                'price' => number_format($price, 2, '.', ''),
            ];
            $totalAmount += $price;
        }

        try {
            $this->db->beginTransaction();

            $orderStatement = $this->db->prepare(
                'INSERT INTO orders (user_id, total_items, total_amount, status, payment_status)
                 VALUES (:user_id, :total_items, :total_amount, :status, :payment_status)'
            );
            $orderStatement->execute([
                'user_id' => $userId,
                'total_items' => count($lineItems),
                'total_amount' => number_format($totalAmount, 2, '.', ''),
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
            'SELECT id, total_items AS totalItems, total_amount AS totalAmount, status, payment_status AS paymentStatus, created_at AS createdAt
             FROM orders
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );
        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, user_id, total_items AS totalItems, total_amount AS totalAmount, status, payment_status AS paymentStatus, created_at AS createdAt
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
            'SELECT id, score_id AS scoreId, score_title AS scoreTitle, price
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );
        $statement->execute([
            'order_id' => $orderId,
        ]);

        return $statement->fetchAll();
    }
}
