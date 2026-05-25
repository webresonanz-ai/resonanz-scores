<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Config;

final class MailService
{
    public function sendComposerRequestDecision(string $email, string $name, string $status): bool
    {
        $fromEmail = (string) Config::get('MAIL_FROM_EMAIL', '');
        $fromName = (string) Config::get('MAIL_FROM_NAME', 'The Resonanz');

        if ($fromEmail === '') {
            return false;
        }

        $decisionText = $status === 'approved' ? 'approved' : 'declined';
        $subject = sprintf('Your composer request was %s', $decisionText);
        $message = $this->buildDecisionMessage($name, $status);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('From: %s <%s>', $fromName, $fromEmail),
        ];

        return mail($email, $subject, $message, implode("\r\n", $headers));
    }

    private function buildDecisionMessage(string $name, string $status): string
    {
        if ($status === 'approved') {
            return sprintf(
                "Hello %s,\n\nYour request to become a composer on The Resonanz has been approved.\nYou can now continue with your composer account and complete your composer profile when the composer dashboard is available.\n\nRegards,\nThe Resonanz Team",
                $name
            );
        }

        return sprintf(
            "Hello %s,\n\nYour request to become a composer on The Resonanz has been declined.\nYou can submit a new request later from your profile if needed.\n\nRegards,\nThe Resonanz Team",
            $name
        );
    }
}
