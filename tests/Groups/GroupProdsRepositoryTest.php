<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Groups\GroupProdsScope;
use ZxArt\Groups\Repositories\GroupProdsRepository;

/**
 * A production is reachable on the site only through a category; one without a
 * category (and every release under it) has no page. The group's lists must
 * leave such items out in the database, so a page holds as many items as asked
 * for and the total matches what can be shown.
 */
class GroupProdsRepositoryTest extends TestCase
{
    private const int GROUP_ID = 100;
    private const int CATEGORY_ID = 900;

    private SQLiteConnection $db;
    private GroupProdsRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->createSchema();
        $this->repository = new GroupProdsRepository($this->db);
    }

    public function testOwnProdWithoutCategoryIsNotListed(): void
    {
        $this->addProd(1, true, 'zxProdGroups');
        $this->addProd(2, false, 'zxProdGroups');

        $page = $this->repository->findPaged(self::GROUP_ID, GroupProdsScope::Own, 0, 10, 'year', 'desc', '', []);

        $this->assertSame([1], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testPublishedProdWithoutCategoryIsNotListed(): void
    {
        $this->addProd(1, true, 'zxProdPublishers');
        $this->addProd(2, false, 'zxProdPublishers');

        $page = $this->repository->findPaged(self::GROUP_ID, GroupProdsScope::Published, 0, 10, 'year', 'desc', '', []);

        $this->assertSame([1], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testHackerReleaseOfProdWithoutCategoryIsNotListed(): void
    {
        $this->addProd(1, true, null);
        $this->addProd(2, false, null);
        $this->addRelease(10, 1);
        $this->addRelease(11, 2);

        $page = $this->repository->findPaged(self::GROUP_ID, GroupProdsScope::Releases, 0, 10, 'year', 'desc', '', []);

        $this->assertSame([10], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testAllScopeLeavesOutUnreachableItems(): void
    {
        $this->addProd(1, true, 'zxProdGroups');
        $this->addProd(2, false, 'zxProdGroups');
        $this->addProd(3, false, 'zxProdPublishers');
        $this->addProd(4, false, null);
        $this->addRelease(10, 4);

        $page = $this->repository->findPaged(self::GROUP_ID, GroupProdsScope::All, 0, 10, 'year', 'desc', '', []);

        $this->assertSame([1], array_column($page['items'], 'id'));
        $this->assertSame(1, $page['total']);
    }

    public function testOwnProdCountLeavesOutProdWithoutCategory(): void
    {
        $this->addProd(1, true, 'zxProdGroups');
        $this->addProd(2, false, 'zxProdGroups');

        $this->assertSame(1, $this->repository->count(self::GROUP_ID, GroupProdsScope::Own));
    }

    private function addProd(int $id, bool $categorized, ?string $groupLinkType): void
    {
        $this->db->table('module_zxprod')->insert(['id' => $id, 'votes' => 0, 'year' => 2000 + $id]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        if ($categorized) {
            $this->addLink(self::CATEGORY_ID, $id, 'zxProdCategory');
        }
        if ($groupLinkType !== null) {
            $this->addLink(self::GROUP_ID, $id, $groupLinkType);
        }
    }

    private function addRelease(int $id, int $prodId): void
    {
        $this->db->table('module_zxrelease')->insert([
            'id' => $id,
            'votes' => 0,
            'year' => 2000 + $id,
            'releaseType' => 'crack',
        ]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        $this->addLink($prodId, $id, 'structure');
        $this->addLink(self::GROUP_ID, $id, 'zxReleasePublishers');
    }

    private function addLink(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $parentId,
            'childStructureId' => $childId,
            'type' => $type,
        ]);
    }

    private function createSchema(): void
    {
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER PRIMARY KEY, votes REAL, year INTEGER)');
        $pdo->exec('CREATE TABLE module_zxrelease (id INTEGER PRIMARY KEY, votes REAL, year INTEGER, releaseType TEXT)');
        $pdo->exec('CREATE TABLE structure_elements (id INTEGER PRIMARY KEY, dateCreated INTEGER)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER PRIMARY KEY, groupId INTEGER)');
    }
}
