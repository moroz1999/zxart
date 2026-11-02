<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Parties;

use controller;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ZxArt\Parties\Dbal\PartySearchDbal;
use ZxArt\Parties\Dto\FoundPartyDto;

/**
 * Parties by a part of the title, abbreviation or original name, of a year;
 * the parties are written by the test, each test is rolled back.
 */
final class PartySearchDbalTest extends TestCase
{
    private const int YEAR_2093 = 990_000_001;
    private const int YEAR_2094 = 990_000_002;
    private const int FEST_2093 = 990_000_011;
    private const int FEST_2094 = 990_000_012;
    private const int RIVAL = 990_000_013;

    private Connection $db;
    private PartySearchDbal $dbal;

    #[\Override]
    protected function setUp(): void
    {
        $container = controller::getInstance()->getContainer();
        $this->db = $container->get(Connection::class);
        $this->dbal = $container->get(PartySearchDbal::class);
        $this->db->beginTransaction();
        $this->year(self::YEAR_2093, '2093');
        $this->year(self::YEAR_2094, '2094');
        $this->party(self::FEST_2093, self::YEAR_2093, 'Zqxparty Fest 2093', 'ZQF&#039;93', 'Зкспати');
        $this->party(self::FEST_2094, self::YEAR_2094, 'Zqxparty Fest 2094', 'ZQF&#039;94', '');
        $this->party(self::RIVAL, self::YEAR_2094, 'Zqx_rival 2094', 'ZQR', '');
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testFindsByAPartOfTheTitleInTitleOrder(): void
    {
        self::assertEquals([
            new FoundPartyDto(self::FEST_2093, 'Zqxparty Fest 2093', "ZQF'93", 2093),
            new FoundPartyDto(self::FEST_2094, 'Zqxparty Fest 2094', "ZQF'94", 2094),
        ], $this->dbal->find('zqxparty fest', null, 20));
    }

    public function testFindsByTheAbbreviationOrTheOriginalName(): void
    {
        self::assertSame([self::FEST_2094], $this->ids($this->dbal->find("ZQF'94", null, 20)));
        self::assertSame([self::FEST_2093], $this->ids($this->dbal->find('зкспати', null, 20)));
    }

    public function testAYearNarrowsToThePartiesOfThatYear(): void
    {
        self::assertSame([self::FEST_2094, self::RIVAL], $this->ids($this->dbal->find('zqx', 2094, 20)));
    }

    public function testAWildcardInThePhraseIsTakenLiterally(): void
    {
        self::assertSame([self::RIVAL], $this->ids($this->dbal->find('zqx_', null, 20)));
    }

    public function testTheLimitCapsTheList(): void
    {
        self::assertSame([self::FEST_2093], $this->ids($this->dbal->find('zqx', null, 1)));
    }

    public function testAnEmptyPhraseFindsNothing(): void
    {
        self::assertSame([], $this->dbal->find('  ', 2094, 20));
    }

    /**
     * @param list<FoundPartyDto> $parties
     * @return list<int>
     */
    private function ids(array $parties): array
    {
        return array_map(static fn(FoundPartyDto $party): int => $party->id, $parties);
    }

    private function year(int $id, string $title): void
    {
        $this->db->table('module_generic')->insert(['id' => $id, 'title' => $title, 'languageId' => 0]);
    }

    private function party(int $id, int $yearId, string $title, string $abbreviation, string $originalName): void
    {
        $this->db->table('module_party')->insert([
            'id' => $id, 'title' => $title, 'abbreviation' => $abbreviation, 'originalName' => $originalName,
            'languageId' => 0, 'country' => 0, 'city' => 0, 'displayInMusic' => 0, 'displayInGraphics' => 0,
            'picturesQuantity' => 0, 'tunesQuantity' => 0, 'image' => '', 'website' => '',
        ]);
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $yearId, 'childStructureId' => $id, 'type' => 'structure', 'position' => 0,
        ]);
    }
}
