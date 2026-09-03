<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Repositories\ImportOriginsRepository;
use ZxArt\Shared\EntityType;

#[AllowMockObjectsWithoutExpectations]
class ImportOriginsRepositoryTest extends TestCase
{
    private Builder&MockObject $builder;
    private Connection&MockObject $db;
    private ImportOriginsRepository $repository;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(Builder::class);
        $this->builder->method('select')->willReturnSelf();
        $this->builder->method('where')->willReturnSelf();
        $this->builder->method('orderBy')->willReturnSelf();

        $this->db = $this->createMock(Connection::class);
        $this->db->method('table')->willReturn($this->builder);

        $this->repository = new ImportOriginsRepository($this->db);
    }

    public function testElementOriginsAreReturnedAsPortalAndIdPairs(): void
    {
        $this->builder->method('get')->willReturn([
            ['importOrigin' => 'zxdb', 'importId' => '1234'],
            ['importOrigin' => 'vt', 'importId' => 'abc'],
        ]);

        $origins = $this->repository->getElementOrigins(42);

        $this->assertSame(
            [
                ['origin' => 'zxdb', 'importId' => '1234'],
                ['origin' => 'vt', 'importId' => 'abc'],
            ],
            $origins,
        );
    }

    public function testElementWithoutOriginsReturnsEmptyList(): void
    {
        $this->builder->method('get')->willReturn([]);

        $this->assertSame([], $this->repository->getElementOrigins(42));
    }

    public function testSavingAnOriginPointsThePortalIdAtTheElement(): void
    {
        $captured = [];
        $this->builder
            ->method('updateOrInsert')
            ->willReturnCallback(function (array $attributes, array $values) use (&$captured): bool {
                $captured = [$attributes, $values];
                return true;
            });

        $this->repository->saveOrigin(42, ImportOrigin::Pouet, '777', EntityType::Prod);

        $this->assertSame(
            [
                ['importOrigin' => 'pouet', 'importId' => '777', 'type' => 'prod'],
                ['elementId' => 42],
            ],
            $captured,
        );
    }
}
