<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Users\ClaimedAuthorService;

final class ClaimedAuthorServiceTest extends TestCase
{
    public function testInvalidSourceAuthorDoesNotQueryOrChangeUsers(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects(self::never())->method('getElementById');
        $database = $this->createMock(Connection::class);
        $database->expects(self::never())->method('table');

        (new ClaimedAuthorService($structureManager, $database))->reassign(0, 42);
    }
}
