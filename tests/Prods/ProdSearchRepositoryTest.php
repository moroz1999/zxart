<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dbal\ProdSearchDbal;

/**
 * Narrows the productions the catalogue filters left by title, years, author
 * and group, and pages them by title.
 */
class ProdSearchRepositoryTest extends TestCase
{
    private const int ELITE = 1;
    private const int EXOLON = 2;
    private const int STAR_WARS = 3;
    private const int AUTHOR = 10;
    private const int AUTHOR_ALIAS = 11;
    private const int GROUP = 20;
    private const int GROUP_ALIAS = 21;

    private SQLiteConnection $db;
    private ProdSearchDbal $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER, title TEXT, altTitle TEXT, year INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, groupId INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->db->table('module_zxprod')->insert([
            ['id' => self::ELITE, 'title' => 'Elite', 'altTitle' => '', 'year' => 1985],
            ['id' => self::EXOLON, 'title' => 'Exolon', 'altTitle' => 'Exolon Plus', 'year' => 1987],
            ['id' => self::STAR_WARS, 'title' => 'Star Wars', 'altTitle' => 'Звёздные войны', 'year' => 1987],
        ]);
        $this->db->table('module_authoralias')->insert(['id' => self::AUTHOR_ALIAS, 'authorId' => self::AUTHOR]);
        $this->db->table('module_groupalias')->insert(['id' => self::GROUP_ALIAS, 'groupId' => self::GROUP]);
        $this->repository = new ProdSearchDbal($this->db);
    }

    public function testTheTitleMatchesTheTitleOrTheAlternativeTitle(): void
    {
        self::assertSame([self::STAR_WARS], $this->search(title: 'войны'));
        self::assertSame([self::EXOLON], $this->search(title: 'plus'));
    }

    public function testTheYearsBoundTheRangeOnEitherSide(): void
    {
        self::assertSame([self::EXOLON, self::STAR_WARS], $this->search(yearFrom: 1986));
        self::assertSame([self::ELITE], $this->search(yearTo: 1986));
        self::assertSame([self::ELITE, self::EXOLON, self::STAR_WARS], $this->search(yearFrom: 1985, yearTo: 1987));
    }

    public function testTheAuthorFindsTheProductionsOfItsAliasesToo(): void
    {
        $this->db->table('authorship')->insert([
            ['authorId' => self::AUTHOR, 'elementId' => self::ELITE, 'type' => 'prod'],
            ['authorId' => self::AUTHOR_ALIAS, 'elementId' => self::EXOLON, 'type' => 'prod'],
            ['authorId' => self::AUTHOR, 'elementId' => self::STAR_WARS, 'type' => 'release'],
        ]);

        self::assertSame([self::ELITE, self::EXOLON], $this->search(authorId: self::AUTHOR));
    }

    public function testTheGroupFindsWhatItOrItsAliasesMadeOrPublished(): void
    {
        $this->db->table('structure_links')->insert([
            ['parentStructureId' => self::GROUP, 'childStructureId' => self::ELITE, 'type' => 'zxProdGroups'],
            ['parentStructureId' => self::GROUP_ALIAS, 'childStructureId' => self::STAR_WARS, 'type' => 'zxProdPublishers'],
            ['parentStructureId' => self::GROUP, 'childStructureId' => self::EXOLON, 'type' => 'tagLink'],
        ]);

        self::assertSame([self::ELITE, self::STAR_WARS], $this->search(groupId: self::GROUP));
    }

    public function testOnlyWhatTheCatalogueLeftIsSearchedAndThePageHolds(): void
    {
        $catalogue = $this->db->table('module_zxprod')->select('id')->where('year', '=', 1987);

        self::assertSame([self::STAR_WARS], $this->repository->findIds($catalogue, null, null, null, null, null, 1, 1));
        self::assertSame(2, $this->repository->count($catalogue, null, null, null, null, null));
    }

    /**
     * @return int[]
     */
    private function search(
        ?string $title = null,
        ?int $yearFrom = null,
        ?int $yearTo = null,
        ?int $authorId = null,
        ?int $groupId = null,
    ): array {
        $catalogue = $this->db->table('module_zxprod')->select('id');

        return $this->repository->findIds($catalogue, $title, $yearFrom, $yearTo, $authorId, $groupId, 0, 10);
    }
}
