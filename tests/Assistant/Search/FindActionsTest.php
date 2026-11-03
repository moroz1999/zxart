<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Search\FindLocationsAction;
use ZxArt\Assistant\Search\FindPartiesAction;
use ZxArt\Assistant\Search\FoundPartiesDto;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\Dto\PlaceSearchResultDto;
use ZxArt\Geo\PlaceSearchService;
use ZxArt\Parties\Dbal\PartySearchDbal;
use ZxArt\Parties\Dto\FoundPartyDto;

final class FindActionsTest extends TestCase
{
    public function testFindLocationsAnswersTheCountriesAndCitiesOfThePhrase(): void
    {
        $places = new PlaceSearchResultDto(
            countries: [],
            cities: [new FoundCityDto(id: 3, title: 'Tallinn', countryId: 2, countryTitle: 'Estonia')],
        );
        $service = $this->createMock(PlaceSearchService::class);
        $service->expects(self::once())->method('find')->with('Tallinn')->willReturn($places);
        $action = new FindLocationsAction($service);

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('find_locations', $action->name());
        self::assertSame(['phrase'], Arguments::schema($action->schema())['required']);
        self::assertSame($places, $action->read(Arguments::parse('{"phrase": "Tallinn"}', $action->schema())));
    }

    public function testFindPartiesAnswersThePartiesOfThePhraseAndYear(): void
    {
        $party = new FoundPartyDto(5, 'Chaos Constructions 2008', "CC'08", 2008);
        $dbal = $this->createMock(PartySearchDbal::class);
        $dbal->expects(self::once())->method('find')->with('chaos', 2008, 20)->willReturn([$party]);
        $action = new FindPartiesAction($dbal);

        self::assertSame('find_parties', $action->name());
        self::assertSame(['phrase', 'year', 'limit'], array_keys(Arguments::schema($action->schema())['properties']));
        self::assertEquals(new FoundPartiesDto([$party]), $action->read(Arguments::parse('{"phrase": "chaos", "year": 2008}', $action->schema())));
    }

    public function testFindPartiesKeepsTheLimitBetweenOneAndAHundred(): void
    {
        $limits = [];
        $dbal = $this->createStub(PartySearchDbal::class);
        $dbal->method('find')->willReturnCallback(static function (string $phrase, ?int $year, int $limit) use (&$limits): array {
            $limits[] = $limit;

            return [];
        });
        $action = new FindPartiesAction($dbal);

        $action->read(Arguments::parse('{"phrase": "chaos", "limit": 0}', $action->schema()));
        $action->read(Arguments::parse('{"phrase": "chaos", "limit": 500}', $action->schema()));

        self::assertSame([1, 100], $limits);
    }
}
