<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;

final class CreatorPrivilegesGranterTest extends TestCase
{
    public function testGrantsEveryListedActionToTheCurrentUserOnTheElement(): void
    {
        $granted = [];
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('setPrivilege')->willReturnCallback(
            function (int $userId, int $elementId, string $type, string $action, string $kind) use (&$granted): void {
                $granted[] = "$userId:$elementId:$type.$action:$kind";
            },
        );
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        (new CreatorPrivilegesGranter($privilegesManager, $currentUserService))
            ->grant(42, [['author', 'publicReceive'], ['file', 'delete']]);

        self::assertSame(['7:42:author.publicReceive:allow', '7:42:file.delete:allow'], $granted);
    }
}
