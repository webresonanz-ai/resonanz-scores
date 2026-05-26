<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Score;
use App\Models\User;
use App\Services\MailService;
use PDO;
use Throwable;

final class ScoreApprovalController
{
    private User $users;
    private Score $scores;
    private PDO $db;

    public function __construct(Database $database, private readonly MailService $mailService)
    {
        $this->users = new User($database);
        $this->scores = new Score($database);
        $this->db = $database->connection();
    }

    public function index(Request $request): never
    {
        Response::json([
            'data' => $this->scores->pendingWithComposers(),
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
        $reviewer = $this->currentUser($request);
        $scoreId = (int) $request->input('scoreId', 0);

        if ($scoreId <= 0) {
            Response::json([
                'message' => 'Score ID is required.',
            ], 422);
        }

        $score = $this->scores->findPendingById($scoreId);

        if ($score === null) {
            Response::json([
                'message' => 'Composition not found or already reviewed.',
            ], 404);
        }

        $composer = $this->users->findById((int) ($score['user_id'] ?? 0));

        if ($composer === null) {
            Response::json([
                'message' => 'Composer account not found for this composition.',
            ], 404);
        }

        try {
            $this->db->beginTransaction();
            $this->scores->markReviewed($scoreId, $status, (int) $reviewer['id']);

            if ($status === 'approved') {
                $this->scores->syncComposerWorkCount((int) $score['user_id']);
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Response::json([
                'message' => 'Failed to review composition.',
                'error' => $throwable->getMessage(),
            ], 500);
        }

        $emailSent = $this->mailService->sendCompositionDecision(
            (string) $composer['email'],
            (string) $composer['name'],
            (string) $score['title'],
            $status
        );

        $message = $status === 'approved'
            ? 'Composition approved and published to the catalog.'
            : 'Composition declined.';

        if (!$emailSent) {
            $message .= ' The review was saved, but the notification email could not be sent.';
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
