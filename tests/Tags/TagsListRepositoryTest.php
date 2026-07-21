<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\DatabaseTable;
use ZxArt\TagsList\Repositories\TagsListRepository;

final class TagsListRepositoryTest extends TestCase
{
    public function testReturnsAmountsCountedWithinTheRequestedSection(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects(self::once())
            ->method('join')
            ->with('module_zxmusic as items', 'items.id', '=', 'links.childStructureId')
            ->willReturnSelf();
        $builder->expects(self::once())
            ->method('where')
            ->with('links.type', '=', 'tagLink')
            ->willReturnSelf();
        $builder->method('groupBy')->willReturnSelf();
        $builder->method('orderBy')->willReturnSelf();
        $builder->method('distinct')->willReturnSelf();
        $builder->method('pluck')->willReturn(['12449', 77, 88]);
        $builder->method('get')
            ->willReturn([
                ['aggregate' => '10'],
                ['aggregate' => 14],
                ['aggregate' => 9],
            ]);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('table')
            ->with('structure_links as links')
            ->willReturn($builder);

        $repository = new TagsListRepository($connection);

        self::assertSame([12449 => 10, 77 => 14], $repository->getSectionTagAmounts(DatabaseTable::ZxMusic, 10));
    }
}
