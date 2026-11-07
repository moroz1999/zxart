<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dbal\GroupSearchDbal;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Prods\ProdLink;
use ZxArt\Search\Dbal\TitleMatch;

/**
 * Groups by a part of the title, of the abbreviation or of an alias, by
 * location, type and dates, by members, by the software they made or
 * published, and by their place among other groups; each group once.
 */
class GroupSearchDbalTest extends TestCase
{
    private const int TRIUMPH = 1;
    private const int SAGE = 2;
    private const int CODE_BUSTERS = 3;
    private const int TRIUMPH_ALIAS = 11;
    private const int TALLINN = 100;
    private const int ESTONIA = 101;
    private const int AUTHOR = 200;
    private const int AUTHOR_ALIAS = 201;
    private const int OTHER_AUTHOR = 202;
    private const int ELITE = 300;
    private const int EXOLON = 301;

    private SQLiteConnection $db;
    private GroupSearchDbal $dbal;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_group (id INTEGER, title TEXT, abbreviation TEXT, country INTEGER, city INTEGER, type TEXT, startDate INTEGER, endDate INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, title TEXT, groupId INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER, year INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->db->table('module_group')->insert([
            $this->group(self::TRIUMPH, 'Triumph', 'TRM', self::ESTONIA, self::TALLINN, 'scene', 631152000, 0),
            $this->group(self::SAGE, 'Sage Group', '', self::ESTONIA, 0, 'company', 473385600, 788918400),
            $this->group(self::CODE_BUSTERS, 'Code Busters', 'CBS', 0, 0, 'scene', 0, 0),
        ]);
        $this->db->table('module_groupalias')->insert(['id' => self::TRIUMPH_ALIAS, 'title' => 'Triumph Brothers', 'groupId' => self::TRIUMPH]);
        $this->db->table('module_authoralias')->insert(['id' => self::AUTHOR_ALIAS, 'authorId' => self::AUTHOR]);
        $this->db->table('module_zxprod')->insert([['id' => self::ELITE, 'year' => 1985], ['id' => self::EXOLON, 'year' => 1987]]);
        $this->dbal = new GroupSearchDbal($this->db, new TitleMatch());
    }


    public function testLocationsAndTypesNarrowTheResult(): void
    {
        self::assertSame([self::SAGE, self::TRIUMPH], $this->search(new GroupSearchFilterDto(countryIds: [self::ESTONIA])));
        self::assertSame([self::TRIUMPH], $this->search(new GroupSearchFilterDto(cityIds: [self::TALLINN])));
        self::assertSame([self::CODE_BUSTERS, self::TRIUMPH], $this->search(new GroupSearchFilterDto(types: ['scene'])));
        self::assertSame(
            [self::CODE_BUSTERS, self::SAGE, self::TRIUMPH],
            $this->search(new GroupSearchFilterDto(types: ['scene', 'company'])),
        );
    }

    public function testTheFoundingAndEndingDatesBoundTheirRanges(): void
    {
        self::assertSame([self::SAGE], $this->search(new GroupSearchFilterDto(foundedTo: 600000000)));
        self::assertSame([self::TRIUMPH], $this->search(new GroupSearchFilterDto(foundedFrom: 600000000)));
        self::assertSame([self::SAGE], $this->search(new GroupSearchFilterDto(endedFrom: 700000000, endedTo: 800000000)));
    }

    public function testMembersFindTheirGroupsThroughEitherSidesAliases(): void
    {
        $this->member(self::AUTHOR_ALIAS, self::TRIUMPH_ALIAS);
        $this->member(self::AUTHOR, self::SAGE);
        $this->member(self::OTHER_AUTHOR, self::CODE_BUSTERS);

        self::assertSame([self::SAGE, self::TRIUMPH], $this->search(new GroupSearchFilterDto(memberIds: [self::AUTHOR])));
    }

    public function testTheSoftwareFindsWhoMadeOrPublishedIt(): void
    {
        $this->link(self::TRIUMPH_ALIAS, self::ELITE, 'zxProdGroups');
        $this->link(self::SAGE, self::ELITE, 'zxProdPublishers');
        $this->link(self::CODE_BUSTERS, self::EXOLON, 'zxProdGroups');

        self::assertSame([self::SAGE, self::TRIUMPH], $this->search(new GroupSearchFilterDto(prodIds: [self::ELITE])));
        self::assertSame([self::TRIUMPH], $this->search(new GroupSearchFilterDto(prodIds: [self::ELITE], prodLink: ProdLink::Made)));
        self::assertSame([self::SAGE], $this->search(new GroupSearchFilterDto(prodLink: ProdLink::Published)));
    }

    public function testTheActiveYearsAreTheYearsOfItsSoftware(): void
    {
        $this->link(self::TRIUMPH, self::ELITE, 'zxProdGroups');
        $this->link(self::CODE_BUSTERS, self::EXOLON, 'zxProdPublishers');

        self::assertSame([self::TRIUMPH], $this->search(new GroupSearchFilterDto(activeTo: 1986)));
        self::assertSame([self::CODE_BUSTERS], $this->search(new GroupSearchFilterDto(activeFrom: 1986)));
    }

    public function testTheFoundSoftwareOfANestedSearchFindsItsGroups(): void
    {
        $this->link(self::TRIUMPH, self::ELITE, 'zxProdGroups');
        $this->link(self::SAGE, self::EXOLON, 'zxProdPublishers');
        $found = $this->db->table('module_zxprod')->select('id')->where('year', '=', 1987);

        self::assertSame([self::SAGE], $this->dbal->findIds(new GroupSearchFilterDto(), $found, 0, 10));
        self::assertSame([], $this->dbal->findIds(new GroupSearchFilterDto(prodLink: ProdLink::Made), $found, 0, 10));
    }

    public function testTheSubgroupsOfAGroupAndTheGroupsAGroupBelongsTo(): void
    {
        $this->link(self::SAGE, self::TRIUMPH, 'groupSub');
        $this->link(self::SAGE, self::CODE_BUSTERS, 'groupSub');

        self::assertSame([self::CODE_BUSTERS, self::TRIUMPH], $this->search(new GroupSearchFilterDto(parentGroupId: self::SAGE)));
        self::assertSame([self::SAGE], $this->search(new GroupSearchFilterDto(subGroupId: self::TRIUMPH)));
    }

    public function testThePageIsOrderedByTitle(): void
    {
        self::assertSame([self::SAGE], $this->dbal->findIds(new GroupSearchFilterDto(), null, 1, 1));
        self::assertSame(3, $this->dbal->count(new GroupSearchFilterDto(), null));
    }

    /**
     * @return int[]
     */
    private function search(GroupSearchFilterDto $filter): array
    {
        return $this->dbal->findIds($filter, null, 0, 10);
    }

    /**
     * @return array<string, int|string>
     */
    private function group(int $id, string $title, string $abbreviation, int $countryId, int $cityId, string $type, int $startDate, int $endDate): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'abbreviation' => $abbreviation,
            'country' => $countryId,
            'city' => $cityId,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function member(int $authorId, int $groupId): void
    {
        $this->db->table('authorship')->insert(['authorId' => $authorId, 'elementId' => $groupId, 'type' => 'group']);
    }

    private function link(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert(['parentStructureId' => $parentId, 'childStructureId' => $childId, 'type' => $type]);
    }
}
