<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ImportGoldenMasterTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Services\AuthorImportService;
use ZxArt\Groups\UseCases\ImportGroupCommand;
use ZxArt\Groups\UseCases\ImportGroupHandler;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Shared\EntityType;

/**
 * Authors and author aliases through {@see AuthorImportService}.
 */
#[RunTestsInSeparateProcesses]
final class AuthorImportGoldenMasterTest extends ImportGoldenMasterTestCase
{
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int CITY_LONDON = 47543;
    private const int COUNTRY_IMPORT_ID = 9900003;
    private const int CITY_IMPORT_ID = 9900004;

    public function testNewAuthorsAliasesAndLabels(): void
    {
        $authors = $this->authorImportService();
        $authors->importAuthor(new PersonLabel(id: 'gm-a1', name: 'Goldmaster Painter', realName: 'Paint Master', locationName: 'London'), ImportOrigin::Zxdb);
        $authors->importAuthor(new PersonLabel(id: 'gm-a2', name: 'Goldmaster Tracker', countryName: 'Poland', cityName: 'Warsaw'), ImportOrigin::Zxdb);
        $authors->importAuthor(new PersonLabel(id: 'gm-a3', realName: 'Goldmaster Realname'), ImportOrigin::Zxdb);
        $authors->importAuthor(new PersonLabel(id: 'gm-a4', name: '1st Goldmaster'), ImportOrigin::Zxdb);
        $authors->importAuthor(new PersonLabel(id: 'gm-a6', name: 'Goldmaster Not Created'), ImportOrigin::Zxdb, false);
        $authors->importAuthorAlias(new PersonLabel(id: 'gm-a1-alias', name: 'Gm Painter Alias'), ImportOrigin::Zxdb);
        $authors->importAuthorAlias(new PersonLabel(id: 'gm-alias-alone', name: 'Goldmaster Lone Alias'), ImportOrigin::Zxdb);
        $authors->importLabel(new PersonLabel(id: 'gm-label', name: 'Goldmaster Label Person', countryName: 'Russia'), ImportOrigin::Zxdb);

        $this->assertMatchesSnapshot();
    }

    public function testAliasPointsAtTheAuthorImportedUnderItsAuthorId(): void
    {
        $authors = $this->authorImportService();
        $authors->importAuthor(new PersonLabel(id: '9900001', name: 'Goldmaster Owner'), ImportOrigin::Zxdb);
        $authors->importAuthorAlias(new PersonLabel(id: '9900002', name: 'Gm Owner Alias', authorId: 9900001), ImportOrigin::Zxdb);

        $this->assertMatchesSnapshot();
    }

    public function testReimportFillsOnlyWhatIsEmpty(): void
    {
        $this->authorImportService()->importAuthor(new PersonLabel(id: 'gm-a1', name: 'Goldmaster Painter'), ImportOrigin::Zxdb);

        $this->authorImportService()->importAuthor(
            new PersonLabel(id: 'gm-a1', name: 'Goldmaster Painter Renamed', realName: 'Paint Master', countryName: 'Poland', cityName: 'Warsaw'),
            ImportOrigin::Zxdb,
        );
        $this->authorImportService()->importAuthor(
            new PersonLabel(id: 'gm-a1', realName: 'Paint Master Second', locationName: 'London'),
            ImportOrigin::Zxdb,
        );

        $this->assertMatchesSnapshot();
    }

    public function testReimportWithForceFlagsOverwritesAndJoinsGroups(): void
    {
        $this->mapImportId(self::COUNTRY_UNITED_KINGDOM, (string)self::COUNTRY_IMPORT_ID, ImportOrigin::Zxdb, EntityType::Country);
        $this->mapImportId(self::CITY_LONDON, (string)self::CITY_IMPORT_ID, ImportOrigin::Zxdb, EntityType::City);
        $this->importGroup(new GroupLabel(id: 'gm-g1', name: 'Goldmaster Painters Guild'), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g2', name: 'Goldmaster Trackers Guild'), ImportOrigin::Zxdb);
        $this->authorImportService()->importAuthor(
            new PersonLabel(id: 'gm-a1', name: 'Goldmaster Painter', realName: 'Paint Master', countryName: 'Poland', cityName: 'Warsaw'),
            ImportOrigin::Zxdb,
        );

        $authors = $this->authorImportService();
        $authors->setForceUpdateRealName(true);
        $authors->setForceUpdateCountry(true);
        $authors->setForceUpdateCity(true);
        $authors->setForceUpdateGroups(true);
        $authors->importAuthor(
            new PersonLabel(
                id: 'gm-a1',
                name: 'Goldmaster Painter Renamed',
                realName: 'Paint Master Second',
                countryName: 'Russia',
                countryId: self::COUNTRY_IMPORT_ID,
                groupIds: ['gm-g1', 'gm-g2', 'gm-missing', null, ''],
                groupRoles: ['graphics'],
            ),
            ImportOrigin::Zxdb,
        );
        $authors->importAuthor(
            new PersonLabel(id: 'gm-a2', name: 'Goldmaster Located By Ids', countryId: self::COUNTRY_IMPORT_ID, cityId: self::CITY_IMPORT_ID),
            ImportOrigin::Zxdb,
        );

        $this->assertMatchesSnapshot();
    }

    public function testAuthorFoundByNameGetsThisSourcesId(): void
    {
        $this->authorImportService()->importAuthor(new PersonLabel(id: 'gm-zxdb-a', name: 'Goldmaster Wanderer', countryName: 'Poland'), ImportOrigin::Zxdb);

        $this->authorImportService()->importAuthor(new PersonLabel(id: 'gm-pouet-a', name: 'Goldmaster Wanderer', realName: 'Wander Master', countryName: 'Poland'), ImportOrigin::Pouet);
        $this->authorImportService()->importLabel(new PersonLabel(id: 'gm-dzoo-a', name: 'Goldmaster Wanderer'), ImportOrigin::Demozoo);

        $this->assertMatchesSnapshot();
    }

    private function authorImportService(): AuthorImportService
    {
        return $this->container->get(AuthorImportService::class);
    }

    private function importGroup(GroupLabel $label, ImportOrigin $origin): void
    {
        $this->container->get(ImportGroupHandler::class)->handle(new ImportGroupCommand($label, $origin));
    }
}
