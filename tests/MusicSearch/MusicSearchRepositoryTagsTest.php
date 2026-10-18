<?php

declare(strict_types=1);

namespace ZxArt\Tests\MusicSearch;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use tagsManager;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\MusicSearchSort;
use ZxArt\MusicSearch\Repositories\MusicSearchRepository;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;

/**
 * Included tags that no tune carries leave nothing to find; the filter must not
 * be dropped and widen the search to the whole collection.
 */
#[AllowMockObjectsWithoutExpectations]
class MusicSearchRepositoryTagsTest extends TestCase
{
    public function testIncludedTagsWithoutTunesMatchNothing(): void
    {
        $tagsManager = $this->createMock(tagsManager::class);
        $tagsManager->method('getConnectedElementIdsByNames')->willReturn([]);
        $connection = $this->createMock(Connection::class);
        $connection->method('table')->willReturnCallback(
            fn(string $table): Builder => (new Builder($connection, new MySqlGrammar(), new MySqlProcessor()))->from($table),
        );
        $repository = new MusicSearchRepository($connection, $tagsManager, $this->createMock(LanguagesManager::class));

        $sql = $repository->buildTunesQuery($this->queryWithTags(['No such tag']))->toSql();

        $this->assertStringContainsString('0 = 1', $sql);
    }

    /**
     * @param string[] $tagsInclude
     */
    private function queryWithTags(array $tagsInclude): MusicSearchQuery
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
            tagsInclude: $tagsInclude,
            tagsExclude: [],
            authorCountryIds: [],
            authorCityIds: [],
            resultsType: PictureSearchResultsType::cases()[0],
            sortParameter: MusicSearchSort::cases()[0],
            sortOrder: PictureSearchOrder::cases()[0],
            start: 0,
            limit: 10,
        );
    }
}
