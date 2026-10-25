<?php

declare(strict_types=1);

namespace ZxArt\Tests\Geo;

use PHPUnit\Framework\TestCase;
use ZxArt\Geo\Dbal\PlaceSearchDbal;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\Dto\FoundCountryDto;
use ZxArt\Geo\PlaceSearchService;

class PlaceSearchServiceTest extends TestCase
{
    public function testFindAnswersTheCountriesAndCitiesOfTheTrimmedPhrase(): void
    {
        $repository = $this->createMock(PlaceSearchDbal::class);
        $repository->expects($this->once())->method('findCountries')->with('Est', 20)
            ->willReturn([['id' => 10, 'title' => 'Estonia']]);
        $repository->expects($this->once())->method('findCities')->with('Est', 20)
            ->willReturn([['id' => 20, 'title' => 'Estepona', 'countryId' => 30, 'countryTitle' => 'Spain']]);

        $result = (new PlaceSearchService($repository))->find('  Est ');

        self::assertEquals([new FoundCountryDto(id: 10, title: 'Estonia')], $result->countries);
        self::assertEquals([new FoundCityDto(id: 20, title: 'Estepona', countryId: 30, countryTitle: 'Spain')], $result->cities);
    }

    public function testAPhraseOfOneLetterFindsNothing(): void
    {
        $repository = $this->createMock(PlaceSearchDbal::class);
        $repository->expects($this->never())->method('findCountries');
        $repository->expects($this->never())->method('findCities');

        $result = (new PlaceSearchService($repository))->find(' T ');

        self::assertSame([], $result->countries);
        self::assertSame([], $result->cities);
    }
}
