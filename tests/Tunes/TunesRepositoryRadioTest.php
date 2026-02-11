<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tunes;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Tunes\Repositories\TunesRepository;

class TunesRepositoryRadioTest extends TestCase
{
    public function testFindRandomIdByCriteriaReturnsNullWhenTopIdsEmpty(): void
    {
        $builder = $this->createQueryBuilderMock([]);
        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new TunesRepository($db, $this->createMock(AlphanumericColumnSearch::class));
        $criteria = $this->makeCriteria(bestVotesLimit: 10);

        $this->assertNull($repository->findRandomIdByCriteria($criteria));
    }

    public function testFindRandomIdByCriteriaReturnsIdFromRandomSelection(): void
    {
        $topBuilder = $this->createQueryBuilderMock([10, 11]);
        $randomBuilder = $this->createQueryBuilderMock([11]);
        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturnOnConsecutiveCalls($topBuilder, $randomBuilder);

        $repository = new TunesRepository($db, $this->createMock(AlphanumericColumnSearch::class));
        $criteria = $this->makeCriteria(bestVotesLimit: 10);

        $this->assertSame(11, $repository->findRandomIdByCriteria($criteria));
    }

    private function createQueryBuilderMock(array $pluckResult): Builder
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('select')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        $builder->method('whereIn')->willReturnSelf();
        $builder->method('whereNotIn')->willReturnSelf();
        $builder->method('join')->willReturnSelf();
        $builder->method('distinct')->willReturnSelf();
        $builder->method('orderBy')->willReturnSelf();
        $builder->method('limit')->willReturnSelf();
        $builder->method('inRandomOrder')->willReturnSelf();
        $builder->method('pluck')->willReturn($pluckResult);
        return $builder;
    }

    private function makeCriteria(?int $bestVotesLimit = null): RadioCriteriaDto
    {
        return new RadioCriteriaDto(
            minRating: null,
            maxRating: null,
            yearsInclude: [],
            yearsExclude: [],
            countriesInclude: [],
            countriesExclude: [],
            formatGroupsInclude: [],
            formatGroupsExclude: [],
            formatsInclude: [],
            formatsExclude: [],
            prodCategoriesInclude: [],
            bestVotesLimit: $bestVotesLimit,
            maxPlays: null,
            minPartyPlace: null,
            requireGame: null,
            hasParty: null,
            notVotedByUserId: null,
        );
    }
}
