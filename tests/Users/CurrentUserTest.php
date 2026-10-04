<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use App\Users\CurrentUser;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ServerSessionManager;

final class CurrentUserTest extends TestCase
{
    /**
     * An instance that never read the session has nothing to write back. If it
     * did, its empty storage would wipe the signed-in user out of the session
     * the real instance shares with it.
     */
    public function testUninitializedUserLeavesSessionUntouchedWhenDestroyed(): void
    {
        $session = $this->createMock(ServerSessionManager::class);
        $session->expects(self::never())->method('set');
        $session->expects(self::never())->method('delete');

        $user = new CurrentUser($this->createStub(privilegesManager::class), $session);
        unset($user);
    }
}
