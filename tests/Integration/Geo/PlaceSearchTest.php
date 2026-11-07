<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Geo;

use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\Dto\FoundCountryDto;
use ZxArt\Geo\PlaceSearchService;

/**
 * Countries and cities by a part of their name in any language, named in the
 * current one; spaces, punctuation and case do not matter.
 */
final class PlaceSearchTest extends TestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;

    public function testACityIsFoundByItsNameInAnyLanguageAndCaseWithItsCountry(): void
    {
        foreach (['варшав', 'WARSAW', 'war-saw'] as $phrase) {
            self::assertContainsEquals($this->warsaw(), $this->service()->find($phrase)->cities, $phrase);
        }
    }

    public function testACountryIsFoundByItsNameInAnotherLanguage(): void
    {
        $poland = new FoundCountryDto(self::COUNTRY_POLAND, $this->currentTitle('module_country', self::COUNTRY_POLAND));

        foreach (['польш', 'Polonia', 'po land'] as $phrase) {
            self::assertContainsEquals($poland, $this->service()->find($phrase)->countries, $phrase);
        }
    }

    public function testAWildcardIsPunctuationLikeAnyOther(): void
    {
        self::assertNotContainsEquals($this->warsaw(), $this->service()->find('W_rsaw')->cities);
        self::assertContainsEquals($this->warsaw(), $this->service()->find('W_arsaw')->cities);
    }

    private function warsaw(): FoundCityDto
    {
        return new FoundCityDto(
            id: self::CITY_WARSAW,
            title: $this->currentTitle('module_city', self::CITY_WARSAW),
            countryId: self::COUNTRY_POLAND,
            countryTitle: $this->currentTitle('module_country', self::COUNTRY_POLAND),
        );
    }

    private function service(): PlaceSearchService
    {
        return controller::getInstance()->getContainer()->get(PlaceSearchService::class);
    }

    private function currentTitle(string $table, int $id): string
    {
        $container = controller::getInstance()->getContainer();

        return (string)$container->get(Connection::class)
            ->table($table)
            ->where('id', '=', $id)
            ->where('languageId', '=', (int)$container->get(LanguagesManager::class)->getCurrentLanguageId())
            ->value('title');
    }
}
