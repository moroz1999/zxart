<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use DI\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Search;
use searchQueryFilter;

/**
 * The two kinds of search need a different order: instant search shows one page and puts
 * the closest matches on top of it, paged search is one alphabetical list the pages are cut from.
 */
#[AllowMockObjectsWithoutExpectations]
class SearchOrderingTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once ROOT_PATH . 'trickster-cms/cms/core/searchQueryFilter.class.php';
    }

    public function testPagedSearchIsOrderedByTitleAlone(): void
    {
        $query = $this->createQuery(['module_author.id', 'module_author.title as ' . searchQueryFilter::SORT_TITLE_COLUMN]);

        $sql = $this->order($query, 'soft', false);

        $this->assertStringEndsWith('order by `' . searchQueryFilter::SORT_TITLE_COLUMN . '` asc', $sql);
        $this->assertSame([], $query->getBindings());
    }

    public function testInstantSearchPutsTheClosestMatchesOnTop(): void
    {
        $query = $this->createQuery(['module_author.id', 'module_author.title as ' . searchQueryFilter::SORT_TITLE_COLUMN]);

        $sql = $this->order($query, 'soft', true);

        $column = '`' . searchQueryFilter::SORT_TITLE_COLUMN . '`';
        $this->assertStringEndsWith(
            'order by (' . $column . ' like ?) desc, '
            . '(' . $column . ' like ?) desc, '
            . '(' . $column . ' like ?) desc, '
            . $column . ' asc',
            $sql,
        );
        $this->assertSame(['soft', 'soft%', '%soft%'], $query->getBindings());
    }

    public function testEveryWordCountsForTheClosestMatches(): void
    {
        $query = $this->createQuery(['module_author.id', 'module_author.title as ' . searchQueryFilter::SORT_TITLE_COLUMN]);

        $this->order($query, ['soft', 'hard'], true);

        $this->assertSame(['soft', 'hard', 'soft%', 'hard%', '%soft%', '%hard%'], $query->getBindings());
    }

    public function testQueryWithoutTheSortColumnIsLeftAlone(): void
    {
        $query = $this->createQuery(['module_author.id']);

        $sql = $this->order($query, 'soft', true);

        $this->assertStringNotContainsString('order by', $sql);
    }

    /**
     * @param string|string[] $input
     */
    private function order(Builder $query, string|array $input, bool $relevance): string
    {
        $search = new class (new Container()) extends Search {
            public function orderQuery(Builder $query, string|array $input): void
            {
                $this->assignOrdering($query, $input);
            }
        };
        $search->setRelevanceOrdering($relevance);
        $search->orderQuery($query, $input);
        return $query->toSql();
    }

    /**
     * @param string[] $columns
     */
    private function createQuery(array $columns): Builder
    {
        $query = new Builder($this->createMock(ConnectionInterface::class), new MySqlGrammar(), new MySqlProcessor());
        return $query->from('module_author')->select($columns);
    }
}
