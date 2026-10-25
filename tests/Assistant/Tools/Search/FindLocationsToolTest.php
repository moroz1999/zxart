<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FindLocationsRequestDto;
use ZxArt\Assistant\Tools\Search\FindLocationsTool;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\Dto\PlaceSearchResultDto;
use ZxArt\Geo\PlaceSearchService;

final class FindLocationsToolTest extends TestCase
{
    public function testItIsTheReadToolNamedFindLocations(): void
    {
        $tool = new FindLocationsTool($this->createStub(PlaceSearchService::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('find_locations', $tool->name());
        self::assertSame(FindLocationsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheCountriesAndCitiesOfThePhrase(): void
    {
        $places = new PlaceSearchResultDto(
            countries: [],
            cities: [new FoundCityDto(id: 3, title: 'Tallinn', countryId: 2, countryTitle: 'Estonia')],
        );
        $service = $this->createMock(PlaceSearchService::class);
        $service->expects(self::once())->method('find')->with('Tallinn')->willReturn($places);

        self::assertSame($places, (new FindLocationsTool($service))->execute(new FindLocationsRequestDto('Tallinn')));
    }
}
