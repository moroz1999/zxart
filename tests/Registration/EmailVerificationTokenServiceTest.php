<?php

declare(strict_types=1);

namespace ZxArt\Tests\Registration;

use ConfigManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Registration\EmailVerificationTokenService;

final class EmailVerificationTokenServiceTest extends TestCase
{
    public function testAcceptsItsOwnToken(): void
    {
        $service = $this->createService();

        self::assertTrue($service->isValid('user@example.com', $service->create('user@example.com')));
    }

    public function testRejectsTokenIssuedForAnotherAddress(): void
    {
        $service = $this->createService();

        self::assertFalse($service->isValid('other@example.com', $service->create('user@example.com')));
    }

    public function testRejectsEmptyToken(): void
    {
        self::assertFalse($this->createService()->isValid('user@example.com', ''));
    }

    /**
     * Links mailed before the SPA route existed carry a plain HMAC of the address,
     * so the service must keep accepting exactly that value.
     */
    public function testAcceptsTokenFromLegacyLink(): void
    {
        $legacyToken = hash_hmac('sha256', 'user@example.com', 'test secret');

        self::assertTrue($this->createService()->isValid('user@example.com', $legacyToken));
    }

    private function createService(): EmailVerificationTokenService
    {
        $configManager = $this->createStub(ConfigManager::class);
        $configManager->method('get')->willReturn('test secret');

        return new EmailVerificationTokenService($configManager);
    }
}
