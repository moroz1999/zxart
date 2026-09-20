<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

#[AllowMockObjectsWithoutExpectations]
class AuthorshipRolesTest extends TestCase
{
    private Builder&MockObject $builder;
    private AuthorshipRepository $repository;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(Builder::class);
        $this->builder->method('select')->willReturnSelf();
        $this->builder->method('where')->willReturnSelf();

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($this->builder);

        $this->repository = new AuthorshipRepository($db, $this->createMock(structureManager::class));
    }

    public function testLegacyEmptyRolesAreReadAsNoRoles(): void
    {
        $this->builder->method('first')->willReturn(['roles' => '']);
        $storedRoles = null;
        $this->builder
            ->method('update')
            ->willReturnCallback(function (array $data) use (&$storedRoles): int {
                $storedRoles = $data['roles'];
                return 1;
            });

        $this->repository->addAuthorship(154946, 33423, EntityType::Prod, ['music']);

        $this->assertSame('["music"]', $storedRoles);
    }

    public function testUnreadableRolesNameTheRecord(): void
    {
        $this->builder->method('first')->willReturn(['roles' => '{broken']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('element 154946 and author 33423 (prod) holds unreadable roles: "{broken"');

        $this->repository->addAuthorship(154946, 33423, EntityType::Prod, ['music']);
    }
}
