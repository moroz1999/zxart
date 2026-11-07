<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dbal\ProdSearchDbal;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\ProdLink;
use ZxArt\Search\Dbal\TitleMatch;

/**
 * Narrows the productions the catalogue filters left by title, dates, people,
 * places, parties and series, and pages them by title.
 */
class ProdSearchDbalTest extends TestCase
{
    private const int ELITE = 1;
    private const int EXOLON = 2;
    private const int STAR_WARS = 3;
    private const int UNDATED = 4;
    private const int AUTHOR = 10;
    private const int AUTHOR_ALIAS = 11;
    private const int OTHER_AUTHOR = 12;
    private const int GROUP = 20;
    private const int GROUP_ALIAS = 21;
    private const int OTHER_GROUP = 22;
    private const int RELEASE_OF_ELITE = 30;
    private const int RELEASE_OF_EXOLON = 31;
    private const int RELEASE_OF_UNDATED = 32;
    private const int PARTY = 40;
    private const int ESTONIA = 50;
    private const int TALLINN = 51;

    private SQLiteConnection $db;
    private ProdSearchDbal $dbal;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER, title TEXT, altTitle TEXT, year INTEGER, party INTEGER, partyplace INTEGER, compo TEXT, dateAdded INTEGER)');
        $pdo->exec('CREATE TABLE module_zxrelease (id INTEGER, year INTEGER)');
        $pdo->exec('CREATE TABLE module_author (id INTEGER, country INTEGER, city INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_group (id INTEGER, country INTEGER, city INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, groupId INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT, roles TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->db->table('module_zxprod')->insert([
            $this->prod(self::ELITE, 'Elite', '', 1985, dateAdded: 1_000_000, party: self::PARTY, partyPlace: 1, compo: 'game'),
            $this->prod(self::EXOLON, 'Exolon', 'Exolon Plus', 1987, dateAdded: 2_000_000, party: self::PARTY, partyPlace: 3, compo: 'game'),
            $this->prod(self::STAR_WARS, 'Star Wars', 'Звёздные войны', 1987, dateAdded: 3_000_000),
        ]);
        $this->db->table('module_zxrelease')->insert([
            ['id' => self::RELEASE_OF_ELITE, 'year' => 1990],
            ['id' => self::RELEASE_OF_EXOLON, 'year' => 1987],
        ]);
        $this->db->table('module_author')->insert([
            ['id' => self::AUTHOR, 'country' => self::ESTONIA, 'city' => self::TALLINN],
            ['id' => self::OTHER_AUTHOR, 'country' => 0, 'city' => 0],
        ]);
        $this->db->table('module_authoralias')->insert(['id' => self::AUTHOR_ALIAS, 'authorId' => self::AUTHOR]);
        $this->db->table('module_group')->insert([
            ['id' => self::GROUP, 'country' => self::ESTONIA, 'city' => self::TALLINN],
            ['id' => self::OTHER_GROUP, 'country' => 0, 'city' => 0],
        ]);
        $this->db->table('module_groupalias')->insert(['id' => self::GROUP_ALIAS, 'groupId' => self::GROUP]);
        $this->link(self::ELITE, self::RELEASE_OF_ELITE, 'structure');
        $this->link(self::EXOLON, self::RELEASE_OF_EXOLON, 'structure');
        $this->dbal = new ProdSearchDbal($this->db, new TitleMatch());
    }


    public function testTheYearsBoundTheRangeOnEitherSide(): void
    {
        self::assertSame([self::EXOLON, self::STAR_WARS], $this->search(new ProdSearchFilterDto(yearFrom: 1986)));
        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(yearTo: 1986)));
    }

    public function testTheYearsLeaveOutAnUnknownYear(): void
    {
        $this->db->table('module_zxprod')->insert($this->prod(self::UNDATED, 'Undated', '', 0, dateAdded: 4_000_000));
        $this->db->table('module_zxrelease')->insert(['id' => self::RELEASE_OF_UNDATED, 'year' => 0]);
        $this->link(self::UNDATED, self::RELEASE_OF_UNDATED, 'structure');

        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(yearTo: 1986)));
        self::assertSame([self::EXOLON], $this->search(new ProdSearchFilterDto(releaseYearTo: 1987)));
    }

    public function testTheReleaseYearsHoldForAnyReleaseOfTheProduction(): void
    {
        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(releaseYearFrom: 1988)));
        self::assertSame([self::EXOLON], $this->search(new ProdSearchFilterDto(releaseYearFrom: 1987, releaseYearTo: 1987)));
    }

    public function testTheAddedDatesBoundWhenTheProductionWasAddedToTheSite(): void
    {
        self::assertSame([self::EXOLON, self::STAR_WARS], $this->search(new ProdSearchFilterDto(addedFrom: 2_000_000)));
        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(addedTo: 2_000_000)));
    }

    public function testAnAuthorFindsTheProductionsOfItsAliasesToo(): void
    {
        $this->credit(self::AUTHOR, self::ELITE);
        $this->credit(self::AUTHOR_ALIAS, self::EXOLON);
        $this->credit(self::AUTHOR, self::STAR_WARS, 'release');

        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(authorIds: [self::AUTHOR])));
    }

    public function testSeveralAuthorsFindWhatTheyMadeTogetherOrWithTheFlagWhatAnyOfThemMade(): void
    {
        $this->credit(self::AUTHOR, self::ELITE);
        $this->credit(self::OTHER_AUTHOR, self::ELITE);
        $this->credit(self::AUTHOR_ALIAS, self::EXOLON);
        $this->credit(self::OTHER_AUTHOR, self::STAR_WARS);
        $authors = [self::AUTHOR, self::OTHER_AUTHOR];

        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(authorIds: $authors)));
        self::assertSame(
            [self::ELITE, self::EXOLON, self::STAR_WARS],
            $this->search(new ProdSearchFilterDto(authorIds: $authors, anyAuthor: true)),
        );
    }

    public function testTheRolesNarrowTheCreditsOfTheAuthorsOrOfAnyone(): void
    {
        $this->credit(self::AUTHOR, self::ELITE, roles: ['code', 'music']);
        $this->credit(self::AUTHOR, self::EXOLON, roles: ['graphics']);
        $this->credit(self::OTHER_AUTHOR, self::STAR_WARS, roles: ['music']);

        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(authorIds: [self::AUTHOR], authorRoles: ['music'])));
        self::assertSame([self::ELITE, self::STAR_WARS], $this->search(new ProdSearchFilterDto(authorRoles: ['music'])));
    }

    public function testAGroupFindsWhatItOrItsAliasesMadeOrPublishedOrOneOfTheTwo(): void
    {
        $this->link(self::GROUP, self::ELITE, 'zxProdGroups');
        $this->link(self::GROUP_ALIAS, self::STAR_WARS, 'zxProdPublishers');
        $this->link(self::OTHER_GROUP, self::EXOLON, 'zxProdGroups');
        $this->link(self::GROUP, self::EXOLON, 'tagLink');

        self::assertSame([self::ELITE, self::STAR_WARS], $this->search(new ProdSearchFilterDto(groupIds: [self::GROUP])));
        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(groupIds: [self::GROUP], groupLink: ProdLink::Made)));
        self::assertSame([self::STAR_WARS], $this->search(new ProdSearchFilterDto(groupIds: [self::GROUP], groupLink: ProdLink::Published)));
        self::assertSame(
            [self::ELITE, self::EXOLON, self::STAR_WARS],
            $this->search(new ProdSearchFilterDto(groupIds: [self::GROUP, self::OTHER_GROUP])),
        );
    }

    public function testAPublisherIsAnAuthorOrAGroupWithTheirAliases(): void
    {
        $this->link(self::AUTHOR_ALIAS, self::ELITE, 'zxProdPublishers');
        $this->link(self::GROUP, self::STAR_WARS, 'zxProdPublishers');
        $this->link(self::OTHER_GROUP, self::EXOLON, 'zxProdGroups');

        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(publisherIds: [self::AUTHOR])));
        self::assertSame([self::ELITE, self::STAR_WARS], $this->search(new ProdSearchFilterDto(publisherIds: [self::AUTHOR, self::GROUP])));
        self::assertSame([], $this->search(new ProdSearchFilterDto(publisherIds: [self::OTHER_GROUP])));
    }

    public function testThePlaceIsWhereAnAuthorOrADeveloperGroupComesFrom(): void
    {
        $this->credit(self::AUTHOR_ALIAS, self::ELITE);
        $this->link(self::GROUP, self::EXOLON, 'zxProdGroups');
        $this->link(self::GROUP, self::STAR_WARS, 'zxProdPublishers');

        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(cityIds: [self::TALLINN])));
        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(countryIds: [self::ESTONIA])));
    }

    public function testThePartyTheCompoAndThePlaceAtIt(): void
    {
        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(partyIds: [self::PARTY])));
        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(compo: 'game')));
        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(partyIds: [self::PARTY], maxPartyPlace: 2)));
    }

    public function testTheProductionsOfASeriesAndTheItemsAndHoldersOfACompilation(): void
    {
        $this->link(self::STAR_WARS, self::ELITE, 'series');
        $this->link(self::STAR_WARS, self::EXOLON, 'series');
        $this->link(self::ELITE, self::EXOLON, 'compilation');

        self::assertSame([self::ELITE, self::EXOLON], $this->search(new ProdSearchFilterDto(seriesId: self::STAR_WARS)));
        self::assertSame([self::EXOLON], $this->search(new ProdSearchFilterDto(compilationId: self::ELITE)));
        self::assertSame([self::ELITE], $this->search(new ProdSearchFilterDto(includedProdId: self::EXOLON)));
    }

    public function testOnlyWhatTheCatalogueLeftIsSearchedAndThePageHolds(): void
    {
        $catalogue = $this->db->table('module_zxprod')->select('id')->where('year', '=', 1987);

        self::assertSame([self::STAR_WARS], $this->dbal->findIds($catalogue, new ProdSearchFilterDto(), 1, 1));
        self::assertSame(2, $this->dbal->count($catalogue, new ProdSearchFilterDto()));
    }

    public function testTheIdsQueryCarriesEveryFilterForAnotherSearchToUse(): void
    {
        $this->credit(self::AUTHOR, self::ELITE);
        $catalogue = $this->db->table('module_zxprod')->select('id');

        $ids = $this->dbal->idsQuery($catalogue, new ProdSearchFilterDto(authorIds: [self::AUTHOR]))->pluck('id');

        self::assertSame([self::ELITE], array_map('intval', $ids));
    }

    /**
     * @return int[]
     */
    private function search(ProdSearchFilterDto $filter): array
    {
        return $this->dbal->findIds($this->db->table('module_zxprod')->select('id'), $filter, 0, 10);
    }

    /**
     * @return array<string, int|string>
     */
    private function prod(
        int $id,
        string $title,
        string $altTitle,
        int $year,
        int $dateAdded,
        int $party = 0,
        int $partyPlace = 0,
        string $compo = '',
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'altTitle' => $altTitle,
            'year' => $year,
            'party' => $party,
            'partyplace' => $partyPlace,
            'compo' => $compo,
            'dateAdded' => $dateAdded,
        ];
    }

    /**
     * @param string[] $roles
     */
    private function credit(int $authorId, int $elementId, string $type = 'prod', array $roles = ['unknown']): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => $elementId,
            'type' => $type,
            'roles' => json_encode($roles, JSON_THROW_ON_ERROR),
        ]);
    }

    private function link(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $parentId,
            'childStructureId' => $childId,
            'type' => $type,
        ]);
    }
}
