<?php

declare(strict_types=1);

namespace ZxArt\Tests\Playlists;

use LanguagesManager;
use linksManager;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Playlists\Exception\PlaylistException;
use ZxArt\Playlists\PlaylistService;
use ZxArt\Playlists\Repositories\PlaylistContentsRepository;

final class PlaylistServiceTest extends TestCase
{
    public function testAnonymousUserCannotLoadPlaylists(): void
    {
        $service = new PlaylistService(
            $this->createStub(structureManager::class),
            $this->createStub(LanguagesManager::class),
            $this->createStub(PlaylistContentsRepository::class),
            $this->createStub(linksManager::class),
            $this->createStub(privilegesManager::class),
        );

        try {
            $service->getForUser(0);
            self::fail('Expected PlaylistException');
        } catch (PlaylistException $exception) {
            self::assertSame(401, $exception->getStatusCode());
        }
    }
}
