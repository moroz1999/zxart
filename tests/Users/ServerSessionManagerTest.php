<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use App\Paths\PathsManager;
use PHPUnit\Framework\TestCase;
use ServerSessionManager;

final class ServerSessionManagerTest extends TestCase
{
    private function createManager(): ServerSessionManager
    {
        $pathsManager = $this->createStub(PathsManager::class);
        $pathsManager->method('getPath')->willReturn(null);

        $manager = new ServerSessionManager($pathsManager);
        $manager->setSessionName('public');
        $manager->setEnabled(true);

        return $manager;
    }

    public function testReadingWithoutAnExistingSessionDoesNotStartOne(): void
    {
        unset($_COOKIE['public']);
        $manager = $this->createManager();

        self::assertNull($manager->get('storage'));
        self::assertNull($manager->getAll());
        self::assertNull($manager->getSessionId());
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testDeletingWithoutAnExistingSessionDoesNotStartOne(): void
    {
        unset($_COOKIE['public']);
        $manager = $this->createManager();

        $manager->delete('storage');

        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testDisabledManagerReadsNothing(): void
    {
        $_COOKIE['public'] = 'an-existing-session-id';
        $manager = $this->createManager();
        $manager->setEnabled(false);

        self::assertNull($manager->get('storage'));
        self::assertSame(PHP_SESSION_NONE, session_status());

        unset($_COOKIE['public']);
    }
}
