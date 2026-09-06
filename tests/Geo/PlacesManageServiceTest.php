<?php

declare(strict_types=1);

namespace ZxArt\Tests\Geo;

use cityElement;
use countryElement;
use LanguagesManager;
use linksManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Geo\Dto\CitySaveDto;
use ZxArt\Geo\Dto\CountrySaveDto;
use ZxArt\Geo\Exception\GeoException;
use ZxArt\Geo\PlacesManageService;
use ZxArt\Geo\Repositories\GeoManageRepository;

/**
 * The two lists the management screen is built from, and the guards that keep a
 * place from being deleted while something still points at it.
 */
#[AllowMockObjectsWithoutExpectations]
class PlacesManageServiceTest extends TestCase
{
    private const int RUSSIAN = 930;
    private const int ENGLISH = 2105;

    private GeoManageRepository&MockObject $repository;
    private structureManager&MockObject $structureManager;
    private linksManager&MockObject $linksManager;
    private PlacesManageService $service;
    /** @var array<int, (cityElement|countryElement)&MockObject> */
    private array $places = [];

    protected function setUp(): void
    {
        $this->repository = $this->createMock(GeoManageRepository::class);
        $this->structureManager = $this->createMock(structureManager::class);
        $this->linksManager = $this->createMock(linksManager::class);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getLanguagesIdList')->willReturn([self::RUSSIAN, self::ENGLISH]);
        $languagesManager->method('getCurrentLanguageId')->willReturn(self::ENGLISH);

        $this->service = new PlacesManageService(
            $this->repository,
            $this->structureManager,
            $this->linksManager,
            $languagesManager,
        );

        $this->repository->method('getCountryRows')->willReturn([
            $this->row(100, self::RUSSIAN, 'Россия', 61.5, 105.3),
            $this->row(100, self::ENGLISH, 'Russia', 61.5, 105.3),
            $this->row(101, self::RUSSIAN, 'Австралия', -25.3, 133.8),
            $this->row(101, self::ENGLISH, 'Australia', -25.3, 133.8),
        ]);
        $this->repository->method('getCityRows')->willReturn([
            $this->row(200, self::RUSSIAN, 'Москва', 55.8, 37.6),
            $this->row(200, self::ENGLISH, 'Moscow', 55.8, 37.6),
            $this->row(201, self::ENGLISH, 'Kazan', 55.8, 49.1),
            $this->row(202, self::ENGLISH, 'Sydney', -33.9, 151.2),
        ]);
        $this->repository->method('getCityCountries')->willReturn([200 => 100, 201 => 100, 202 => 101]);
        $this->repository->method('getPlaceUsages')->willReturn([100 => 12, 200 => 3]);
    }

    public function testCountriesAreAlphabeticalInTheCurrentLanguage(): void
    {
        $this->assertSame(
            ['Australia', 'Russia'],
            array_map(static fn($country): string => $country->title, $this->service->getCountries()),
        );
    }

    public function testCountryCarriesEveryLanguageItsCoordinatesAndItsCounts(): void
    {
        [, $russia] = $this->service->getCountries();

        $this->assertSame([self::RUSSIAN => 'Россия', self::ENGLISH => 'Russia'], $russia->titles);
        $this->assertSame([61.5, 105.3], [$russia->latitude, $russia->longitude]);
        $this->assertSame([12, 2], [$russia->usages, $russia->cities]);
    }

    public function testCitiesNameTheirCountry(): void
    {
        $cities = $this->service->getCities();

        $this->assertSame(
            [['Kazan', 100, 0], ['Moscow', 100, 3], ['Sydney', 101, 0]],
            array_map(static fn($city): array => [$city->title, $city->countryId, $city->usages], $cities),
        );
    }

    public function testCityWithoutACountryIsLeftOut(): void
    {
        $repository = $this->createMock(GeoManageRepository::class);
        $repository->method('getCityRows')->willReturn([$this->row(300, self::ENGLISH, 'Nowhere', 0.0, 0.0)]);
        $repository->method('getCityCountries')->willReturn([]);
        $repository->method('getPlaceUsages')->willReturn([]);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageId')->willReturn(self::ENGLISH);
        $service = new PlacesManageService(
            $repository,
            $this->structureManager,
            $this->createMock(linksManager::class),
            $languagesManager,
        );

        $this->assertSame([], $service->getCities());
    }

    public function testCountryStillNamedByAnEntityIsNotDeleted(): void
    {
        $this->structureManager->method('getElementById')->willReturn($this->createMock(countryElement::class));

        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('still named by 12 entity(ies)');

        $this->service->deleteCountry(100);
    }

