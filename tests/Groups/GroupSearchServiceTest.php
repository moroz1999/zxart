<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dbal\GroupSearchRepository;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\Services\GroupSearchService;

class GroupSearchServiceTest extends TestCase
{
    public function testSearchPassesTheTrimmedNameAndTheFiltersOn(): void
    {
        $repository = $this->createMock(GroupSearchRepository::class);
        $repository->expects($this->once())->method('count')->with('Triumph', 1, 2, 'demogroup')->willReturn(7);
        $repository->expects($this->once())->method('findIds')->with('Triumph', 1, 2, 'demogroup', 20, 10)->willReturn([4, 5]);

        $page = (new GroupSearchService($repository))->search(
            new GroupSearchFilterDto(name: ' Triumph ', countryId: 1, cityId: 2, type: 'demogroup'),
            20,
            10,
        );

        self::assertSame(7, $page->total);
        self::assertSame([4, 5], $page->ids);
    }
}
