<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use controller;
use EmailDispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use settingsManager;
use translationsManager;
use ZxArt\Users\PasswordReminderService;
use ZxArt\Users\PasswordResetTokenService;

final class PasswordReminderServiceTest extends TestCase
{
    public function testUnknownEmailReturnsGenericSuccessWithoutDispatchingEmail(): void
    {
        $query = $this->createStub(Builder::class);
        $query->method('where')->willReturnSelf();
        $query->method('first')->willReturn(null);
        $database = $this->createMock(Connection::class);
        $database->expects(self::once())->method('table')->with('module_user')->willReturn($query);
        $emailDispatcher = $this->createMock(EmailDispatcher::class);
        $emailDispatcher->expects(self::never())->method('getEmptyDispatchment');
        $controller = $this->createStub(controller::class);
        $controller->baseURL = 'https://zxart.ee/';
        $service = new PasswordReminderService(
            $database,
            $emailDispatcher,
            $this->createStub(settingsManager::class),
            $this->createStub(translationsManager::class),
            $controller,
            new PasswordResetTokenService(),
        );

        $result = $service->request('missing@example.com');

        self::assertTrue($result->success);
        self::assertSame('password-reminder.requested', $result->message);
    }
}
