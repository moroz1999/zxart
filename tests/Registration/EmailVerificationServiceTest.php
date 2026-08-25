<?php

declare(strict_types=1);

namespace ZxArt\Tests\Registration;

use App\Users\CurrentUserService;
use ConfigManager;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ZxArt\Registration\Dto\EmailVerificationRequestDto;
use ZxArt\Registration\EmailVerificationService;
use ZxArt\Registration\EmailVerificationTokenService;
use ZxArt\Registration\Exception\EmailVerificationException;

final class EmailVerificationServiceTest extends TestCase
{
    public function testMalformedAddressIsRejectedBeforeAnyLookup(): void
    {
        try {
            $this->createService()->verify(new EmailVerificationRequestDto('not-an-email', 'whatever'));
            self::fail('Expected EmailVerificationException');
        } catch (EmailVerificationException $exception) {
            self::assertSame(422, $exception->getStatusCode());
            self::assertSame('verify-email.error-invalid', $exception->getMessage());
        }
    }

    public function testWrongSignatureIsRejectedBeforeAnyLookup(): void
    {
        try {
            $this->createService()->verify(new EmailVerificationRequestDto('user@example.com', 'forged'));
            self::fail('Expected EmailVerificationException');
        } catch (EmailVerificationException $exception) {
            self::assertSame(403, $exception->getStatusCode());
            self::assertSame('verify-email.error-invalid', $exception->getMessage());
        }
    }

    private function createService(): EmailVerificationService
    {
        $configManager = $this->createStub(ConfigManager::class);
        $configManager->method('get')->willReturn('test secret');

        return new EmailVerificationService(
            $this->createStub(Connection::class),
            $this->createStub(CurrentUserService::class),
            new EmailVerificationTokenService($configManager),
        );
    }
}
