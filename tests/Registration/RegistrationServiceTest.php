<?php

declare(strict_types=1);

namespace ZxArt\Tests\Registration;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use LanguagesManager;
use linksManager;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ServerSessionManager;
use structureManager;
use translationsManager;
use ZxArt\Email\EmailValidationService;
use ZxArt\Registration\Dto\RegistrationRequestDto;
use ZxArt\Registration\Exception\RegistrationException;
use ZxArt\Registration\RegistrationService;

final class RegistrationServiceTest extends TestCase
{
    public function testMissingRequiredFieldsReturnValidationError(): void
    {
        $service = $this->createService('anonymous');

        try {
            $service->register(new RegistrationRequestDto('', '', '', ''));
            self::fail('Expected RegistrationException');
        } catch (RegistrationException $exception) {
            self::assertSame(422, $exception->getStatusCode());
            self::assertSame('userdata.bad_form_data', $exception->getMessage());
        }
    }

    public function testAuthenticatedUserCannotRegisterAgain(): void
    {
        $service = $this->createService('existing-user');

        try {
            $service->register(new RegistrationRequestDto('user', 'user@example.com', 'secret', 'secret'));
            self::fail('Expected RegistrationException');
        } catch (RegistrationException $exception) {
            self::assertSame(409, $exception->getStatusCode());
        }
    }

    private function createService(string $userName): RegistrationService
    {
        $user = new CurrentUser(
            $this->createStub(privilegesManager::class),
            $this->createStub(ServerSessionManager::class),
        );
        $user->userName = $userName;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);
        $translationsManager = $this->createStub(translationsManager::class);
        $translationsManager->method('getTranslationByName')
            ->willReturnCallback(static fn(string $key): string => $key);

        return new RegistrationService(
            $this->createStub(structureManager::class),
            $this->createStub(LanguagesManager::class),
            $currentUserService,
            $this->createStub(EmailValidationService::class),
            $this->createStub(linksManager::class),
            $translationsManager,
        );
    }
}
