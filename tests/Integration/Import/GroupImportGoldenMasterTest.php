<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ImportGoldenMasterTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Groups\UseCases\ImportGroupCommand;
use ZxArt\Groups\UseCases\ImportGroupHandler;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Shared\EntityType;

/**
 * Groups and group aliases through {@see ImportGroupHandler}.
 */
#[RunTestsInSeparateProcesses]
final class GroupImportGoldenMasterTest extends ImportGoldenMasterTestCase
{
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int COUNTRY_IMPORT_ID = 9900005;

    public function testNewGroupsSubgroupsAndAliases(): void
    {
        $this->mapImportId(self::COUNTRY_UNITED_KINGDOM, (string)self::COUNTRY_IMPORT_ID, ImportOrigin::Zxdb, EntityType::Country);
        $this->importGroup(
            new GroupLabel(id: 'gm-g1', name: 'Goldmaster Federation', locationName: 'London', type: 'organization', abbreviation: 'GMF', website: 'https://federation.invalid/'),
            ImportOrigin::Zxdb,
        );
        $this->importGroup(new GroupLabel(id: 'gm-g2', name: 'Goldmaster Division', countryName: 'Poland', cityName: 'Warsaw', parentGroupIds: ['gm-g1', 'gm-missing'], type: 'demo'), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g3', name: '9 Goldmaster Numbers', countryId: self::COUNTRY_IMPORT_ID), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g1-alias', isAlias: true, name: 'Gm Federation Alias', aliasParentGroupId: 'gm-g1'), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-alias-alone', isAlias: true, name: 'Goldmaster Lone Group Alias', aliasParentGroupId: 'gm-missing'), ImportOrigin::Zxdb);

        $this->assertMatchesSnapshot();
    }

    public function testReimportFillsOnlyWhatIsEmptyAndRewritesParents(): void
    {
        $this->importGroup(new GroupLabel(id: 'gm-g1', name: 'Goldmaster Federation'), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g2', name: 'Goldmaster Division', type: 'unknown'), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g2-alias', isAlias: true, name: 'Gm Division Alias'), ImportOrigin::Zxdb);

        $this->importGroup(
            new GroupLabel(id: 'gm-g2', name: 'Goldmaster Division Renamed', countryName: 'Russia', parentGroupIds: ['gm-g1'], type: 'crack', abbreviation: 'GMD', website: 'https://division.invalid/'),
            ImportOrigin::Zxdb,
        );
        $this->importGroup(new GroupLabel(id: 'gm-g2', type: 'demo', abbreviation: 'GMD2', parentGroupIds: []), ImportOrigin::Zxdb);
        $this->importGroup(new GroupLabel(id: 'gm-g2-alias', isAlias: true, name: 'Gm Division Alias Renamed', aliasParentGroupId: 'gm-g2'), ImportOrigin::Zxdb);

        $this->assertMatchesSnapshot();
    }

    public function testGroupFoundByNameGetsThisSourcesId(): void
    {
        $this->importGroup(new GroupLabel(id: 'gm-zxdb-g', name: 'Goldmaster Wanderers', countryName: 'Poland'), ImportOrigin::Zxdb);

        $this->importGroup(new GroupLabel(id: 'gm-pouet-g', name: 'Goldmaster Wanderers', countryName: 'Poland', website: 'https://wanderers.invalid/'), ImportOrigin::Pouet);

        $this->assertMatchesSnapshot();
    }

    private function importGroup(GroupLabel $label, ImportOrigin $origin): void
    {
        $this->container->get(ImportGroupHandler::class)->handle(new ImportGroupCommand($label, $origin));
    }
}