    public function testCountryStillHoldingCitiesIsNotDeleted(): void
    {
        $this->structureManager->method('getElementById')->willReturn($this->createMock(countryElement::class));

        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('still has cities');

        // nobody names Australia, but Sydney still hangs under it
        $this->service->deleteCountry(101);
    }

    public function testCityStillNamedByAnEntityIsNotDeleted(): void
    {
        $this->structureManager->method('getElementById')->willReturn($this->createMock(cityElement::class));

        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('still named by 3 entity(ies)');

        $this->service->deleteCity(200);
    }

    public function testUnusedCityIsDeleted(): void
    {
        $element = $this->createMock(cityElement::class);
        $element->expects($this->once())->method('deleteElementData');
        $this->structureManager->method('getElementById')->willReturn($element);

        $this->service->deleteCity(201);
    }

    public function testACountryIsNotACity(): void
    {
        $this->structureManager->method('getElementById')->willReturn($this->createMock(countryElement::class));

        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('City not found');

        $this->service->deleteCity(100);
    }

    public function testCoordinatesOffTheGlobeAreRefused(): void
    {
        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('Latitude must be between -90 and 90');

        $this->service->updateCountry(new CountrySaveDto(
            titles: [self::RUSSIAN => 'Россия', self::ENGLISH => 'Russia'],
            id: 100,
            latitude: 91.0,
        ));
    }

    public function testCityCreationNeedsItsCountry(): void
    {
        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('Missing required field: countryId');

        $this->service->createCity(new CitySaveDto(titles: [self::RUSSIAN => 'Москва', self::ENGLISH => 'Moscow']));
    }

    public function testTitleMissingInOneLanguageIsRefused(): void
    {
        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('Title is required for language ' . self::ENGLISH);

        $this->service->updateCountry(new CountrySaveDto(titles: [self::RUSSIAN => 'Россия'], id: 100));
    }

    public function testUpdatingACityUnderAnotherCountryMovesIt(): void
    {
        $this->structureManager->method('getElementById')->willReturnCallback(
            fn(int $id): cityElement|countryElement => $id === 200
                ? $this->place(cityElement::class, $id)
                : $this->place(countryElement::class, $id),
        );

        $this->linksManager->expects($this->once())->method('unLinkElements')->with(100, 200, 'structure');
        $this->linksManager->expects($this->once())->method('linkElements')->with(101, 200, 'structure');

        $this->service->updateCity($this->citySave(200, 101));
    }

    public function testUpdatingACityUnderItsOwnCountryTouchesNoLinks(): void
    {
        $this->structureManager->method('getElementById')->willReturn($this->place(cityElement::class, 200));

        $this->linksManager->expects($this->never())->method('unLinkElements');
        $this->linksManager->expects($this->never())->method('linkElements');

        $this->service->updateCity($this->citySave(200, 100));
    }

    public function testACityUpdateWithoutACountryIsABadRequest(): void
    {
        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('Missing required field: countryId');

        $this->service->updateCity(new CitySaveDto(
            titles: [self::RUSSIAN => 'Москва', self::ENGLISH => 'Moscow'],
            id: 200,
        ));
    }

    public function testACityCannotBeMovedUnderAnUnknownCountry(): void
    {
        $this->structureManager->method('getElementById')->willReturnCallback(
            fn(int $id): ?cityElement => $id === 200 ? $this->place(cityElement::class, $id) : null,
        );

        $this->expectException(GeoException::class);
        $this->expectExceptionMessage('Country not found');

        $this->service->updateCity($this->citySave(200, 999));
    }

    private function citySave(int $id, int $countryId): CitySaveDto
    {
        return new CitySaveDto(
            titles: [self::RUSSIAN => 'Москва', self::ENGLISH => 'Moscow'],
            id: $id,
            countryId: $countryId,
        );
    }

    /**
     * @template T of cityElement|countryElement
     * @param class-string<T> $class
     * @return T&MockObject
     */
    private function place(string $class, int $id): cityElement|countryElement
    {
        if (!isset($this->places[$id])) {
            $element = $this->createMock($class);
            $element->method('getId')->willReturn($id);
            $element->method('importExternalData')->willReturn(true);
            $this->places[$id] = $element;
        }

        return $this->places[$id];
    }

    /**
     * @return array{id: int, languageId: int, title: string, latitude: float, longitude: float}
     */
    private function row(int $id, int $languageId, string $title, float $latitude, float $longitude): array
    {
        return [
            'id' => $id,
            'languageId' => $languageId,
            'title' => $title,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }
}
