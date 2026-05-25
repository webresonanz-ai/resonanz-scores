<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Composer;
use App\Models\ComposerRequest;
use App\Models\User;
use App\Services\MailService;
use PDO;
use Throwable;

final class ComposerRequestController
{
    private User $users;
    private Composer $composers;
    private ComposerRequest $requests;
    private PDO $db;

    public function __construct(Database $database, private readonly MailService $mailService)
    {
        $this->users = new User($database);
        $this->composers = new Composer($database);
        $this->requests = new ComposerRequest($database);
        $this->db = $database->connection();
    }

    public function mine(Request $request): never
    {
        $user = $this->currentUser($request);

        Response::json([
            'data' => $this->requests->findLatestForUser((int) $user['id']),
        ]);
    }

    public function submit(Request $request): never
    {
        $user = $this->currentUser($request);
        $role = (string) ($user['role'] ?? 'customer');

        if ($role === 'admin') {
            Response::json([
                'message' => 'Admin accounts cannot submit composer requests.',
            ], 422);
        }

        if ($role === 'composer') {
            Response::json([
                'message' => 'You are already a composer.',
            ], 422);
        }

        if ($this->requests->hasPendingForUser((int) $user['id'])) {
            Response::json([
                'message' => 'You already have a pending composer request.',
            ], 409);
        }

        $requestId = $this->requests->create((int) $user['id']);

        Response::json([
            'message' => 'Composer request submitted successfully.',
            'data' => $this->requests->findLatestForUser((int) $user['id']),
            'requestId' => $requestId,
        ], 201);
    }

    public function index(Request $request): never
    {
        Response::json([
            'data' => $this->requests->pendingWithUsers(),
        ]);
    }

    public function approve(Request $request): never
    {
        $this->review($request, 'approved');
    }

    public function decline(Request $request): never
    {
        $this->review($request, 'declined');
    }

    private function review(Request $request, string $status): never
    {
        $admin = $this->currentUser($request);
        $requestId = (int) $request->input('requestId', 0);

        if ($requestId <= 0) {
            Response::json([
                'message' => 'Request ID is required.',
            ], 422);
        }

        $composerRequest = $this->requests->findPendingById($requestId);

        if ($composerRequest === null) {
            Response::json([
                'message' => 'Composer request not found or already reviewed.',
            ], 404);
        }

        $user = $this->users->findById((int) $composerRequest['userId']);

        if ($user === null) {
            Response::json([
                'message' => 'Requested user not found.',
            ], 404);
        }

        try {
            $this->db->beginTransaction();
            $this->requests->markReviewed($requestId, $status, (int) $admin['id']);

            if ($status === 'approved') {
                $this->users->updateRole((int) $user['id'], 'composer');
                $this->composers->createOrSyncApprovedComposer((int) $user['id'], (string) $user['name']);
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Response::json([
                'message' => 'Failed to review composer request.',
                'error' => $throwable->getMessage(),
            ], 500);
        }

        $emailSent = $this->mailService->sendComposerRequestDecision(
            (string) $user['email'],
            (string) $user['name'],
            $status
        );

        $message = $status === 'approved'
            ? 'Composer request approved.'
            : 'Composer request declined.';

        if (!$emailSent) {
            $message .= ' The account was updated, but the notification email could not be sent.';
        }

        Response::json([
            'message' => $message,
            'emailSent' => $emailSent,
        ]);
    }

    private function currentUser(Request $request): array
    {
        $currentUser = $request->attribute('currentUser');

        if (is_array($currentUser)) {
            return $currentUser;
        }

        $auth = $request->attribute('auth', []);
        $user = $this->users->findById((int) ($auth['sub'] ?? 0));

        if ($user === null) {
            Response::json([
                'message' => 'User not found.',
            ], 404);
        }

        return $user;
    }
}
