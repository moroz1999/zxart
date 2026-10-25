<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Geo;

use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Geo\Dto\FoundCityDto;
use ZxArt\Geo\PlaceSearchService;

/**
 * The search runs on the MariaDB collation, which the unit tests on SQLite do
 * not have: a name matches regardless of case, and a wildcard is escaped.
 */
final class PlaceSearchTest extends TestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;

    public function testACityIsFoundByItsNameInAnyLanguageAndCase(): void
    {
        $expected = new FoundCityDto(
            id: self::CITY_WARSAW,
            title: $this->currentTitle('module_city', self::CITY_WARSAW),
            countryId: self::COUNTRY_POLAND,
            countryTitle: $this->currentTitle('module_country', self::COUNTRY_POLAND),
        );
        $service = controller::getInstance()->getContainer()->get(PlaceSearchService::class);

        foreach (['варшав', 'WARSAW'] as $phrase) {
            self::assertContainsEquals($expected, $service->find($phrase)->cities, $phrase);
        }
        // a wildcard in the phrase is taken literally
        self::assertNotContainsEquals($expected, $service->find('W_rsaw')->cities);
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
