<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use PHPUnit\Framework\TestCase;
use ZxArt\Users\PasswordResetTokenService;

final class PasswordResetTokenServiceTest extends TestCase
{
    private PasswordResetTokenService $service;

    protected function setUp(): void
    {
        $this->service = new PasswordResetTokenService();
    }

    public function testCreatedTokenIsValidDuringItsLifetime(): void
    {
        $token = $this->service->create(42, 'User@example.com', 'password-hash', 1234, 10_000);

        self::assertTrue($this->service->isValid(
            $token,
            42,
            'user@example.com',
            'password-hash',
            1234,
            10_000 + PasswordResetTokenService::LIFETIME_SECONDS,
        ));
    }

    public function testExpiredTokenIsRejected(): void
    {
        $token = $this->service->create(42, 'user@example.com', 'password-hash', 1234, 10_000);

        self::assertFalse($this->service->isValid(
            $token,
            42,
            'user@example.com',
            'password-hash',
            1234,
            10_001 + PasswordResetTokenService::LIFETIME_SECONDS,
        ));
    }

    public function testTamperedTokenIsRejected(): void
    {
        $token = $this->service->create(42, 'user@example.com', 'password-hash', 1234, 10_000);

        self::assertFalse($this->service->isValid(
            $token . '0',
            42,
            'user@example.com',
            'password-hash',
            1234,
            10_001,
        ));
    }

    public function testPasswordOrModificationChangeInvalidatesToken(): void
    {
        $token = $this->service->create(42, 'user@example.com', 'old-hash', 1234, 10_000);

        self::assertFalse($this->service->isValid(
            $token,
            42,
            'user@example.com',
            'new-hash',
            1234,
            10_001,
        ));
        self::assertFalse($this->service->isValid(
            $token,
            42,
            'user@example.com',
            'old-hash',
            1235,
            10_001,
        ));
    }
}
