<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dbal\AuthorSearchRepository;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Services\AuthorSearchService;

class AuthorSearchServiceTest extends TestCase
{
    public function testSearchPassesTheTrimmedNameAndTheFiltersOn(): void
    {
        $repository = $this->createMock(AuthorSearchRepository::class);
        $repository->expects($this->once())->method('count')->with('Alex', 1, 2, 3)->willReturn(7);
        $repository->expects($this->once())->method('findIds')->with('Alex', 1, 2, 3, 20, 10)->willReturn([4, 5]);

        $page = (new AuthorSearchService($repository))->search(
            new AuthorSearchFilterDto(name: ' Alex ', countryId: 1, cityId: 2, groupId: 3),
            20,
            10,
        );

        self::assertSame(7, $page->total);
        self::assertSame([4, 5], $page->ids);
    }
}
