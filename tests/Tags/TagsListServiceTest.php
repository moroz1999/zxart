<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

require_once __DIR__ . '/../Doubles/Elements/TagElementStub.php';

use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Shared\DatabaseTable;
use ZxArt\TagsList\Dto\TagListItemDto;
use ZxArt\TagsList\Repositories\TagsListRepository;
use ZxArt\TagsList\TagsListService;
use ZxArt\Tests\Doubles\Elements\TagElementStub;

#[AllowMockObjectsWithoutExpectations]
final class TagsListServiceTest extends TestCase
{
    public function testReturnsTagsWithSectionAmountsInTitleOrder(): void
    {
        $service = $this->createService([
            new TagElementStub(1, 'Zulu'),
            new TagElementStub(3, 'Alpha'),
        ], [1 => 5, 3 => 10]);

        $items = $service->getSectionTags('graphics', 3);

        self::assertSame([3, 1], array_map(static fn(TagListItemDto $item): int => $item->id, $items));
        self::assertSame([10, 5], array_map(static fn(TagListItemDto $item): int => $item->amount, $items));
    }

    public function testDefaultMinimumIsThree(): void
    {
        $service = $this->createService([
            new TagElementStub(1, 'Included'),
        ], [1 => 3]);

        $items = $service->getSectionTags('music');

        self::assertSame([1], array_map(static fn(TagListItemDto $item): int => $item->id, $items));
    }

    public function testMinimumOneIncludesEveryUsedTag(): void
    {
        $service = $this->createService([
            new TagElementStub(1, 'Used'),
        ], [1 => 1]);

        $items = $service->getSectionTags('music', 1);

        self::assertSame([1], array_map(static fn(TagListItemDto $item): int => $item->id, $items));
    }

    public function testSoftwareSectionUsesTheProdsTable(): void
    {
        $repository = $this->createMock(TagsListRepository::class);
        $repository->expects(self::once())
            ->method('getSectionTagAmounts')
            ->with(DatabaseTable::ZxProd, TagsListService::DEFAULT_MINIMUM_AMOUNT)
            ->willReturn([]);

        $service = new TagsListService(
            $repository,
            $this->createMock(structureManager::class),
            $this->createMock(LanguagesManager::class),
        );

        self::assertSame([], $service->getSectionTags('software'));
    }

    /**
     * @param TagElementStub[] $elements
     * @param array<int, int> $amounts
     */
    private function createService(array $elements, array $amounts): TagsListService
    {
        $repository = $this->createMock(TagsListRepository::class);
        $repository->method('getSectionTagAmounts')->willReturn($amounts);

        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementsByIdList')->willReturn($elements);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageId')->willReturn(1);

        return new TagsListService($repository, $structureManager, $languagesManager);
    }
}
