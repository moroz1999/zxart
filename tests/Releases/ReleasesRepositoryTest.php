<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Releases\Repositories\ReleasesRepository;

/**
 * A release is reachable only through its production, which needs a category.
 * The latest-added selection leaves the others out in the database, so a block
 * shows as many releases as it asks for.
 */
class ReleasesRepositoryTest extends TestCase
{
    private const int CATEGORY_ID = 900;
    private const int PROD_ID = 800;
    private const int ORPHAN_PROD_ID = 801;

    private SQLiteConnection $db;
    private ReleasesRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxrelease (id INTEGER PRIMARY KEY)');
        $pdo->exec('CREATE TABLE structure_elements (id INTEGER PRIMARY KEY, dateCreated INTEGER)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->addLink(self::CATEGORY_ID, self::PROD_ID, 'zxProdCategory');
        $this->repository = new ReleasesRepository($this->db);
    }

    public function testLatestAddedLeavesOutReleaseOfProdWithoutCategory(): void
    {
        $this->addRelease(1, self::PROD_ID);
        $this->addRelease(2, self::ORPHAN_PROD_ID);
        $this->addRelease(3, self::PROD_ID);

        $this->assertSame([3, 1], $this->repository->getLatestAddedIds(2));
    }

    private function addRelease(int $id, int $prodId): void
    {
        $this->db->table('module_zxrelease')->insert(['id' => $id]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        $this->addLink($prodId, $id, 'structure');
    }

    private function addLink(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $parentId,
            'childStructureId' => $childId,
            'type' => $type,
        ]);
    }
}
