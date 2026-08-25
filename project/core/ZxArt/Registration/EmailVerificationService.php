<?php

declare(strict_types=1);

namespace ZxArt\Registration;

use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use ZxArt\Registration\Dto\EmailVerificationRequestDto;
use ZxArt\Registration\Dto\EmailVerificationResultDto;
use ZxArt\Registration\Exception\EmailVerificationException;

/**
 * Applies the link the registration email carries: it marks the address
 * verified and signs the visitor in, so an account is usable straight from the
 * mailbox.
 */
readonly class EmailVerificationService
{
    public function __construct(
        private Connection $database,
        private CurrentUserService $currentUserService,
        private EmailVerificationTokenService $tokenService,
    ) {
    }

    public function verify(EmailVerificationRequestDto $request): EmailVerificationResultDto
    {
        $email = trim($request->email);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new EmailVerificationException('verify-email.error-invalid', 422);
        }
        if (!$this->tokenService->isValid($email, $request->key)) {
            throw new EmailVerificationException('verify-email.error-invalid', 403);
        }

        $user = $this->getUserByEmail($email);
        if ($user === null) {
            throw new EmailVerificationException('verify-email.error-unknown-account', 404);
        }

        $userId = (int)$user['id'];
        if ((int)$user['verified'] === 1) {
            return new EmailVerificationResultDto(true, 'verify-email.already-verified');
        }

        $this->database->table('module_user')->where('id', '=', $userId)->update(['verified' => 1]);
        $this->currentUserService->getCurrentUser()->switchUser($userId, false);

        return new EmailVerificationResultDto(true, 'verify-email.verified');
    }

    /** @return array<string, mixed>|null */
    private function getUserByEmail(string $email): ?array
    {
        $row = $this->database->table('module_user')
            ->where('email', '=', $email)
            ->where('banned', '=', 0)
            ->first();

        return $row === null ? null : (array)$row;
    }
}
