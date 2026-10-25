<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dbal\AuthorSearchDbal;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Prods\ProdLink;

/**
 * Authors by a part of the nickname, of the real name in any language or of an
 * alias, by location, by group and by the software they are credited on;
 * each author once, never an alias.
 */
class AuthorSearchDbalTest extends TestCase
{
    private const int ALEX = 1;
    private const int BORIS = 2;
    private const int CLAUDE = 3;
    private const int ALEX_ALIAS = 11;
    private const int TALLINN = 100;
    private const int TARTU = 102;
    private const int ESTONIA = 101;
    private const int GROUP = 200;
    private const int GROUP_ALIAS = 201;
    private const int OTHER_GROUP = 202;
    private const int ELITE = 300;
    private const int EXOLON = 301;
    private const int STAR_WARS = 302;

    private SQLiteConnection $db;
    private AuthorSearchDbal $dbal;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_author (id INTEGER, languageId INTEGER, title TEXT, realName TEXT, country INTEGER, city INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, title TEXT, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, title TEXT, groupId INTEGER)');
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER, year INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT, roles TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->addAuthor(self::ALEX, 'Alex', ['Alexander Ivanov', 'Александр Иванов'], self::ESTONIA, self::TALLINN);
        $this->addAuthor(self::BORIS, 'Boris', ['Boris Petrov', 'Борис Петров'], self::ESTONIA, self::TARTU);
        $this->addAuthor(self::CLAUDE, 'Claude', ['', ''], 0, 0);
        $this->db->table('module_authoralias')->insert(['id' => self::ALEX_ALIAS, 'title' => 'Zorro', 'authorId' => self::ALEX]);
        $this->db->table('module_groupalias')->insert(['id' => self::GROUP_ALIAS, 'title' => 'Crew', 'groupId' => self::GROUP]);
        $this->db->table('module_zxprod')->insert([
            ['id' => self::ELITE, 'year' => 1985],
            ['id' => self::EXOLON, 'year' => 1987],
            ['id' => self::STAR_WARS, 'year' => 0],
        ]);
        $this->dbal = new AuthorSearchDbal($this->db);
    }

    public function testANameMatchesTheNicknameTheRealNameInAnyLanguageOrAnAlias(): void
    {
        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(name: ' Иванов ')));
        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(name: 'orr')));
        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(name: 'o')));
        self::assertSame(2, $this->dbal->count(new AuthorSearchFilterDto(name: 'o'), null));
    }

    public function testTheCountriesAndCitiesNarrowTheResult(): void
    {
        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(countryIds: [self::ESTONIA])));
        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(cityIds: [self::TALLINN, self::TARTU])));
        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(cityIds: [self::TALLINN])));
    }

    public function testGroupsFindTheirMembersThroughEitherSidesAliases(): void
    {
        $this->addMembership(self::ALEX_ALIAS, self::GROUP);
        $this->addMembership(self::BORIS, self::GROUP_ALIAS);
        $this->addMembership(self::CLAUDE, self::OTHER_GROUP);

        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(groupIds: [self::GROUP])));
        self::assertSame(
            [self::ALEX, self::BORIS, self::CLAUDE],
            $this->search(new AuthorSearchFilterDto(groupIds: [self::GROUP, self::OTHER_GROUP])),
        );
    }

    public function testTheActiveYearsAreTheYearsOfTheSoftwareTheyAreCreditedOn(): void
    {
        $this->credit(self::ALEX_ALIAS, self::ELITE);
        $this->credit(self::BORIS, self::EXOLON);
        $this->credit(self::CLAUDE, self::STAR_WARS);

        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(activeTo: 1986)));
        self::assertSame([self::BORIS], $this->search(new AuthorSearchFilterDto(activeFrom: 1986)));
        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(activeFrom: 1985, activeTo: 1987)));
    }

    public function testTheSoftwareFindsItsAuthorsInTheRolesGiven(): void
    {
        $this->credit(self::ALEX_ALIAS, self::ELITE, ['code']);
        $this->credit(self::BORIS, self::ELITE, ['music']);
        $this->credit(self::CLAUDE, self::EXOLON, ['music']);

        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(prodIds: [self::ELITE])));
        self::assertSame([self::BORIS], $this->search(new AuthorSearchFilterDto(prodIds: [self::ELITE], prodRoles: ['music'])));
        self::assertSame([self::BORIS, self::CLAUDE], $this->search(new AuthorSearchFilterDto(prodRoles: ['music'])));
    }

    public function testTheFoundSoftwareOfANestedSearchFindsItsAuthors(): void
    {
        $this->credit(self::ALEX, self::ELITE, ['code']);
        $this->credit(self::BORIS, self::EXOLON, ['music']);
        $this->credit(self::CLAUDE, self::EXOLON, ['code']);
        $found = $this->db->table('module_zxprod')->select('id')->where('year', '=', 1987);

        self::assertSame([self::BORIS, self::CLAUDE], $this->dbal->findIds(new AuthorSearchFilterDto(), $found, 0, 10));
        self::assertSame([self::CLAUDE], $this->dbal->findIds(new AuthorSearchFilterDto(prodRoles: ['code']), $found, 0, 10));
        self::assertSame(1, $this->dbal->count(new AuthorSearchFilterDto(prodRoles: ['code']), $found));
    }

    public function testAPublisherIsLinkedToTheSoftwareItPublished(): void
    {
        $this->credit(self::ALEX, self::ELITE, ['code']);
        $this->publish(self::BORIS, self::ELITE);
        $this->publish(self::GROUP, self::ELITE);
        $this->publish(self::ALEX_ALIAS, self::EXOLON);
        $this->credit(self::CLAUDE, self::EXOLON, ['music']);

        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(prodIds: [self::ELITE])));
        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(prodIds: [self::ELITE], prodLink: ProdLink::Made)));
        self::assertSame([self::BORIS], $this->search(new AuthorSearchFilterDto(prodIds: [self::ELITE], prodLink: ProdLink::Published)));
        self::assertSame([self::ALEX, self::BORIS], $this->search(new AuthorSearchFilterDto(prodLink: ProdLink::Published)));
        // a publisher holds no role
        self::assertSame([self::CLAUDE], $this->search(new AuthorSearchFilterDto(prodIds: [self::EXOLON], prodRoles: ['music'])));
    }

    public function testThePublishedSoftwareCountsForTheNestedSearchAndTheActiveYears(): void
    {
        $this->publish(self::ALEX_ALIAS, self::EXOLON);
        $this->credit(self::CLAUDE, self::EXOLON, ['music']);
        $this->publish(self::BORIS, self::ELITE);
        $found = $this->db->table('module_zxprod')->select('id')->where('year', '=', 1987);

        self::assertSame([self::ALEX], $this->dbal->findIds(new AuthorSearchFilterDto(prodLink: ProdLink::Published), $found, 0, 10));
        self::assertSame([self::ALEX, self::CLAUDE], $this->dbal->findIds(new AuthorSearchFilterDto(), $found, 0, 10));
        self::assertSame([self::ALEX, self::CLAUDE], $this->search(new AuthorSearchFilterDto(activeFrom: 1986)));
    }

    public function testACoAuthorFindsWhoSharedSoftwareWithIt(): void
    {
        $this->credit(self::ALEX_ALIAS, self::ELITE);
        $this->credit(self::BORIS, self::ELITE);
        $this->credit(self::CLAUDE, self::EXOLON);

        self::assertSame([self::BORIS], $this->search(new AuthorSearchFilterDto(coAuthorId: self::ALEX)));
        self::assertSame([self::ALEX], $this->search(new AuthorSearchFilterDto(coAuthorId: self::BORIS)));
    }

    public function testThePageIsOrderedByNickname(): void
    {
        self::assertSame([self::BORIS], $this->dbal->findIds(new AuthorSearchFilterDto(), null, 1, 1));
        self::assertSame(3, $this->dbal->count(new AuthorSearchFilterDto(), null));
    }

    /**
     * @return int[]
     */
    private function search(AuthorSearchFilterDto $filter): array
    {
        return $this->dbal->findIds($filter, null, 0, 10);
    }

    /**
     * @param array{string, string} $realNames one per language
     */
    private function addAuthor(int $id, string $title, array $realNames, int $countryId, int $cityId): void
    {
        foreach ($realNames as $languageId => $realName) {
            $this->db->table('module_author')->insert([
                'id' => $id,
                'languageId' => $languageId + 1,
                'title' => $title,
                'realName' => $realName,
                'country' => $countryId,
                'city' => $cityId,
            ]);
        }
    }

    private function addMembership(int $authorId, int $groupId): void
    {
        $this->db->table('authorship')->insert(['authorId' => $authorId, 'elementId' => $groupId, 'type' => 'group', 'roles' => '[]']);
    }

    /**
     * @param string[] $roles
     */
    private function publish(int $publisherId, int $prodId): void
    {
        $this->db->table('structure_links')->insert(['parentStructureId' => $publisherId, 'childStructureId' => $prodId, 'type' => 'zxProdPublishers']);
    }

    private function credit(int $authorId, int $prodId, array $roles = ['unknown']): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => $prodId,
            'type' => 'prod',
            'roles' => json_encode($roles, JSON_THROW_ON_ERROR),
        ]);
    }
}
