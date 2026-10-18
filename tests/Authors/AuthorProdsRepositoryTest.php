<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Repositories\AuthorProdsRepository;

/**
 * A production is reachable on the site only through a category; one without a
 * category (and every release under it) has no page. The author's list must
 * leave such items out in the database, so a page holds as many items as asked
 * for and the total matches what can be shown.
 */
class AuthorProdsRepositoryTest extends TestCase
{
    private const int AUTHOR_ID = 100;
    private const int CATEGORY_ID = 900;
    private const int FOREIGN_PROD_ID = 800;
    private const int ORPHAN_FOREIGN_PROD_ID = 801;

    private SQLiteConnection $db;
    private AuthorProdsRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->createSchema();
        $this->repository = new AuthorProdsRepository($this->db);
    }

    public function testProdWithoutCategoryIsNotListed(): void
    {
        $this->addProd(1, 2020, true);
        $this->addProd(2, 2021, false);

        $page = $this->repository->findPagedByAuthorId(self::AUTHOR_ID, 0, 10, 'year', 'desc', '');

        $this->assertSame([1], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testPageIsFilledWithReachableProdsOnly(): void
    {
        $this->addProd(1, 2023, false);
        $this->addProd(2, 2022, true);
        $this->addProd(3, 2021, true);
        $this->addProd(4, 2020, false);
        $this->addProd(5, 2019, true);

        $page = $this->repository->findPagedByAuthorId(self::AUTHOR_ID, 0, 2, 'year', 'desc', '');

        $this->assertSame([2, 3], array_column($page['items'], 'id'));
        $this->assertSame(3, $page['total']);
    }

    public function testReleaseOfProdWithoutCategoryIsNotListed(): void
    {
        $this->addForeignProd(self::FOREIGN_PROD_ID, true);
        $this->addForeignProd(self::ORPHAN_FOREIGN_PROD_ID, false);
        $this->addRelease(10, self::FOREIGN_PROD_ID, 2020);
        $this->addRelease(11, self::ORPHAN_FOREIGN_PROD_ID, 2021);

        $page = $this->repository->findPagedByAuthorId(self::AUTHOR_ID, 0, 10, 'year', 'desc', '');

        $this->assertSame([10], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testProdWithoutCategoryIsNotCounted(): void
    {
        $this->addProd(1, 2020, true);
        $this->addProd(2, 2021, false);

        $this->assertSame(1, $this->repository->countByAuthorId(self::AUTHOR_ID));
    }

    private function addProd(int $id, int $year, bool $categorized): void
    {
        $this->addForeignProd($id, $categorized, $year);
        $this->db->table('authorship')->insert([
            'elementId' => $id,
            'authorId' => self::AUTHOR_ID,
            'type' => 'prod',
            'roles' => '["code"]',
        ]);
    }

    private function addForeignProd(int $id, bool $categorized, int $year = 2000): void
    {
        $this->db->table('module_zxprod')->insert(['id' => $id, 'votes' => 0, 'year' => $year]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        if ($categorized) {
            $this->db->table('structure_links')->insert([
                'parentStructureId' => self::CATEGORY_ID,
                'childStructureId' => $id,
                'type' => 'zxProdCategory',
            ]);
        }
    }

    private function addRelease(int $id, int $prodId, int $year): void
    {
        $this->db->table('module_zxrelease')->insert([
            'id' => $id,
            'votes' => 0,
            'year' => $year,
            'downloads' => 0,
            'plays' => 0,
        ]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $prodId,
            'childStructureId' => $id,
            'type' => 'structure',
        ]);
        $this->db->table('authorship')->insert([
            'elementId' => $id,
            'authorId' => self::AUTHOR_ID,
            'type' => 'release',
            'roles' => '["release"]',
        ]);
    }

    private function createSchema(): void
    {
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER PRIMARY KEY, votes REAL, year INTEGER)');
        $pdo->exec('CREATE TABLE module_zxrelease (id INTEGER PRIMARY KEY, votes REAL, year INTEGER, downloads INTEGER, plays INTEGER)');
        $pdo->exec('CREATE TABLE structure_elements (id INTEGER PRIMARY KEY, dateCreated INTEGER)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $pdo->exec('CREATE TABLE authorship (elementId INTEGER, authorId INTEGER, type TEXT, roles TEXT)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER PRIMARY KEY, authorId INTEGER)');
    }
}
