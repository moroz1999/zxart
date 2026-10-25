<?php

declare(strict_types=1);

namespace ZxArt\Geo;

use ZxArt\Geo\Dbal\PlaceSearchDbal;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\Dto\FoundCountryDto;
use ZxArt\Geo\Dto\PlaceSearchResultDto;

/**
 * Countries and cities by a part of their name in any language, for picking
 * the id of a place.
 *
 * @psalm-api
 */
final readonly class PlaceSearchService
{
    private const int MIN_PHRASE_LENGTH = 2;
    private const int LIMIT = 20;

    public function __construct(
        private PlaceSearchDbal $placeSearchDbal,
    ) {
    }

    public function find(string $phrase): PlaceSearchResultDto
    {
        $phrase = trim($phrase);
        if (mb_strlen($phrase) < self::MIN_PHRASE_LENGTH) {
            return new PlaceSearchResultDto(countries: [], cities: []);
        }

        return new PlaceSearchResultDto(
            countries: array_map(
                static fn(array $row): FoundCountryDto => new FoundCountryDto(id: $row['id'], title: $row['title']),
                $this->placeSearchDbal->findCountries($phrase, self::LIMIT),
            ),
            cities: array_map(
                static fn(array $row): FoundCityDto => new FoundCityDto(
                    id: $row['id'],
                    title: $row['title'],
                    countryId: $row['countryId'],
                    countryTitle: $row['countryTitle'],
                ),
                $this->placeSearchDbal->findCities($phrase, self::LIMIT),
            ),
        );
    }
}
