<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Comments\Repositories\CommentsRepository;

final class CommentsRepositoryTest extends TestCase
{
    public function testThreadCountIncludesRepliesAtEveryDepth(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('whereIn')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        // Work 1 has comments 10 and 11; 11 has reply 12; 12 has no replies.
        $builder->method('pluck')
            ->with('childStructureId')
            ->willReturnOnConsecutiveCalls([10, 11], [12], []);

        $connection = $this->createMock(Connection::class);
        $connection->method('table')->with('structure_links')->willReturn($builder);

        $repository = new CommentsRepository($connection);

        self::assertSame(3, $repository->countThread(1));
    }

    public function testThreadCountIsZeroWithoutComments(): void
    {
        $builder = $this->createStub(Builder::class);
        $builder->method('whereIn')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        $builder->method('pluck')->willReturn([]);

        $connection = $this->createStub(Connection::class);
        $connection->method('table')->willReturn($builder);

        $repository = new CommentsRepository($connection);

        self::assertSame(0, $repository->countThread(1));
    }
}
