<?php

declare(strict_types=1);

namespace ZxArt\Users;

readonly class PasswordResetTokenService
{
    public const int LIFETIME_SECONDS = 3600;

    public function create(
        int $userId,
        string $email,
        string $passwordHash,
        int $dateModified,
        ?int $issuedAt = null,
    ): string {
        $issuedAt ??= time();
        $signature = hash_hmac(
            'sha256',
            $this->payload($userId, $email, $dateModified, $issuedAt),
            $passwordHash,
        );

        return $issuedAt . '.' . $signature;
    }

    public function isValid(
        string $token,
        int $userId,
        string $email,
        string $passwordHash,
        int $dateModified,
        ?int $now = null,
    ): bool {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            return false;
        }

        $issuedAt = (int)$parts[0];
        $now ??= time();
        if ($issuedAt > $now || $issuedAt < $now - self::LIFETIME_SECONDS) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $this->payload($userId, $email, $dateModified, $issuedAt),
            $passwordHash,
        );

        return hash_equals($expected, $parts[1]);
    }

    private function payload(int $userId, string $email, int $dateModified, int $issuedAt): string
    {
        return implode('|', [$userId, strtolower($email), $dateModified, $issuedAt]);
    }
}
