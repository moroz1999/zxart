<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use authorSearchQueryFilter;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use searchQueryFilter;

/**
 * Search orders the results in the database, and the query filters have to give it
 * a column to order by — including for a "distinct" query, whose ordering columns
 * must be part of the column list.
 */
#[AllowMockObjectsWithoutExpectations]
class SearchQueryFilterSortColumnTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // query filters are not autoloaded; the CMS includes them on demand
        require_once ROOT_PATH . 'trickster-cms/cms/core/QueryFilter.php';
        require_once ROOT_PATH . 'trickster-cms/cms/core/searchQueryFilter.class.php';
        require_once ROOT_PATH . 'project/modules/queryFilters/authorSearch.class.php';
    }

    public function testMainTitleIsExposedAsTheSortColumn(): void
    {
        $query = $this->createQuery(['module_author.id']);

        $sql = (new authorSearchQueryFilter())->getFilteredIdList('soft', $query)->toSql();

        $this->assertStringContainsString(
            '`module_author`.`title` as `' . searchQueryFilter::SORT_TITLE_COLUMN . '`',
            $sql,
        );
        // two title fields are searched, only the main one is exposed for ordering
        $this->assertSame(['%soft%', '%soft%'], $query->getBindings());
    }

    public function testOrderingIsLeftToTheCaller(): void
    {
        $query = $this->createQuery(['module_author.id']);

        $sql = (new authorSearchQueryFilter())->getFilteredIdList('soft', $query)->toSql();

        $this->assertStringNotContainsString('order by', $sql);
    }

    public function testQueryWithoutOwnColumnsIsNotNarrowedDown(): void
    {
        $query = $this->createQuery(null);

        $sql = (new authorSearchQueryFilter())->getFilteredIdList('soft', $query)->toSql();

        $this->assertStringContainsString('select *', $sql);
        $this->assertStringNotContainsString(searchQueryFilter::SORT_TITLE_COLUMN, $sql);
    }

    /**
     * @param string[]|null $columns
     */
    private function createQuery(?array $columns): Builder
    {
        $query = new Builder($this->createMock(ConnectionInterface::class), new MySqlGrammar(), new MySqlProcessor());
        $query->from('module_author');
        if ($columns !== null) {
            $query->select($columns);
        }
        return $query;
    }
}
