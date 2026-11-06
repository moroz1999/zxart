<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Stats;

use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use ZxArt\LinkTypes;
use ZxArt\Shared\EntityType;
use ZxArt\Stats\Dbal\StatsDbal;

/**
 * Country distributions run against the real schema; every write is rolled back.
 */
final class StatsDbalTest extends TestCase
{
    private const int ESTONIA = 47585;
    private const int AUTHOR = 990_100_001;
    private const int AUTHOR_ALIAS = 990_100_002;
    private const int GROUP = 990_100_003;
    private const int GROUP_ALIAS = 990_100_004;
    private const int YEAR = 2099;

    private Connection $db;
    private StatsDbal $dbal;
    private int $languageId;
    private string $countryTitle;

    #[\Override]
    protected function setUp(): void
    {
        $container = controller::getInstance()->getContainer();
        $this->db = $container->get(Connection::class);
        $this->dbal = $container->get(StatsDbal::class);
        $this->languageId = (int)$container->get(LanguagesManager::class)->getCurrentLanguageId();
        $this->countryTitle = (string)$this->db->table('module_country')
            ->where('id', '=', self::ESTONIA)
            ->where('languageId', '=', $this->languageId)
            ->value('title');

        $this->db->beginTransaction();
        $this->cloneRow('module_author', self::AUTHOR, [
            'languageId' => $this->languageId,
            'country' => self::ESTONIA,
            'title' => 'Stats test author',
        ]);
        $this->cloneRow('module_authoralias', self::AUTHOR_ALIAS, [
            'languageId' => 0,
            'authorId' => self::AUTHOR,
            'title' => 'Stats test author alias',
        ]);
        $this->cloneRow('module_group', self::GROUP, [
            'languageId' => 0,
            'country' => self::ESTONIA,
            'title' => 'Stats test group',
        ]);
        $this->cloneRow('module_groupalias', self::GROUP_ALIAS, [
            'languageId' => 0,
            'groupId' => self::GROUP,
            'title' => 'Stats test group alias',
        ]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testPictureCountryCountsAliasWorksAndCountsEachWorkOnce(): void
    {
        $before = $this->countryCount($this->dbal->pictureCountryDistribution());
        $this->work('module_zxpicture', 990_100_011);
        $this->work('module_zxpicture', 990_100_012);
        $this->link(self::AUTHOR_ALIAS, 990_100_011, LinkTypes::AUTHOR_PICTURE);
        $this->link(self::AUTHOR, 990_100_012, LinkTypes::AUTHOR_PICTURE);
        $this->link(self::AUTHOR_ALIAS, 990_100_012, LinkTypes::AUTHOR_PICTURE);

        // An alias inherits its author's country, and several credits from that country still describe one work.
        $after = $this->countryCount($this->dbal->pictureCountryDistribution());
        self::assertSame($before + 2, $after);
    }

    public function testMusicCountryCountsAliasWorks(): void
    {
        $before = $this->countryCount($this->dbal->musicCountryDistribution());
        $this->work('module_zxmusic', 990_100_021);
        $this->link(self::AUTHOR_ALIAS, 990_100_021, LinkTypes::AUTHOR_MUSIC);

        $after = $this->countryCount($this->dbal->musicCountryDistribution());
        self::assertSame($before + 1, $after);
    }

    public function testProdCountryCountsAuthorAndDeveloperGroupAliasesAndCountsEachWorkOnce(): void
    {
        $before = $this->countryCount($this->dbal->prodCountryDistribution());
        foreach (range(990_100_031, 990_100_035) as $workId) {
            $this->work('module_zxprod', $workId);
        }
        $this->credit(self::AUTHOR, 990_100_031);
        $this->credit(self::AUTHOR_ALIAS, 990_100_032);
        $this->link(self::GROUP, 990_100_033, LinkTypes::ZX_PROD_GROUPS);
        $this->link(self::GROUP_ALIAS, 990_100_034, LinkTypes::ZX_PROD_GROUPS);
        $this->credit(self::AUTHOR, 990_100_035);
        $this->credit(self::AUTHOR_ALIAS, 990_100_035);
        $this->link(self::GROUP, 990_100_035, LinkTypes::ZX_PROD_GROUPS);
        $this->link(self::GROUP_ALIAS, 990_100_035, LinkTypes::ZX_PROD_GROUPS);

        // A production belongs to a country once, however many local authors, aliases, or developer groups credit it.
        $after = $this->countryCount($this->dbal->prodCountryDistribution());
        self::assertSame($before + 5, $after);
    }

    /**
     * @param array<int, array<string, int>> $distribution
     */
    private function countryCount(array $distribution): int
    {
        return $distribution[self::YEAR][$this->countryTitle] ?? 0;
    }

    /**
     * @param array<string, int|string> $changes
     */
    private function cloneRow(string $table, int $id, array $changes): void
    {
        $row = $this->db->table($table)->first();
        self::assertIsArray($row, $table . ' must contain a fixture row');
        $row['id'] = $id;
        $this->db->table($table)->insert(array_replace($row, $changes));
    }

    private function work(string $table, int $id): void
    {
        $this->cloneRow($table, $id, ['year' => self::YEAR]);
    }

    private function link(int $parentId, int $childId, LinkTypes $type): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $parentId,
            'childStructureId' => $childId,
            'type' => $type->value,
        ]);
    }

    private function credit(int $authorId, int $workId): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => $workId,
            'type' => EntityType::Prod->value,
            'roles' => '[]',
        ]);
    }
}
