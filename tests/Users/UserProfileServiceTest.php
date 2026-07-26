<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ServerSessionManager;
use structureManager;
use ZxArt\Users\PasswordChangeResult;
use ZxArt\Users\UserProfileService;

final class UserProfileServiceTest extends TestCase
{
    public function testAnonymousUserHasNoProfileAndCannotChangePassword(): void
    {
        $currentUser = new CurrentUser(
            $this->createStub(privilegesManager::class),
            $this->createStub(ServerSessionManager::class),
        );
        $currentUser->id = 1;
        $currentUser->userName = 'anonymous';
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($currentUser);
        $service = new UserProfileService(
            $currentUserService,
            $this->createStub(structureManager::class),
        );

        self::assertNull($service->getProfile());
        self::assertSame(
            PasswordChangeResult::Unauthorized,
            $service->changePassword('old', 'new', 'new'),
        );
    }
}
