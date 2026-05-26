<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;

final class PaymentController
{
    private Order $orders;

    public function __construct(Database $database)
    {
        $this->orders = new Order($database);
    }

    public function checkout(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $transactionData = $request->body;

        if (!is_array($transactionData) || $transactionData === []) {
            Response::json([
                'error' => 'Payment payload is required.',
            ], 400);
        }

        $orderNumber = trim((string) (($transactionData['transaction_details']['order_id'] ?? '')));
        $grossAmount = (int) round((float) (($transactionData['transaction_details']['gross_amount'] ?? 0)));

        if ($orderNumber === '' || $grossAmount <= 0) {
            Response::json([
                'error' => 'transaction_details.order_id and gross_amount are required.',
            ], 422);
        }

        $order = $this->orders->findByOrderNumber($orderNumber);

        if ($order === null || (int) ($order['userId'] ?? 0) !== $userId) {
            Response::json([
                'error' => 'Order not found.',
            ], 404);
        }

        $expectedAmount = (int) round((float) ($order['totalAmount'] ?? 0));

        if ($grossAmount !== $expectedAmount) {
            Response::json([
                'error' => 'Payment amount does not match the order total.',
            ], 422);
        }

        $transactionData['transaction_details']['gross_amount'] = $expectedAmount;

        $url = (string) Config::get('MIDTRANS_BASE_URL', '');
        $serverKey = (string) Config::get('MIDTRANS_SERVER_KEY', '');

        if ($url === '' || $serverKey === '') {
            Response::json([
                'error' => 'Missing Midtrans configuration',
                'details' => 'MIDTRANS_BASE_URL and MIDTRANS_SERVER_KEY are required',
            ], 500);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($transactionData),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            Response::json([
                'error' => 'cURL error',
                'details' => curl_error($ch),
            ], 500);
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decoded = json_decode((string) $response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            Response::json(is_array($decoded) ? $decoded : [], $httpCode);
        }

        Response::json([
            'error' => 'Failed to process checkout',
            'details' => is_array($decoded) ? $decoded : $response,
        ], $httpCode > 0 ? $httpCode : 502);
    }

    public function midtransStatus(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $orderNumber = trim((string) ($request->query['order_id'] ?? ''));

        if ($orderNumber === '') {
            Response::json([
                'error' => 'order_id is required',
            ], 400);
        }

        $order = $this->orders->findByOrderNumber($orderNumber);

        if ($order === null || (int) ($order['userId'] ?? 0) !== $userId) {
            Response::json([
                'error' => 'Order not found.',
            ], 404);
        }

        $serverKey = (string) Config::get('MIDTRANS_SERVER_KEY', '');

        if ($serverKey === '') {
            Response::json([
                'error' => 'Missing Midtrans configuration',
                'details' => 'MIDTRANS_SERVER_KEY is required',
            ], 500);
        }

        $apiBase = rtrim((string) Config::get('MIDTRANS_API_BASE_URL', 'https://api.sandbox.midtrans.com'), '/');
        $statusUrl = sprintf('%s/v2/%s/status', $apiBase, rawurlencode($orderNumber));

        $ch = curl_init($statusUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            Response::json([
                'error' => 'cURL error',
                'details' => curl_error($ch),
            ], 500);
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decoded = json_decode((string) $response, true);

        if ($httpCode === 200 && is_array($decoded)) {
            $transactionStatus = (string) ($decoded['transaction_status'] ?? '');
            $this->orders->applyMidtransStatus((int) $order['id'], $transactionStatus);

            $updated = $this->orders->find((int) $order['id']);

            Response::json([
                'midtrans' => $decoded,
                'order' => $updated,
            ]);
        }

        Response::json([
            'error' => 'Failed to get payment status',
            'details' => is_array($decoded) ? $decoded : $response,
        ], $httpCode > 0 ? $httpCode : 502);
    }

    public function webhook(Request $request): never
    {
        $payload = $request->body;
        $orderNumber = trim((string) ($payload['order_id'] ?? ''));
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');

        if ($orderNumber !== '' && $transactionStatus !== '') {
            $order = $this->orders->findByOrderNumber($orderNumber);

            if ($order !== null) {
                $this->orders->applyMidtransStatus((int) $order['id'], $transactionStatus);
            }
        }

        Response::json([
            'message' => 'Webhook received.',
        ]);
    }
}
