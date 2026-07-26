<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use tagsManager;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\MusicSearchSort;
use ZxArt\MusicSearch\Repositories\MusicSearchRepository;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\PictureSearchSort;
use ZxArt\PictureSearch\Repositories\PictureSearchRepository;

#[AllowMockObjectsWithoutExpectations]
class SearchAuthorLocalizationTest extends TestCase
{
    public function testPictureAuthorResultsUseCurrentLanguageRow(): void
    {
        [$connection, $builder, $whereCalls] = $this->createQueryDoubles();
        $repository = new PictureSearchRepository(
            $connection,
            $this->createMock(tagsManager::class),
            $this->createLanguagesManager(),
        );

        self::assertSame($builder, $repository->buildAuthorsQuery($this->createPictureQuery()));
        self::assertSame([
            ['languageId', '=', 930],
            ['displayInGraphics', '=', 1],
        ], $whereCalls());
    }

    public function testMusicAuthorResultsUseCurrentLanguageRow(): void
    {
        [$connection, $builder, $whereCalls] = $this->createQueryDoubles();
        $repository = new MusicSearchRepository(
            $connection,
            $this->createMock(tagsManager::class),
            $this->createLanguagesManager(),
        );

        self::assertSame($builder, $repository->buildAuthorsQuery($this->createMusicQuery()));
        self::assertSame([
            ['languageId', '=', 930],
            ['displayInMusic', '=', 1],
        ], $whereCalls());
    }

    /**
     * @return array{Connection, Builder, callable(): array<int, array{string, string, int}>}
     */
    private function createQueryDoubles(): array
    {
        $builder = $this->createMock(Builder::class);
        $whereCalls = [];
        $builder->method('where')->willReturnCallback(
            static function (string $column, string $operator, int $value) use ($builder, &$whereCalls): Builder {
                $whereCalls[] = [$column, $operator, $value];
                return $builder;
            },
        );

        $connection = $this->createMock(Connection::class);
        $connection->method('table')->with('module_author')->willReturn($builder);

        return [$connection, $builder, static function () use (&$whereCalls): array {
            return $whereCalls;
        }];
    }

    private function createLanguagesManager(): LanguagesManager
    {
        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageId')->willReturn(930);
        return $languagesManager;
    }

    private function createPictureQuery(): PictureSearchQuery
    {
        return new PictureSearchQuery(
            titleWord: null,
            startYear: null,
            endYear: null,
            minRating: null,
            minPartyPlace: null,
            pictureType: null,
            realtimeOnly: false,
            inspirationOnly: false,
            stagesOnly: false,
            fromGameOnly: false,
            tagsInclude: [],
            tagsExclude: [],
            authorCountryIds: [],
            authorCityIds: [],
            resultsType: PictureSearchResultsType::Authors,
            sortParameter: PictureSearchSort::Title,
            sortOrder: PictureSearchOrder::Asc,
            start: 0,
            limit: 60,
        );
    }

    private function createMusicQuery(): MusicSearchQuery
    {
        return new MusicSearchQuery(
            titleWord: null,
            startYear: null,
            endYear: null,
            minRating: null,
            minPartyPlace: null,
            formatGroup: null,
            format: null,
            realtimeOnly: false,
            tagsInclude: [],
            tagsExclude: [],
            authorCountryIds: [],
            authorCityIds: [],
            resultsType: PictureSearchResultsType::Authors,
            sortParameter: MusicSearchSort::Title,
            sortOrder: PictureSearchOrder::Asc,
            start: 0,
            limit: 60,
        );
    }
}
